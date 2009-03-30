<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee <typo3-coding@oliverklee.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_t3lib . 'class.t3lib_page.php');

/**
 * Class 'tx_oelib_db' for the 'oelib' extension.
 *
 * This class provides some static database-related functions.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_db {
	/**
	 * @var t3lib_pageSelect page object which we will use to call
	 *                       enableFields on
	 */
	private static $pageForEnableFields = null;

	/**
	 * @var array cached results for the enableFields function
	 */
	private static $enableFieldsCache = array();

	/**
	 * @var array cache for the results of existsTable with the table names
	 *            as keys and the table SHOW STATUS information (in an array)
	 *            as values
	 */
	private static $tableNameCache = array();

	/**
	 * @var array cache for the results of hasTableColumn with the column names
	 *            as keys and the SHOW COLUMNS field information (in an array)
	 *            as values
	 */
	private static $tableColumnCache = array();

	/**
	 * @var array cache for all TCA arrays
	 */
	private static $tcaCache = array();

	/**
	 * Enables query logging in TYPO3's DB class.
	 */
	public static function enableQueryLogging() {
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
	}

	/**
	 * Wrapper function for t3lib_pageSelect::enableFields() since it is no
	 * longer accessible statically.
	 *
	 * Returns a part of a WHERE clause which will filter out records with
	 * start/end times or deleted/hidden/fe_groups fields set to values that
	 * should de-select them according to the current time, preview settings or
	 * user login.
	 * Is using the $TCA arrays "ctrl" part where the key "enablefields"
	 * determines for each table which of these features applies to that table.
	 *
	 * @param string table name found in the $TCA array
	 * @param integer If $showHidden is set (0/1), any hidden-fields in
	 *                records are ignored. NOTICE: If you call this function,
	 *                consider what to do with the show_hidden parameter.
	 *                Maybe it should be set? See tslib_cObj->enableFields
	 *                where it's implemented correctly.
	 * @param array Array you can pass where keys can be "disabled",
	 *              "starttime", "endtime", "fe_group" (keys from
	 *              "enablefields" in TCA) and if set they will make sure
	 *              that part of the clause is not added. Thus disables
	 *              the specific part of the clause. For previewing etc.
	 * @param boolean If set, enableFields will be applied regardless of
	 *                any versioning preview settings which might otherwise
	 *                disable enableFields.
	 *
	 * @return string the WHERE clause starting like " AND ...=... AND ...=..."
	 */
	public static function enableFields(
		$table, $showHidden = -1, array $ignoreArray = array(),
		$noVersionPreview = false
	) {
		if (!in_array($showHidden, array(-1, 0, 1))) {
			throw new Exception(
				'$showHidden may only be -1, 0 or 1, but actually is ' .
					$showHidden
			);
		}

		// maps $showHidden (-1..1) to (0..2) which ensures valid array keys
		$showHiddenKey = $showHidden + 1;
		$ignoresKey = serialize($ignoreArray);
		$previewKey = intval($noVersionPreview);
		if (!isset(self::$enableFieldsCache[$table][$showHiddenKey][$ignoresKey]
			[$previewKey])
		) {
			self::retrievePageForEnableFields();
			self::$enableFieldsCache[$table][$showHiddenKey][$ignoresKey]
				[$previewKey]
				= self::$pageForEnableFields->enableFields(
					$table,
					$showHidden,
					$ignoreArray,
					$noVersionPreview
				);
		}

		return self::$enableFieldsCache[$table][$showHiddenKey][$ignoresKey]
			[$previewKey];
	}

	/**
	 * Makes sure that self::$pageForEnableFields is a page object.
	 */
	private static function retrievePageForEnableFields() {
		if (!is_object(self::$pageForEnableFields)) {
			if (isset($GLOBALS['TSFE'])
				&& is_object($GLOBALS['TSFE']->sys_page)
			) {
				self::$pageForEnableFields = $GLOBALS['TSFE']->sys_page;
			} else {
				self::$pageForEnableFields
					= t3lib_div::makeInstance('t3lib_pageSelect');
			}
		}
	}

	/**
	 * Recursively creates a comma-separated list of subpage UIDs from
	 * a list of pages. The result also includes the original pages.
	 * The maximum level of recursion can be limited:
	 * 0 = no recursion (the default value, will return $startPages),
	 * 1 = only direct child pages,
	 * ...,
	 * 250 = all descendants for all sane cases
	 *
	 * Note: The returned page list is _not_ sorted.
	 *
	 * @param string comma-separated list of page UIDs to start from,
	 *               must only contain numbers and commas, may be empty
	 * @param integer maximum depth of recursion, must be >= 0
	 *
	 * @return string comma-separated list of subpage UIDs including the
	 *                UIDs provided in $startPages, will be empty if
	 *                $startPages is empty
	 */
	public static function createRecursivePageList(
		$startPages, $recursionDepth = 0
	) {
		if ($recursionDepth < 0) {
			throw new Exception('$recursionDepth must be >= 0.');
		}
		if ($recursionDepth == 0) {
			return $startPages;
		}
		if ($startPages == '') {
			return '';
		}

		$dbResult = tx_oelib_db::select(
			'uid',
			'pages',
			'pid IN (' . $startPages . ')' . tx_oelib_db::enableFields('pages')
		);

		$subPages = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			$subPages[] = $row['uid'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		if (!empty($subPages)) {
			$result = $startPages . ',' . self::createRecursivePageList(
				implode(',', $subPages), $recursionDepth - 1
			);
		} else {
			$result = $startPages;
		}

		return $result;
	}


	////////////////////////////////
	// Wrappers for common queries
	////////////////////////////////

	/**
	 * Executes a DELETE query.
	 *
	 * @throws tx_oelib_Exception_Database if an error has occured
	 *
	 * @param string the name of the table from which to delete, must not be
	 *               empty
	 * @param string the WHERE clause to select the records, may be empty
	 *
	 * @return integer the number of affected rows, might be 0
	 */
	public static function delete($tableName, $whereClause) {
		if ($tableName == '') {
			throw new Exception('The table name must not be empty.');
		}

		self::enableQueryLogging();
		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$tableName, $whereClause
		);
		if (!$dbResult) {
			throw new tx_oelib_Exception_Database();
		}

		return $GLOBALS['TYPO3_DB']->sql_affected_rows();
	}

	/**
	 * Executes an UPDATE query.
	 *
	 * @throws tx_oelib_Exception_Database if an error has occured
	 *
	 * @param string the name of the table to change, must not be empty
	 * @param string the WHERE clause to select the records, may be empty
	 * @param array key/value pairs of the fields to change, may be empty
	 *
	 * @return integer the number of affected rows, might be 0
	 */
	public static function update($tableName, $whereClause, array $fields) {
		if ($tableName == '') {
			throw new Exception('The table name must not be empty.');
		}

		self::enableQueryLogging();
		$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			$tableName, $whereClause, $fields
		);
		if (!$dbResult) {
			throw new tx_oelib_Exception_Database();
		}

		return $GLOBALS['TYPO3_DB']->sql_affected_rows();
	}

	/**
	 * Executes an INSERT query.
	 *
	 * @throws tx_oelib_Exception_Database if an error has occured
	 *
	 * @param string the name of the table in which the record should be
	 *               created, must not be empty
	 * @param array key/value pairs of the record to insert, must not be empty
	 *
	 * @return integer the UID of the created record, will be 0 if the table
	 *                 has no UID column
	 */
	public static function insert($tableName, array $recordData) {
		if ($tableName == '') {
			throw new Exception('The table name must not be empty.');
		}
		if (empty($recordData)) {
			throw new Exception('$recordData must not be empty.');
		}

		self::enableQueryLogging();
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			$tableName, $recordData
		);
		if (!$dbResult) {
			throw new tx_oelib_Exception_Database();
		}

		return $GLOBALS['TYPO3_DB']->sql_insert_id();
	}

	/**
	 * Executes a SELECT query.
	 *
	 * @throws tx_oelib_Exception_Database if an error has occured
	 *
	 * @param string list of fields to select, may be "*", must not be empty
	 * @param string comma-separated list of tables from which to select, must
	 *               not be empty
	 * @param string WHERE clause, may be empty
	 * @param string GROUP BY field(s), may be empty
	 * @param string ORDER BY field(s), may be empty
	 * @param string LIMIT value ([begin,]max), may be empty
	 *
	 * @return resource MySQL result pointer
	 */
	public static function select(
		$fields, $tableNames, $whereClause = '', $groupBy = '', $orderBy = '',
		$limit = ''
	) {
		if ($tableNames == '') {
			throw new Exception('The table names must not be empty.');
		}
		if ($fields == '') {
			throw new Exception('$fields must not be empty.');
		}

		self::enableQueryLogging();
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$fields, $tableNames, $whereClause, $groupBy, $orderBy, $limit
		);
		if (!$dbResult) {
			throw new tx_oelib_Exception_Database();
		}

		return $dbResult;
	}

	/**
	 * Executes a SELECT query and returns the single result row as an
	 * associative array.
	 *
	 * If there is more than one matching record, only one will be returned.
	 *
	 * @throws tx_oelib_Exception_Database if an error has occured
	 * @throws tx_oelib_Exception_EmptyQueryResult if there is no matching
	 *                                             record
	 *
	 * @param string list of fields to select, may be "*", must not be empty
	 * @param string comma-separated list of tables from which to select, must
	 *               not be empty
	 * @param string WHERE clause, may be empty
	 * @param string GROUP BY field(s), may be empty
	 *
	 * @return array the single result row, will not be empty
	 */
	public static function selectSingle(
		$fields, $tableNames, $whereClause = '', $groupBy = ''
	) {
		$result = self::selectMultiple(
			$fields, $tableNames, $whereClause, $groupBy, '', 1
		);
		if (empty($result)) {
			throw new tx_oelib_Exception_EmptyQueryResult();
		}

		return $result[0];
	}

	/**
	 * Executes a SELECT query and returns the result rows as a two-dimensional
	 * associative array.
	 *
	 * If there is more than one matching record, only one will be returned.
	 *
	 * @throws tx_oelib_Exception_Database if an error has occured
	 *
	 * @param string list of fields to select, may be "*", must not be empty
	 * @param string comma-separated list of tables from which to select, must
	 *               not be empty
	 * @param string WHERE clause, may be empty
	 * @param string GROUP BY field(s), may be empty
	 * @param string ORDER BY field(s), may be empty
	 * @param string LIMIT value ([begin,]max), may be empty
	 *
	 * @return array the query result rows, will be empty if there are no
	 *               matching records
	 */
	public static function selectMultiple(
		$fields, $tableNames, $whereClause = '', $groupBy = '', $orderBy = '',
		$limit = ''
	) {
		$result = array();
		$dbResult = self::select(
			$fields, $tableNames, $whereClause, $groupBy, $orderBy, $limit
		);

		while ($recordData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			$result[] = $recordData;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		return $result;
	}


	/////////////////////////////////////
	// Functions concerning table names
	/////////////////////////////////////

	/**
	 * Returns a list of all table names that are available in the current
	 * database.
	 *
	 * @return array list of table names
	 */
	public static function getAllTableNames() {
		self::retrieveTableNames();

		return array_keys(self::$tableNameCache);
	}

	/**
	 * Retrieves the table names of the current DB and stores them in
	 * self::$tableNameCache.
	 *
	 * This function does nothing if the table names already have been
	 * retrieved.
	 */
	private static function retrieveTableNames() {
		if (!empty(self::$tableNameCache)) {
			return;
		}

		self::$tableNameCache = $GLOBALS['TYPO3_DB']->admin_get_tables();
	}

	/**
	 * Checks whether a database table exists.
	 *
	 * @param string the name of the table to check for, must not be empty
	 *
	 * @return boolean true if the table $tableName exists, false otherwise
	 */
	public function existsTable($tableName) {
		if ($tableName == '') {
			throw new Exception('The table name must not be empty.');
		}

		self::retrieveTableNames();

		return isset(self::$tableNameCache[$tableName]);
	}


	////////////////////////////////////////////////
	// Functions concerning the columns of a table
	////////////////////////////////////////////////

	/**
	 * Gets the column data for a table.
	 *
	 * @param string the name of the table for which the column names should be
	 *               retrieved, must not be empty
	 *
	 * @return array the column data for the table $table with the column names
	 *               as keys and the SHOW COLUMNS field information (in an
	 *               array) as values
	 */
	public function getColumnsInTable($table) {
		self::retrieveColumnsForTable($table);

		return self::$tableColumnCache[$table];
	}

	/**
	 * Gets the column definition for a field in $table.
	 *
	 * @param string the name of the table for which the column names should be
	 *               retrieved, must not be empty
	 * @param string the name of the field of which to retrieve the definition,
	 *               must not be empty
	 *
	 * @return array the field definition for the field in $table, will not be
	 *               empty
	 */
	public function getColumnDefinition($table, $column) {
		self::retrieveColumnsForTable($table);

		return self::$tableColumnCache[$table][$column];
	}

	/**
	 * Retrieves and caches the column data for the table $table.
	 *
	 * If the column data for that table already is cached, this function does
	 * nothing.
	 *
	 * @param string the name of the table for which the column names should be
	 *               retrieved, must not be empty
	 */
	private static function retrieveColumnsForTable($table) {
		if ($table == '') {
			throw new Exception('The table name must not be empty.');
		}

		if (!isset(self::$tableColumnCache[$table])) {
			self::$tableColumnCache[$table] =
				$GLOBALS['TYPO3_DB']->admin_get_fields($table);

		}
	}

	/**
	 * Checks whether a table has a column with a particular name.
	 *
	 * To get a boolean true as result, the table must contain a column with the
	 * given name.
	 *
	 * @param string the name of the table to check, must not be empty
	 * @param string the column name to check, must not be empty
	 *
	 * @return boolean true if the column with the provided name exists, false
	 *                 otherwise
	 */
	public static function tableHasColumn($table, $column) {
		if ($column == '') {
			return false;
		}

		self::retrieveColumnsForTable($table);

		return isset(self::$tableColumnCache[$table][$column]);
	}

	/**
	 * Checks whether a table has a column "uid".
	 *
	 * @param string the name of the table to check, must not be empty
	 *
	 * @return boolean true if a valid column was found, false otherwise
	 */
	public static function tableHasColumnUid($table) {
		return self::tableHasColumn($table, 'uid');
	}


	/////////////////////////////////
	// Functions concerning the TCA
	/////////////////////////////////

	/**
	 * Returns the TCA for a certain table.
	 *
	 * @param string the table name to look up, must not be empty
	 *
	 * @return array associative array with the TCA description for this table
	 */
	public static function getTcaForTable($tableName) {
		if (isset(self::$tcaCache[$tableName])) {
			return self::$tcaCache[$tableName];
		}

		t3lib_div::loadTCA($tableName);
		if (!isset($GLOBALS['TCA'][$tableName])) {
			throw new Exception(
				'The table "' . $tableName . '" has no TCA.'
			);
		}
		self::$tcaCache[$tableName] = $GLOBALS['TCA'][$tableName];

		return self::$tcaCache[$tableName];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_db.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_db.php']);
}
?>