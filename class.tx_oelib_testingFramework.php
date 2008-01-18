<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Mario Rimann (typo3-coding@rimann.org)
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

/**
 * Class 'tx_oelib_testingFramework' for the 'oelib' extension.
 *
 * This class provides various functions to handle dummy records in unit tests.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Mario Rimann <typo3-coding@rimann.org>
 */

final class tx_oelib_testingFramework {
	/** Prefix of the extension for which this instance of the testing framework was instantiated */
	private $tablePrefix = '';

	/** Array of all table names to which this instance of the testing framework has access */
	private $allowedTables = array();

	/**
	 * Array of all sytem table names to which this instance of the testing
	 * framework has access.
	 */
	private $allowedSystemTables = array();

	/**
	 * Array of all "dirty" non-system tables (i.e. all tables that were used
	 * for testing and need to be cleaned up)
	 */
	private $dirtyTables = array();

	/**
	 * Array of all "dirty" system tables (i.e. all tables that were used for
	 * testing and need to be cleaned up)
	 */
	private $dirtySystemTables = array();

	/** Array of the sorting values of all relation tables. */
	private $relationSorting = array();

	/**
	 * The constructor for this class.
	 *
	 * This testing framework can be instantiated for one extension at a time.
	 * Example: In your testcase, you'll have something similar to this line of code:
	 * $this->fixture = new tx_oelib_testingFramework('tx_seminars');
	 * The parameter you provide is the prefix of the table names of that particular
	 * extension. Like this, we ensure that the testing framework creates and
	 * deletes records only on table with this prefix.
	 *
	 * If you need dummy records on tables of multiple extensions, you'll have to
	 * instantiate the testing frame work multiple times (once per extension).
	 *
	 * @param	string		the table name prefix of the extension for which
	 * 						this instance of the testing framework should be used
	 */
	public function __construct($tablePrefix) {
		$this->tablePrefix = $tablePrefix;
		$this->createListOfAllowedTables();
	}

	/**
	 * Creates a new dummy record for unit tests.
	 *
	 * If no record data for the new array is given, an empty record will be
	 * created. It will only contain a valid UID and the "is_dummy_record" flag
	 * will be set to 1.
	 *
	 * Should there be any problem creating the record (wrong table name or a
	 * problem with the database), 0 instead of a valid UID will be returned.
	 *
	 * @param	string		the name of the table on which the record should
	 * 						be created, must not be empty
	 * @param	array		associative array that contains the data to save in
	 * 						the new record, may be empty, but must not contain
	 * 						the	 key "uid"
	 *
	 * @return	integer		the UID of the new record, will be > 0
	 */
	public function createRecord($table, array $recordData = array()) {
		if (!$this->isTableNameAllowed($table)) {
			throw new Exception('The table name "'.$table.'" is not allowed.');
		}
		if (isset($recordData['uid'])) {
			throw new Exception(
				'The column "uid" must not be set in $recordData.'
			);
		}

		return $this->createRecordWithoutTableNameChecks(
			$table, $recordData
		);
	}

	/**
	 * Creates a new dummy record for unit tests without checks for the table
	 * name.
	 *
	 * If no record data for the new array is given, an empty record will be
	 * created. It will only contain a valid UID and the "is_dummy_record" flag
	 * will be set to 1.
	 *
	 * Should there be any problem creating the record (wrong table name or a
	 * problem with the database), 0 instead of a valid UID will be returned.
	 *
	 * @param	string		the name of the table on which the record should
	 * 						be created, must not be empty
	 * @param	array		associative array that contains the data to save in
	 * 						the new record, may be empty, but must not contain
	 * 						the	 key "uid"
	 *
	 * @return	integer		the UID of the new record, will be > 0
	 */
	private function createRecordWithoutTableNameChecks(
		$table, array $recordData
	) {
		$dummyColumnName = $this->getDummyColumnName($table);
		$recordData[$dummyColumnName] = 1;

		// Stores the record in the database.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			$table,
			$recordData
		);
		if ($dbResult) {
			$result = $GLOBALS['TYPO3_DB']->sql_insert_id();
			$this->markTableAsDirty($table);
		} else {
			throw new Exception('There was an error with the database query.');
		}

		return $result;
	}

	/**
	 * Creates a front-end page on the page with the UID given by the first
	 * parameter $parentId.
	 *
	 * @param	integer		UID of the page on which the page should be created
	 * @param	array		associative array that contains the data to save in
	 * 						the new page, may be empty, but must not contain
	 * 						the keys "uid", "pid" or "doktype"
	 *
	 * @return	integer		the UID of the new page, will be > 0
	 */
	public function createFrontEndPage(
		$parentId = 0, array $recordData = array()
	) {
		return $this->createGeneralPageRecord(1, $parentId, $recordData);
	}

	/**
	 * Creates a system folder on the page with the UID given by the first
	 * parameter $parentId.
	 *
	 * @param	integer		UID of the page on which the system folder should be
	 * 						created
	 * @param	array		associative array that contains the data to save in
	 * 						the new page, may be empty, but must not contain
	 * 						the keys "uid", "pid" or "doktype"
	 *
	 * @return	integer		the UID of the new system folder, will be > 0
	 */
	public function createSystemFolder(
		$parentId = 0, array $recordData = array()
	) {
		return $this->createGeneralPageRecord(254, $parentId, $recordData);
	}

	/**
	 * Creates a page record with the document type given by the first parameter
	 * $documentType.
	 *
	 * The record will be created on the page with the UID given by the second
	 * parameter $parentId.
	 *
	 * @param	integer		document type of the record to create, must be > 0
	 * @param	integer		UID of the page on which the record should be
	 * 						created
	 * @param	array		associative array that contains the data to save in
	 * 						the record, may be empty, but must not contain the
	 * 						keys "uid", "pid" or "doktype"
	 *
	 * @return	integer		the UID of the new record, will be > 0
	 */
	private function createGeneralPageRecord(
		$documentType, $parentId, array $recordData
	) {
		if (isset($recordData['uid'])) {
			throw new Exception(
				'The column "uid" must not be set in $recordData.'
			);
		}
		if (isset($recordData['pid'])) {
			throw new Exception(
				'The column "pid" must not be set in $recordData.'
			);
		}
		if (isset($recordData['doktype'])) {
			throw new Exception(
				'The column "doktype" must not be set in $recordData.'
			);
		}

		$completeRecordData = $recordData;
		$completeRecordData['pid'] = $parentId;
		$completeRecordData['doktype'] = $documentType;

		return $this->createRecordWithoutTableNameChecks(
			'pages', $completeRecordData
		);
	}

	/**
	 * Creates a FE content element on the page with the UID given by the first
	 * parameter $pageId.
	 *
	 * Created content elements are text elements by default, but the content
	 * element's type can be overwritten by setting the key 'CType' in the
	 * parameter $recordData.
	 *
	 * @param	integer		UID of the page on which the content element should
	 * 						be created
	 * @param	array		associative array that contains the data to save in
	 * 						the content element, may be empty, but must not
	 * 						contain the keys "uid" or "pid"
	 *
	 * @return	integer		the UID of the new content element, will be > 0
	 */
	public function createContentElement(
		$pageId = 0, array $recordData = array()
	) {
		if (isset($recordData['uid'])) {
			throw new Exception(
				'The column "uid" must not be set in $recordData.'
			);
		}
		if (isset($recordData['pid'])) {
			throw new Exception(
				'The column "pid" must not be set in $recordData.'
			);
		}

		$completeRecordData = $recordData;
		$completeRecordData['pid'] = $pageId;
		if (!isset($completeRecordData['CType'])) {
			$completeRecordData['CType'] = 'text';
		}

		return $this->createRecordWithoutTableNameChecks(
			'tt_content', $completeRecordData
		);
	}

	/**
	 * Creates a new page cache entry.
	 *
	 * @param	integer		UID of the page for which a cache entry should be
	 * 						created, must be > 0
	 * @param	array		associative array that contains the data to save
	 * 						as an entry in "cache_pages", may be empty, but must
	 * 						not contain the keys "page_id" or "id"
	 *
	 * @return	integer		the ID of the new cache entry, will be > 0
	 */
	public function createPageCacheEntry(
		$pageId = 0, array $recordData = array()
	) {
		if (isset($recordData['id'])) {
			throw new Exception(
				'The column "id" must not be set in $recordData.'
			);
		}
		if (isset($recordData['page_id'])) {
			throw new Exception(
				'The column "page_id" must not be set in $recordData.'
			);
		}

		$completeRecordData = $recordData;
		$completeRecordData['page_id'] = $pageId;

		return $this->createRecordWithoutTableNameChecks(
			'cache_pages', $completeRecordData
		);
	}

	/**
	 * Changes an existing dummy record and stores the new data for this
	 * record. Only fields that get new values in $recordData will be changed,
	 * everything else will stay untouched.
	 *
	 * The array with the new recordData must contain at least one entry, but
	 * must not contain a new UID for the record. If you need to change the UID,
	 * you have to create a new record!
	 *
	 * @param	string		the name of the table, must not be empty
	 * @param	integer		the UID of the record to change, must not be empty
	 * @param	array		associative array containing key => value pairs for
	 * 						those fields of the record that need to be changed,
	 * 						must not be empty
	 */
	public function changeRecord($table, $uid, $recordData) {
		$dummyColumnName = $this->getDummyColumnName($table);

		if (!$this->isTableNameAllowed($table) && !$this->isSystemTableNameAllowed($table)) {
			throw new Exception(
				'The table "'.$table.'" is not on the lists with allowed tables.'
			);
		}
		if ($uid == 0) {
			throw new Exception('The parameter $uid must not be zero.');
		}
		if (empty($recordData)) {
			throw new Exception('The array with the new record data must not be empty.');
		}
		if (isset($recordData['uid'])) {
			throw new Exception(
				'The parameter $recordData must not contain changes to the UID of a record.'
			);
		}
		if (isset($recordData[$dummyColumnName])) {
			throw new Exception(
				'The parameter $recordData must not contain changes to the field "'
				.$dummyColumnName.'". It is impossible to convert a dummy record '
				.'into a regular record.');
		}
		if (!$this->countRecords($table, 'uid='.$uid)) {
			throw new Exception(
				'There is no record with UID '.$uid.' on table "'.$table.'".'
			);
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			$table,
			'uid='.$uid.' AND '.$dummyColumnName.'=1',
			$recordData
		);

		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}
	}

	/**
	 * Deletes a dummy record from the database.
	 *
	 * Important: Only dummy records from non-system tables can be deleted with
	 * this method. Should there for any reason exist a real record with that
	 * UID, it won't be deleted.
	 *
	 * @param	string		name of the table from which the record should be
	 * 						deleted, must not be empty
	 * @param	integer		UID of the record to delete, must be > 0
	 */
	public function deleteRecord($table, $uid) {
		if (!$this->isTableNameAllowed($table)) {
			throw new Exception('The table name "'.$table.'" is not allowed.');
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$table,
			'uid='.$uid.' AND is_dummy_record=1'
		);

		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}
	}

	/**
	 * Creates a relation between two records on different tables (so called
	 * m:n relation).
	 *
	 * @param	string		name of the m:n table to which the record should be
	 * 						added, must not be empty
	 * @param	integer		UID of the local table, must be > 0
	 * @param	integer		UID of the foreign table, must be > 0
	 * @param	integer		sorting value of the relation, the default value is
	 * 						0, which enables automatic sorting, a value >= 0
	 * 						overwrites the automatic sorting
	 */
	public function createRelation($table, $uidLocal, $uidForeign, $sorting = 0) {
		if (!$this->isTableNameAllowed($table)) {
			throw new Exception('The table name "'.$table.'" is not allowed.');
		}

		// Checks that the two given UIDs are valid.
		if (intval($uidLocal) == 0) {
			throw new Exception(
				'$uidLocal must be an integer > 0, but actually is "'
					.$uidLocal.'"'
			);
		}
		if  (intval($uidForeign) == 0) {
			throw new Exception(
				'$uidForeign must be an integer > 0, but actually is "'
					.$uidForeign.'"'
			);
		}

		$this->markTableAsDirty($table);

		$recordData = array(
			'uid_local' => $uidLocal,
			'uid_foreign' => $uidForeign,
			'sorting' => (($sorting > 0) ?
				$sorting : $this->getRelationSorting($table, $uidLocal)),
			'is_dummy_record' => 1
		);

		// Stores the record in the database.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			$table,
			$recordData
		);

		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}
	}

	/**
	 * Deletes a dummy relation from an m:n table in the database.
	 *
	 * Important: Only dummy records can be deleted with this method. Should there
	 * for any reason exist a real record with that combination of local and
	 * foreign UID, it won't be deleted!
	 *
	 * @param	string		name of the table from which the record should be
	 * 						deleted, must not be empty
	 * @param	integer		UID on the local table, must be > 0
	 * @param	integer		UID on the foreign table, must be > 0
	 */
	public function removeRelation($table, $uidLocal, $uidForeign) {
		if (!$this->isTableNameAllowed($table)) {
			throw new Exception('The table name "'.$table.'" is not allowed.');
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$table,
			'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
				.' AND is_dummy_record=1'
		);

		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}
	}

	/**
	 * Deletes all dummy records that have been added through this framework.
	 * For this, all records with the "is_dummy_record" flag set to 1 will be
	 * deleted from all tables that have been used within this instance of the
	 * testing framework.
	 *
	 * If you set $performDeepCleanUp to true, it will go through ALL tables to
	 * which the current instance of the testing framework has access. Please
	 * consider well, whether you want to do this as it's a huge performance
	 * issue.
	 *
	 * @param	boolean		whether a deep clean up should be performed, may
	 * 						be empty
	 */
	public function cleanUp($performDeepCleanUp = false) {
		$this->cleanUpTableSet(false, $performDeepCleanUp);
		$this->cleanUpTableSet(true, $performDeepCleanUp);
	}

	/**
	 * Deletes a set of records that have been added through this framework for
	 * a set of tables (either the test tables or the allowed system tables).
	 * For this, all records with the "is_dummy_record" flag set to 1 will be
	 * deleted from all tables that have been used within this instance of the
	 * testing framework.
	 *
	 * If you set $performDeepCleanUp to true, it will go through ALL tables to
	 * which the current instance of the testing framework has access. Please
	 * consider well, whether you want to do this as it's a huge performance
	 * issue.
	 *
	 * @param	boolean		whether to clean up the system tables (true) or
	 * 						the non-system test tables (false)
	 * @param	boolean		whether a deep clean up should be performed, may
	 * 						be empty
	 */
	private function cleanUpTableSet($useSystemTables, $performDeepCleanUp) {
		if ($useSystemTables) {
			$tablesToCleanUp = ($performDeepCleanUp)
				? $this->allowedSystemTables
				: $this->dirtySystemTables;
		} else {
			$tablesToCleanUp = ($performDeepCleanUp)
				? $this->allowedTables
				: $this->dirtyTables;
		}

		foreach ($tablesToCleanUp as $currentTable) {
			$dummyColumnName = $this->getDummyColumnName($currentTable);

			// Runs a delete query for each allowed table. A "one-query-deletes-them-all"
			// approach was tested but we didn't find a working solution for that.
			$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
				$currentTable,
				$dummyColumnName.'=1'
			);

			// Resets the auto increment setting of the current table.
			$this->resetAutoIncrement($currentTable);

			if (!$dbResult) {
				throw new Exception(
					'There was an error with the database query.'
				);
			}
		}

		// Resets the list of dirty tables.
		$this->dirtyTables = array();
	}


	// ----------------------------------------------------------------------
	// Various helper functions
	// ----------------------------------------------------------------------

	/**
	 * Generates a list of allowed tables to which this instance of the testing
	 * framework has access to create/remove test records.
	 *
	 * The generated list is based on the list of all tables that TYPO3 can
	 * access (which will be all tables in this database), filtered by prefix of
	 * the extension to test.
	 *
	 * The array with the allowed table names is written directly to
	 * $this->allowedTables.
	 */
	private function createListOfAllowedTables() {
		$this->allowedTables = array();
		$allTables = $GLOBALS['TYPO3_DB']->admin_get_tables();
		$length = strlen($this->tablePrefix);

		foreach ($allTables as $currentTableName) {
			if (substr_compare(
					$this->tablePrefix, $currentTableName, 0, $length
				) == 0
			) {
				$this->allowedTables[] = $currentTableName;
			}
		}

		$this->allowedSystemTables = array(
			'cache_pages', 'fe_users', 'pages', 'tt_content'
		);
	}

	/**
	 * Checks whether the given table name is in the list of allowed tables for
	 * this instance of the testing framework.
	 *
	 * @param	string		the name of the table to check, must not be empty
	 *
	 * @return	boolean		true if the name of the table is in the list of
	 * 						allowed tables, false otherwise
	 */
	private function isTableNameAllowed($table) {
		return in_array($table, $this->allowedTables);
	}

	/**
	 * Checks whether the given table name is in the list of allowed
	 * system tables for this instance of the testing framework.
	 *
	 * @param	string		the name of the table to check, must not be empty
	 *
	 * @return	boolean		true if the name of the table is in the list of
	 * 						allowed system tables, false otherwise
	 */
	private function isSystemTableNameAllowed($table) {
		return in_array($table, $this->allowedSystemTables);
	}

	/**
	 * Returns the name of the column that marks a record as a dummy record.
	 *
	 * On most tables this is "is_dummy_record", but on system tables like
	 * "pages" or "fe_users", the column is called "tx_oelib_dummy_record".
	 *
	 * @param	string		the table name to look up, must not be empty
	 *
	 * @return	string		the name of the column that marks a record as dummy
	 * 						record
	 */
	private function getDummyColumnName($table) {
		$result = 'is_dummy_record';

		if ($this->isSystemTableNameAllowed($table)) {
			$result = 'tx_oelib_is_dummy_record';
		}

		return $result;
	}

	/**
	 * Retrieves a database result row as an associative array.
	 *
	 * @param	mixed	either a DB query result resource or false (for failed
	 * 					queries)
	 *
	 * @return	array	the database result as an associative array
	 */
	public function getAssociativeDatabaseResult($queryResult) {
		if (!$queryResult) {
			throw new Exception('There was an error with the database query.');
		}

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($queryResult);
		if (!$row) {
			throw new Exception(
				'There was an error with the result of the database query.'
			);
		}

		return $row;
	}

	/**
	 * Counts the records on the table given by the first parameter $table that
	 * match a given WHERE clause.
	 *
	 * This function will work on any table that has been registered in TYPO3.
	 *
	 * @param	string		the name of the table to query, must not be empty
	 * @param	string		the where part of the query, may be empty (all records
	 * 						will be counted in that case)
	 *
	 * @return	integer		the number of records that have been found
	 */
	public function countRecords($table, $whereClause = '1=1') {
		if (!$this->isTable($table)) {
			throw new Exception(
				'The method countRecords() was called with an empty table name'
					.' or a table name that is not allowed within the current'
					.' instance of the testing framework.'
			);
		}

		$row = $this->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'COUNT(*) AS number',
				$table,
				$whereClause
			)
		);

		return intval($row['number']);
	}

	/**
	 * Resets the auto increment value for a given table to the highest existing
	 * UID + 1. This is required to leave the table in the same status that it
	 * had before adding dummy records.
	 *
	 * @param	string		the name of the table on which we're going to reset
	 * 						the auto increment entry, must not be empty
	 */
	public function resetAutoIncrement($table) {
		if (!$this->isTableNameAllowed($table)
			&& !$this->isSystemTableNameAllowed($table)
		) {
			throw new Exception(
				'The given table name is invalid. This means it is either empty'
				.' or not in the list of allowed tables.'
			);
		}

		// Checks whether the current table qualifies for this method. If there
		// is no column "uid" that has the "auto_icrement" flag set, we should not
		// try to reset this inexistent auto increment index to avoid DB errors.
		if (!$this->hasTableColumnUid($table)) {
			return;
		}

		// Searches for the record with the highest UID in this table.
		$row = $this->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->sql_query(
				'SELECT MAX(uid) AS uid FROM '.$table.';'
			)
		);
		$newAutoIncrementValue = $row['uid'] + 1;

		// Updates the auto increment index for this table. The index will be set
		// to one UID above the highest existing UID.
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'ALTER TABLE '.$table.' AUTO_INCREMENT='.$newAutoIncrementValue.';'
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}
	}

	/**
	 * Checks whether a table has a column "uid".
	 *
	 * To get a boolean true as result, the table must contain a column named
	 * "uid" that has the "auto_increment" flag set.
	 *
	 * @param	string		the name of the table to check
	 *
	 * @return	boolean		true if a valid column was found, false otherwise
	 */
	public function hasTableColumnUid($table) {
		$result = false;

		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'DESCRIBE '.$table.';'
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}

		// Walks through all the columns for this tables. As soon as a valid
		// column is found, we'll exit the while loop.
		while (!$result
				&& ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult))
		) {
			// Checks whether we have a valid column.
			$result = (($row['Field'] == 'uid')
				&& ($row['Extra'] == 'auto_increment'));
		}

		return $result;
	}

	/**
	 * Returns the list of allowed table names.
	 *
	 * @return	array		all allowed table names for this instance of the
	 * 						testing framework
	 */
	public function getListOfAllowedTableNames() {
		return $this->allowedTables;
	}

	/**
	 * Puts a table name on the list of dirty tables (which represents a list
	 * of tables that were used for testing and contain dummy records and
	 * thus are called "dirty" until the next clean up).
	 *
	 * @param	string		the table name to put on the list of dirty tables
	 */
	public function markTableAsDirty($table) {
		if ($this->isTableNameAllowed($table)) {
			$this->dirtyTables[$table] = $table;
		} elseif ($this->isSystemTableNameAllowed($table)) {
			$this->dirtySystemTables[$table] = $table;
		} else {
			throw new Exception(
				'The table name "'.$table
					.'" is not allowed for markTableAsDirty.'
			);
		}
	}

	/**
	 * Returns the list of tables that contain dummy records from testing. These
	 * tables are called "dirty tables" as they need to be cleaned up.
	 *
	 * @return	array		associative array containing names of database tables
	 * 						that need to be cleaned up
	 */
	public function getListOfDirtyTables() {
		return $this->dirtyTables;
	}

	/**
	 * Returns the list of system tables that contain dummy records from
	 * testing. These tables are called "dirty tables" as they need to be
	 * cleaned up.
	 *
	 * @return	array		associative array containing names of system
	 * 						database tables that need to be cleaned up
	 */
	public function getListOfDirtySystemTables() {
		return $this->dirtySystemTables;
	}

	/**
	 * Returns the next sorting value of the relation table which should be used.
	 *
	 * TODO: This function doesn't take already existing relations in the
	 * database - which were created without using the testing framework - into
	 * account. So you always should create new dummy records and create a
	 * relation between these two dummy records, so you're sure there aren't
	 * already relations for a local UID in the database.
	 *
	 * @see		https://bugs.oliverklee.com/show_bug.cgi?id=1423
	 *
	 * @param	string		the relation table, must not be empty
	 * @param	integer		UID of the local table, must be > 0
	 *
	 * @return	integer		the next sorting value to use (> 0)
	 */
	public function getRelationSorting($table, $uidLocal) {
		if (!$this->relationSorting[$table][$uidLocal]) {
			$this->relationSorting[$table][$uidLocal] = 0;
		}

		$this->relationSorting[$table][$uidLocal]++;

		return $this->relationSorting[$table][$uidLocal];
	}

	/**
	 * Checks whether the value of the parameter $table is the name of a
	 * database table that has been registered in TYPO3.
	 *
	 * @param	string		the name of of table to check, must not be empty
	 *
	 * @return	boolean		true if the table is registered in TYPO3, false
	 * 						otherwise
	 */
	private function isTable($table) {
		static $registeredTables = array();

		if ($table == '') {
			return false;
		}

		if (empty($registeredTables)) {
			$registeredTables = $GLOBALS['TYPO3_DB']->admin_get_tables();
		}

		return (in_array($table, $registeredTables));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminarst/class.tx_oelib_testingFramework.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_oelib_testingFramework.php']);
}

?>
