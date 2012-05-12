<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2011 Mario Rimann (typo3-coding@rimann.org)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

if (!defined('OELIB_TESTTABLE')) {
	define('OELIB_TESTTABLE', 'tx_oelib_test');
}
if (!defined('OELIB_TESTTABLE_MM')) {
	define('OELIB_TESTTABLE_MM', 'tx_oelib_test_article_mm');
}

/**
 * Testcase for the tx_oelib_testingFramework class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_TestingFrameworkTest extends tx_phpunit_testcase {
	/** @var tx_oelib_testingFramework */
	private $fixture;

	/**
	 * @var string absolute path to a "foreign" file which was created for test
	 *             purposes and which should be deleted in tearDown(); this is
	 *             needed for testDeleteDummyFileWithForeignFileThrowsException
	 */
	private $foreignFileToDelete = '';

	/**
	 * @var string absolute path to a "foreign" folder which was created for
	 *             test purposes and which should be deleted in tearDown();
	 *             this is needed for
	 *             testDeleteDummyFolderWithForeignFolderThrowsException
	 */
	private $foreignFolderToDelete = '';

	/**
	 * backed-up extension configuration of the TYPO3 configuration variables
	 *
	 * @var array
	 */
	private $extConfBackup = array();

	/**
	 * backed-up T3_VAR configuration
	 *
	 * @var array
	 */
	private $t3VarBackup = array();

	public function setUp() {
		$this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		$this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];

		$this->fixture = new tx_oelib_testingFramework(
			'tx_oelib', array('user_oelibtest')
		);
	}

	public function tearDown() {
		$this->fixture->setResetAutoIncrementThreshold(1);
		$this->fixture->cleanUp();
		$this->fixture->purgeHooks();
		$this->deleteForeignFile();
		$this->deleteForeignFolder();

		unset($this->fixture);

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
		$GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
	}


	// ---------------------------------------------------------------------
	// Utility functions.
	// ---------------------------------------------------------------------

	/**
	 * Returns the sorting value of the relation between the local UID given by
	 * the first parameter $uidLocal and the foreign UID given by the second
	 * parameter $uidForeign.
	 *
	 * @param integer $uidLocal
	 *        the UID of the local record, must be > 0
	 * @param integer $uidForeign
	 *        the UID of the foreign record, must be > 0
	 *
	 * @return integer the sorting value of the relation
	 */
	private function getSortingOfRelation($uidLocal, $uidForeign) {
		$row = tx_oelib_db::selectSingle(
			'sorting',
			OELIB_TESTTABLE_MM,
			'uid_local = ' . $uidLocal.' AND uid_foreign = ' . $uidForeign
		);

		return intval($row['sorting']);
	}

	/**
	 * Checks whether the extension user_oelibtest is currently loaded and lets
	 * a test fail if the extension is not loaded.
	 */
	private function checkIfExtensionUserOelibtestIsLoaded() {
		if (!t3lib_extMgm::isLoaded('user_oelibtest')) {
			$this->fail(
				'Extension user_oelibtest is not installed but needs to be ' .
					'installed! Please install it from EXT:oelib/tests/' .
					'fixtures/user_oelibtest.t3x.'
			);
		}
	}

	/**
	 * Checks whether the extension user_oelibtest2 is currently loaded and lets
	 * a test fail if the extension is not loaded.
	 */
	private function checkIfExtensionUserOelibtest2IsLoaded() {
		if (!t3lib_extMgm::isLoaded('user_oelibtest')) {
			$this->fail(
				'Extension user_oelibtest2 is not installed but needs to be ' .
					'installed! Please install it from EXT:oelib/tests/' .
					'fixtures/user_oelibtest2.t3x.'
			);
		}
	}

	/**
	 * Deletes a "foreign" file which was created for test purposes.
	 */
	private function deleteForeignFile() {
		if ($this->foreignFileToDelete == '') {
			return;
		}

		@unlink($this->foreignFileToDelete);
		$this->foreignFileToDelete = '';
	}

	/**
	 * Deletes a "foreign" folder which was created for test purposes.
	 */
	private function deleteForeignFolder() {
		if ($this->foreignFolderToDelete == '') {
			return;
		}

		t3lib_div::rmdir($this->foreignFolderToDelete);
		$this->foreignFolderToDelete = '';
	}

	/**
	 * Marks a test as skipped if the ZIPArchive class is not available in the
	 * PHP installation.
	 */
	private function markAsSkippedForNoZipArchive() {
		try {
			$this->fixture->checkForZipArchive();
		} catch (Exception $exception) {
			$this->markTestSkipped($exception->getMessage());
		}
	}


	// ---------------------------------------------------------------------
	// Tests regarding markTableAsDirty()
	// ---------------------------------------------------------------------

	public function testMarkTableAsDirty() {
		$this->assertSame(
			array(),
			$this->fixture->getListOfDirtyTables()
		);

		$this->fixture->createRecord(OELIB_TESTTABLE, array());
		$this->assertSame(
			array(
				OELIB_TESTTABLE => OELIB_TESTTABLE
			),
			$this->fixture->getListOfDirtyTables()
		);
	}

	public function testMarkTableAsDirtyWillCleanUpANonSystemTable() {
		$uid = tx_oelib_db::insert(
			OELIB_TESTTABLE, array('is_dummy_record' => 1)
		);

		$this->fixture->markTableAsDirty(OELIB_TESTTABLE);
		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid=' . $uid)
		);
	}

	public function testMarkTableAsDirtyWillCleanUpASystemTable() {
		$uid = tx_oelib_db::insert (
			'pages', array('tx_oelib_is_dummy_record' => 1)
		);

		$this->fixture->markTableAsDirty('pages');
		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords('pages', 'uid=' . $uid)
		);
	}

	public function testMarkTableAsDirtyWillCleanUpAdditionalAllowedTable() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$uid = tx_oelib_db::insert(
			'user_oelibtest_test', array('tx_oelib_is_dummy_record' => 1)
		);

		$this->fixture->markTableAsDirty('user_oelibtest_test');
		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords('user_oelibtest_test', 'uid=' . $uid)
		);
	}

	public function testMarkTableAsDirtyFailsOnInexistentTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "tx_oelib_DOESNOTEXIST" is not allowed for markTableAsDirty.'
		);
		$this->fixture->markTableAsDirty('tx_oelib_DOESNOTEXIST');
	}

	public function testMarkTableAsDirtyFailsOnNotAllowedSystemTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "sys_domain" is not allowed for markTableAsDirty.'
		);
		$this->fixture->markTableAsDirty('sys_domain');
	}

	public function testMarkTableAsDirtyFailsOnForeignTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "tx_seminars_seminars" is not allowed for markTableAsDirty.'
		);
		$this->fixture->markTableAsDirty('tx_seminars_seminars');
	}

	public function testMarkTableAsDirtyFailsWithEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "" is not allowed for markTableAsDirty.'
		);
		$this->fixture->markTableAsDirty('');
	}

	public function testMarkTableAsDirtyAcceptsCommaSeparatedListOfTableNames() {
		$this->fixture->markTableAsDirty(OELIB_TESTTABLE.','.OELIB_TESTTABLE_MM);
		$this->assertSame(
			array(
				OELIB_TESTTABLE => OELIB_TESTTABLE,
				OELIB_TESTTABLE_MM => OELIB_TESTTABLE_MM
			),
			$this->fixture->getListOfDirtyTables()
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createRecord()
	// ---------------------------------------------------------------------

	public function testCreateRecordOnValidTableWithNoData() {
		$this->assertNotSame(
			0,
			$this->fixture->createRecord(OELIB_TESTTABLE, array())
		);
	}

	public function testCreateRecordWithValidData() {
		$title = 'TEST record';
		$uid = $this->fixture->createRecord(
			OELIB_TESTTABLE,
			array(
				'title' => $title
			)
		);
		$this->assertNotSame(
			0,
			$uid
		);

		$row = tx_oelib_db::selectSingle(
			'title',
			OELIB_TESTTABLE,
			'uid = ' . $uid
		);

		$this->assertSame(
			$title,
			$row['title']
		);
	}

	public function testCreateRecordOnInvalidTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "tx_oelib_DOESNOTEXIST" is not allowed.'
		);
		$this->fixture->createRecord('tx_oelib_DOESNOTEXIST', array());
	}

	public function testCreateRecordWithEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "" is not allowed.'
		);
		$this->fixture->createRecord('', array());
	}

	public function testCreateRecordWithUidFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('uid' => 99999)
		);
	}

	public function testCreateRecordOnValidAdditionalAllowedTableWithValidDataSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$title = 'TEST record';
		$this->fixture->createRecord(
			'user_oelibtest_test',
			array(
				'title' => $title
			)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding changeRecord()
	// ---------------------------------------------------------------------

	public function testChangeRecordWithExistingRecord() {
		$uid = $this->fixture->createRecord(
			OELIB_TESTTABLE,
			array('title' => 'foo')
		);

		$this->fixture->changeRecord(
			OELIB_TESTTABLE,
			$uid,
			array('title' => 'bar')
		);

		$row = tx_oelib_db::selectSingle(
			'title',
			OELIB_TESTTABLE,
			'uid = ' . $uid
		);

		$this->assertSame(
			'bar',
			$row['title']
		);
	}

	public function testChangeRecordFailsOnForeignTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table "tx_seminars_seminars" is not on the lists with allowed tables.'
		);
		$this->fixture->changeRecord(
			'tx_seminars_seminars',
			99999,
			array('title' => 'foo')
		);
	}

	public function testChangeRecordFailsOnInexistentTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table "tx_oelib_DOESNOTEXIST" is not on the lists with allowed tables.'
		);
		$this->fixture->changeRecord(
			'tx_oelib_DOESNOTEXIST',
			99999,
			array('title' => 'foo')
		);
	}

	public function testChangeRecordOnAllowedSystemTableForPages() {
		$pid = $this->fixture->createFrontEndPage(0, array('title' => 'foo'));

		$this->fixture->changeRecord(
			'pages',
			$pid,
			array('title' => 'bar')
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords('pages', 'uid='.$pid.' AND title="bar"')
		);
	}

	public function testChangeRecordOnAllowedSystemTableForContent() {
		$pid = $this->fixture->createFrontEndPage(0, array('title' => 'foo'));
		$uid = $this->fixture->createContentElement(
			$pid,
			array('titleText' => 'foo')
		);

		$this->fixture->changeRecord(
			'tt_content',
			$uid,
			array('titleText' => 'bar')
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords('tt_content', 'uid=' . $uid.' AND titleText="bar"')
		);
	}

	public function testChangeRecordFailsOnOtherSystemTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table "sys_domain" is not on the lists with allowed tables.'
		);
		$this->fixture->changeRecord(
			'sys_domain',
			1,
			array('title' => 'bar')
		);
	}

	public function testChangeRecordOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$uid = $this->fixture->createRecord(
			'user_oelibtest_test',
			array('title' => 'foo')
		);

		$this->fixture->changeRecord(
			'user_oelibtest_test',
			$uid,
			array('title' => 'bar')
		);
	}

	public function testChangeRecordFailsWithUidZero() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $uid must not be zero.'
		);
		$this->fixture->changeRecord(OELIB_TESTTABLE, 0, array('title' => 'foo'));
	}

	public function testChangeRecordFailsWithEmptyData() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The array with the new record data must not be empty.'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());

		$this->fixture->changeRecord(
			OELIB_TESTTABLE, $uid, array()
		);
	}

	public function testChangeRecordFailsWithUidFieldInRecordData() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $recordData must not contain changes to the UID of a record.'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());

		$this->fixture->changeRecord(
			OELIB_TESTTABLE, $uid, array('uid' => '55742')
		);
	}

	public function testChangeRecordFailsWithDummyRecordFieldInRecordData() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $recordData must not contain changes to the field ' .
				'"is_dummy_record". It is impossible to convert a dummy record into a regular record.'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());

		$this->fixture->changeRecord(
			OELIB_TESTTABLE, $uid, array('is_dummy_record' => 0)
		);
	}

	public function testChangeRecordFailsOnInexistentRecord() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());
		$this->setExpectedException(
			'BadMethodCallException',
			'There is no record with UID ' . ($uid + 1) . ' on table "' . OELIB_TESTTABLE . '".'
		);

		$this->fixture->changeRecord(
			OELIB_TESTTABLE, $uid + 1, array('title' => 'foo')
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding deleteRecord()
	// ---------------------------------------------------------------------

	public function testDeleteRecordOnValidDummyRecord() {
		// Creates and directly destroys a dummy record.
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid);

		// Checks whether the record really was removed from the database.
		$this->assertSame(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid=' . $uid)
		);
	}

	public function testDeleteRecordOnValidDummyRecordOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		// Creates and directly destroys a dummy record.
		$uid = $this->fixture->createRecord('user_oelibtest_test', array());
		$this->fixture->deleteRecord('user_oelibtest_test', $uid);
	}

	public function testDeleteRecordOnInexistentRecord() {
		$uid = 99999;

		// Checks that the record is inexistent before testing on it.
		$this->assertSame(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid=' . $uid)
		);

		// Runs our delete function - it should run through even when it can't
		// delete a record.
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid);
	}

	public function testDeleteRecordOnForeignTable() {
		$table = 'tx_seminars_seminars';
		$uid = 99999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->deleteRecord($table, $uid);
	}

	public function testDeleteRecordOnInexistentTable() {
		$table = 'tx_oelib_DOESNOTEXIST';
		$uid = 99999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->deleteRecord($table, $uid);
	}

	public function testDeleteRecordWithEmptyTableName() {
		$table = '';
		$uid = 99999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->deleteRecord($table, $uid);
	}

	public function testDeleteRecordOnNonTestRecordNotDeletesRecord() {
		// Create a new record that looks like a real record, i.e. the
		// is_dummy_record flag is set to 0.
		$uid = tx_oelib_db::insert(
			OELIB_TESTTABLE,
			array(
				'title' => 'TEST',
				'is_dummy_record' => 0
			)
		);

		// Runs our delete method which should NOT affect the record created
		// above.
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid);

		// Remembers whether the record still exists.
		$counter = tx_oelib_db::count(OELIB_TESTTABLE, 'uid = ' . $uid);

		// Deletes the record as it will not be caught by the clean up function.
		tx_oelib_db::delete(
			OELIB_TESTTABLE,
			'uid = ' . $uid . ' AND is_dummy_record = 0'
		);

		// Checks whether the record still had existed.
		$this->assertSame(
			1,
			$counter
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createRelation()
	// ---------------------------------------------------------------------

	public function testCreateRelationWithValidData() {
		$uidLocal = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->fixture->createRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);

		// Checks whether the record really exists.
		$this->assertSame(
			1,
			$this->fixture->countRecords(
				OELIB_TESTTABLE_MM,
				'uid_local=' . $uidLocal.' AND uid_foreign=' . $uidForeign
			)
		);
	}

	public function testCreateRelationWithValidDataOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$uidLocal = $this->fixture->createRecord('user_oelibtest_test');
		$uidForeign = $this->fixture->createRecord('user_oelibtest_test');

		$this->fixture->createRelation(
			'user_oelibtest_test_article_mm', $uidLocal, $uidForeign
		);
	}

	public function testCreateRelationWithInvalidTable() {
		$table = 'tx_oelib_test_DOESNOTEXIST_mm';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->createRelation($table, $uidLocal, $uidForeign);
	}

	public function testCreateRelationWithEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "" is not allowed.'
		);
		$this->fixture->createRelation('', 99999, 199999);
	}

	public function testCreateRelationWithZeroFirstUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidLocal must be an integer > 0, but actually is "0"'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(OELIB_TESTTABLE_MM, 0, $uid);
	}

	public function testCreateRelationWithZeroSecondUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidForeign must be an integer > 0, but actually is "0"'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(OELIB_TESTTABLE_MM, $uid, 0);
	}

	public function testCreateRelationWithNegativeFirstUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidLocal must be an integer > 0, but actually is "-1"'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(OELIB_TESTTABLE_MM, -1, $uid);
	}

	public function testCreateRelationWithNegativeSecondUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidForeign must be an integer > 0, but actually is "-1"'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(OELIB_TESTTABLE_MM, $uid, -1);
	}


	public function testCreateRelationWithAutomaticSorting() {
		$uidLocal = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);
		$previousSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
		$this->assertGreaterThan(
			0,
			$previousSorting
		);


		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);
		$nextSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
		$this->assertSame(
			$previousSorting + 1,
			$nextSorting
		);
	}

	public function testCreateRelationWithManualSorting() {
		$uidLocal = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);
		$sorting = 99999;

		$this->fixture->createRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign, $sorting
		);

		$this->assertSame(
			$sorting,
			$this->getSortingOfRelation($uidLocal, $uidForeign)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createRelationFromTca()
	// ---------------------------------------------------------------------

	public function testCreateRelationAndUpdateCounterIncreasesZeroValueCounterByOne() {
		$firstRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->fixture->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'related_records'
		);

		$row = tx_oelib_db::selectSingle(
			'related_records',
			OELIB_TESTTABLE,
			'uid = ' . $firstRecordUid
		);

		$this->assertSame(
			1,
			intval($row['related_records'])
		);
	}

	public function testCreateRelationAndUpdateCounterIncreasesNonZeroValueCounterToOne() {
		$firstRecordUid = $this->fixture->createRecord(
			OELIB_TESTTABLE,
			array('related_records' => 1)
		);
		$secondRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->fixture->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'related_records'
		);

		$row = tx_oelib_db::selectSingle(
			'related_records',
			OELIB_TESTTABLE,
			'uid = ' . $firstRecordUid
		);

		$this->assertSame(
			2,
			intval($row['related_records'])
		);
	}

	public function testCreateRelationAndUpdateCounterCreatesRecordInRelationTable() {
		$firstRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->fixture->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'related_records'
		);

		$count = $this->fixture->countRecords(
			OELIB_TESTTABLE_MM,
			'uid_local=' . $firstRecordUid
		);
		$this->assertSame(
			1,
			$count
		);
	}


	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesCounter() {
		$firstRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->fixture->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'bidirectional'
		);

		$row = tx_oelib_db::selectSingle(
			'bidirectional',
			OELIB_TESTTABLE,
			'uid = ' . $firstRecordUid
		);

		$this->assertSame(
			1,
			intval($row['bidirectional'])
		);
	}

	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesOppositeFieldCounterInForeignTable() {
		$firstRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->fixture->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'bidirectional'
		);

		$row = tx_oelib_db::selectSingle(
			'related_records',
			OELIB_TESTTABLE,
			'uid = ' . $secondRecordUid
		);

		$this->assertSame(
			1,
			intval($row['related_records'])
		);
	}

	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterWithBidirectionalRelationCreatesRecordInRelationTable() {
		$firstRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->fixture->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'bidirectional'
		);

		$count = $this->fixture->countRecords(
			OELIB_TESTTABLE_MM,
			'uid_local=' . $secondRecordUid . ' AND uid_foreign=' .
				$firstRecordUid
		);
		$this->assertSame(
			1,
			$count
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding removeRelation()
	// ---------------------------------------------------------------------

	public function testRemoveRelationOnValidDummyRecord() {
		$uidLocal = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);

		// Creates and directly destroys a dummy record.
		$this->fixture->createRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);
		$this->fixture->removeRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);

		// Checks whether the record really was removed from the database.
		$this->assertSame(
			0,
			$this->fixture->countRecords(
				OELIB_TESTTABLE_MM,
				'uid_local=' . $uidLocal.' AND uid_foreign=' . $uidForeign
			)
		);
	}

	public function testRemoveRelationOnValidDummyRecordOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$uidLocal = $this->fixture->createRecord('user_oelibtest_test');
		$uidForeign = $this->fixture->createRecord('user_oelibtest_test');

		// Creates and directly destroys a dummy record.
		$this->fixture->createRelation(
			'user_oelibtest_test_article_mm', $uidLocal, $uidForeign
		);
		$this->fixture->removeRelation(
			'user_oelibtest_test_article_mm', $uidLocal, $uidForeign
		);
	}

	public function testRemoveRelationOnInexistentRecord() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidLocal = $uid + 1;
		$uidForeign = $uid + 2;

		// Checks that the record is inexistent before testing on it.
		$this->assertSame(
			0,
			$this->fixture->countRecords(
				OELIB_TESTTABLE_MM,
				'uid_local=' . $uidLocal.' AND uid_foreign=' . $uidForeign
			)
		);

		// Runs our delete function - it should run through even when it can't
		// delete a record.
		$this->fixture->removeRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);
	}

	public function testRemoveRelationOnForeignTable() {
		$table = 'tx_seminars_seminars_places_mm';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
	}

	public function testRemoveRelationOnInexistentTable() {
		$table = 'tx_oelib_DOESNOTEXIST';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
	}

	public function testRemoveRelationWithEmptyTableName() {
		$table = '';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
	}

	public function testRemoveRelationOnRealRecordNotRemovesRelation() {
		$uidLocal = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);;

		// Create a new record that looks like a real record, i.e. the
		// is_dummy_record flag is set to 0.
		tx_oelib_db::insert(
			OELIB_TESTTABLE_MM,
			array(
				'uid_local' => $uidLocal,
				'uid_foreign' => $uidForeign,
				'is_dummy_record' => 0
			)
		);

		// Runs our delete method which should NOT affect the record created
		// above.
		$this->fixture->removeRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);

		// Caches the value that will be tested for later. We need to use the
		// following order to make sure the test record gets deleted even if
		// this test fails:
		// 1. reads the value to test
		// 2. deletes the test record
		// 3. tests the previously read value (and possibly fails)
		$numberOfCreatedRelations = tx_oelib_db::count(
			OELIB_TESTTABLE_MM,
			'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign
		);

		// Deletes the record as it will not be caught by the clean up function.
		tx_oelib_db::delete(
			OELIB_TESTTABLE_MM,
			'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign
				.' AND is_dummy_record = 0'
		);

		// Checks whether the relation had been created further up.
		$this->assertSame(
			1,
			$numberOfCreatedRelations
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding cleanUp()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function cleanUpWithRegularCleanUp() {
		// Creates a dummy record (and marks that table as dirty).
		$this->fixture->createRecord(OELIB_TESTTABLE);

		// Creates a dummy record directly in the database, without putting this
		// table name to the list of dirty tables.
		tx_oelib_db::insert(
			OELIB_TESTTABLE_MM, array('is_dummy_record' => 1)
		);

		// Runs a regular clean up. This should now delete only the first record
		// which was created through the testing framework and thus that table
		// is on the list of dirty tables. The second record was directly put
		// into the database and it's table is not on this list and will not be
		// removed by a regular clean up run.
		$this->fixture->cleanUp();

		// Checks whether the first dummy record is deleted.
		$this->assertSame(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE),
			'Some test records were not deleted from table "tx_oelib_test"'
		);

		// Checks whether the second dummy record still exists.
		$this->assertSame(
			1,
			$this->fixture->countRecords(OELIB_TESTTABLE_MM)
		);

		// Runs a deep clean up to delete all dummy records.
		$this->fixture->cleanUp(TRUE);
	}

	/**
	 * @test
	 */
	public function cleanUpWithDeepCleanup() {
		// Creates a dummy record (and marks that table as dirty).
		$this->fixture->createRecord(OELIB_TESTTABLE);

		// Creates a dummy record directly in the database without putting this
		// table name to the list of dirty tables.
		tx_oelib_db::insert(
			OELIB_TESTTABLE_MM, array('is_dummy_record' => 1)
		);

		// Deletes all dummy records.
		$this->fixture->cleanUp(TRUE);

		// Checks whether ALL dummy records were deleted (independent of the
		// list of dirty tables).
		$allowedTables = $this->fixture->getListOfDirtyTables();
		foreach ($allowedTables as $currentTable) {
			$this->assertSame(
				0,
				$this->fixture->countRecords($currentTable),
				'Some test records were not deleted from table "'.$currentTable.'"'
			);
		}
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedDummyFile() {
		$fileName = $this->fixture->createDummyFile();

		$this->fixture->cleanUp();

		$this->assertFalse(file_exists($fileName));
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedDummyFolder() {
		$folderName = $this->fixture->createDummyFolder('test_folder');

		$this->fixture->cleanUp();

		$this->assertFalse(file_exists($folderName));
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedNestedDummyFolders() {
		$outerDummyFolder = $this->fixture->createDummyFolder('test_folder');
		$innerDummyFolder = $this->fixture->createDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->fixture->cleanUp();

		$this->assertFalse(
			file_exists($outerDummyFolder) && file_exists($innerDummyFolder)
		);
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedDummyUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->fixture->createDummyFile();

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));

		$this->fixture->cleanUp();

		$this->assertFalse(is_dir($this->fixture->getUploadFolderPath()));
	}

	/**
	 * @test
	 */
	public function cleanUpExecutesCleanUpHook() {
		$hookClassName = uniqid('cleanUpHook');
		$cleanUpHookMock = $this->getMock(
			$hookClassName, array('cleanUp')
		);
		$cleanUpHookMock->expects($this->atLeastOnce())->method('cleanUp');

		$GLOBALS['T3_VAR']['getUserObj'][$hookClassName] = $cleanUpHookMock;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']
			['testingFrameworkCleanUp'][$hookClassName] = $hookClassName;

		$this->fixture->cleanUp();
	}


	// ---------------------------------------------------------------------
	// Tests regarding createListOfAllowedTables()
	//
	// The method is called in the constructor of the fixture.
	// ---------------------------------------------------------------------

	public function testCreateListOfAllowedTablesContainsOurTestTable() {
		$allowedTables = $this->fixture->getListOfOwnAllowedTableNames();
		$this->assertContains(
			OELIB_TESTTABLE,
			$allowedTables
		);
	}

	public function testCreateListOfAllowedTablesDoesNotContainForeignTables() {
		$allowedTables = $this->fixture->getListOfOwnAllowedTableNames();
		$this->assertNotContains(
			'be_users',
			$allowedTables
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createListOfAdditionalAllowedTables()
	//
	// (That method is called in the constructor of the fixture.)
	// ---------------------------------------------------------------------

	public function testCreateListOfAdditionalAllowedTablesContainsOurTestTable() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$allowedTables = $this->fixture->getListOfAdditionalAllowedTableNames();
		$this->assertContains(
			'user_oelibtest_test',
			$allowedTables
		);
	}

	public function testCreateListOfAdditionalAllowedTablesDoesNotContainForeignTables() {
		$allowedTables = $this->fixture->getListOfAdditionalAllowedTableNames();
		$this->assertNotContains(
			'be_users',
			$allowedTables
		);
	}

	public function testCreateListOfAdditionalAllowedTablesContainsOurTestTables() {
		$this->checkIfExtensionUserOelibtestIsLoaded();
		$this->checkIfExtensionUserOelibtest2IsLoaded();

		$fixture = new tx_oelib_testingFramework(
			'tx_oelib', array('user_oelibtest', 'user_oelibtest2')
		);

		$allowedTables = $fixture->getListOfAdditionalAllowedTableNames();
		$this->assertContains(
			'user_oelibtest_test',
			$allowedTables
		);
		$this->assertContains(
			'user_oelibtest2_test',
			$allowedTables
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding getAutoIncrement()
	// ---------------------------------------------------------------------

	public function testGetAutoIncrementReturnsOneForTruncatedTable() {
		tx_oelib_db::enableQueryLogging();
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'TRUNCATE TABLE ' . OELIB_TESTTABLE . ';'
		);
		if (!$dbResult) {
			throw new tx_oelib_Exception_Database();
		}

		$this->assertSame(
			1,
			$this->fixture->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	public function testGetAutoIncrementGetsCurrentAutoIncrement() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);

		// $uid will equals be the previous auto increment value, so $uid + 1
		// should be equal to the current auto inrement value.
		$this->assertSame(
			$uid + 1,
			$this->fixture->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	public function testGetAutoIncrementForFeUsersTableIsAllowed() {
		$this->fixture->getAutoIncrement('fe_users');
	}

	public function testGetAutoIncrementForPagesTableIsAllowed() {
		$this->fixture->getAutoIncrement('pages');
	}

	public function testGetAutoIncrementForTtContentTableIsAllowed() {
		$this->fixture->getAutoIncrement('tt_content');
	}

	public function testGetAutoIncrementWithOtherSystemTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('sys_domains');
	}

	public function testGetAutoIncrementWithEmptyTableNameFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('');
	}

	public function testGetAutoIncrementWithForeignTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('tx_seminars_seminars');
	}

	public function testGetAutoIncrementWithInexistentTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('tx_oelib_DOESNOTEXIST');
	}

	public function testGetAutoIncrementWithTableWithoutUidFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('OELIB_TESTTABLE_MM');
	}


	// ---------------------------------------------------------------------
	// Tests regarding countRecords()
	// ---------------------------------------------------------------------

	public function testCountRecordsWithEmptyWhereClauseIsAllowed() {
		$this->fixture->countRecords(OELIB_TESTTABLE, '');
	}

	public function testCountRecordsWithMissingWhereClauseIsAllowed() {
		$this->fixture->countRecords(OELIB_TESTTABLE);
	}

	public function testCountRecordsWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->countRecords('');
	}

	public function testCountRecordsWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$table = 'foo_bar';
		$this->fixture->countRecords($table);
	}

	public function testCountRecordsWithFeGroupsTableIsAllowed() {
		$table = 'fe_groups';
		$this->fixture->countRecords($table);
	}

	public function testCountRecordsWithFeUsersTableIsAllowed() {
		$table = 'fe_users';
		$this->fixture->countRecords($table);
	}

	public function testCountRecordsWithPagesTableIsAllowed() {
		$table = 'pages';
		$this->fixture->countRecords($table);
	}

	public function testCountRecordsWithTtContentTableIsAllowed() {
		$table = 'tt_content';
		$this->fixture->countRecords($table);
	}

	public function testCountRecordsWithOtherTableThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->countRecords('sys_domain');
	}

	public function testCountRecordsReturnsZeroForNoMatches() {
		$this->assertSame(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	public function testCountRecordsReturnsOneForOneDummyRecordMatch() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	public function testCountRecordsWithMissingWhereClauseReturnsOneForOneDummyRecordMatch() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(OELIB_TESTTABLE)
		);
	}

	public function testCountRecordsReturnsTwoForTwoMatches() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertSame(
			2,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	public function testCountRecordsForPagesTableIsAllowed() {
		$this->fixture->countRecords('pages');
	}

	public function testCountRecordsIgnoresNonDummyRecords() {
		tx_oelib_db::insert(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$testResult = $this->fixture->countRecords(
			OELIB_TESTTABLE, 'title = "foo"'
		);

		tx_oelib_db::delete(
			OELIB_TESTTABLE,
			'title = "foo"'
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertSame(
			0,
			$testResult
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding existsRecord()
	// ---------------------------------------------------------------------

	public function testExistsRecordWithEmptyWhereClauseIsAllowed() {
		$this->fixture->existsRecord(OELIB_TESTTABLE, '');
	}

	public function testExistsRecordWithMissingWhereClauseIsAllowed() {
		$this->fixture->existsRecord(OELIB_TESTTABLE);
	}

	public function testExistsRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->existsRecord('');
	}

	public function testExistsRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$table = 'foo_bar';
		$this->fixture->existsRecord($table);
	}

	public function testExistsRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			$this->fixture->existsRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	public function testExistsRecordForOneMatchReturnsTrue() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->existsRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	public function testExistsRecordForTwoMatchesReturnsTrue() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->existsRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	public function testExistsRecordIgnoresNonDummyRecords() {
		tx_oelib_db::insert(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$testResult = $this->fixture->existsRecord(
			OELIB_TESTTABLE, 'title = "foo"'
		);

		tx_oelib_db::delete(
			OELIB_TESTTABLE,
			'title = "foo"'
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertFalse(
			$testResult
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding existsRecordWithUid()
	// ---------------------------------------------------------------------

	public function testExistsRecordWithUidWithZeroUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->fixture->existsRecordWithUid(OELIB_TESTTABLE, 0);
	}

	public function testExistsRecordWithUidWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->fixture->existsRecordWithUid(OELIB_TESTTABLE, -1);
	}

	public function testExistsRecordWithUidWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->existsRecordWithUid('', 1);
	}

	public function testExistsRecordWithUidWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$table = 'foo_bar';
		$this->fixture->existsRecordWithUid($table, 1);
	}

	public function testExistsRecordWithUidForNoMatcheReturnsFalse() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid);

		$this->assertFalse(
			$this->fixture->existsRecordWithUid(
				OELIB_TESTTABLE, $uid
			)
		);
	}

	public function testExistsRecordWithUidForAMatchReturnsTrue() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->assertTrue(
			$this->fixture->existsRecordWithUid(OELIB_TESTTABLE, $uid)
		);
	}

	public function testExistsRecordWithUidIgnoresNonDummyRecords() {
		$uid = tx_oelib_db::insert(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$testResult = $this->fixture->existsRecordWithUid(
			OELIB_TESTTABLE, $uid
		);

		tx_oelib_db::delete(
			OELIB_TESTTABLE, 'uid = ' . $uid
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertFalse(
			$testResult
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding existsExactlyOneRecord()
	// ---------------------------------------------------------------------

	public function testExistsExactlyOneRecordWithEmptyWhereClauseIsAllowed() {
		$this->fixture->existsExactlyOneRecord(OELIB_TESTTABLE, '');
	}

	public function testExistsExactlyOneRecordWithMissingWhereClauseIsAllowed() {
		$this->fixture->existsExactlyOneRecord(OELIB_TESTTABLE);
	}

	public function testExistsExactlyOneRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->existsExactlyOneRecord('');
	}

	public function testExistsExactlyOneRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$table = 'foo_bar';
		$this->fixture->existsExactlyOneRecord($table);
	}

	public function testExistsExactlyOneRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			$this->fixture->existsExactlyOneRecord(
				OELIB_TESTTABLE, 'title = "foo"'
			)
		);
	}

	public function testExistsExactlyOneRecordForOneMatchReturnsTrue() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->existsExactlyOneRecord(
				OELIB_TESTTABLE, 'title = "foo"'
			)
		);
	}

	public function testExistsExactlyOneRecordForTwoMatchesReturnsFalse() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertFalse(
			$this->fixture->existsExactlyOneRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	public function testExistsExactlyOneRecordIgnoresNonDummyRecords() {
		tx_oelib_db::insert(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$testResult = $this->fixture->existsExactlyOneRecord(
			OELIB_TESTTABLE, 'title = "foo"'
		);

		tx_oelib_db::delete(
			OELIB_TESTTABLE,
			'title = "foo"'
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertFalse(
			$testResult
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding resetAutoIncrement()
	// ---------------------------------------------------------------------

	public function testResetAutoIncrementForTestTableSucceeds() {
		$latestUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertSame(
			$latestUid,
			$this->fixture->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	public function testResetAutoIncrementForUnchangedTestTableCanBeRun() {
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);
	}

	public function testResetAutoIncrementForAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		// Creates and deletes a record and then resets the auto increment.
		$latestUid = $this->fixture->createRecord('user_oelibtest_test');
		$this->fixture->deleteRecord('user_oelibtest_test', $latestUid);
		$this->fixture->resetAutoIncrement('user_oelibtest_test');
	}

	public function testResetAutoIncrementForTableWithoutUidIsAllowed() {
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE_MM);
	}

	public function testResetAutoIncrementForFeUsersTableIsAllowed() {
		$this->fixture->resetAutoIncrement('fe_users');
	}

	public function testResetAutoIncrementForPagesTableIsAllowed() {
		$this->fixture->resetAutoIncrement('pages');
	}

	public function testResetAutoIncrementForTtContentTableIsAllowed() {
		$this->fixture->resetAutoIncrement('tt_content');
	}

	public function testResetAutoIncrementWithOtherSystemTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrement('sys_domains');
	}

	public function testResetAutoIncrementWithEmptyTableNameFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrement('');
	}

	public function testResetAutoIncrementWithForeignTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrement('tx_seminars_seminars');
	}

	public function testResetAutoIncrementWithInexistentTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrement('tx_oelib_DOESNOTEXIST');
	}


	// ---------------------------------------------------------------------
	// Tests regarding resetAutoIncrementLazily() and
	// setResetAutoIncrementThreshold
	// ---------------------------------------------------------------------

	public function testResetAutoIncrementLazilyForTestTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily(OELIB_TESTTABLE);
	}

	public function testResetAutoIncrementLazilyForTableWithoutUidIsAllowed() {
		$this->fixture->resetAutoIncrementLazily(OELIB_TESTTABLE_MM);
	}

	public function testResetAutoIncrementLazilyForFeUsersTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('fe_users');
	}

	public function testResetAutoIncrementLazilyForPagesTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('pages');
	}

	public function testResetAutoIncrementLazilyForTtContentTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('tt_content');
	}

	public function testResetAutoIncrementLazilyWithOtherSystemTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrementLazily('sys_domains');
	}

	public function testResetAutoIncrementLazilyWithEmptyTableNameFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrementLazily('');
	}

	public function testResetAutoIncrementLazilyWithForeignTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrementLazily('tx_seminars_seminars');
	}

	public function testResetAutoIncrementLazilyWithInexistentTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrementLazily('tx_oelib_DOESNOTEXIST');
	}

	public function testResetAutoIncrementLazilyDoesNothingAfterOneNewRecordByDefault() {
		$oldAutoIncrement = $this->fixture->getAutoIncrement(OELIB_TESTTABLE);

		$latestUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->fixture->resetAutoIncrementLazily(OELIB_TESTTABLE);

		$this->assertNotSame(
			$oldAutoIncrement,
			$this->fixture->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	public function testResetAutoIncrementLazilyCleansUpsAfterOneNewRecordWithThreshholdOfOne() {
		$oldAutoIncrement = $this->fixture->getAutoIncrement(OELIB_TESTTABLE);
		$this->fixture->setResetAutoIncrementThreshold(1);

		$latestUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->fixture->resetAutoIncrementLazily(OELIB_TESTTABLE);

		$this->assertSame(
			$oldAutoIncrement,
			$this->fixture->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	public function testResetAutoIncrementLazilyCleansUpsAfter100NewRecordsByDefault() {
		$oldAutoIncrement = $this->fixture->getAutoIncrement(OELIB_TESTTABLE);

		for ($i = 0; $i < 100; $i++) {
			$latestUid = $this->fixture->createRecord(OELIB_TESTTABLE);
			$this->fixture->deleteRecord(OELIB_TESTTABLE, $latestUid);
		}

		$this->fixture->resetAutoIncrementLazily(OELIB_TESTTABLE);

		$this->assertSame(
			$oldAutoIncrement,
			$this->fixture->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	public function testSetResetAutoIncrementThresholdForOneIsAllowed() {
		$this->fixture->setResetAutoIncrementThreshold(1);
	}

	public function testSetResetAutoIncrementThresholdFor100IsAllowed() {
		$this->fixture->setResetAutoIncrementThreshold(100);
	}

	public function testSetResetAutoIncrementThresholdForZeroFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$threshold must be > 0.'
		);

		$this->fixture->setResetAutoIncrementThreshold(0);
	}

	public function testSetResetAutoIncrementThresholdForMinus1Fails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$threshold must be > 0.'
		);

		$this->fixture->setResetAutoIncrementThreshold(-1);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndPage()
	// ---------------------------------------------------------------------

	public function testFrontEndPageCanBeCreated() {
		$uid = $this->fixture->createFrontEndPage();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	public function testCreateFrontEndPageSetsCorrectDocumentType() {
		$uid = $this->fixture->createFrontEndPage();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = tx_oelib_db::selectSingle(
			'doktype',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			1,
			intval($row['doktype'])
		);
	}

	public function testFrontEndPageWillBeCreatedOnRootPage() {
		$uid = $this->fixture->createFrontEndPage();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = tx_oelib_db::selectSingle(
			'pid',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			0,
			intval($row['pid'])
		);
	}

	public function testFrontEndPageCanBeCreatedOnOtherPage() {
		$parent = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createFrontEndPage($parent);

		$this->assertNotSame(
			0,
			$uid
		);

		$row = tx_oelib_db::selectSingle(
			'pid',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			$parent,
			intval($row['pid'])
		);
	}

	public function testFrontEndPageCanBeDirty() {
		$this->assertSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createFrontEndPage();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testFrontEndPageWillBeCleanedUp() {
		$uid = $this->fixture->createFrontEndPage();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	public function testFrontEndPageHasNoTitleByDefault() {
		$uid = $this->fixture->createFrontEndPage();

		$row = tx_oelib_db::selectSingle(
			'title',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			'',
			$row['title']
		);
	}

	public function testFrontEndPageCanHaveTitle() {
		$uid = $this->fixture->createFrontEndPage(
			0,
			array('title' => 'Test title')
		);

		$row = tx_oelib_db::selectSingle(
			'title',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			'Test title',
			$row['title']
		);
	}

	public function testFrontEndPageMustHaveNoZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('pid' => 0));
	}

	public function testFrontEndPageMustHaveNoNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('pid' => 99999));
	}

	public function testFrontEndPageMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('uid' => 0));
	}

	public function testFrontEndPageMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('uid' => 99999));
	}

	public function testFrontEndPageMustHaveNoZeroDoktype() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('doktype' => 0));
	}

	public function testFrontEndPageMustHaveNoNonZeroDoktype() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('doktype' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createSystemFolder()
	// ---------------------------------------------------------------------

	public function testSystemFolderCanBeCreated() {
		$uid = $this->fixture->createSystemFolder();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	public function testCreateSystemFolderSetsCorrectDocumentType() {
		$uid = $this->fixture->createSystemFolder();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = tx_oelib_db::selectSingle(
			'doktype',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			254,
			intval($row['doktype'])
		);
	}

	public function testSystemFolderWillBeCreatedOnRootPage() {
		$uid = $this->fixture->createSystemFolder();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = tx_oelib_db::selectSingle(
			'pid',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			0,
			intval($row['pid'])
		);
	}

	public function testSystemFolderCanBeCreatedOnOtherPage() {
		$parent = $this->fixture->createSystemFolder();
		$uid = $this->fixture->createSystemFolder($parent);

		$this->assertNotSame(
			0,
			$uid
		);

		$row = tx_oelib_db::selectSingle(
			'pid',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			$parent,
			intval($row['pid'])
		);
	}

	public function testSystemFolderCanBeDirty() {
		$this->assertSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createSystemFolder();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testSystemFolderWillBeCleanedUp() {
		$uid = $this->fixture->createSystemFolder();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	public function testSystemFolderHasNoTitleByDefault() {
		$uid = $this->fixture->createSystemFolder();

		$row = tx_oelib_db::selectSingle(
			'title',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			'',
			$row['title']
		);
	}

	public function testSystemFolderCanHaveTitle() {
		$uid = $this->fixture->createSystemFolder(
			0,
			array('title' => 'Test title')
		);

		$row = tx_oelib_db::selectSingle(
			'title',
			'pages',
			'uid = ' . $uid
		);

		$this->assertSame(
			'Test title',
			$row['title']
		);
	}

	public function testSystemFolderMustHaveNoZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('pid' => 0));
	}

	public function testSystemFolderMustHaveNoNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('pid' => 99999));
	}

	public function testSystemFolderMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('uid' => 0));
	}

	public function testSystemFolderMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('uid' => 99999));
	}

	public function testSystemFolderMustHaveNoZeroDoktype() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('doktype' => 0));
	}

	public function testSystemFolderMustHaveNoNonZeroDoktype() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('doktype' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createContentElement()
	// ---------------------------------------------------------------------

	public function testContentElementCanBeCreated() {
		$uid = $this->fixture->createContentElement();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(
				'tt_content', 'uid=' . $uid
			)
		);
	}

	public function testContentElementWillBeCreatedOnRootPage() {
		$uid = $this->fixture->createContentElement();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = tx_oelib_db::selectSingle(
			'pid',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertSame(
			0,
			intval($row['pid'])
		);
	}

	public function testContentElementCanBeCreatedOnNonRootPage() {
		$parent = $this->fixture->createSystemFolder();
		$uid = $this->fixture->createContentElement($parent);

		$this->assertNotSame(
			0,
			$uid
		);

		$row = tx_oelib_db::selectSingle(
			'pid',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertSame(
			$parent,
			intval($row['pid'])
		);
	}

	public function testContentElementCanBeDirty() {
		$this->assertSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createContentElement();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testContentElementWillBeCleanedUp() {
		$uid = $this->fixture->createContentElement();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords(
				'tt_content', 'uid=' . $uid
			)
		);
	}

	public function testContentElementHasNoHeaderByDefault() {
		$uid = $this->fixture->createContentElement();

		$row = tx_oelib_db::selectSingle(
			'header',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertSame(
			'',
			$row['header']
		);
	}

	public function testContentElementCanHaveHeader() {
		$uid = $this->fixture->createContentElement(
			0,
			array('header' => 'Test header')
		);

		$row = tx_oelib_db::selectSingle(
			'header',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertSame(
			'Test header',
			$row['header']
		);
	}

	public function testContentElementIsTextElementByDefault() {
		$uid = $this->fixture->createContentElement();

		$row = tx_oelib_db::selectSingle(
			'CType',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertSame(
			'text',
			$row['CType']
		);
	}

	public function testContentElementCanHaveOtherType() {
		$uid = $this->fixture->createContentElement(
			0,
			array('CType' => 'list')
		);

		$row = tx_oelib_db::selectSingle(
			'CType',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertSame(
			'list',
			$row['CType']
		);
	}

	public function testContentElementMustHaveNoZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createContentElement(0, array('pid' => 0));
	}

	public function testContentElementMustHaveNoNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createContentElement(0, array('pid' => 99999));
	}

	public function testContentElementMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createContentElement(0, array('uid' => 0));
	}

	public function testContentElementMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createContentElement(0, array('uid' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createTemplate()
	// ---------------------------------------------------------------------

	public function testTemplateCanBeCreatedOnNonRootPage() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(
				'sys_template', 'uid=' . $uid
			)
		);
	}

	public function testTemplateCannotBeCreatedOnRootPage() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$pageId must be > 0.'
		);

		$this->fixture->createTemplate(0);
	}

	public function testTemplateCannotBeCreatedWithNegativePageNumber() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$pageId must be > 0.'
		);

		$this->fixture->createTemplate(-1);
	}

	public function testTemplateCanBeDirty() {
		$this->assertSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);

		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testTemplateWillBeCleanedUp() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$this->assertNotSame(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords(
				'sys_template', 'uid=' . $uid
			)
		);
	}

	public function testTemplateInitiallyHasNoConfig() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$row = tx_oelib_db::selectSingle(
			'config',
			'sys_template',
			'uid = ' . $uid
		);

		$this->assertFalse(
			isset($row['config'])
		);
	}

	public function testTemplateCanHaveConfig() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate(
			$pageId,
			array('config' => 'plugin.tx_oelib.test = 1')
		);
		$row = tx_oelib_db::selectSingle(
			'config',
			'sys_template',
			'uid = ' . $uid
		);

		$this->assertSame(
			'plugin.tx_oelib.test = 1',
			$row['config']
		);
	}

	public function testTemplateConfigIsReadableAsTsTemplate() {
		$pageId = $this->fixture->createFrontEndPage();
		$this->fixture->createTemplate(
			$pageId,
			array('config' => 'plugin.tx_oelib.test = 42')
		);
		$templateHelper = new tx_oelib_templatehelperchild(array());
		$configuration = $templateHelper->retrievePageConfig($pageId);

		$this->assertTrue(
			isset($configuration['test'])
		);
		$this->assertSame(
			'42',
			$configuration['test']
		);
	}

	public function testTemplateInitiallyHasNoConstants() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$row = tx_oelib_db::selectSingle(
			'constants',
			'sys_template',
			'uid = ' . $uid
		);

		$this->assertFalse(
			isset($row['constants'])
		);
	}

	public function testTemplateCanHaveConstants() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate(
			$pageId,
			array('constants' => 'plugin.tx_oelib.test = 1')
		);
		$row = tx_oelib_db::selectSingle(
			'constants',
			'sys_template',
			'uid = ' . $uid
		);

		$this->assertSame(
			'plugin.tx_oelib.test = 1',
			$row['constants']
		);
	}

	public function testTemplateConstantsAreUsedInTsSetup() {
		$pageId = $this->fixture->createFrontEndPage();
		$this->fixture->createTemplate(
			$pageId,
			array(
				'constants' => 'plugin.tx_oelib.test = 42',
				'config' => 'plugin.tx_oelib.test = {$plugin.tx_oelib.test}'
			)
		);
		$templateHelper = new tx_oelib_templatehelperchild(array());
		$configuration = $templateHelper->retrievePageConfig($pageId);

		$this->assertTrue(
			isset($configuration['test'])
		);
		$this->assertSame(
			'42',
			$configuration['test']
		);
	}

	public function testTemplateMustNotHaveAZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createTemplate(42, array('pid' => 0));
	}

	public function testTemplateMustNotHaveANonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createTemplate(42, array('pid' => 99999));
	}

	public function testTemplateMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createTemplate(42, array('uid' => 0));
	}

	public function testTemplateMustNotHaveANonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createTemplate(42, array('uid' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createDummyFile()
	// ---------------------------------------------------------------------

	public function testCreateDummyFileCreatesFile() {
		$dummyFile = $this->fixture->createDummyFile();

		$this->assertTrue(file_exists($dummyFile));
	}

	public function testCreateDummyFileCreatesFileInSubfolder() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$dummyFile = $this->fixture->createDummyFile(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder) .
				'/test.txt'
		);

		$this->assertTrue(file_exists($dummyFile));
	}

	public function testCreateDummyFileCreatesFileWithTheProvidedContent() {
		$dummyFile = $this->fixture->createDummyFile('test.txt', 'Hello world!');

		$this->assertSame('Hello world!', file_get_contents($dummyFile));
	}

	public function testCreateDummyFileForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->fixture->createDummyFile();

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));
	}

	public function testCreateDummyFileForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->fixture->createDummyFile();

		$this->assertTrue(file_exists($dummyFile));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createDummyZipArchive()
	// ---------------------------------------------------------------------

	public function testCreateDummyZipArchiveForNoContentProvidedCreatesZipArchive() {
		$this->markAsSkippedForNoZipArchive();

		$dummyFile = $this->fixture->createDummyZipArchive();

		$this->assertTrue(file_exists($dummyFile));
	}

	public function testCreateDummyZipArchiveForFileNameInSubFolderProvidedCreatesZipArchiveInSubFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFolder = $this->fixture->getPathRelativeToUploadDirectory(
			$this->fixture->createDummyFolder('sub-folder')
		);
		$this->fixture->createDummyZipArchive($dummyFolder . 'foo.zip');

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . $dummyFolder . 'foo.zip')
		);
	}

	public function testCreateDummyZipArchiveForNoContentProvidedCreatesZipArchiveWithDummyFile() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->fixture->createDummyZipArchive();
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'test.txt')
		);
	}

	public function testCreateDummyZipArchiveForFileProvidedCreatesZipArchiveWithThatFile() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->fixture->createDummyZipArchive(
			'foo.zip', array($this->fixture->createDummyFile('bar.txt'))
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'bar.txt')
		);
	}

	public function testCreateDummyZipArchiveForFileProvidedWithContentCreatesZipArchiveWithThatFileAndContentInIt() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->fixture->createDummyZipArchive(
			'foo.zip', array($this->fixture->createDummyFile('bar.txt', 'foo bar'))
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertSame(
			'foo bar',
			file_get_contents($this->fixture->getUploadFolderPath() . 'bar.txt')
		);
	}

	public function testCreateDummyZipArchiveForTwoFilesProvidedCreatesZipArchiveWithTheseFiles() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->fixture->createDummyZipArchive(
			'foo.zip', array(
				$this->fixture->createDummyFile('foo.txt'),
				$this->fixture->createDummyFile('bar.txt'),
			)
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'foo.txt')
		);
		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'bar.txt')
		);
	}

	public function testCreateDummyZipArchiveForFileInSubFolderOfUploadFolderProvidedCreatesZipArchiveWithFileInSubFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->fixture->createDummyFolder('sub-folder');
		$dummyFile = $this->fixture->createDummyZipArchive(
			'foo.zip', array($this->fixture->createDummyFile('sub-folder/foo.txt'))
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'sub-folder/foo.txt')
		);
	}

	public function testCreateDummyZipArchiveForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->fixture->createDummyZipArchive();

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));
	}

	public function testCreateDummyZipArchiveForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->fixture->createDummyZipArchive();

		$this->assertTrue(file_exists($dummyFile));
	}


	// ---------------------------------------------------------------------
	// Tests regarding deleteDummyFile()
	// ---------------------------------------------------------------------

	public function testDeleteDummyFileDeletesCreatedDummyFile() {
		$dummyFile = $this->fixture->createDummyFile();
		$this->fixture->deleteDummyFile(basename($dummyFile));

		$this->assertFalse(file_exists($dummyFile));
	}

	public function testDeleteDummyFileWithAlreadyDeletedFileThrowsNoException() {
		$dummyFile = $this->fixture->createDummyFile();
		unlink($dummyFile);

		$this->fixture->deleteDummyFile(basename($dummyFile));
	}

	public function testDeleteDummyFileWithInexistentFileThrowsException() {
		$uniqueFileName = $this->fixture->getUniqueFileOrFolderPath('test.txt');

		$this->setExpectedException(
			'InvalidArgumentException',
			'The file "' . $uniqueFileName . '" which you are ' .
				'trying to delete does not exist and has never been created by this instance of the testing framework.'
		);

		$this->fixture->deleteDummyFile(basename($uniqueFileName));
	}

	public function testDeleteDummyFileWithForeignFileThrowsException() {
		$uniqueFileName = $this->fixture->getUniqueFileOrFolderPath('test.txt');
		t3lib_div::writeFile($uniqueFileName, '');
		$this->foreignFileToDelete = $uniqueFileName;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The file "' . $uniqueFileName . '" which you are ' .
				'trying to delete was not created by this instance of the testing framework.'
		);

		$this->fixture->deleteDummyFile(basename($uniqueFileName));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createDummyFolder()
	// ---------------------------------------------------------------------

	public function testCreateDummyFolderCreatesFolder() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($dummyFolder));
	}

	public function testCreateDummyFolderCanCreateFolderInDummyFolder() {
		$outerDummyFolder = $this->fixture->createDummyFolder('test_folder');
		$innerDummyFolder = $this->fixture->createDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->assertTrue(is_dir($innerDummyFolder));
	}

	public function testCreateDummyFolderForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->fixture->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));
	}

	public function testCreateDummyFolderForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($dummyFolder));
	}


	// ---------------------------------------------------------------------
	// Tests regarding deleteDummyFolder()
	// ---------------------------------------------------------------------

	public function testDeleteDummyFolderDeletesCreatedDummyFolder() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);

		$this->assertFalse(is_dir($dummyFolder));
	}

	public function testDeleteDummyFolderWithInexistentFolderThrowsException() {
		$uniqueFolderName = $this->fixture->getUniqueFileOrFolderPath('test_folder');

		$this->setExpectedException(
			'InvalidArgumentException',
			'The folder "' . $uniqueFolderName . '" which you are trying to delete does not exist.'
		);

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($uniqueFolderName)
		);
	}

	public function testDeleteDummyFolderWithForeignFolderThrowsException() {
		$uniqueFolderName = $this->fixture->getUniqueFileOrFolderPath('test_folder');
		t3lib_div::mkdir($uniqueFolderName);
		$this->foreignFolderToDelete = $uniqueFolderName;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The folder "' . $uniqueFolderName . '" which you are ' .
				'trying to delete was not created by this instance of the testing framework.'
		);

		$this->fixture->deleteDummyFolder(basename($uniqueFolderName));
	}

	public function testDeleteDummyFolderCanDeleteCreatedDummyFolderInDummyFolder() {
		$outerDummyFolder = $this->fixture->createDummyFolder('test_folder');
		$innerDummyFolder = $this->fixture->createDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($innerDummyFolder)
		);

		$this->assertFalse(file_exists($innerDummyFolder));
		$this->assertTrue(file_exists($outerDummyFolder));
	}

	public function testDeleteDummyFolderWithNonEmptyDummyFolderThrowsException() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$this->fixture->createDummyFile(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder) .
				'/test.txt'
		);

		$this->setExpectedException(
			'RuntimeException',
			'The folder "' . $dummyFolder . '" could not be deleted.'
		);

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);
	}

	public function testDeleteDummyFolderWithFolderNameConsistingOnlyOfNumbersDoesNotThrowAnException() {
		$dummyFolder = $this->fixture->createDummyFolder('123');

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding set- and getUploadFolderPath()
	// ---------------------------------------------------------------------

	public function testGetUploadFolderPathReturnsUploadFolderPathIncludingTablePrefix() {
		$this->assertRegExp(
			'/\/uploads\/tx_oelib\/$/',
			$this->fixture->getUploadFolderPath()
		);
	}

	public function testGetUploadFolderPathAfterSetReturnsSetUploadFolderPath() {
		$this->fixture->setUploadFolderPath('/foo/bar/');

		$this->assertSame(
			'/foo/bar/',
			$this->fixture->getUploadFolderPath()
		);
	}

	public function testSetUploadFolderPathAfterCreatingADummyFileThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The upload folder path must not be changed if there are already dummy files or folders.'
		);

		$this->fixture->createDummyFile();
		$this->fixture->setUploadFolderPath('/foo/bar/');
	}


	// ---------------------------------------------------------------------
	// Tests regarding getPathRelativeToUploadDirectory()
	// ---------------------------------------------------------------------

	public function testGetPathRelativeToUploadDirectoryWithPathOutsideUploadDirectoryThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The first parameter $absolutePath is not within the calling extension\'s upload directory.'
		);

		$this->fixture->getPathRelativeToUploadDirectory(PATH_site);
	}


	// ---------------------------------------------------------------------
	// Tests regarding getUniqueFileOrFolderPath()
	// ---------------------------------------------------------------------

	public function testGetUniqueFileOrFolderPathWithEmptyPathThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The first parameter $path must not be empty.'
		);

		$this->fixture->getUniqueFileOrFolderPath('');
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndUserGroup()
	// ---------------------------------------------------------------------

	public function testFrontEndUserGroupCanBeCreated() {
		$uid = $this->fixture->createFrontEndUserGroup();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(
				'fe_groups', 'uid=' . $uid
			)
		);
	}

	public function testFrontEndUserGroupTableCanBeDirty() {
		$this->assertSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createFrontEndUserGroup();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testFrontEndUserGroupTableWillBeCleanedUp() {
		$uid = $this->fixture->createFrontEndUserGroup();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords(
				'fe_groups', 'uid=' . $uid
			)
		);
	}

	public function testFrontEndUserGroupHasNoTitleByDefault() {
		$uid = $this->fixture->createFrontEndUserGroup();

		$row = tx_oelib_db::selectSingle(
			'title',
			'fe_groups',
			'uid = ' . $uid
		);

		$this->assertSame(
			'',
			$row['title']
		);
	}

	public function testFrontEndUserGroupCanHaveATitle() {
		$uid = $this->fixture->createFrontEndUserGroup(
			array('title' => 'Test title')
		);

		$row = tx_oelib_db::selectSingle(
			'title',
			'fe_groups',
			'uid = ' . $uid
		);

		$this->assertSame(
			'Test title',
			$row['title']
		);
	}

	public function testFrontEndUserGroupMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUserGroup(array('uid' => 0));
	}

	public function testFrontEndUserGroupMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUserGroup(array('uid' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndUser()
	// ---------------------------------------------------------------------

	public function testFrontEndUserCanBeCreated() {
		$uid = $this->fixture->createFrontEndUser();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(
				'fe_users', 'uid=' . $uid
			)
		);
	}

	public function testFrontEndUserTableCanBeDirty() {
		$this->assertSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createFrontEndUser();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->greaterThan(
			1,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testFrontEndUserTableWillBeCleanedUp() {
		$uid = $this->fixture->createFrontEndUser();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords(
				'fe_users', 'uid=' . $uid
			)
		);
	}

	public function testFrontEndUserHasNoUserNameByDefault() {
		$uid = $this->fixture->createFrontEndUser();

		$row = tx_oelib_db::selectSingle(
			'username',
			'fe_users',
			'uid = ' . $uid
		);

		$this->assertSame(
			'',
			$row['username']
		);
	}

	public function testFrontEndUserCanHaveAUserName() {
		$uid = $this->fixture->createFrontEndUser(
			'',
			array('username' => 'Test name')
		);

		$row = tx_oelib_db::selectSingle(
			'username',
			'fe_users',
			'uid = ' . $uid
		);

		$this->assertSame(
			'Test name',
			$row['username']
		);
	}

	public function testFrontEndUserCanHaveSeveralUserGroups() {
		$feUserGroupUidOne = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidTwo = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidThree = $this->fixture->createFrontEndUserGroup();
		$uid = $this->fixture->createFrontEndUser(
			$feUserGroupUidOne.', '.$feUserGroupUidTwo.', '.$feUserGroupUidThree
		);

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(
				'fe_users', 'uid=' . $uid
			)
		);
	}

	public function testFrontEndUserMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser('', array('uid' => 0));
	}

	public function testFrontEndUserMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser('', array('uid' => 99999));
	}

	public function testFrontEndUserMustHaveNoZeroUserGroupInTheDataArray() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "usergroup" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser('', array('usergroup' => 0));
	}

	public function testFrontEndUserMustHaveNoNonZeroUserGroupInTheDataArray() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "usergroup" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser('', array('usergroup' => 99999));
	}

	public function testFrontEndUserMustHaveNoUserGroupListInTheDataArray() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "usergroup" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser(
			'', array('usergroup' => '1,2,4,5')
		);
	}

	public function testCreateFrontEndUserWithEmptyGroupCreatesGroup() {
		$this->fixture->createFrontEndUser('');

		$this->assertTrue(
			$this->fixture->existsExactlyOneRecord('fe_groups')
		);
	}

	public function testFrontEndUserMustHaveNoZeroUserGroupEvenIfSeveralGroupsAreProvided() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.'
		);

		$feUserGroupUidOne = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidTwo = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidThree = $this->fixture->createFrontEndUserGroup();

		$this->fixture->createFrontEndUser(
			$feUserGroupUidOne.', '.$feUserGroupUidTwo.', 0, '.$feUserGroupUidThree
		);
	}

	public function testFrontEndUserMustHaveNoAlphabeticalCharactersInTheUserGroupList() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.'
		);

		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();

		$this->fixture->createFrontEndUser(
			$feUserGroupUid.', abc'
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createBackEndUser()
	// ---------------------------------------------------------------------

	public function testCreateBackEndUserReturnsUidGreaterZero() {
		$this->assertNotSame(
			0,
			$this->fixture->createBackEndUser()
		);
	}

	public function testCreateBackEndUserCreatesBackEndUserRecordInTheDatabase() {
		$this->assertSame(
			1,
			$this->fixture->countRecords(
				'be_users', 'uid=' . $this->fixture->createBackEndUser()
			)
		);
	}

	public function testCreateBackEndUserMarksBackEndUserTableAsDirty() {
		$this->assertSame(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$this->fixture->createBackEndUser();

		$this->greaterThan(
			1,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	/**
	 * @test
	 */
	public function cleanUpCleansUpDirtyBackEndUserTable() {
		$uid = $this->fixture->createBackEndUser();

		$this->fixture->cleanUp();
		$this->assertSame(
			0,
			$this->fixture->countRecords('be_users', 'uid=' . $uid)
		);
	}

	public function testCreateBackEndUserCreatesRecordWithoutUserNameByDefault() {
		$uid = $this->fixture->createBackEndUser();

		$row = tx_oelib_db::selectSingle('username', 'be_users', 'uid = ' . $uid);

		$this->assertSame(
			'',
			$row['username']
		);
	}

	public function testCreateBackEndUserForUserNameProvidedCreatesRecordWithUserName() {
		$uid = $this->fixture->createBackEndUser(array('username' => 'Test name'));

		$row = tx_oelib_db::selectSingle('username', 'be_users', 'uid = ' . $uid);

		$this->assertSame(
			'Test name',
			$row['username']
		);
	}

	public function testCreateBackEndUserWithZeroUidProvidedInRecordDataThrowsExeption() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createBackEndUser(array('uid' => 0));
	}

	public function testCreateBackEndUserWithNonZeroUidProvidedInRecordDataThrowsExeption() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createBackEndUser(array('uid' => 999999));
	}


	// ---------------------------------------------------------------------
	// Tests concerning fakeFrontend
	// ---------------------------------------------------------------------

	public function testCreateFakeFrontEndCreatesGlobalFrontEnd() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE'] instanceof tslib_fe
		);
	}

	public function testCreateFakeFrontEndReturnsPositivePageUidIfCalledWithoutParameters() {
		$this->assertGreaterThan(
			0,
			$this->fixture->createFakeFrontEnd()
		);
	}

	public function testCreateFakeFrontEndReturnsCurrentFrontEndPageUid() {
		$GLOBALS['TSFE'] = NULL;
		$result = $this->fixture->createFakeFrontEnd();

		$this->assertSame(
			$GLOBALS['TSFE']->id,
			$result
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesNullTimeTrackInstance() {
		$GLOBALS['TT'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TT'] instanceof t3lib_timeTrackNull
		);
	}

	public function testCreateFakeFrontEndCreatesSysPage() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->sys_page instanceof t3lib_pageSelect
		);
	}

	public function testCreateFakeFrontEndCreatesFrontEndUser() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->fe_user instanceof tslib_feUserAuth
		);
	}

	public function testCreateFakeFrontEndCreatesContentObject() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->cObj instanceof tslib_cObj
		);
	}

	public function testCreateFakeFrontEndCreatesTemplate() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->tmpl instanceof t3lib_TStemplate
		);
	}

	public function testCreateFakeFrontEndReadsTypoScriptSetupFromPage() {
		$pageUid = $this->fixture->createFrontEndPage();
		$this->fixture->createTemplate(
			$pageUid,
			array('config' => 'foo = bar')
		);

		$this->fixture->createFakeFrontEnd($pageUid);

		$this->assertSame(
			'bar',
			$GLOBALS['TSFE']->tmpl->setup['foo']
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndWithTemplateRecordMarksTemplateAsLoaded() {
		$pageUid = $this->fixture->createFrontEndPage();
		$this->fixture->createTemplate(
			$pageUid,
			array('config' => 'foo = 42')
		);

		$this->fixture->createFakeFrontEnd($pageUid);

		$this->assertSame(
			1,
			$GLOBALS['TSFE']->tmpl->loaded
		);
	}

	public function testCreateFakeFrontEndCreatesConfiguration() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			is_array($GLOBALS['TSFE']->config)
		);
	}

	public function testLoginUserIsZeroAfterCreateFakeFrontEnd() {
		$this->fixture->createFakeFrontEnd();

		$this->assertSame(
			0,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndSetsDefaultGroupList() {
		$this->fixture->createFakeFrontEnd();

		$this->assertSame(
			'0,-1',
			$GLOBALS['TSFE']->gr_list
		);
	}

	public function testDiscardFakeFrontEndNullsOutGlobalFrontEnd() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();

		$this->assertNull(
			$GLOBALS['TSFE']
		);
	}

	public function testDiscardFakeFrontEndNullsOutGlobalTimeTrack() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();

		$this->assertNull(
			$GLOBALS['TT']
		);
	}

	public function testDiscardFakeFrontEndCanBeCalledTwoTimesInARow() {
		$this->fixture->discardFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();
	}

	public function testHasFakeFrontEndInitiallyIsFalse() {
		$this->assertFalse(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	public function testHasFakeFrontEndIsTrueAfterCreateFakeFrontEnd() {
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	public function testHasFakeFrontEndIsFalseAfterCreateAndDiscardFakeFrontEnd() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();

		$this->assertFalse(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function cleanUpDiscardsFakeFrontEnd() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->cleanUp();

		$this->assertFalse(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	public function testCreateFakeFrontEndReturnsProvidedPageUid() {
		$pageUid = $this->fixture->createFrontEndPage();

		$this->assertSame(
			$pageUid,
			$this->fixture->createFakeFrontEnd($pageUid)
		);
	}

	public function testCreateFakeFrontEndUsesProvidedPageUidAsFrontEndId() {
		$pageUid = $this->fixture->createFrontEndPage();
		$this->fixture->createFakeFrontEnd($pageUid);

		$this->assertSame(
			$pageUid,
			$GLOBALS['TSFE']->id
		);
	}

	public function testCreateFakeFrontThrowsExceptionForNegativePageUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$pageUid must be >= 0.'
		);

		$this->fixture->createFakeFrontEnd(-1);
	}

	// Note: In the unit tests, the src attribute of the generated image tag
	// will be empty because the IMAGE handles does not accept absolute paths
	// and handles relative paths and EXT: paths inconsistently:
	//
	// It correctly resolves paths which are relative to the TYPO3 document
	// root, but then calls t3lib_stdGraphic::getImageDimensions (which is
	// inherited by tslib_gifBuilder) which again uses the relative path. So
	// IMAGE will use the path to the TYPO3 root (which is the same as relative
	// to the FE index.php), but getImageDimensions use the path relative to the
	// executed script which is the FE index.php or the PHPUnit BE module
	// index.php. This results getImageDimensions not returning anything useful.
	public function testFakeFrontEndCObjImageCreatesImageTagForExistingImageFile() {
		$this->fixture->createFakeFrontEnd();

		$this->assertContains(
			'<img ',
			$GLOBALS['TSFE']->cObj->IMAGE(
				array('file' => 'typo3conf/ext/oelib/tests/fixtures/test.png')
			)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding user login and logout
	// ---------------------------------------------------------------------

	public function testIsLoggedInInitiallyIsFalse() {
		$this->fixture->createFakeFrontEnd();

		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}

	public function testIsLoggedThrowsExceptionWithoutFrontEnd() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please create a front end before calling isLoggedIn.'
		);

		$this->fixture->isLoggedIn();
	}

	public function testLoginFrontEndUserSwitchesToLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserSwitchesLoginManagerToLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertTrue(
			tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()
		);
	}

	public function testLoginFrontEndUserSetsLoginUserToOne() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertSame(
			1,
			$GLOBALS['TSFE']->loginUser
		);
	}

	public function testLoginFrontEndUserRetrievesNameOfUser() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser(
			'', array('name' => 'John Doe')
		);
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertSame(
			'John Doe',
			$GLOBALS['TSFE']->fe_user->user['name']
		);
	}

	public function testLoginFrontEndUserWithZeroUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The user ID must be > 0.'
		);

		$this->fixture->createFakeFrontEnd();

		$this->fixture->loginFrontEndUser(0);
	}

	public function testLoginFrontEndUserWithoutFrontEndThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please create a front end before calling loginFrontEndUser.'
		);

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);
	}

	public function testLoginFrontEndUserSetsGroupDataOfUser() {
		$this->fixture->createFakeFrontEnd();

		$feUserGroupUid = $this->fixture->createFrontEndUserGroup(
			array('title' => 'foo')
		);
		$feUserId = $this->fixture->createFrontEndUser($feUserGroupUid);
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertSame(
			array($feUserGroupUid => 'foo'),
			$GLOBALS['TSFE']->fe_user->groupData['title']
		);
	}

	public function testLogoutFrontEndUserAfterLoginSwitchesToNotLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);
		$this->fixture->logoutFrontEndUser();

		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function logoutFrontEndUserAfterLoginSwitchesLoginManagerToNotLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);
		$this->fixture->logoutFrontEndUser();

		$this->assertFalse(
			tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()
		);
	}

	public function testLogoutFrontEndUserSetsLoginUserToZero() {
		$this->fixture->createFakeFrontEnd();

		$this->fixture->logoutFrontEndUser();

		$this->assertSame(
			0,
			$GLOBALS['TSFE']->loginUser
		);
	}

	public function testLoginFrontEndUserNotInDatabaseSwitchesToLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUser = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array());
		$this->fixture->loginFrontEndUser($feUser->getUid());

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	public function testLoginFrontEndUserNotInDatabaseSetsLoginUserToOne() {
		$this->fixture->createFakeFrontEnd();

		$feUser = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array());
		$this->fixture->loginFrontEndUser($feUser->getUid());

		$this->assertSame(
			1,
			$GLOBALS['TSFE']->loginUser
		);
	}

	public function testLoginFrontEndUserNotInDatabaseRetrievesNameOfUser() {
		$this->fixture->createFakeFrontEnd();

		$feUser = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array('name' => 'John Doe'));
		$this->fixture->loginFrontEndUser($feUser->getUid());

		$this->assertSame(
			'John Doe',
			$GLOBALS['TSFE']->fe_user->user['name']
		);
	}

	public function testLoginFrontEndUserNotInDatabaseWithoutFrontEndThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please create a front end before calling loginFrontEndUser.'
		);

		$feUser = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array());
		$this->fixture->loginFrontEndUser($feUser->getUid());
	}

	public function testLoginFrontEndUserMappedAsGhostAndInDatabaseSetsGroupDataOfUser() {
		$this->fixture->createFakeFrontEnd();

		$feUserGroupUid = $this->fixture->createFrontEndUserGroup(
			array('title' => 'foo')
		);
		$feUser = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUser')
			->find($this->fixture->createFrontEndUser($feUserGroupUid));
		$this->fixture->loginFrontEndUser($feUser->getUid());

		$this->assertSame(
			array($feUserGroupUid => 'foo'),
			$GLOBALS['TSFE']->fe_user->groupData['title']
		);
	}

	public function testLogoutFrontEndUserWithoutFrontEndThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please create a front end before calling logoutFrontEndUser.'
		);

		$this->fixture->logoutFrontEndUser();
	}

	public function testLogoutFrontEndUserCanBeCalledTwoTimesInARow() {
		$this->fixture->createFakeFrontEnd();

		$this->fixture->logoutFrontEndUser();
		$this->fixture->logoutFrontEndUser();
	}

	public function testCreateAndLogInFrontEndUserCreatesFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->createAndLogInFrontEndUser();

		$this->assertSame(
			1,
			$this->fixture->countRecords('fe_users')
		);
	}

	public function testCreateAndLogInFrontEndUserWithRecordDataCreatesFrontEndUserWithThatData() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->createAndLogInFrontEndUser(
			'', array('name' => 'John Doe')
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords('fe_users', 'name = "John Doe"')
		);
	}

	public function testCreateAndLogInFrontEndUserLogsInFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->createAndLogInFrontEndUser();

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	public function testCreateAndLogInFrontEndUserWithFrontEndUserGroupCreatesFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$this->fixture->createAndLogInFrontEndUser($frontEndUserGroupUid);

		$this->assertSame(
			1,
			$this->fixture->countRecords('fe_users')
		);
	}

	public function testCreateAndLogInFrontEndUserWithFrontEndUserGroupCreatesFrontEndUserWithGivenGroup() {
		$this->fixture->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$frontEndUserUid = $this->fixture->createAndLogInFrontEndUser(
			$frontEndUserGroupUid
		);

		$dbResultRow = tx_oelib_db::selectSingle(
			'usergroup',
			'fe_users',
			'uid = ' . $frontEndUserUid
		);

		$this->assertSame(
			$frontEndUserGroupUid,
			intval($dbResultRow['usergroup'])
		);
	}

	public function testCreateAndLogInFrontEndUserWithFrontEndUserGroupDoesNotCreateAFrontEndUserGroup() {
		$this->fixture->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$this->fixture->createAndLogInFrontEndUser(
			$frontEndUserGroupUid
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords('fe_groups')
		);
	}

	public function testCreateAndLogInFrontEndUserWithFrontEndUserGroupLogsInFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$this->fixture->createAndLogInFrontEndUser($frontEndUserGroupUid);

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding increaseRelationCounter()
	// ---------------------------------------------------------------------

	public function testIncreaseRelationCounterIncreasesNonZeroFieldValueByOne() {
		$uid = $this->fixture->createRecord(
			OELIB_TESTTABLE,
			array('related_records' => 41)
		);

		$this->fixture->increaseRelationCounter(
			OELIB_TESTTABLE,
			$uid,
			'related_records'
		);

		$row = tx_oelib_db::selectSingle(
			'related_records',
			OELIB_TESTTABLE,
			'uid = ' . $uid
		);

		$this->assertSame(
			42,
			intval($row['related_records'])
		);
	}

	public function testIncreaseRelationCounterThrowsExceptionOnInvalidUid() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$invalidUid = $uid + 1;

		$this->setExpectedException(
			'BadMethodCallException',
			'The table ' . OELIB_TESTTABLE . ' does not contain a record with UID ' . $invalidUid . '.'
		);
		$this->fixture->increaseRelationCounter(
			OELIB_TESTTABLE,
			$invalidUid,
			'related_records'
		);
	}

	public function testIncreaseRelationCounterThrowsExceptionOnInvalidTableName() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "tx_oelib_inexistent" is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->increaseRelationCounter(
			'tx_oelib_inexistent',
			$uid,
			'related_records'
		);
	}

	public function testIncreaseRelationCounterThrowsExceptionOnInexistentFieldName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table ' . OELIB_TESTTABLE . ' has no column inexistent_column.'
		);

		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->increaseRelationCounter(
			OELIB_TESTTABLE,
			$uid,
			'inexistent_column'
		);
	}

	/**
	 * @test
	 */
	public function getDummyColumnNameForExtensionTableReturnsDummyColumnName() {
		$this->assertSame(
			'is_dummy_record',
			$this->fixture->getDummyColumnName('tx_oelib_test')
		);
	}

	/**
	 * @test
	 */
	public function getDummyColumnNameForSystemTableReturnsOelibPrefixedColumnName() {
		$this->assertSame(
			'tx_oelib_is_dummy_record',
			$this->fixture->getDummyColumnName('fe_users')
		);
	}

	/**
	 * @test
	 */
	public function getDummyColumnNameForThirdPartyExtensionTableReturnsPrefixedColumnName() {
		$testingFramework = new tx_oelib_testingFramework(
			'user_oelibtest', array('user_oelibtest2')
		);
		$this->assertSame(
			'user_oelibtest_is_dummy_record',
			$testingFramework->getDummyColumnName('user_oelibtest2_test')
		);
	}


	////////////////////////////////////////////
	// Tests concerning createBackEndUserGroup
	////////////////////////////////////////////

	public function test_createBackEndUserGroupForNoDataGiven_CreatesBackEndGroup() {
		$this->fixture->createBackEndUserGroup(array());

		$this->assertTrue(
			$this->fixture->existsRecord('be_groups')
		);
	}

	public function test_createBackEndUserGroupForNoDataGiven_ReturnsUidOfCreatedBackEndGroup() {
		$backendGroupUid = $this->fixture->createBackEndUserGroup(array());

		$this->assertTrue(
			$this->fixture->existsRecord(
				'be_groups', 'uid = ' . $backendGroupUid
			)
		);
	}

	public function test_createBackEndUserGroupForTitleGiven_StoresTitleInGroupRecord() {
		$this->fixture->createBackEndUserGroup(
			array('title' => 'foo group')
		);

		$this->assertTrue(
			$this->fixture->existsRecord(
				'be_groups', 'title = "foo group"'
			)
		);
	}
}
?>