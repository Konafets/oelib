<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2013 Mario Rimann (typo3-coding@rimann.org)
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

if (!defined('OELIB_TESTTABLE')) {
	define('OELIB_TESTTABLE', 'tx_oelib_test');
}
if (!defined('OELIB_TESTTABLE_MM')) {
	define('OELIB_TESTTABLE_MM', 'tx_oelib_test_article_mm');
}

/**
 * Test case.
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
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	private function markAsSkippedForNoZipArchive() {
		try {
			$this->fixture->checkForZipArchive();
		} catch (Exception $exception) {
			$this->markTestSkipped($exception->getMessage());
		}
	}


	/*
	 * Tests regarding markTableAsDirty()
	 */

	/**
	 * @test
	 */
	public function markTableAsDirty() {
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

	/**
	 * @test
	 */
	public function markTableAsDirtyWillCleanUpANonSystemTable() {
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

	/**
	 * @test
	 */
	public function markTableAsDirtyWillCleanUpASystemTable() {
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

	/**
	 * @test
	 */
	public function markTableAsDirtyWillCleanUpAdditionalAllowedTable() {
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

	/**
	 * @test
	 */
	public function markTableAsDirtyFailsOnInexistentTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "tx_oelib_DOESNOTEXIST" is not allowed for markTableAsDirty.'
		);
		$this->fixture->markTableAsDirty('tx_oelib_DOESNOTEXIST');
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyFailsOnNotAllowedSystemTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "sys_domain" is not allowed for markTableAsDirty.'
		);
		$this->fixture->markTableAsDirty('sys_domain');
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyFailsOnForeignTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "tx_seminars_seminars" is not allowed for markTableAsDirty.'
		);
		$this->fixture->markTableAsDirty('tx_seminars_seminars');
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyFailsWithEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "" is not allowed for markTableAsDirty.'
		);
		$this->fixture->markTableAsDirty('');
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyAcceptsCommaSeparatedListOfTableNames() {
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

	/**
	 * @test
	 */
	public function createRecordOnValidTableWithNoData() {
		$this->assertNotSame(
			0,
			$this->fixture->createRecord(OELIB_TESTTABLE, array())
		);
	}

	/**
	 * @test
	 */
	public function createRecordWithValidData() {
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

	/**
	 * @test
	 */
	public function createRecordOnInvalidTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "tx_oelib_DOESNOTEXIST" is not allowed.'
		);
		$this->fixture->createRecord('tx_oelib_DOESNOTEXIST', array());
	}

	/**
	 * @test
	 */
	public function createRecordWithEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "" is not allowed.'
		);
		$this->fixture->createRecord('', array());
	}

	/**
	 * @test
	 */
	public function createRecordWithUidFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('uid' => 99999)
		);
	}

	/**
	 * @test
	 */
	public function createRecordOnValidAdditionalAllowedTableWithValidDataSucceeds() {
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

	/**
	 * @test
	 */
	public function changeRecordWithExistingRecord() {
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

	/**
	 * @test
	 */
	public function changeRecordFailsOnForeignTable() {
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

	/**
	 * @test
	 */
	public function changeRecordFailsOnInexistentTable() {
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

	/**
	 * @test
	 */
	public function changeRecordOnAllowedSystemTableForPages() {
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

	/**
	 * @test
	 */
	public function changeRecordOnAllowedSystemTableForContent() {
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

	/**
	 * @test
	 */
	public function changeRecordFailsOnOtherSystemTable() {
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

	/**
	 * @test
	 */
	public function changeRecordOnAdditionalAllowedTableSucceeds() {
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

	/**
	 * @test
	 */
	public function changeRecordFailsWithUidZero() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $uid must not be zero.'
		);
		$this->fixture->changeRecord(OELIB_TESTTABLE, 0, array('title' => 'foo'));
	}

	/**
	 * @test
	 */
	public function changeRecordFailsWithEmptyData() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The array with the new record data must not be empty.'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());

		$this->fixture->changeRecord(
			OELIB_TESTTABLE, $uid, array()
		);
	}

	/**
	 * @test
	 */
	public function changeRecordFailsWithUidFieldInRecordData() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $recordData must not contain changes to the UID of a record.'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());

		$this->fixture->changeRecord(
			OELIB_TESTTABLE, $uid, array('uid' => '55742')
		);
	}

	/**
	 * @test
	 */
	public function changeRecordFailsWithDummyRecordFieldInRecordData() {
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

	/**
	 * @test
	 */
	public function changeRecordFailsOnInexistentRecord() {
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

	/**
	 * @test
	 */
	public function deleteRecordOnValidDummyRecord() {
		// Creates and directly destroys a dummy record.
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid);

		// Checks whether the record really was removed from the database.
		$this->assertSame(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid=' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function deleteRecordOnValidDummyRecordOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		// Creates and directly destroys a dummy record.
		$uid = $this->fixture->createRecord('user_oelibtest_test', array());
		$this->fixture->deleteRecord('user_oelibtest_test', $uid);
	}

	/**
	 * @test
	 */
	public function deleteRecordOnInexistentRecord() {
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

	/**
	 * @test
	 */
	public function deleteRecordOnForeignTable() {
		$table = 'tx_seminars_seminars';
		$uid = 99999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->deleteRecord($table, $uid);
	}

	/**
	 * @test
	 */
	public function deleteRecordOnInexistentTable() {
		$table = 'tx_oelib_DOESNOTEXIST';
		$uid = 99999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->deleteRecord($table, $uid);
	}

	/**
	 * @test
	 */
	public function deleteRecordWithEmptyTableName() {
		$table = '';
		$uid = 99999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->deleteRecord($table, $uid);
	}

	/**
	 * @test
	 */
	public function deleteRecordOnNonTestRecordNotDeletesRecord() {
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

	/**
	 * @test
	 */
	public function createRelationWithValidData() {
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

	/**
	 * @test
	 */
	public function createRelationWithValidDataOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$uidLocal = $this->fixture->createRecord('user_oelibtest_test');
		$uidForeign = $this->fixture->createRecord('user_oelibtest_test');

		$this->fixture->createRelation(
			'user_oelibtest_test_article_mm', $uidLocal, $uidForeign
		);
	}

	/**
	 * @test
	 */
	public function createRelationWithInvalidTable() {
		$table = 'tx_oelib_test_DOESNOTEXIST_mm';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->createRelation($table, $uidLocal, $uidForeign);
	}

	/**
	 * @test
	 */
	public function createRelationWithEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "" is not allowed.'
		);
		$this->fixture->createRelation('', 99999, 199999);
	}

	/**
	 * @test
	 */
	public function createRelationWithZeroFirstUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidLocal must be an integer > 0, but actually is "0"'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(OELIB_TESTTABLE_MM, 0, $uid);
	}

	/**
	 * @test
	 */
	public function createRelationWithZeroSecondUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidForeign must be an integer > 0, but actually is "0"'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(OELIB_TESTTABLE_MM, $uid, 0);
	}

	/**
	 * @test
	 */
	public function createRelationWithNegativeFirstUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidLocal must be an integer > 0, but actually is "-1"'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(OELIB_TESTTABLE_MM, -1, $uid);
	}

	/**
	 * @test
	 */
	public function createRelationWithNegativeSecondUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidForeign must be an integer > 0, but actually is "-1"'
		);
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->createRelation(OELIB_TESTTABLE_MM, $uid, -1);
	}


	/**
	 * @test
	 */
	public function createRelationWithAutomaticSorting() {
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

	/**
	 * @test
	 */
	public function createRelationWithManualSorting() {
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

	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterIncreasesZeroValueCounterByOne() {
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

	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterIncreasesNonZeroValueCounterToOne() {
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

	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterCreatesRecordInRelationTable() {
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

	/**
	 * @test
	 */
	public function removeRelationOnValidDummyRecord() {
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

	/**
	 * @test
	 */
	public function removeRelationOnValidDummyRecordOnAdditionalAllowedTableSucceeds() {
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

	/**
	 * @test
	 */
	public function removeRelationOnInexistentRecord() {
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

	/**
	 * @test
	 */
	public function removeRelationOnForeignTable() {
		$table = 'tx_seminars_seminars_places_mm';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
	}

	/**
	 * @test
	 */
	public function removeRelationOnInexistentTable() {
		$table = 'tx_oelib_DOESNOTEXIST';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
	}

	/**
	 * @test
	 */
	public function removeRelationWithEmptyTableName() {
		$table = '';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "' . $table . '" is not allowed.'
		);
		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
	}

	/**
	 * @test
	 */
	public function removeRelationOnRealRecordNotRemovesRelation() {
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

	/**
	 * @test
	 */
	public function createListOfAllowedTablesContainsOurTestTable() {
		$allowedTables = $this->fixture->getListOfOwnAllowedTableNames();
		$this->assertContains(
			OELIB_TESTTABLE,
			$allowedTables
		);
	}

	/**
	 * @test
	 */
	public function createListOfAllowedTablesDoesNotContainForeignTables() {
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

	/**
	 * @test
	 */
	public function createListOfAdditionalAllowedTablesContainsOurTestTable() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$allowedTables = $this->fixture->getListOfAdditionalAllowedTableNames();
		$this->assertContains(
			'user_oelibtest_test',
			$allowedTables
		);
	}

	/**
	 * @test
	 */
	public function createListOfAdditionalAllowedTablesDoesNotContainForeignTables() {
		$allowedTables = $this->fixture->getListOfAdditionalAllowedTableNames();
		$this->assertNotContains(
			'be_users',
			$allowedTables
		);
	}

	/**
	 * @test
	 */
	public function createListOfAdditionalAllowedTablesContainsOurTestTables() {
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

	/**
	 * @test
	 */
	public function getAutoIncrementReturnsOneForTruncatedTable() {
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

	/**
	 * @test
	 */
	public function getAutoIncrementGetsCurrentAutoIncrement() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);

		// $uid will equals be the previous auto increment value, so $uid + 1
		// should be equal to the current auto inrement value.
		$this->assertSame(
			$uid + 1,
			$this->fixture->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function getAutoIncrementForFeUsersTableIsAllowed() {
		$this->fixture->getAutoIncrement('fe_users');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementForPagesTableIsAllowed() {
		$this->fixture->getAutoIncrement('pages');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementForTtContentTableIsAllowed() {
		$this->fixture->getAutoIncrement('tt_content');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithOtherSystemTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('sys_domains');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithEmptyTableNameFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithForeignTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('tx_seminars_seminars');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithInexistentTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('tx_oelib_DOESNOTEXIST');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithTableWithoutUidFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->fixture->getAutoIncrement('OELIB_TESTTABLE_MM');
	}


	// ---------------------------------------------------------------------
	// Tests regarding countRecords()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function countRecordsWithEmptyWhereClauseIsAllowed() {
		$this->fixture->countRecords(OELIB_TESTTABLE, '');
	}

	/**
	 * @test
	 */
	public function countRecordsWithMissingWhereClauseIsAllowed() {
		$this->fixture->countRecords(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function countRecordsWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->countRecords('');
	}

	/**
	 * @test
	 */
	public function countRecordsWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$table = 'foo_bar';
		$this->fixture->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithFeGroupsTableIsAllowed() {
		$table = 'fe_groups';
		$this->fixture->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithFeUsersTableIsAllowed() {
		$table = 'fe_users';
		$this->fixture->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithPagesTableIsAllowed() {
		$table = 'pages';
		$this->fixture->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithTtContentTableIsAllowed() {
		$table = 'tt_content';
		$this->fixture->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithOtherTableThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->countRecords('sys_domain');
	}

	/**
	 * @test
	 */
	public function countRecordsReturnsZeroForNoMatches() {
		$this->assertSame(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function countRecordsReturnsOneForOneDummyRecordMatch() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function countRecordsWithMissingWhereClauseReturnsOneForOneDummyRecordMatch() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function countRecordsReturnsTwoForTwoMatches() {
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

	/**
	 * @test
	 */
	public function countRecordsForPagesTableIsAllowed() {
		$this->fixture->countRecords('pages');
	}

	/**
	 * @test
	 */
	public function countRecordsIgnoresNonDummyRecords() {
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

	/**
	 * @test
	 */
	public function existsRecordWithEmptyWhereClauseIsAllowed() {
		$this->fixture->existsRecord(OELIB_TESTTABLE, '');
	}

	/**
	 * @test
	 */
	public function existsRecordWithMissingWhereClauseIsAllowed() {
		$this->fixture->existsRecord(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function existsRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->existsRecord('');
	}

	/**
	 * @test
	 */
	public function existsRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$table = 'foo_bar';
		$this->fixture->existsRecord($table);
	}

	/**
	 * @test
	 */
	public function existsRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			$this->fixture->existsRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForOneMatchReturnsTrue() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->existsRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForTwoMatchesReturnsTrue() {
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

	/**
	 * @test
	 */
	public function existsRecordIgnoresNonDummyRecords() {
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

	/**
	 * @test
	 */
	public function existsRecordWithUidWithZeroUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->fixture->existsRecordWithUid(OELIB_TESTTABLE, 0);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->fixture->existsRecordWithUid(OELIB_TESTTABLE, -1);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->existsRecordWithUid('', 1);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$table = 'foo_bar';
		$this->fixture->existsRecordWithUid($table, 1);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForNoMatcheReturnsFalse() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid);

		$this->assertFalse(
			$this->fixture->existsRecordWithUid(
				OELIB_TESTTABLE, $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForAMatchReturnsTrue() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->assertTrue(
			$this->fixture->existsRecordWithUid(OELIB_TESTTABLE, $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidIgnoresNonDummyRecords() {
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

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed() {
		$this->fixture->existsExactlyOneRecord(OELIB_TESTTABLE, '');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed() {
		$this->fixture->existsExactlyOneRecord(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->existsExactlyOneRecord('');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$table = 'foo_bar';
		$this->fixture->existsExactlyOneRecord($table);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			$this->fixture->existsExactlyOneRecord(
				OELIB_TESTTABLE, 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForOneMatchReturnsTrue() {
		$this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->existsExactlyOneRecord(
				OELIB_TESTTABLE, 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForTwoMatchesReturnsFalse() {
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

	/**
	 * @test
	 */
	public function existsExactlyOneRecordIgnoresNonDummyRecords() {
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

	/**
	 * @test
	 */
	public function resetAutoIncrementForTestTableSucceeds() {
		$latestUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertSame(
			$latestUid,
			$this->fixture->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForUnchangedTestTableCanBeRun() {
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		// Creates and deletes a record and then resets the auto increment.
		$latestUid = $this->fixture->createRecord('user_oelibtest_test');
		$this->fixture->deleteRecord('user_oelibtest_test', $latestUid);
		$this->fixture->resetAutoIncrement('user_oelibtest_test');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForTableWithoutUidIsAllowed() {
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE_MM);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForFeUsersTableIsAllowed() {
		$this->fixture->resetAutoIncrement('fe_users');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForPagesTableIsAllowed() {
		$this->fixture->resetAutoIncrement('pages');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForTtContentTableIsAllowed() {
		$this->fixture->resetAutoIncrement('tt_content');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementWithOtherSystemTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrement('sys_domains');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementWithEmptyTableNameFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrement('');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementWithForeignTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrement('tx_seminars_seminars');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementWithInexistentTableFails() {
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

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForTestTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForTableWithoutUidIsAllowed() {
		$this->fixture->resetAutoIncrementLazily(OELIB_TESTTABLE_MM);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForFeUsersTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('fe_users');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForPagesTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('pages');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForTtContentTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('tt_content');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyWithOtherSystemTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrementLazily('sys_domains');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyWithEmptyTableNameFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrementLazily('');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyWithForeignTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrementLazily('tx_seminars_seminars');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyWithInexistentTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->fixture->resetAutoIncrementLazily('tx_oelib_DOESNOTEXIST');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyDoesNothingAfterOneNewRecordByDefault() {
		$oldAutoIncrement = $this->fixture->getAutoIncrement(OELIB_TESTTABLE);

		$latestUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->fixture->resetAutoIncrementLazily(OELIB_TESTTABLE);

		$this->assertNotSame(
			$oldAutoIncrement,
			$this->fixture->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyCleansUpsAfterOneNewRecordWithThreshholdOfOne() {
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

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyCleansUpsAfter100NewRecordsByDefault() {
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

	/**
	 * @test
	 */
	public function setResetAutoIncrementThresholdForOneIsAllowed() {
		$this->fixture->setResetAutoIncrementThreshold(1);
	}

	/**
	 * @test
	 */
	public function setResetAutoIncrementThresholdFor100IsAllowed() {
		$this->fixture->setResetAutoIncrementThreshold(100);
	}

	/**
	 * @test
	 */
	public function setResetAutoIncrementThresholdForZeroFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$threshold must be > 0.'
		);

		$this->fixture->setResetAutoIncrementThreshold(0);
	}

	/**
	 * @test
	 */
	public function setResetAutoIncrementThresholdForMinus1Fails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$threshold must be > 0.'
		);

		$this->fixture->setResetAutoIncrementThreshold(-1);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndPage()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function frontEndPageCanBeCreated() {
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

	/**
	 * @test
	 */
	public function createFrontEndPageSetsCorrectDocumentType() {
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

	/**
	 * @test
	 */
	public function frontEndPageWillBeCreatedOnRootPage() {
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

	/**
	 * @test
	 */
	public function frontEndPageCanBeCreatedOnOtherPage() {
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

	/**
	 * @test
	 */
	public function frontEndPageCanBeDirty() {
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

	/**
	 * @test
	 */
	public function frontEndPageWillBeCleanedUp() {
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

	/**
	 * @test
	 */
	public function frontEndPageHasNoTitleByDefault() {
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

	/**
	 * @test
	 */
	public function frontEndPageCanHaveTitle() {
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

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('pid' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('pid' => 99999));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('uid' => 99999));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoZeroDocumentType() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('doktype' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoNonZeroDocumentType() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->fixture->createFrontEndPage(0, array('doktype' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createSystemFolder()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function systemFolderCanBeCreated() {
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

	/**
	 * @test
	 */
	public function createSystemFolderSetsCorrectDocumentType() {
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

	/**
	 * @test
	 */
	public function systemFolderWillBeCreatedOnRootPage() {
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

	/**
	 * @test
	 */
	public function systemFolderCanBeCreatedOnOtherPage() {
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

	/**
	 * @test
	 */
	public function systemFolderCanBeDirty() {
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

	/**
	 * @test
	 */
	public function systemFolderWillBeCleanedUp() {
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

	/**
	 * @test
	 */
	public function systemFolderHasNoTitleByDefault() {
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

	/**
	 * @test
	 */
	public function systemFolderCanHaveTitle() {
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

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('pid' => 0));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('pid' => 99999));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('uid' => 99999));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoZeroDoktype() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('doktype' => 0));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoNonZeroDoktype() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->fixture->createSystemFolder(0, array('doktype' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createContentElement()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function contentElementCanBeCreated() {
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

	/**
	 * @test
	 */
	public function contentElementWillBeCreatedOnRootPage() {
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

	/**
	 * @test
	 */
	public function contentElementCanBeCreatedOnNonRootPage() {
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

	/**
	 * @test
	 */
	public function contentElementCanBeDirty() {
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

	/**
	 * @test
	 */
	public function contentElementWillBeCleanedUp() {
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

	/**
	 * @test
	 */
	public function contentElementHasNoHeaderByDefault() {
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

	/**
	 * @test
	 */
	public function contentElementCanHaveHeader() {
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

	/**
	 * @test
	 */
	public function contentElementIsTextElementByDefault() {
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

	/**
	 * @test
	 */
	public function contentElementCanHaveOtherType() {
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

	/**
	 * @test
	 */
	public function contentElementMustHaveNoZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createContentElement(0, array('pid' => 0));
	}

	/**
	 * @test
	 */
	public function contentElementMustHaveNoNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createContentElement(0, array('pid' => 99999));
	}

	/**
	 * @test
	 */
	public function contentElementMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createContentElement(0, array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function contentElementMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createContentElement(0, array('uid' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createTemplate()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function templateCanBeCreatedOnNonRootPage() {
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

	/**
	 * @test
	 */
	public function templateCannotBeCreatedOnRootPage() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$pageId must be > 0.'
		);

		$this->fixture->createTemplate(0);
	}

	/**
	 * @test
	 */
	public function templateCannotBeCreatedWithNegativePageNumber() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$pageId must be > 0.'
		);

		$this->fixture->createTemplate(-1);
	}

	/**
	 * @test
	 */
	public function templateCanBeDirty() {
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

	/**
	 * @test
	 */
	public function templateWillBeCleanedUp() {
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

	/**
	 * @test
	 */
	public function templateInitiallyHasNoConfig() {
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

	/**
	 * @test
	 */
	public function templateCanHaveConfig() {
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

	/**
	 * @test
	 */
	public function templateConfigIsReadableAsTsTemplate() {
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

	/**
	 * @test
	 */
	public function templateInitiallyHasNoConstants() {
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

	/**
	 * @test
	 */
	public function templateCanHaveConstants() {
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

	/**
	 * @test
	 */
	public function templateConstantsAreUsedInTsSetup() {
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

	/**
	 * @test
	 */
	public function templateMustNotHaveAZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createTemplate(42, array('pid' => 0));
	}

	/**
	 * @test
	 */
	public function templateMustNotHaveANonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->fixture->createTemplate(42, array('pid' => 99999));
	}

	/**
	 * @test
	 */
	public function templateMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createTemplate(42, array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function templateMustNotHaveANonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->fixture->createTemplate(42, array('uid' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createDummyFile()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function createDummyFileCreatesFile() {
		$dummyFile = $this->fixture->createDummyFile();

		$this->assertTrue(file_exists($dummyFile));
	}

	/**
	 * @test
	 */
	public function createDummyFileCreatesFileInSubfolder() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$dummyFile = $this->fixture->createDummyFile(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder) .
				'/test.txt'
		);

		$this->assertTrue(file_exists($dummyFile));
	}

	/**
	 * @test
	 */
	public function createDummyFileCreatesFileWithTheProvidedContent() {
		$dummyFile = $this->fixture->createDummyFile('test.txt', 'Hello world!');

		$this->assertSame('Hello world!', file_get_contents($dummyFile));
	}

	/**
	 * @test
	 */
	public function createDummyFileForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->fixture->createDummyFile();

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));
	}

	/**
	 * @test
	 */
	public function createDummyFileForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->fixture->createDummyFile();

		$this->assertTrue(file_exists($dummyFile));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createDummyZipArchive()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function createDummyZipArchiveForNoContentProvidedCreatesZipArchive() {
		$this->markAsSkippedForNoZipArchive();

		$dummyFile = $this->fixture->createDummyZipArchive();

		$this->assertTrue(file_exists($dummyFile));
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForFileNameInSubFolderProvidedCreatesZipArchiveInSubFolder() {
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

	/**
	 * @test
	 */
	public function createDummyZipArchiveForNoContentProvidedCreatesZipArchiveWithDummyFile() {
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

	/**
	 * @test
	 */
	public function createDummyZipArchiveForFileProvidedCreatesZipArchiveWithThatFile() {
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

	/**
	 * @test
	 */
	public function createDummyZipArchiveForFileProvidedWithContentCreatesZipArchiveWithThatFileAndContentInIt() {
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

	/**
	 * @test
	 */
	public function createDummyZipArchiveForTwoFilesProvidedCreatesZipArchiveWithTheseFiles() {
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

	/**
	 * @test
	 */
	public function createDummyZipArchiveForFileInSubFolderOfUploadFolderProvidedCreatesZipArchiveWithFileInSubFolder() {
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

	/**
	 * @test
	 */
	public function createDummyZipArchiveForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->fixture->createDummyZipArchive();

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->fixture->createDummyZipArchive();

		$this->assertTrue(file_exists($dummyFile));
	}


	// ---------------------------------------------------------------------
	// Tests regarding deleteDummyFile()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function deleteDummyFileDeletesCreatedDummyFile() {
		$dummyFile = $this->fixture->createDummyFile();
		$this->fixture->deleteDummyFile(basename($dummyFile));

		$this->assertFalse(file_exists($dummyFile));
	}

	/**
	 * @test
	 */
	public function deleteDummyFileWithAlreadyDeletedFileThrowsNoException() {
		$dummyFile = $this->fixture->createDummyFile();
		unlink($dummyFile);

		$this->fixture->deleteDummyFile(basename($dummyFile));
	}

	/**
	 * @test
	 */
	public function deleteDummyFileWithInexistentFileThrowsException() {
		$uniqueFileName = $this->fixture->getUniqueFileOrFolderPath('test.txt');

		$this->setExpectedException(
			'InvalidArgumentException',
			'The file "' . $uniqueFileName . '" which you are ' .
				'trying to delete does not exist and has never been created by this instance of the testing framework.'
		);

		$this->fixture->deleteDummyFile(basename($uniqueFileName));
	}

	/**
	 * @test
	 */
	public function deleteDummyFileWithForeignFileThrowsException() {
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

	/**
	 * @test
	 */
	public function createDummyFolderCreatesFolder() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($dummyFolder));
	}

	/**
	 * @test
	 */
	public function createDummyFolderCanCreateFolderInDummyFolder() {
		$outerDummyFolder = $this->fixture->createDummyFolder('test_folder');
		$innerDummyFolder = $this->fixture->createDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->assertTrue(is_dir($innerDummyFolder));
	}

	/**
	 * @test
	 */
	public function createDummyFolderForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->fixture->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));
	}

	/**
	 * @test
	 */
	public function createDummyFolderForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($dummyFolder));
	}


	// ---------------------------------------------------------------------
	// Tests regarding deleteDummyFolder()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function deleteDummyFolderDeletesCreatedDummyFolder() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);

		$this->assertFalse(is_dir($dummyFolder));
	}

	/**
	 * @test
	 */
	public function deleteDummyFolderWithInexistentFolderThrowsException() {
		$uniqueFolderName = $this->fixture->getUniqueFileOrFolderPath('test_folder');

		$this->setExpectedException(
			'InvalidArgumentException',
			'The folder "' . $uniqueFolderName . '" which you are trying to delete does not exist.'
		);

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($uniqueFolderName)
		);
	}

	/**
	 * @test
	 */
	public function deleteDummyFolderWithForeignFolderThrowsException() {
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

	/**
	 * @test
	 */
	public function deleteDummyFolderCanDeleteCreatedDummyFolderInDummyFolder() {
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

	/**
	 * @test
	 *
	 * @expectedException PHPUnit_Framework_Error_Warning
	 */
	public function deleteDummyFolderWithNonEmptyDummyFolderRaisesWarning() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 6002000) {
			$this->markTestSkipped('This test is available in TYPO3 below version 6.2.');
		}

		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$this->fixture->createDummyFile(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder) .
			'/test.txt'
		);

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException t3lib_exception
	 */
	public function deleteDummyFolderWithNonEmptyDummyFolderThrowsException() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6002000) {
			$this->markTestSkipped('This test is available in TYPO3 6.2 and above.');
		}

		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$this->fixture->createDummyFile(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder) .
			'/test.txt'
		);

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);
	}

	/**
	 * @test
	 */
	public function deleteDummyFolderWithFolderNameConsistingOnlyOfNumbersDoesNotThrowAnException() {
		$dummyFolder = $this->fixture->createDummyFolder('123');

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding set- and getUploadFolderPath()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function getUploadFolderPathReturnsUploadFolderPathIncludingTablePrefix() {
		$this->assertRegExp(
			'/\/uploads\/tx_oelib\/$/',
			$this->fixture->getUploadFolderPath()
		);
	}

	/**
	 * @test
	 */
	public function getUploadFolderPathAfterSetReturnsSetUploadFolderPath() {
		$this->fixture->setUploadFolderPath('/foo/bar/');

		$this->assertSame(
			'/foo/bar/',
			$this->fixture->getUploadFolderPath()
		);
	}

	/**
	 * @test
	 */
	public function setUploadFolderPathAfterCreatingADummyFileThrowsException() {
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

	/**
	 * @test
	 */
	public function getPathRelativeToUploadDirectoryWithPathOutsideUploadDirectoryThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The first parameter $absolutePath is not within the calling extension\'s upload directory.'
		);

		$this->fixture->getPathRelativeToUploadDirectory(PATH_site);
	}


	// ---------------------------------------------------------------------
	// Tests regarding getUniqueFileOrFolderPath()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function getUniqueFileOrFolderPathWithEmptyPathThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The first parameter $path must not be empty.'
		);

		$this->fixture->getUniqueFileOrFolderPath('');
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndUserGroup()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function frontEndUserGroupCanBeCreated() {
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

	/**
	 * @test
	 */
	public function frontEndUserGroupTableCanBeDirty() {
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

	/**
	 * @test
	 */
	public function frontEndUserGroupTableWillBeCleanedUp() {
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

	/**
	 * @test
	 */
	public function frontEndUserGroupHasNoTitleByDefault() {
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

	/**
	 * @test
	 */
	public function frontEndUserGroupCanHaveATitle() {
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

	/**
	 * @test
	 */
	public function frontEndUserGroupMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUserGroup(array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndUserGroupMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUserGroup(array('uid' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndUser()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function frontEndUserCanBeCreated() {
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

	/**
	 * @test
	 */
	public function frontEndUserTableCanBeDirty() {
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

	/**
	 * @test
	 */
	public function frontEndUserTableWillBeCleanedUp() {
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

	/**
	 * @test
	 */
	public function frontEndUserHasNoUserNameByDefault() {
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

	/**
	 * @test
	 */
	public function frontEndUserCanHaveAUserName() {
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

	/**
	 * @test
	 */
	public function frontEndUserCanHaveSeveralUserGroups() {
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

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser('', array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser('', array('uid' => 99999));
	}

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoZeroUserGroupInTheDataArray() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "usergroup" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser('', array('usergroup' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoNonZeroUserGroupInTheDataArray() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "usergroup" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser('', array('usergroup' => 99999));
	}

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoUserGroupListInTheDataArray() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "usergroup" must not be set in $recordData.'
		);

		$this->fixture->createFrontEndUser(
			'', array('usergroup' => '1,2,4,5')
		);
	}

	/**
	 * @test
	 */
	public function createFrontEndUserWithEmptyGroupCreatesGroup() {
		$this->fixture->createFrontEndUser('');

		$this->assertTrue(
			$this->fixture->existsExactlyOneRecord('fe_groups')
		);
	}

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoZeroUserGroupEvenIfSeveralGroupsAreProvided() {
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

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoAlphabeticalCharactersInTheUserGroupList() {
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

	/**
	 * @test
	 */
	public function createBackEndUserReturnsUidGreaterZero() {
		$this->assertNotSame(
			0,
			$this->fixture->createBackEndUser()
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserCreatesBackEndUserRecordInTheDatabase() {
		$this->assertSame(
			1,
			$this->fixture->countRecords(
				'be_users', 'uid=' . $this->fixture->createBackEndUser()
			)
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserMarksBackEndUserTableAsDirty() {
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

	/**
	 * @test
	 */
	public function createBackEndUserCreatesRecordWithoutUserNameByDefault() {
		$uid = $this->fixture->createBackEndUser();

		$row = tx_oelib_db::selectSingle('username', 'be_users', 'uid = ' . $uid);

		$this->assertSame(
			'',
			$row['username']
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserForUserNameProvidedCreatesRecordWithUserName() {
		$uid = $this->fixture->createBackEndUser(array('username' => 'Test name'));

		$row = tx_oelib_db::selectSingle('username', 'be_users', 'uid = ' . $uid);

		$this->assertSame(
			'Test name',
			$row['username']
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserWithZeroUidProvidedInRecordDataThrowsExeption() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createBackEndUser(array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function createBackEndUserWithNonZeroUidProvidedInRecordDataThrowsExeption() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->fixture->createBackEndUser(array('uid' => 999999));
	}


	// ---------------------------------------------------------------------
	// Tests concerning fakeFrontend
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesGlobalFrontEnd() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE'] instanceof tslib_fe
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndReturnsPositivePageUidIfCalledWithoutParameters() {
		$this->assertGreaterThan(
			0,
			$this->fixture->createFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndReturnsCurrentFrontEndPageUid() {
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

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesSysPage() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->sys_page instanceof t3lib_pageSelect
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesFrontEndUser() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->fe_user instanceof tslib_feUserAuth
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesContentObject() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->cObj instanceof tslib_cObj
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesTemplate() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->tmpl instanceof t3lib_TStemplate
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndReadsTypoScriptSetupFromPage() {
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

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesConfiguration() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			is_array($GLOBALS['TSFE']->config)
		);
	}

	/**
	 * @test
	 */
	public function loginUserIsZeroAfterCreateFakeFrontEnd() {
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

	/**
	 * @test
	 */
	public function discardFakeFrontEndNullsOutGlobalFrontEnd() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();

		$this->assertNull(
			$GLOBALS['TSFE']
		);
	}

	/**
	 * @test
	 */
	public function discardFakeFrontEndNullsOutGlobalTimeTrack() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();

		$this->assertNull(
			$GLOBALS['TT']
		);
	}

	/**
	 * @test
	 */
	public function discardFakeFrontEndCanBeCalledTwoTimesInARow() {
		$this->fixture->discardFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function hasFakeFrontEndInitiallyIsFalse() {
		$this->assertFalse(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function hasFakeFrontEndIsTrueAfterCreateFakeFrontEnd() {
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function hasFakeFrontEndIsFalseAfterCreateAndDiscardFakeFrontEnd() {
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

	/**
	 * @test
	 */
	public function createFakeFrontEndReturnsProvidedPageUid() {
		$pageUid = $this->fixture->createFrontEndPage();

		$this->assertSame(
			$pageUid,
			$this->fixture->createFakeFrontEnd($pageUid)
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndUsesProvidedPageUidAsFrontEndId() {
		$pageUid = $this->fixture->createFrontEndPage();
		$this->fixture->createFakeFrontEnd($pageUid);

		$this->assertSame(
			$pageUid,
			$GLOBALS['TSFE']->id
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontThrowsExceptionForNegativePageUid() {
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
	/**
	 * @test
	 */
	public function fakeFrontEndCObjImageCreatesImageTagForExistingImageFile() {
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

	/**
	 * @test
	 */
	public function isLoggedInInitiallyIsFalse() {
		$this->fixture->createFakeFrontEnd();

		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function isLoggedThrowsExceptionWithoutFrontEnd() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please create a front end before calling isLoggedIn.'
		);

		$this->fixture->isLoggedIn();
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserSwitchesToLoggedIn() {
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

	/**
	 * @test
	 */
	public function loginFrontEndUserSetsLoginUserToOne() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertSame(
			1,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserRetrievesNameOfUser() {
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

	/**
	 * @test
	 */
	public function loginFrontEndUserWithZeroUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The user ID must be > 0.'
		);

		$this->fixture->createFakeFrontEnd();

		$this->fixture->loginFrontEndUser(0);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserWithoutFrontEndThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please create a front end before calling loginFrontEndUser.'
		);

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserSetsGroupDataOfUser() {
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

	/**
	 * @test
	 */
	public function logoutFrontEndUserAfterLoginSwitchesToNotLoggedIn() {
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

	/**
	 * @test
	 */
	public function logoutFrontEndUserSetsLoginUserToZero() {
		$this->fixture->createFakeFrontEnd();

		$this->fixture->logoutFrontEndUser();

		$this->assertSame(
			0,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserNotInDatabaseSwitchesToLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUser = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array());
		$this->fixture->loginFrontEndUser($feUser->getUid());

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserNotInDatabaseSetsLoginUserToOne() {
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

	/**
	 * @test
	 */
	public function loginFrontEndUserNotInDatabaseRetrievesNameOfUser() {
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

	/**
	 * @test
	 */
	public function loginFrontEndUserNotInDatabaseWithoutFrontEndThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please create a front end before calling loginFrontEndUser.'
		);

		$feUser = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array());
		$this->fixture->loginFrontEndUser($feUser->getUid());
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserMappedAsGhostAndInDatabaseSetsGroupDataOfUser() {
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

	/**
	 * @test
	 */
	public function logoutFrontEndUserWithoutFrontEndThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please create a front end before calling logoutFrontEndUser.'
		);

		$this->fixture->logoutFrontEndUser();
	}

	/**
	 * @test
	 */
	public function logoutFrontEndUserCanBeCalledTwoTimesInARow() {
		$this->fixture->createFakeFrontEnd();

		$this->fixture->logoutFrontEndUser();
		$this->fixture->logoutFrontEndUser();
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserCreatesFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->createAndLogInFrontEndUser();

		$this->assertSame(
			1,
			$this->fixture->countRecords('fe_users')
		);
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserWithRecordDataCreatesFrontEndUserWithThatData() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->createAndLogInFrontEndUser(
			'', array('name' => 'John Doe')
		);

		$this->assertSame(
			1,
			$this->fixture->countRecords('fe_users', 'name = "John Doe"')
		);
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserLogsInFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->createAndLogInFrontEndUser();

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserWithFrontEndUserGroupCreatesFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$this->fixture->createAndLogInFrontEndUser($frontEndUserGroupUid);

		$this->assertSame(
			1,
			$this->fixture->countRecords('fe_users')
		);
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserWithFrontEndUserGroupCreatesFrontEndUserWithGivenGroup() {
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

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserWithFrontEndUserGroupDoesNotCreateAFrontEndUserGroup() {
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

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserWithFrontEndUserGroupLogsInFrontEndUser() {
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

	/**
	 * @test
	 */
	public function increaseRelationCounterIncreasesNonZeroFieldValueByOne() {
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

	/**
	 * @test
	 */
	public function increaseRelationCounterThrowsExceptionOnInvalidUid() {
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

	/**
	 * @test
	 */
	public function increaseRelationCounterThrowsExceptionOnInvalidTableName() {
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

	/**
	 * @test
	 */
	public function increaseRelationCounterThrowsExceptionOnInexistentFieldName() {
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

	/**
	 * @test
	 */
	public function createBackEndUserGroupForNoDataGivenCreatesBackEndGroup() {
		$this->fixture->createBackEndUserGroup(array());

		$this->assertTrue(
			$this->fixture->existsRecord('be_groups')
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserGroupForNoDataGivenReturnsUidOfCreatedBackEndGroup() {
		$backendGroupUid = $this->fixture->createBackEndUserGroup(array());

		$this->assertTrue(
			$this->fixture->existsRecord(
				'be_groups', 'uid = ' . $backendGroupUid
			)
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserGroupForTitleGivenStoresTitleInGroupRecord() {
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