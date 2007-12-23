<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Mario Rimann (typo3-coding@rimann.org)
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
 * Class 'tx_oelib_testingframework' for the 'oelib' extension.
 *
 * This is mere a class used for unit tests of the 'oelib' extension. Don't
 * use it for any other purpose.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Mario Rimann <typo3-coding@rimann.org>
 */

final class tx_oelib_testingframework {
	/** Prefix of the extension for which this instance of the testing framework was instantiated */
	private $tablePrefix = '';

	/** Array of all table names to which this instance of the testing framework has access */
	private $allowedTables = array();

	/** Array of all "dirty" tables (i.e. all tables that were used for testing and need to be cleaned up) */
	private $dirtyTables = array();

	/**
	 * The constructor for this class.
	 *
	 * This testing framework can be instantiated for one extension at a time.
	 * Example: In your testcase, you'll have something similar to this line of code:
	 * $this->fixture = new tx_oelib_testingframework('tx_seminars');
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
	 * @return	integer		the UID of the new record or 0 if there was a problem
	 * 						and no record was created
	 */
	public function createRecord($table, array $recordData = array()) {
		if (!$this->isTableNameAllowed($table)
			|| isset($recordData['uid'])
		) {
			return 0;
		}

		// Ensures that this account will have the test flag.
		$recordData['is_dummy_record'] = 1;

		// Stores the record in the database.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			$table,
			$recordData
		);
		if ($dbResult) {
			$result = $GLOBALS['TYPO3_DB']->sql_insert_id();
			$this->markTableAsDirty($table);
		} else {
			// Something went wront while inserting the record into the DB.
			$result = 0;
		}

		return $result;
	}

	/**
	 * Deletes a dummy record from the database.
	 *
	 * Important: Only dummy records can be deleted with this method. Should there
	 * for any reason exist a real record with that UID, it won't be deleted.
	 *
	 * @param	string		name of the table from which the record should be
	 * 						deleted, must not be empty
	 * @param	integer		UID of the record to delete, must be > 0
	 *
	 * @return	boolean		true if everything went well (even if no record was
	 * 						deleted), false otherwise
	 */
	public function deleteRecord($table, $uid) {
		if (!$this->isTableNameAllowed($table)) {
			return false;
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$table,
			'uid='.$uid.' AND is_dummy_record=1'
		);

		return (boolean) $dbResult;
	}

	/**
	 * Creates a relation between two records on different tables (so called
	 * m:n relation).
	 *
	 * This method returns a boolean true if everything was fine and false if
	 * something went wrong (table name not allowed or insert query failed for
	 * some other reason).
	 *
	 * @param	string		name of the m:n table to which the record should be
	 * 						added, must not be empty
	 * @param	integer		UID of the local table, must be > 0
	 * @param	integer		UID of the foreign table, must be > 0
	 *
	 * @return	boolean		true if the record was properly saved, false otherwise
	 */
	public function createRelation($table, $uidLocal, $uidForeign) {
		if (!$this->isTableNameAllowed($table)) {
			return false;
		}

		// Checks that the two given UIDs are valid.
		if ((intval($uidLocal) == 0)
			|| (intval($uidForeign) == 0)
		) {
			return false;
		}

		$this->markTableAsDirty($table);

		$recordData = array(
			'uid_local' => $uidLocal,
			'uid_foreign' => $uidForeign,
			'is_dummy_record' => 1
		);

		// Stores the record in the database.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			$table,
			$recordData
		);

		// Checks whether the insert query was successful.
		return (boolean) $dbResult;
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
	 *
	 * @return	boolean		true if everything went well, false otherwise
	 */
	public function removeRelation($table, $uidLocal, $uidForeign) {
		if (!$this->isTableNameAllowed($table)) {
			return false;
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$table,
			'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
				.' AND is_dummy_record=1'
		);

		return (boolean) $dbResult;
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
	 * @param	boolean		whether a deep clean up should be performed, may be empty
	 */
	public function cleanUp($performDeepCleanUp = false) {
		$tablesToCleanUp = ($performDeepCleanUp)
			? $this->allowedTables
			: $this->dirtyTables;

		foreach ($tablesToCleanUp as $currentTable) {
			// Runs a delete query for each allowed table. A "one-query-deletes-them-all"
			// approach was tested but we didn't find a working solution for that.
			$GLOBALS['TYPO3_DB']->exec_DELETEquery(
				$currentTable,
				'is_dummy_record=1'
			);

			// Resets the auto increment setting of the current table.
			$this->resetAutoIncrement($currentTable);
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
	 * Counts the records on the table $table that match a given WHERE clause.
	 *
	 * @param	string		the name of the table to query, must not be empty
	 * @param	string		the where part of the query, may be empty (all records
	 * 						will be counted in that case)
	 *
	 * @return	integer		the number of records that have been found
	 */
	public function countRecords($table, $whereClause = '1=1') {
		if (!$this->isTableNameAllowed($table)) {
			throw new Exception(
				'The method countRecords() was called with an empty table name or'
				.' a table name that is not allowed within the current instance'
				.' of the testing framework.'
			);
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS number',
			$table,
			$whereClause
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		if (!$row) {
			throw new Exception(
				'There was an error with the result of the database query.'
			);
		}

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
		if (!$this->isTableNameAllowed($table)) {
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

		// Searches for the record with the highes UID in this table.
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'SELECT MAX(uid) AS uid FROM '.$table.';'
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		if (!$row) {
			throw new Exception(
				'There was an error with the result of the database query.'
			);
		}
		$newAutoIncrementValue = $row['uid'] + 1;

		// Updates the auto increment index for this table. The index will be set
		// to one UID above the highest existing UID.
		$GLOBALS['TYPO3_DB']->sql_query(
			'ALTER TABLE '.$table.' AUTO_INCREMENT='.$newAutoIncrementValue.';'
		);
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
			$result = (($row['Field'] == 'uid') && ($row['Extra'] == 'auto_increment'));
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
	private function markTableAsDirty($table) {
		$this->dirtyTables[$table] = $table;
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminarst/tests/fixtures/class.tx_oelib_testingframework.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_oelib_testingframework.php']);
}

?>
