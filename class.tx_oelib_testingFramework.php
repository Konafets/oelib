<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Mario Rimann (typo3-coding@rimann.org)
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

// In the back end, PATH_tslib isn't defined yet.
if (!defined('PATH_tslib')) {
	define('PATH_tslib', t3lib_extMgm::extPath('cms') . 'tslib/');
}

require_once(PATH_t3lib . 'class.t3lib_timetrack.php');
require_once(PATH_tslib . 'class.tslib_fe.php');
require_once(PATH_t3lib . 'class.t3lib_page.php');
require_once(PATH_tslib . 'class.tslib_content.php');
require_once(PATH_t3lib . 'class.t3lib_userauth.php');
require_once(PATH_tslib . 'class.tslib_feuserauth.php');
require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
require_once(PATH_t3lib . 'class.t3lib_cs.php');
require_once(PATH_t3lib . 'class.t3lib_stdgraphic.php');
require_once(PATH_tslib . 'class.tslib_gifbuilder.php');

/**
 * Class 'tx_oelib_testingFramework' for the 'oelib' extension.
 *
 * This class provides various functions to handle dummy records in unit tests.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
final class tx_oelib_testingFramework {
	/**
	 * @var string prefix of the extension for which this instance of the
	 *             testing framework was instantiated (e.g. "tx_seminars")
	 */
	private $tablePrefix = '';

	/**
	 * @var array prefixes of additional extensions to which this instance
	 *            of the testing framework has access (e.g. "tx_seminars")
	 */
	private $additionalTablePrefixes = array();

	/**
	 * @var array cache for all TCA arrays
	 */
	private static $tcaCache = array();

	/**
	 * @var array cache for all DB table names in the DB
	 */
	private static $allTablesCache = array();

	/**
	 * @var array all own DB table names to which this instance of the
	 *            testing framework has access
	 */
	private $ownAllowedTables = array();

	/**
	 * @var array all additional DB table names to which this instance of
	 *            the testing framework has access
	 */
	private $additionalAllowedTables = array();

	/**
	 * @var array all sytem table names to which this instance of the
	 *            testing framework has access
	 */
	private $allowedSystemTables = array(
		'cache_pages', 'fe_groups', 'fe_users', 'pages', 'sys_template',
		'tt_content'
	);

	/**
	 * @var array all "dirty" non-system tables (i.e. all tables that were
	 * used for testing and need to be cleaned up)
	 */
	private $dirtyTables = array();

	/**
	 * @var array all "dirty" system tables (i.e. all tables that were
	 *            used for testing and need to be cleaned up)
	 */
	private $dirtySystemTables = array();

	/**
	 * @var array sorting values of all relation tables
	 */
	private $relationSorting = array();

	/**
	 * @var integer the number of unusable UIDs after the maximum UID in a
	 * table before the auto increment value will be reset by
	 * resetAutoIncrementLazily
	 */
	private $resetAutoIncrementThreshold = 100;

	/**
	 * @var array the names of the created dummy files relative to the upload
	 *            folder of the extension to test
	 */
	private $dummyFiles = array();

	/**
	 * @var array the names of the created dummy folders relative to the
	 *            upload folder of the extension to test
	 */
	private $dummyFolders = array();

	/**
	 * @var string the absolute path to the upload folder of the extension
	 * to test
	 */
	private $uploadFolderPath = '';

	/**
	 * @var t3lib_basicFileFunctions an instance of t3lib_basicFileFunctions
	 *                               for retrieving a unique file name
	 */
	private static $fileNameProcessor = null;

	/** @var boolean whether a fake front end has been created */
	private $hasFakeFrontEnd = false;

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
	 * @param string the table name prefix of the extension for which
	 *               this instance of the testing framework should be used
	 * @param array the additional table name prefixes of the extensions
	 *              for which this instance of the testing framework
	 *              should be used, may be empty
	 */
	public function __construct(
		$tablePrefix, array $additionalTablePrefixes = array()
	) {
		$this->tablePrefix = $tablePrefix;
		$this->additionalTablePrefixes = $additionalTablePrefixes;
		$this->createListOfOwnAllowedTables();
		$this->createListOfAdditionalAllowedTables();
		$this->uploadFolderPath
			= PATH_site . 'uploads/' . $this->tablePrefix . '/';
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
	 * @param string the name of the table on which the record should
	 *               be created, must not be empty
	 * @param array associative array that contains the data to save in
	 *              the new record, may be empty, but must not contain
	 *              the key "uid"
	 *
	 * @return integer the UID of the new record, will be > 0
	 */
	public function createRecord($table, array $recordData = array()) {
		if (!$this->isNoneSystemTableNameAllowed($table)) {
			throw new Exception('The table name "' . $table . '" is not allowed.');
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
	 * @param string the name of the table on which the record should
	 *               be created, must not be empty
	 * @param array associative array that contains the data to save in
	 *              the new record, may be empty, but must not contain
	 *              the key "uid"
	 *
	 * @return integer the UID of the new record, will be > 0
	 */
	private function createRecordWithoutTableNameChecks(
		$table, array $recordData
	) {
		$dummyColumnName = $this->getDummyColumnName($table);
		$recordData[$dummyColumnName] = 1;

		$uid = tx_oelib_db::insert(
			$table, $recordData
		);

		$this->markTableAsDirty($table);

		return $uid;
	}

	/**
	 * Creates a front-end page on the page with the UID given by the first
	 * parameter $parentId.
	 *
	 * @param integer UID of the page on which the page should be created
	 * @param array associative array that contains the data to save in
	 *              the new page, may be empty, but must not contain
	 *              the keys "uid", "pid" or "doktype"
	 *
	 * @return integer the UID of the new page, will be > 0
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
	 * @param integer UID of the page on which the system folder should be
	 *                created
	 * @param array associative array that contains the data to save in
	 *              the new page, may be empty, but must not contain
	 *              the keys "uid", "pid" or "doktype"
	 *
	 * @return integer the UID of the new system folder, will be > 0
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
	 * @param integer document type of the record to create, must be > 0
	 * @param integer UID of the page on which the record should be created
	 * @param array associative array that contains the data to save in
	 *              the record, may be empty, but must not contain the
	 *              keys "uid", "pid" or "doktype"
	 *
	 * @return integer the UID of the new record, will be > 0
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
	 * @param integer UID of the page on which the content element should
	 *                be created
	 * @param array associative array that contains the data to save in
	 *              the content element, may be empty, but must not
	 *              contain the keys "uid" or "pid"
	 *
	 * @return integer the UID of the new content element, will be > 0
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
	 * @param integer UID of the page for which a cache entry should be
	 *                created, must be > 0
	 * @param array associative array that contains the data to save
	 *              as an entry in "cache_pages", may be empty, but must
	 *              not contain the keys "page_id" or "id"
	 *
	 * @return integer the ID of the new cache entry, will be > 0
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
	 * Creates a template on the page with the UID given by the first parameter
	 * $pageId.
	 *
	 * @param integer UID of the page on which the template should be
	 *                created, must be > 0
	 * @param array associative array that contains the data to save in
	 *              the new template, may be empty, but must not contain
	 *              the keys "uid" or "pid"
	 *
	 * @return integer the UID of the new template, will be > 0
	 */
	public function createTemplate(
		$pageId, array $recordData = array()
	) {
		if ($pageId <= 0) {
			throw new Exception(
				'$pageId must be > 0.'
			);
		}
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

		return $this->createRecordWithoutTableNameChecks(
			'sys_template', $completeRecordData
		);
	}

	/**
	 * Creates a FE user group.
	 *
	 * @param array associative array that contains the data to save
	 *              in the new user group record, may be empty, but must
	 *              not contain the key "uid"
	 *
	 * @return integer the UID of the new user group, will be > 0
	 */
	public function createFrontEndUserGroup(
		array $recordData = array()
	) {
		if (isset($recordData['uid'])) {
			throw new Exception(
				'The column "uid" must not be set in $recordData.'
			);
		}

		return $this->createRecordWithoutTableNameChecks(
			'fe_groups', $recordData
		);
	}

	/**
	 * Creates a FE user record.
	 *
	 * @param string comma-separated list of UIDs of the user groups to
	 *               which the new user belongs, each must be > 0, may
	 *               contain spaces, if empty a new FE user group will be
	 *               created
	 * @param array associative array that contains the data to save
	 *              in the new user record, may be empty, but must not
	 *              contain the keys "uid" or "usergroup"
	 *
	 * @return integer the UID of the new FE user, will be > 0
	 */
	public function createFrontEndUser(
		$frontEndUserGroups = '', array $recordData = array()
	) {
		$frontEndUserGroupsWithoutSpaces = str_replace(' ', '', $frontEndUserGroups);

		if ($frontEndUserGroupsWithoutSpaces == '') {
			$frontEndUserGroupsWithoutSpaces = $this->createFrontEndUserGroup();
		}
		if (!preg_match('/^([1-9]+[0-9]*,?)+$/', $frontEndUserGroupsWithoutSpaces)
		) {
			throw new Exception(
				'$frontEndUserGroups must contain a comma-separated list of UIDs. '
					.'Each UID must be > 0.'
			);
		}
		if (isset($recordData['uid'])) {
			throw new Exception(
				'The column "uid" must not be set in $recordData.'
			);
		}
		if (isset($recordData['usergroup'])) {
			throw new Exception(
				'The column "usergroup" must not be set in $recordData.'
			);
		}

		$completeRecordData = $recordData;
		$completeRecordData['usergroup'] = $frontEndUserGroupsWithoutSpaces;

		return $this->createRecordWithoutTableNameChecks(
			'fe_users', $completeRecordData
		);
	}

	/**
	 * Creates and logs in an FE user.
	 *
	 * @param string comma-separated list of UIDs of the user groups to
	 *               which the new user belongs, each must be > 0, may
	 *               contain spaces; if empty a new front-end user group
	 *               is created
	 *
	 * @return integer the UID of the new FE user, will be > 0
	 */
	public function createAndLoginFrontEndUser($frontEndUserGroups = '') {
		$frontEndUserUid = $this->createFrontEndUser($frontEndUserGroups);

		$this->loginFrontEndUser($frontEndUserUid);

		return $frontEndUserUid;
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
	 * @param string the name of the table, must not be empty
	 * @param integer the UID of the record to change, must not be empty
	 * @param array associative array containing key => value pairs for
	 *              those fields of the record that need to be changed,
	 *              must not be empty
	 */
	public function changeRecord($table, $uid, $recordData) {
		$dummyColumnName = $this->getDummyColumnName($table);

		if (!$this->isTableNameAllowed($table)) {
			throw new Exception(
				'The table "' . $table . '" is not on the lists with allowed tables.'
			);
		}
		if ($uid == 0) {
			throw new Exception('The parameter $uid must not be zero.');
		}
		if (empty($recordData)) {
			throw new Exception(
				'The array with the new record data must not be empty.'
			);
		}
		if (isset($recordData['uid'])) {
			throw new Exception(
				'The parameter $recordData must not contain changes to the UID' .
				' of a record.'
			);
		}
		if (isset($recordData[$dummyColumnName])) {
			throw new Exception(
				'The parameter $recordData must not contain changes to the ' .
					'field "' . $dummyColumnName . '". It is impossible to ' .
					'convert a dummy record into a regular record.'
			);
		}
		if (!$this->countRecords($table, 'uid='.$uid)) {
			throw new Exception(
				'There is no record with UID ' . $uid . ' on table "' . $table . '".'
			);
		}

		tx_oelib_db::update(
			$table,
			'uid = ' . $uid . ' AND ' . $dummyColumnName . ' = 1',
			$recordData
		);
	}

	/**
	 * Deletes a dummy record from the database.
	 *
	 * Important: Only dummy records from non-system tables can be deleted with
	 * this method. Should there for any reason exist a real record with that
	 * UID, it won't be deleted.
	 *
	 * @param string name of the table from which the record should be
	 *               deleted, must not be empty
	 * @param integer UID of the record to delete, must be > 0
	 */
	public function deleteRecord($table, $uid) {
		if (!$this->isNoneSystemTableNameAllowed($table)) {
			throw new Exception(
				'The table name "' . $table . '" is not allowed.'
			);
		}

		tx_oelib_db::delete(
			$table,
			'uid = ' . $uid . ' AND ' . $this->getDummyColumnName($table) .
				' = 1'
		);
	}

	/**
	 * Creates a relation between two records on different tables (so called
	 * m:n relation).
	 *
	 * @param string name of the m:n table to which the record should be
	 *               added, must not be empty
	 * @param integer UID of the local table, must be > 0
	 * @param integer UID of the foreign table, must be > 0
	 * @param integer sorting value of the relation, the default value is
	 *                0, which enables automatic sorting, a value >= 0
	 *                overwrites the automatic sorting
	 */
	public function createRelation($table, $uidLocal, $uidForeign, $sorting = 0) {
		if (!$this->isNoneSystemTableNameAllowed($table)) {
			throw new Exception('The table name "' . $table . '" is not allowed.');
		}

		// Checks that the two given UIDs are valid.
		if (intval($uidLocal) <= 0) {
			throw new Exception(
				'$uidLocal must be an integer > 0, but actually is "' .
					$uidLocal . '"'
			);
		}
		if  (intval($uidForeign) <= 0) {
			throw new Exception(
				'$uidForeign must be an integer > 0, but actually is "' .
					$uidForeign . '"'
			);
		}

		$this->markTableAsDirty($table);

		$recordData = array(
			'uid_local' => $uidLocal,
			'uid_foreign' => $uidForeign,
			'sorting' => (($sorting > 0) ?
				$sorting : $this->getRelationSorting($table, $uidLocal)),
			$this->getDummyColumnName($table) => 1
		);

		tx_oelib_db::insert(
			$table, $recordData
		);
	}

	/**
	 * Creates a relation between two records based on the rules defined in TCA
	 * regarding the relation.
	 *
	 * @param string name of the table from which a relation should be created,
	 *               must not be empty
	 * @param integer UID of the record in the local table, must be > 0
	 * @param integer UID of the record in the foreign table, must be > 0
	 * @param string name of the column in which the relation counter should be
	 *               updated, must not be empty
	 */
	public function createRelationAndUpdateCounter(
		$tableName, $uidLocal, $uidForeign, $columnName
	) {
		if (!$this->isTableNameAllowed($tableName)) {
			throw new Exception(
				'The table name "' . $tableName . '" is not allowed.'
			);
		}

		if ($uidLocal <= 0) {
			throw new Exception(
				'$uidLocal must be > 0, but actually is "' . $uidLocal . '"'
			);
		}
		if ($uidForeign <= 0) {
			throw new Exception(
				'$uidForeign must be  > 0, but actually is "' . $uidForeign . '"'
			);
		}

		$tca = $this->getTcaForTable($tableName);
		$relationConfiguration = $tca['columns'][$columnName];

		if (!isset($relationConfiguration['config']['MM'])
			|| ($relationConfiguration['config']['MM'] == '')
		) {
			throw new Exception(
				'The column ' . $columnName . ' in the table ' . $tableName .
				' is not configured to contain m:n relations using a m:n table.'
			);
		}

		$this->createRelation(
			$relationConfiguration['config']['MM'],
			$uidLocal,
			$uidForeign
		);

		$this->increaseRelationCounter($tableName, $uidLocal, $columnName);
	}

	/**
	 * Deletes a dummy relation from an m:n table in the database.
	 *
	 * Important: Only dummy records can be deleted with this method. Should there
	 * for any reason exist a real record with that combination of local and
	 * foreign UID, it won't be deleted!
	 *
	 * @param string name of the table from which the record should be
	 *               deleted, must not be empty
	 * @param integer UID on the local table, must be > 0
	 * @param integer UID on the foreign table, must be > 0
	 */
	public function removeRelation($table, $uidLocal, $uidForeign) {
		if (!$this->isNoneSystemTableNameAllowed($table)) {
			throw new Exception(
				'The table name "' . $table . '" is not allowed.'
			);
		}

		tx_oelib_db::delete(
			$table,
			'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign .
				' AND ' . $this->getDummyColumnName($table) . ' = 1'
		);
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
	 * @param boolean whether a deep clean up should be performed, may be empty
	 */
	public function cleanUp($performDeepCleanUp = false) {
		$this->cleanUpTableSet(false, $performDeepCleanUp);
		$this->cleanUpTableSet(true, $performDeepCleanUp);
		$this->cleanUpFiles();
		$this->cleanUpFolders();
		$this->discardFakeFrontEnd();
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
	 * @param boolean whether to clean up the system tables (true) or
	 *                the non-system test tables (false)
	 * @param boolean whether a deep clean up should be performed, may be empty
	 */
	private function cleanUpTableSet($useSystemTables, $performDeepCleanUp) {
		if ($useSystemTables) {
			$tablesToCleanUp = ($performDeepCleanUp)
				? $this->allowedSystemTables
				: $this->dirtySystemTables;
		} else {
			$tablesToCleanUp = ($performDeepCleanUp)
				? $this->ownAllowedTables
				: $this->dirtyTables;
		}

		foreach ($tablesToCleanUp as $currentTable) {
			$dummyColumnName = $this->getDummyColumnName($currentTable);

			// Runs a delete query for each allowed table. A
			// "one-query-deletes-them-all" approach was tested but we didn't
			// find a working solution for that.
			tx_oelib_db::delete(
				$currentTable,
				$dummyColumnName . ' = 1'
			);

			// Resets the auto increment setting of the current table.
			$this->resetAutoIncrementLazily($currentTable);
		}

		// Resets the list of dirty tables.
		$this->dirtyTables = array();
	}

	/**
	 * Deletes all created dummy files.
	 */
	private function cleanUpFiles() {
		foreach ($this->dummyFiles as $dummyFile) {
			$this->deleteDummyFile($dummyFile);
		}
	}

	/**
	 * Deletes all created dummy folders.
	 */
	private function cleanUpFolders() {
		foreach ($this->dummyFolders as $dummyFolder) {
			$this->deleteDummyFolder($dummyFolder);
		}
	}


	// ----------------------------------------------------------------------
	// File creation and deletion
	// ----------------------------------------------------------------------

	/**
	 * Creates an empty dummy file with a unique file name in the calling
	 * extension's upload directory.
	 *
	 * @param string path of the dummy file to create, relative to the
	 *               calling extension's upload directory, must not be empty
	 *
	 * @return string the absolute path of the created dummy file, will
	 *                not be empty
	 */
	public function createDummyFile($fileName = 'test.txt') {
		$uniqueFileName = $this->getUniqueFileOrFolderPath($fileName);

		if (!@t3lib_div::writeFile($uniqueFileName, '')) {
			throw new Exception($uniqueFileName . ' could not be created.');
		}

		$relativeFileName = $this->getPathRelativeToUploadDirectory(
			$uniqueFileName
		);

		$this->dummyFiles[$relativeFileName] = $relativeFileName;

		return $uniqueFileName;
	}

	/**
	 * Deletes the dummy file specified by the first parameter $fileName.
	 *
	 * @throws exception if the file does not exist
	 * @throws exception if the file was not created with the current instance
	 *                   of the testing framework
	 * @throws exception if the file could not be deleted
	 *
	 * @param string the path to the file to delete relative to
	 *               $this->uploadFolderPath, must not be empty
	 */
	public function deleteDummyFile($fileName) {
		$absolutePathToFile = $this->uploadFolderPath . $fileName;

		if (!file_exists($absolutePathToFile)) {
			throw new Exception(
				'The file "' . $absolutePathToFile . '" which you ' .
					'are trying to delete does not exist.'
			);
		}

		if (!isset($this->dummyFiles[$fileName])) {
			throw new Exception(
				'The file "' . $absolutePathToFile . '" which you ' .
			 		'are trying to delete was not created by this instance of ' .
			 		'the testing framework.'
			);
		}

		if (!@unlink($absolutePathToFile)) {
			throw new Exception(
				'The file "' . $absolutePathToFile . '" could not ' .
					'be deleted.'
			);
		}

		unset($this->dummyFiles[$fileName]);
	}

	/**
	 * Creates a dummy folder with a unique folder name in the calling extension's
	 * upload directory.
	 *
	 * @param string name of the dummy folder to create relative to
	 *               $this->uploadFolderPath, must not be empty
	 *
	 * @return string the absolute path of the created dummy folder, will
	 *                not be empty
	 */
	public function createDummyFolder($folderName) {
		$uniqueFolderName = $this->getUniqueFileOrFolderPath($folderName);

		if (!t3lib_div::mkdir($uniqueFolderName)) {
			throw new Exception($uniqueFolderName . ' could not be created.');
		}

		$relativeUniqueFolderName = $this->getPathRelativeToUploadDirectory(
			$uniqueFolderName
		);

		// Adds the created dummy folder to the top of $this->dummyFolders so
		// it gets deleted before previously created folders through
		// $this->cleanUpFolders(). This is needed for nested dummy folders.
		$this->dummyFolders = array(
				$relativeUniqueFolderName => $relativeUniqueFolderName,
			) + $this->dummyFolders;

		return $uniqueFolderName;
	}

	/**
	 * Deletes the dummy folder specified in the first parameter $folderName.
	 * The folder must be empty (no files or subfolders).
	 *
	 * @throws exception if the folder does not exist
	 * @throws exception if the folder was not created with the current instance
	 *                   of the testing framework
	 * @throws exception if the folder could not be deleted
	 *
	 * @param string the path to the folder to delete relative to
	 *               $this->uploadFolderPath, must not be empty
	 */
	public function deleteDummyFolder($folderName) {
		$absolutePathToFolder = $this->uploadFolderPath . $folderName;

		if (!file_exists($absolutePathToFolder)) {
			throw new Exception(
				'The folder "' . $absolutePathToFolder . '" which you ' .
					'are trying to delete does not exist.'
			);
		}

		if (!isset($this->dummyFolders[$folderName])) {
			throw new Exception(
				'The folder "' . $absolutePathToFolder . '" which you ' .
			 		'are trying to delete was not created by this instance of ' .
			 		'the testing framework.'
			);
		}

		if (!@rmdir($absolutePathToFolder)) {
			throw new Exception(
				'The folder "' . $absolutePathToFolder . '" could not ' .
					'be deleted.'
			);
		}

		unset($this->dummyFolders[$folderName]);
	}

	/**
	 * Returns the absolute path to the upload folder of the extension to test.
	 *
	 * @return string the absolute path to the upload folder of the
	 *                extension to test, including the trailing slash
	 */
	public function getUploadFolderPath() {
		return $this->uploadFolderPath;
	}

	/**
	 * Returns the path relative to the calling extension's upload directory for
	 * a path given in the first parameter $absolutePath.
	 *
	 * @throws exception if the first parameter $absolutePath is not within
	 *                   the calling extension's upload directory
	 *
	 * @param string the absolute path to process, must be within the
	 *               calling extension's upload directory, must not be empty
	 *
	 * @return string the path relative to the calling extension's upload
	 *                directory
	 */
	public function getPathRelativeToUploadDirectory($absolutePath) {
		if (!preg_match(
				'/^' . str_replace('/', '\/', $this->getUploadFolderPath()) . '.*$/',
				$absolutePath
		)) {
			throw new Exception(
				'The first parameter $absolutePath is not within the calling ' .
					'extension\'s upload directory.'
			);
		}

		return mb_substr(
			$absolutePath,
			mb_strlen($this->getUploadFolderPath())
		);
	}

	/**
	 * Returns a unique absolut path of a file or folder.
	 *
	 * @param string the path of a file or folder relative to the calling
	 *               extension's upload directory, must not be empty
	 *
	 * @return string the unique absolut path of a file or folder
	 */
	public function getUniqueFileOrFolderPath($path) {
		if (empty($path)) {
			throw new Exception('The first parameter $path must not be emtpy.');
		}

		if (!self::$fileNameProcessor) {
			self::$fileNameProcessor = t3lib_div::makeInstance(
				't3lib_basicFileFunctions'
			);
		}

		return self::$fileNameProcessor->getUniqueName(
			basename($path),
			$this->uploadFolderPath . t3lib_div::dirname($path)
		);
	}


	// ----------------------------------------------------------------------
	// Functions concerning a fake front end
	// ----------------------------------------------------------------------

	/**
	 * Fakes a TYPO3 front end, using $pageUid as front-end page ID if provided.
	 *
	 * If $pageUid is zero, the UID of the start page of the current domain
	 * will be used as page UID.
	 *
	 * This function creates $GLOBALS['TSFE'] and $GLOBALS['TT'].
	 *
	 * Note: This function does not set TYPO3_MODE to "FE" (because the value of
	 * a constant cannot be changed after it has once been set).
	 *
	 * @throws Exception if $pageUid is < 0
	 *
	 * @param integer UID of a page record to use, must be >= 0
	 *
	 * @return integer the UID of the used front-end page, will be > 0
	 */
	public function createFakeFrontEnd($pageUid = 0) {
		if ($pageUid < 0) {
			throw new Exception('$pageUid must be >= 0.');
		}

		$this->suppressFrontEndCookies();
		$this->discardFakeFrontEnd();

		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_timeTrack');

		$className = t3lib_div::makeInstanceClassName('tslib_fe');
		$frontEnd = new $className(
			$GLOBALS['TYPO3_CONF_VARS'], $pageUid, 0
		);

		// simulates a normal FE without any logged-in FE or BE user
		$frontEnd->beUserLogin = false;
		$frontEnd->workspacePreview = '';
		$frontEnd->initFEuser();
		$frontEnd->determineId();
		$frontEnd->initTemplate();
		$frontEnd->tmpl->getFileName_backPath = PATH_site;
		// $frontEnd->getConfigArray() doesn't work here because the dummy FE
		// page is not required to have a template.
		$frontEnd->config = array();
		$frontEnd->settingLanguage();
		$frontEnd->settingLocale();
		$frontEnd->newCObj();

		$GLOBALS['TSFE'] = $frontEnd;

		$this->hasFakeFrontEnd = true;

		return $GLOBALS['TSFE']->id;
	}

	/**
	 * Discards the fake front end.
	 *
	 * This function nulls out $GLOBALS['TSFE'] and $GLOBALS['TT']. In addition,
	 * any logged-in front-end user will be logged out.
	 *
	 * The page record for the current front end will _not_ be deleted by this
	 * function, though.
	 *
	 * If no fake front end has been created, this function does nothing.
	 */
	public function discardFakeFrontEnd() {
		if (!$this->hasFakeFrontEnd()) {
			return;
		}

		$this->logoutFrontEndUser();

		unset(
			$GLOBALS['TSFE']->tmpl, $GLOBALS['TSFE']->sys_page,
			$GLOBALS['TSFE']->fe_user, $GLOBALS['TSFE']->TYPO3_CONF_VARS,
			$GLOBALS['TSFE']->config, $GLOBALS['TSFE']->TCAcachedExtras,
			$GLOBALS['TSFE']->imagesOnPage, $GLOBALS['TSFE']->cObj,
			$GLOBALS['TSFE']->csConvObj, $GLOBALS['TSFE']->pagesection_lockObj,
			$GLOBALS['TSFE']->pages_lockObj
		);
		$GLOBALS['TSFE'] = null;
		$GLOBALS['TT'] = null;

		$this->hasFakeFrontEnd = false;
	}

	/**
	 * Returns whether this testing framework instance has a fake front end.
	 *
	 * @return boolean true if this instance has a fake front end, false
	 *                 otherwise
	 */
	public function hasFakeFrontEnd() {
		return $this->hasFakeFrontEnd;
	}

	/**
	 * Makes sure that no FE login cookies will be sent.
	 */
	private function suppressFrontEndCookies() {
		$_POST['FE_SESSION_KEY'] = '';
		$_GET['FE_SESSION_KEY'] = '';
		$GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie'] = 1;
	}


	// ----------------------------------------------------------------------
	// FE user activities
	// ----------------------------------------------------------------------

	/**
	 * Fakes that a front-end user has logged in.
	 *
	 * If a front-end user currently is logged in, he/she will be logged out
	 * first.
	 *
	 * @throws Exception if no front end has been created
	 *
	 * @param integer UID of the FE user, must be > 0
	 */
	public function loginFrontEndUser($userId) {
		if (intval($userId) == 0) {
			throw new Exception('The user ID must be > 0.');
		}
		if (!$this->hasFakeFrontEnd()) {
			throw new Exception(
				'Please create a front end before calling loginFrontEndUser.'
			);
		}

		if ($this->isLoggedIn()) {
			$this->logoutFrontEndUser();
		}

		$this->suppressFrontEndCookies();

		$GLOBALS['TSFE']->fe_user->createUserSession(array());
		$GLOBALS['TSFE']->fe_user->user
			= $GLOBALS['TSFE']->fe_user->getRawUserByUid($userId);
		$GLOBALS['TSFE']->fe_user->fetchGroupData();
		$GLOBALS['TSFE']->loginUser = 1;
	}

	/**
	 * Logs out the current front-end user.
	 *
	 * If no front-end user is logged in, this function does nothing.
	 *
	 * @throws Exception if no front end has been created
	 */
	public function logoutFrontEndUser() {
		if (!$this->hasFakeFrontEnd()) {
			throw new Exception(
				'Please create a front end before calling logoutFrontEndUser.'
			);
		}
		if (!$this->isLoggedIn()) {
			return;
		}

		$this->suppressFrontEndCookies();

		$GLOBALS['TSFE']->fe_user->logoff();
		$GLOBALS['TSFE']->loginUser = 0;
	}

	/**
	 * Checks whether a FE user is logged in.
	 *
	 * @throws Exception if no front end has been created
	 *
	 * @return boolean true if a FE user is logged in, false otherwise
	 */
	public function isLoggedIn() {
		if (!$this->hasFakeFrontEnd()) {
			throw new Exception(
				'Please create a front end before calling isLoggedIn.'
			);
		}

		return (boolean) $GLOBALS['TSFE']->loginUser;
	}


	// ----------------------------------------------------------------------
	// Various helper functions
	// ----------------------------------------------------------------------

	/**
	 * Returns a list of all table names that are available in the current
	 * database. There is no check whether these tables are accessible for the
	 * testing framework - this has to be done separately!
	 *
	 * Note: Since TYPO3 4.2, the t3lib_DB::admin_get_tables() method returns
	 * way more data per table and not just the table name. This method deals
	 * with this by just using the keys from the returned array.
	 *
	 * @return array list of table names
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1726
	 * @see http://typo3.svn.sourceforge.net/viewvc/typo3/TYPO3core/branches/TYPO3_4-2/t3lib/class.t3lib_db.php?r1=3326&r2=3365
	 */
	private function getListOfAllTables() {
		if (empty(self::$allTablesCache)) {
			self::$allTablesCache = array_keys(
				$GLOBALS['TYPO3_DB']->admin_get_tables()
			);
		}

		return self::$allTablesCache;
	}

	/**
	 * Generates a list of allowed tables to which this instance of the testing
	 * framework has access to create/remove test records.
	 *
	 * The generated list is based on the list of all tables that TYPO3 can
	 * access (which will be all tables in this database), filtered by prefix of
	 * the extension to test.
	 *
	 * The array with the allowed table names is written directly to
	 * $this->ownAllowedTables.
	 */
	private function createListOfOwnAllowedTables() {
		$this->ownAllowedTables = array();
		$allTables = $this->getListOfAllTables();
		$length = strlen($this->tablePrefix);

		foreach ($allTables as $currentTable) {
			if (substr_compare(
					$this->tablePrefix, $currentTable, 0, $length
				) == 0
			) {
				$this->ownAllowedTables[] = $currentTable;
			}
		}
	}

	/**
	 * Generates a list of additional allowed tables to which this instance of
	 * the testing framework has access to create/remove test records.
	 *
	 * The generated list is based on the list of all tables that TYPO3 can
	 * access (which will be all tables in this database), filtered by the
	 * prefixes of additional extensions.
	 *
	 * The array with the allowed table names is written directly to
	 * $this->additionalAllowedTables.
	 */
	private function createListOfAdditionalAllowedTables() {
		$allTables = implode(',', $this->getListOfAllTables());
		$additionalTablePrefixes = implode('|', $this->additionalTablePrefixes);

		$matches = array();

		preg_match_all(
			'/(('.$additionalTablePrefixes.')_[a-z0-9]+[a-z0-9_]*)(,|$)/',
			$allTables,
			$matches
		);

		if (isset($matches[1])) {
			$this->additionalAllowedTables = $matches[1];
		}
	}

	/**
	 * Checks whether the given table name is in the list of allowed tables for
	 * this instance of the testing framework.
	 *
	 * @param string the name of the table to check, must not be empty
	 *
	 * @return boolean true if the name of the table is in the list of
	 *                 allowed tables, false otherwise
	 */
	private function isOwnTableNameAllowed($table) {
		return in_array($table, $this->ownAllowedTables);
	}

	/**
	 * Checks whether the given table name is in the list of additional allowed
	 * tables for this instance of the testing framework.
	 *
	 * @param string the name of the table to check, must not be empty
	 *
	 * @return boolean true if the name of the table is in the list of
	 *                 additional allowed tables, false otherwise
	 */
	private function isAdditionalTableNameAllowed($table) {
		return in_array($table, $this->additionalAllowedTables);
	}

	/**
	 * Checks whether the given table name is in the list of allowed
	 * system tables for this instance of the testing framework.
	 *
	 * @param string the name of the table to check, must not be empty
	 *
	 * @return boolean true if the name of the table is in the list of
	 *                 allowed system tables, false otherwise
	 */
	private function isSystemTableNameAllowed($table) {
		return in_array($table, $this->allowedSystemTables);
	}

	/**
	 * Checks whether the given table name is in the list of allowed tables or
	 * additional allowed tables for this instance of the testing framework.
	 *
	 * @param string the name of the table to check, must not be empty
	 *
	 * @return boolean true if the name of the table is in the list of
	 *                 allowed tables or additional allowed tables, false
	 *                 otherwise
	 */
	private function isNoneSystemTableNameAllowed($table) {
		return $this->isOwnTableNameAllowed($table)
			|| $this->isAdditionalTableNameAllowed($table);
	}

	/**
	 * Checks whether the given table name is in the list of allowed tables,
	 * additional allowed tables or allowed system tables.
	 *
	 * @param string the name of the table to check, must not be empty
	 *
	 * @return boolean true if the name of the table is in the list of
	 *                 allowed tables, additional allowed tables or allowed
	 *                 system tables, false otherwise
	 */
	private function isTableNameAllowed($table) {
		return $this->isNoneSystemTableNameAllowed($table)
			|| $this->isSystemTableNameAllowed($table);
	}

	/**
	 * Returns the name of the column that marks a record as a dummy record.
	 *
	 * On most tables this is "is_dummy_record", but on system tables like
	 * "pages" or "fe_users", the column is called "tx_oelib_dummy_record".
	 *
	 * On additional tables, the column is built using $this->tablePrefix as
	 * prefix e.g. "tx_seminars_is_dummy_record" if $this->tablePrefix =
	 * "tx_seminars".
	 *
	 * @param string the table name to look up, must not be empty
	 *
	 * @return string the name of the column that marks a record as dummy record
	 */
	private function getDummyColumnName($table) {
		$result = 'is_dummy_record';

		if ($this->isSystemTableNameAllowed($table)) {
			$result = 'tx_oelib_' . $result;
		} elseif ($this->isAdditionalTableNameAllowed($table)) {
			$result = $this->tablePrefix . '_' . $result;
		}

		return $result;
	}

	/**
	 * Retrieves a database result row as an associative array.
	 *
	 * @param mixed either a DB query result resource or false (for failed
	 *              queries)
	 *
	 * @return array the database result as an associative array
	 */
	public function getAssociativeDatabaseResult($queryResult) {
		if (!$queryResult) {
			throw new Exception(DATABASE_QUERY_ERROR);
		}

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($queryResult);
		if (!$row) {
			throw new Exception(DATABASE_RESULT_ERROR);
		}

		return $row;
	}

	/**
	 * Counts the dummy records in the table given by the first parameter $table
	 * that match a given WHERE clause.
	 *
	 * @param string the name of the table to query, must not be empty
	 * @param string the WHERE part of the query, may be empty (all
	 * records will be counted in that case)
	 *
	 * @return integer the number of records that have been found, will be >= 0
	 */
	public function countRecords($table, $whereClause = '') {
		if (!$this->isTableNameAllowed($table)) {
			throw new Exception(
				'The given table name is invalid. This means it is either ' .
					'empty or not in the list of allowed tables.'
			);
		}

		$whereForDummyColumn = $this->getDummyColumnName($table) . ' = 1';
		$compoundWhereClause = ($whereClause != '')
			? '(' . $whereClause . ') AND ' . $whereForDummyColumn
			: $whereForDummyColumn;

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS number',
			$table,
			$compoundWhereClause
		);

		$row = $this->getAssociativeDatabaseResult($dbResult);
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		return intval($row['number']);
	}

	/**
	 * Checks whether there are any dummy records in the table given by the
	 * first parameter $table that match a given WHERE clause.
	 *
	 * @param string the name of the table to query, must not be empty
	 * @param string the WHERE part of the query, may be empty (all
	 *               records will be counted in that case)
	 *
	 * @return boolean true if there is at least one matching record,
	 *                 false otherwise
	 */
	public function existsRecord($table, $whereClause = '') {
		return ($this->countRecords($table, $whereClause) > 0);
	}

	/**
	 * Checks whether there is a dummy record in the table given by the first
	 * parameter $table that has the given UID.
	 *
	 * @param string the name of the table to query, must not be empty
	 * @param integer the UID of the record to look up, must be > 0
	 *
	 * @return boolean true if there is a matching record, false otherwise
	 */
	public function existsRecordWithUid($table, $uid) {
		if ($uid <= 0) {
			throw new Exception('$uid must be > 0.');
		}

		return ($this->countRecords($table, 'uid = ' . $uid) > 0);
	}

	/**
	 * Checks whether there is exactly one dummy record in the table given by
	 * the first parameter $table that matches a given WHERE clause.
	 *
	 * @param string the name of the table to query, must not be empty
	 * @param string the WHERE part of the query, may be empty (all
	 *               records will be counted in that case)
	 *
	 * @return boolean true if there is exactly one matching record,
	 *                 false otherwise
	 */
	public function existsExactlyOneRecord($table, $whereClause = '') {
		return ($this->countRecords($table, $whereClause) == 1);
	}

	/**
	 * Eagerly resets the auto increment value for a given table to the highest
	 * existing UID + 1.
	 *
	 * @param string the name of the table on which we're going to reset
	 *               the auto increment entry, must not be empty
	 *
	 * @see resetAutoIncrementLazily
	 */
	public function resetAutoIncrement($table) {
		if (!$this->isTableNameAllowed($table)) {
			throw new Exception(
				'The given table name is invalid. This means it is either ' .
					'empty or not in the list of allowed tables.'
			);
		}

		// Checks whether the current table qualifies for this method. If there
		// is no column "uid" that has the "auto_increment" flag set, we should
		// not try to reset this inexistent auto increment index to avoid DB
		// errors.
		if (!tx_oelib_db::tableHasColumnUid($table)) {
			return;
		}

		$newAutoIncrementValue = $this->getMaximumUidFromTable($table) + 1;

		// Updates the auto increment index for this table. The index will be
		// set to one UID above the highest existing UID.
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'ALTER TABLE ' . $table . ' AUTO_INCREMENT=' .
				$newAutoIncrementValue . ';'
		);
		if (!$dbResult) {
			throw new Exception(DATABASE_QUERY_ERROR);
		}
	}

	/**
	 * Resets the auto increment value for a given table to the highest existing
	 * UID + 1 if the current auto increment value is higher than a certain
	 * threshold over the current maximum UID.
	 *
	 * The threshhold is 100 by default and can be set using
	 * setResetAutoIncrementThreshold.
	 *
	 * @param string the name of the table on which we're going to reset
	 *               the auto increment entry, must not be empty
	 *
	 * @see resetAutoIncrement
	 */
	public function resetAutoIncrementLazily($table) {
		if (!$this->isTableNameAllowed($table)) {
			throw new Exception(
				'The given table name is invalid. This means it is either ' .
					'empty or not in the list of allowed tables.'
			);
		}

		// Checks whether the current table qualifies for this method. If there
		// is no column "uid" that has the "auto_increment" flag set, we should
		// not try to reset this inexistent auto increment index to avoid
		// database errors.
		if (!tx_oelib_db::tableHasColumnUid($table)) {
			return;
		}

		if ($this->getAutoIncrement($table) >
			($this->getMaximumUidFromTable($table)
				+ $this->resetAutoIncrementThreshold)
		) {
			$this->resetAutoIncrement($table);
		}
	}

	/**
	 * Sets the threshold for resetAutoIncrementLazily.
	 *
	 * @param integer threshold, must be > 0
	 *
	 * @see resetAutoIncrementLazily
	 */
	public function setResetAutoIncrementThreshold($threshold) {
		if ($threshold <= 0) {
			throw new Exception('$threshold must be > 0.');
		}

		$this->resetAutoIncrementThreshold = $threshold;
	}

	/**
	 * Reads the highest UID for a database table.
	 *
	 * This function may only be called after that the provided table name
	 * has been checked to be non-empty, valid and pointing to an existing
	 * database table that has the "uid" column.
	 *
	 * @param string the name of an existing table that has the "uid" column
	 *
	 * @return integer the highest UID from this table, will be >= 0
	 */
	private function getMaximumUidFromTable($table) {
		$row = $this->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->sql_query(
				'SELECT MAX(uid) AS uid FROM ' . $table . ';'
			)
		);

		return $row['uid'];
	}

	/**
	 * Reads the current auto increment value for a given table.
	 *
	 * This function is only valid for tables that actually have an auto
	 * increment value.
	 *
	 * @param string the name of the table for which the auto increment
	 *               value should be retrieved, must not be empty
	 *
	 * @return integer the current auto_increment value of table $table,
	 *                 will be > 0
	 */
	public function getAutoIncrement($table) {
		if (!$this->isTableNameAllowed($table)) {
			throw new Exception(
				'The given table name is invalid. This means it is either ' .
					'empty or not in the list of allowed tables.'
			);
		}

		$row = $this->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->sql_query(
				'SHOW TABLE STATUS WHERE Name=\'' . $table . '\';'
			)
		);

		return $row['Auto_increment'];
	}

	/**
	 * Returns the list of allowed table names.
	 *
	 * @return array all allowed table names for this instance of the
	 *               testing framework
	 */
	public function getListOfOwnAllowedTableNames() {
		return $this->ownAllowedTables;
	}

	/**
	 * Returns the list of additional allowed table names.
	 *
	 * @return array all additional allowed table names for this instance
	 *               of the testing framework, may be empty
	 */
	public function getListOfAdditionalAllowedTableNames() {
		return $this->additionalAllowedTables;
	}

	/**
	 * Puts one or multiple table names on the list of dirty tables (which
	 * represents a list of tables that were used for testing and contain dummy
	 * records and thus are called "dirty" until the next clean up).
	 *
	 * @param string the table name or a comma-separated list of table
	 *               names to put on the list of dirty tables, must not
	 *               be empty
	 */
	public function markTableAsDirty($tableNames) {
		foreach (explode(',', $tableNames) as $currentTable) {
			if ($this->isNoneSystemTableNameAllowed($currentTable)) {
				$this->dirtyTables[$currentTable] = $currentTable;
			} elseif ($this->isSystemTableNameAllowed($currentTable)) {
				$this->dirtySystemTables[$currentTable] = $currentTable;
			} else {
				throw new Exception(
					'The table name "' . $currentTable . '" is not allowed for' .
						' markTableAsDirty.'
				);
			}
		}
	}

	/**
	 * Returns the list of tables that contain dummy records from testing. These
	 * tables are called "dirty tables" as they need to be cleaned up.
	 *
	 * @return array associative array containing names of database tables
	 *               that need to be cleaned up
	 */
	public function getListOfDirtyTables() {
		return $this->dirtyTables;
	}

	/**
	 * Returns the list of system tables that contain dummy records from
	 * testing. These tables are called "dirty tables" as they need to be
	 * cleaned up.
	 *
	 * @return array associative array containing names of system
	 *               database tables that need to be cleaned up
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
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1423
	 *
	 * @param string the relation table, must not be empty
	 * @param integer UID of the local table, must be > 0
	 *
	 * @return integer the next sorting value to use (> 0)
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
	 * @param string the name of of table to check, must not be empty
	 *
	 * @return boolean true if the table is registered in TYPO3, false otherwise
	 */
	private function isTable($table) {
		if ($table == '') {
			throw new Exception('$table must not be empty.');
		}

		return (in_array($table, $this->getListOfAllTables()));
	}

	/**
	 * Clears all static caches of the testing framework.
	 *
	 * This function usually should only be called when testing the testing
	 * framework.
	 */
	public function clearCaches() {
		self::$allTablesCache = array();
	}

	/**
	 * Returns the TCA for a certain table.
	 *
	 * @param string the table name to look up, must not be empty
	 *
	 * @return array associative array with the TCA description for this table
	 */
	public function getTcaForTable($tableName) {
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

	/**
	 * Updates an integer field of a database table by one. This is mainly needed
	 * for counting up the relation counter when creating a database relation.
	 *
	 * The field to update must be of type integer.
	 *
	 * @param string name of the table, must not be empty
	 * @param integer the UID of the record to modify, must be > 0
	 * @param string the field name of the field to modify, must not be empty
	 */
	public function increaseRelationCounter($tableName, $uid, $fieldName) {
		if (!$this->isTableNameAllowed($tableName)) {
			throw new Exception(
				'The table name "' . $tableName . '" is invalid. This means ' .
					'it is either empty or not in the list of allowed tables.'
			);
		}
		if (!tx_oelib_db::tableHasColumn($tableName, $fieldName)) {
			throw new Exception(
				'The table ' . $tableName . ' has no column ' . $fieldName . '.'
			);
		}

		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'UPDATE ' . $tableName . ' SET ' . $fieldName . '=' .
			$fieldName . '+1 WHERE uid=' . $uid
		);
		if (!$dbResult) {
			throw new Exception(DATABASE_QUERY_ERROR);
		}

		if ($GLOBALS['TYPO3_DB']->sql_affected_rows() == 0) {
			throw new Exception(
				'The table ' . $tableName .
					' does not contain a record with UID ' . $uid . '.'
			);
		}

		$this->markTableAsDirty($tableName);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_testingFramework.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_testingFramework.php']);
}
?>