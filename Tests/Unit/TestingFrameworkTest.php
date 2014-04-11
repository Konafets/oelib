<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2014 Mario Rimann (typo3-coding@rimann.org)
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
	/**
	 * @var string
	 */
	define('OELIB_TESTTABLE', 'tx_oelib_test');
}
if (!defined('OELIB_TESTTABLE_MM')) {
	/**
	 * @var string
	 */
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
class Tx_Oelib_TestingFrameworkTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $subject;

	/**
	 * @var string absolute path to a "foreign" file which was created for test
	 *             purposes and which should be deleted in tearDown(); this is
	 *             needed for testDeleteDummyFileWithForeignFileThrowsException
	 */
	protected $foreignFileToDelete = '';

	/**
	 * @var string absolute path to a "foreign" folder which was created for
	 *             test purposes and which should be deleted in tearDown();
	 *             this is needed for
	 *             testDeleteDummyFolderWithForeignFolderThrowsException
	 */
	protected $foreignFolderToDelete = '';

	/**
	 * backed-up extension configuration of the TYPO3 configuration variables
	 *
	 * @var array
	 */
	protected $extConfBackup = array();

	/**
	 * backed-up T3_VAR configuration
	 *
	 * @var array
	 */
	protected $t3VarBackup = array();

	public function setUp() {
		$this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		$this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];

		$this->subject = new Tx_Oelib_TestingFramework(
			'tx_oelib', array('user_oelibtest')
		);
	}

	public function tearDown() {
		$this->subject->setResetAutoIncrementThreshold(1);
		$this->subject->cleanUp();
		$this->subject->purgeHooks();
		$this->deleteForeignFile();
		$this->deleteForeignFolder();

		unset($this->subject);

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
		$GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
	}


	/*
	 * Utility functions.
	 */

	/**
	 * Checks whether TYPO3 is in version 6.0.0 or higher. If it is lower, the current test will be skipped.
	 *
	 * @return void
	 */
	protected function checkForTypo3SixOrHigher() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6000000) {
			$this->markTestSkipped('This test is available in TYPO3 6.0 and above.');
		}
	}

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
	protected function getSortingOfRelation($uidLocal, $uidForeign) {
		$row = Tx_Oelib_Db::selectSingle(
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
	protected function checkIfExtensionUserOelibtestIsLoaded() {
		if (!t3lib_extMgm::isLoaded('user_oelibtest')) {
			$this->markTestSkipped(
				'The extension user_oelibtest is not installed, but needs to be installed. Please install it.'
			);
		}
	}

	/**
	 * Checks whether the extension user_oelibtest2 is currently loaded and lets
	 * a test fail if the extension is not loaded.
	 *
	 * @return void
	 */
	protected function checkIfExtensionUserOelibtest2IsLoaded() {
		if (!t3lib_extMgm::isLoaded('user_oelibtest')) {
			$this->markTestSkipped(
				'The extension user_oelibtest2 is not installed, but needs to be installed. Please install it.'
			);
		}
	}

	/**
	 * Deletes a "foreign" file which was created for test purposes.
	 *
	 * @return void
	 */
	protected function deleteForeignFile() {
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
	protected function deleteForeignFolder() {
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
	protected function markAsSkippedForNoZipArchive() {
		try {
			$this->subject->checkForZipArchive();
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
			$this->subject->getListOfDirtyTables()
		);

		$this->subject->createRecord(OELIB_TESTTABLE, array());
		$this->assertSame(
			array(
				OELIB_TESTTABLE => OELIB_TESTTABLE
			),
			$this->subject->getListOfDirtyTables()
		);
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyWillCleanUpNonSystemTable() {
		$uid = Tx_Oelib_Db::insert(
			OELIB_TESTTABLE, array('is_dummy_record' => 1)
		);

		$this->subject->markTableAsDirty(OELIB_TESTTABLE);
		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords(OELIB_TESTTABLE, 'uid=' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyWillCleanUpSystemTable() {
		$uid = Tx_Oelib_Db::insert (
			'pages', array('tx_oelib_is_dummy_record' => 1)
		);

		$this->subject->markTableAsDirty('pages');
		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords('pages', 'uid=' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyWillCleanUpAdditionalAllowedTable() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$uid = Tx_Oelib_Db::insert(
			'user_oelibtest_test', array('tx_oelib_is_dummy_record' => 1)
		);

		$this->subject->markTableAsDirty('user_oelibtest_test');
		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords('user_oelibtest_test', 'uid=' . $uid)
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
		$this->subject->markTableAsDirty('tx_oelib_DOESNOTEXIST');
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyFailsOnNotAllowedSystemTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "sys_domain" is not allowed for markTableAsDirty.'
		);
		$this->subject->markTableAsDirty('sys_domain');
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyFailsOnForeignTable() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "tx_seminars_seminars" is not allowed for markTableAsDirty.'
		);
		$this->subject->markTableAsDirty('tx_seminars_seminars');
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyFailsWithEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "" is not allowed for markTableAsDirty.'
		);
		$this->subject->markTableAsDirty('');
	}

	/**
	 * @test
	 */
	public function markTableAsDirtyAcceptsCommaSeparatedListOfTableNames() {
		$this->subject->markTableAsDirty(OELIB_TESTTABLE.','.OELIB_TESTTABLE_MM);
		$this->assertSame(
			array(
				OELIB_TESTTABLE => OELIB_TESTTABLE,
				OELIB_TESTTABLE_MM => OELIB_TESTTABLE_MM
			),
			$this->subject->getListOfDirtyTables()
		);
	}


	/*
	 * Tests regarding createRecord()
	 */

	/**
	 * @test
	 */
	public function createRecordOnValidTableWithNoData() {
		$this->assertNotSame(
			0,
			$this->subject->createRecord(OELIB_TESTTABLE, array())
		);
	}

	/**
	 * @test
	 */
	public function createRecordWithValidData() {
		$title = 'TEST record';
		$uid = $this->subject->createRecord(
			OELIB_TESTTABLE,
			array(
				'title' => $title
			)
		);
		$this->assertNotSame(
			0,
			$uid
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$this->subject->createRecord('tx_oelib_DOESNOTEXIST', array());
	}

	/**
	 * @test
	 */
	public function createRecordWithEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "" is not allowed.'
		);
		$this->subject->createRecord('', array());
	}

	/**
	 * @test
	 */
	public function createRecordWithUidFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('uid' => 99999)
		);
	}

	/**
	 * @test
	 */
	public function createRecordOnValidAdditionalAllowedTableWithValidDataSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$title = 'TEST record';
		$this->subject->createRecord(
			'user_oelibtest_test',
			array(
				'title' => $title
			)
		);
	}


	/*
	 * Tests regarding changeRecord()
	 */

	/**
	 * @test
	 */
	public function changeRecordWithExistingRecord() {
		$uid = $this->subject->createRecord(
			OELIB_TESTTABLE,
			array('title' => 'foo')
		);

		$this->subject->changeRecord(
			OELIB_TESTTABLE,
			$uid,
			array('title' => 'bar')
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$this->subject->changeRecord(
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
		$this->subject->changeRecord(
			'tx_oelib_DOESNOTEXIST',
			99999,
			array('title' => 'foo')
		);
	}

	/**
	 * @test
	 */
	public function changeRecordOnAllowedSystemTableForPages() {
		$pid = $this->subject->createFrontEndPage(0, array('title' => 'foo'));

		$this->subject->changeRecord(
			'pages',
			$pid,
			array('title' => 'bar')
		);

		$this->assertSame(
			1,
			$this->subject->countRecords('pages', 'uid='.$pid.' AND title="bar"')
		);
	}

	/**
	 * @test
	 */
	public function changeRecordOnAllowedSystemTableForContent() {
		$pid = $this->subject->createFrontEndPage(0, array('title' => 'foo'));
		$uid = $this->subject->createContentElement(
			$pid,
			array('titleText' => 'foo')
		);

		$this->subject->changeRecord(
			'tt_content',
			$uid,
			array('titleText' => 'bar')
		);

		$this->assertSame(
			1,
			$this->subject->countRecords('tt_content', 'uid=' . $uid.' AND titleText="bar"')
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
		$this->subject->changeRecord(
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

		$uid = $this->subject->createRecord(
			'user_oelibtest_test',
			array('title' => 'foo')
		);

		$this->subject->changeRecord(
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
		$this->subject->changeRecord(OELIB_TESTTABLE, 0, array('title' => 'foo'));
	}

	/**
	 * @test
	 */
	public function changeRecordFailsWithEmptyData() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The array with the new record data must not be empty.'
		);
		$uid = $this->subject->createRecord(OELIB_TESTTABLE, array());

		$this->subject->changeRecord(
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
		$uid = $this->subject->createRecord(OELIB_TESTTABLE, array());

		$this->subject->changeRecord(
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
		$uid = $this->subject->createRecord(OELIB_TESTTABLE, array());

		$this->subject->changeRecord(
			OELIB_TESTTABLE, $uid, array('is_dummy_record' => 0)
		);
	}

	/**
	 * @test
	 */
	public function changeRecordFailsOnInexistentRecord() {
		$uid = $this->subject->createRecord(OELIB_TESTTABLE, array());
		$this->setExpectedException(
			'BadMethodCallException',
			'There is no record with UID ' . ($uid + 1) . ' on table "' . OELIB_TESTTABLE . '".'
		);

		$this->subject->changeRecord(
			OELIB_TESTTABLE, $uid + 1, array('title' => 'foo')
		);
	}


	/*
	 * Tests regarding deleteRecord()
	 */

	/**
	 * @test
	 */
	public function deleteRecordOnValidDummyRecord() {
		// Creates and directly destroys a dummy record.
		$uid = $this->subject->createRecord(OELIB_TESTTABLE, array());
		$this->subject->deleteRecord(OELIB_TESTTABLE, $uid);

		// Checks whether the record really was removed from the database.
		$this->assertSame(
			0,
			$this->subject->countRecords(OELIB_TESTTABLE, 'uid=' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function deleteRecordOnValidDummyRecordOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		// Creates and directly destroys a dummy record.
		$uid = $this->subject->createRecord('user_oelibtest_test', array());
		$this->subject->deleteRecord('user_oelibtest_test', $uid);
	}

	/**
	 * @test
	 */
	public function deleteRecordOnInexistentRecord() {
		$uid = 99999;

		// Checks that the record is inexistent before testing on it.
		$this->assertSame(
			0,
			$this->subject->countRecords(OELIB_TESTTABLE, 'uid=' . $uid)
		);

		// Runs our delete function - it should run through even when it can't
		// delete a record.
		$this->subject->deleteRecord(OELIB_TESTTABLE, $uid);
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
		$this->subject->deleteRecord($table, $uid);
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
		$this->subject->deleteRecord($table, $uid);
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
		$this->subject->deleteRecord($table, $uid);
	}

	/**
	 * @test
	 */
	public function deleteRecordOnNonTestRecordNotDeletesRecord() {
		// Create a new record that looks like a real record, i.e. the
		// is_dummy_record flag is set to 0.
		$uid = Tx_Oelib_Db::insert(
			OELIB_TESTTABLE,
			array(
				'title' => 'TEST',
				'is_dummy_record' => 0
			)
		);

		// Runs our delete method which should NOT affect the record created
		// above.
		$this->subject->deleteRecord(OELIB_TESTTABLE, $uid);

		// Remembers whether the record still exists.
		$counter = Tx_Oelib_Db::count(OELIB_TESTTABLE, 'uid = ' . $uid);

		// Deletes the record as it will not be caught by the clean up function.
		Tx_Oelib_Db::delete(
			OELIB_TESTTABLE,
			'uid = ' . $uid . ' AND is_dummy_record = 0'
		);

		// Checks whether the record still had existed.
		$this->assertSame(
			1,
			$counter
		);
	}


	/*
	 * Tests regarding createRelation()
	 */

	/**
	 * @test
	 */
	public function createRelationWithValidData() {
		$uidLocal = $this->subject->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->subject->createRecord(OELIB_TESTTABLE);

		$this->subject->createRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);

		// Checks whether the record really exists.
		$this->assertSame(
			1,
			$this->subject->countRecords(
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

		$uidLocal = $this->subject->createRecord('user_oelibtest_test');
		$uidForeign = $this->subject->createRecord('user_oelibtest_test');

		$this->subject->createRelation(
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
		$this->subject->createRelation($table, $uidLocal, $uidForeign);
	}

	/**
	 * @test
	 */
	public function createRelationWithEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "" is not allowed.'
		);
		$this->subject->createRelation('', 99999, 199999);
	}

	/**
	 * @test
	 */
	public function createRelationWithZeroFirstUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidLocal must be an integer > 0, but actually is "0"'
		);
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->createRelation(OELIB_TESTTABLE_MM, 0, $uid);
	}

	/**
	 * @test
	 */
	public function createRelationWithZeroSecondUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidForeign must be an integer > 0, but actually is "0"'
		);
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->createRelation(OELIB_TESTTABLE_MM, $uid, 0);
	}

	/**
	 * @test
	 */
	public function createRelationWithNegativeFirstUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidLocal must be an integer > 0, but actually is "-1"'
		);
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->createRelation(OELIB_TESTTABLE_MM, -1, $uid);
	}

	/**
	 * @test
	 */
	public function createRelationWithNegativeSecondUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidForeign must be an integer > 0, but actually is "-1"'
		);
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->createRelation(OELIB_TESTTABLE_MM, $uid, -1);
	}


	/**
	 * @test
	 */
	public function createRelationWithAutomaticSorting() {
		$uidLocal = $this->subject->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->createRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);
		$previousSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
		$this->assertGreaterThan(
			0,
			$previousSorting
		);


		$uidForeign = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->createRelation(
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
		$uidLocal = $this->subject->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->subject->createRecord(OELIB_TESTTABLE);
		$sorting = 99999;

		$this->subject->createRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign, $sorting
		);

		$this->assertSame(
			$sorting,
			$this->getSortingOfRelation($uidLocal, $uidForeign)
		);
	}


	/*
	 * Tests regarding createRelationFromTca()
	 */

	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterIncreasesZeroValueCounterByOne() {
		$firstRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);

		$this->subject->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'related_records'
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$firstRecordUid = $this->subject->createRecord(
			OELIB_TESTTABLE,
			array('related_records' => 1)
		);
		$secondRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);

		$this->subject->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'related_records'
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$firstRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);

		$this->subject->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'related_records'
		);

		$count = $this->subject->countRecords(
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
		$firstRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);

		$this->subject->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'bidirectional'
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$firstRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);

		$this->subject->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'bidirectional'
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$firstRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);
		$secondRecordUid = $this->subject->createRecord(OELIB_TESTTABLE);

		$this->subject->createRelationAndUpdateCounter(
			OELIB_TESTTABLE,
			$firstRecordUid,
			$secondRecordUid,
			'bidirectional'
		);

		$count = $this->subject->countRecords(
			OELIB_TESTTABLE_MM,
			'uid_local=' . $secondRecordUid . ' AND uid_foreign=' .
				$firstRecordUid
		);
		$this->assertSame(
			1,
			$count
		);
	}


	/*
	 * Tests regarding removeRelation()
	 */

	/**
	 * @test
	 */
	public function removeRelationOnValidDummyRecord() {
		$uidLocal = $this->subject->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->subject->createRecord(OELIB_TESTTABLE);

		// Creates and directly destroys a dummy record.
		$this->subject->createRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);
		$this->subject->removeRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);

		// Checks whether the record really was removed from the database.
		$this->assertSame(
			0,
			$this->subject->countRecords(
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

		$uidLocal = $this->subject->createRecord('user_oelibtest_test');
		$uidForeign = $this->subject->createRecord('user_oelibtest_test');

		// Creates and directly destroys a dummy record.
		$this->subject->createRelation(
			'user_oelibtest_test_article_mm', $uidLocal, $uidForeign
		);
		$this->subject->removeRelation(
			'user_oelibtest_test_article_mm', $uidLocal, $uidForeign
		);
	}

	/**
	 * @test
	 */
	public function removeRelationOnInexistentRecord() {
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);
		$uidLocal = $uid + 1;
		$uidForeign = $uid + 2;

		// Checks that the record is inexistent before testing on it.
		$this->assertSame(
			0,
			$this->subject->countRecords(
				OELIB_TESTTABLE_MM,
				'uid_local=' . $uidLocal.' AND uid_foreign=' . $uidForeign
			)
		);

		// Runs our delete function - it should run through even when it can't
		// delete a record.
		$this->subject->removeRelation(
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
		$this->subject->removeRelation($table, $uidLocal, $uidForeign);
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
		$this->subject->removeRelation($table, $uidLocal, $uidForeign);
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
		$this->subject->removeRelation($table, $uidLocal, $uidForeign);
	}

	/**
	 * @test
	 */
	public function removeRelationOnRealRecordNotRemovesRelation() {
		$uidLocal = $this->subject->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->subject->createRecord(OELIB_TESTTABLE);

		// Create a new record that looks like a real record, i.e. the
		// is_dummy_record flag is set to 0.
		Tx_Oelib_Db::insert(
			OELIB_TESTTABLE_MM,
			array(
				'uid_local' => $uidLocal,
				'uid_foreign' => $uidForeign,
				'is_dummy_record' => 0
			)
		);

		// Runs our delete method which should NOT affect the record created
		// above.
		$this->subject->removeRelation(
			OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
		);

		// Caches the value that will be tested for later. We need to use the
		// following order to make sure the test record gets deleted even if
		// this test fails:
		// 1. reads the value to test
		// 2. deletes the test record
		// 3. tests the previously read value (and possibly fails)
		$numberOfCreatedRelations = Tx_Oelib_Db::count(
			OELIB_TESTTABLE_MM,
			'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign
		);

		// Deletes the record as it will not be caught by the clean up function.
		Tx_Oelib_Db::delete(
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


	/*
	 * Tests regarding cleanUp()
	 */

	/**
	 * @test
	 */
	public function cleanUpWithRegularCleanUp() {
		// Creates a dummy record (and marks that table as dirty).
		$this->subject->createRecord(OELIB_TESTTABLE);

		// Creates a dummy record directly in the database, without putting this
		// table name to the list of dirty tables.
		Tx_Oelib_Db::insert(
			OELIB_TESTTABLE_MM, array('is_dummy_record' => 1)
		);

		// Runs a regular clean up. This should now delete only the first record
		// which was created through the testing framework and thus that table
		// is on the list of dirty tables. The second record was directly put
		// into the database and it's table is not on this list and will not be
		// removed by a regular clean up run.
		$this->subject->cleanUp();

		// Checks whether the first dummy record is deleted.
		$this->assertSame(
			0,
			$this->subject->countRecords(OELIB_TESTTABLE),
			'Some test records were not deleted from table "tx_oelib_test"'
		);

		// Checks whether the second dummy record still exists.
		$this->assertSame(
			1,
			$this->subject->countRecords(OELIB_TESTTABLE_MM)
		);

		// Runs a deep clean up to delete all dummy records.
		$this->subject->cleanUp(TRUE);
	}

	/**
	 * @test
	 */
	public function cleanUpWithDeepCleanup() {
		// Creates a dummy record (and marks that table as dirty).
		$this->subject->createRecord(OELIB_TESTTABLE);

		// Creates a dummy record directly in the database without putting this
		// table name to the list of dirty tables.
		Tx_Oelib_Db::insert(
			OELIB_TESTTABLE_MM, array('is_dummy_record' => 1)
		);

		// Deletes all dummy records.
		$this->subject->cleanUp(TRUE);

		// Checks whether ALL dummy records were deleted (independent of the
		// list of dirty tables).
		$allowedTables = $this->subject->getListOfDirtyTables();
		foreach ($allowedTables as $currentTable) {
			$this->assertSame(
				0,
				$this->subject->countRecords($currentTable),
				'Some test records were not deleted from table "'.$currentTable.'"'
			);
		}
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedDummyFile() {
		$fileName = $this->subject->createDummyFile();

		$this->subject->cleanUp();

		$this->assertFalse(file_exists($fileName));
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedDummyFolder() {
		$folderName = $this->subject->createDummyFolder('test_folder');

		$this->subject->cleanUp();

		$this->assertFalse(file_exists($folderName));
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedNestedDummyFolders() {
		$outerDummyFolder = $this->subject->createDummyFolder('test_folder');
		$innerDummyFolder = $this->subject->createDummyFolder(
			$this->subject->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->subject->cleanUp();

		$this->assertFalse(
			file_exists($outerDummyFolder) && file_exists($innerDummyFolder)
		);
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedDummyUploadFolder() {
		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->subject->createDummyFile();

		$this->assertTrue(is_dir($this->subject->getUploadFolderPath()));

		$this->subject->cleanUp();

		$this->assertFalse(is_dir($this->subject->getUploadFolderPath()));
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

		$this->subject->cleanUp();
	}


	/*
	 * Tests regarding createListOfAllowedTables()
	 *
	 * The method is called in the constructor of the subject.
	 */

	/**
	 * @test
	 */
	public function createListOfAllowedTablesContainsOurTestTable() {
		$allowedTables = $this->subject->getListOfOwnAllowedTableNames();
		$this->assertContains(
			OELIB_TESTTABLE,
			$allowedTables
		);
	}

	/**
	 * @test
	 */
	public function createListOfAllowedTablesDoesNotContainForeignTables() {
		$allowedTables = $this->subject->getListOfOwnAllowedTableNames();
		$this->assertNotContains(
			'be_users',
			$allowedTables
		);
	}


	/*
	 * Tests regarding createListOfAdditionalAllowedTables()
	 *
	 * (That method is called in the constructor of the subject.)
	 */

	/**
	 * @test
	 */
	public function createListOfAdditionalAllowedTablesContainsOurTestTable() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$allowedTables = $this->subject->getListOfAdditionalAllowedTableNames();
		$this->assertContains(
			'user_oelibtest_test',
			$allowedTables
		);
	}

	/**
	 * @test
	 */
	public function createListOfAdditionalAllowedTablesDoesNotContainForeignTables() {
		$allowedTables = $this->subject->getListOfAdditionalAllowedTableNames();
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

		$subject = new Tx_Oelib_TestingFramework(
			'tx_oelib', array('user_oelibtest', 'user_oelibtest2')
		);

		$allowedTables = $subject->getListOfAdditionalAllowedTableNames();
		$this->assertContains(
			'user_oelibtest_test',
			$allowedTables
		);
		$this->assertContains(
			'user_oelibtest2_test',
			$allowedTables
		);
	}


	/*
	 * Tests regarding getAutoIncrement()
	 */

	/**
	 * @test
	 */
	public function getAutoIncrementReturnsOneForTruncatedTable() {
		Tx_Oelib_Db::enableQueryLogging();
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'TRUNCATE TABLE ' . OELIB_TESTTABLE . ';'
		);
		if (!$dbResult) {
			throw new tx_oelib_Exception_Database();
		}

		$this->assertSame(
			1,
			$this->subject->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function getAutoIncrementGetsCurrentAutoIncrement() {
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);

		// $uid will equals be the previous auto increment value, so $uid + 1
		// should be equal to the current auto inrement value.
		$this->assertSame(
			$uid + 1,
			$this->subject->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function getAutoIncrementForFeUsersTableIsAllowed() {
		$this->subject->getAutoIncrement('fe_users');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementForPagesTableIsAllowed() {
		$this->subject->getAutoIncrement('pages');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementForTtContentTableIsAllowed() {
		$this->subject->getAutoIncrement('tt_content');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithOtherSystemTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->subject->getAutoIncrement('sys_domains');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithEmptyTableNameFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->subject->getAutoIncrement('');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithForeignTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->subject->getAutoIncrement('tx_seminars_seminars');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithInexistentTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->subject->getAutoIncrement('tx_oelib_DOESNOTEXIST');
	}

	/**
	 * @test
	 */
	public function getAutoIncrementWithTableWithoutUidFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->subject->getAutoIncrement('OELIB_TESTTABLE_MM');
	}


	/*
	 * Tests regarding countRecords()
	 */

	/**
	 * @test
	 */
	public function countRecordsWithEmptyWhereClauseIsAllowed() {
		$this->subject->countRecords(OELIB_TESTTABLE, '');
	}

	/**
	 * @test
	 */
	public function countRecordsWithMissingWhereClauseIsAllowed() {
		$this->subject->countRecords(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function countRecordsWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->countRecords('');
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
		$this->subject->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithFeGroupsTableIsAllowed() {
		$table = 'fe_groups';
		$this->subject->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithFeUsersTableIsAllowed() {
		$table = 'fe_users';
		$this->subject->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithPagesTableIsAllowed() {
		$table = 'pages';
		$this->subject->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithTtContentTableIsAllowed() {
		$table = 'tt_content';
		$this->subject->countRecords($table);
	}

	/**
	 * @test
	 */
	public function countRecordsWithOtherTableThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->countRecords('sys_domain');
	}

	/**
	 * @test
	 */
	public function countRecordsReturnsZeroForNoMatches() {
		$this->assertSame(
			0,
			$this->subject->countRecords(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function countRecordsReturnsOneForOneDummyRecordMatch() {
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertSame(
			1,
			$this->subject->countRecords(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function countRecordsWithMissingWhereClauseReturnsOneForOneDummyRecordMatch() {
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertSame(
			1,
			$this->subject->countRecords(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function countRecordsReturnsTwoForTwoMatches() {
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertSame(
			2,
			$this->subject->countRecords(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function countRecordsForPagesTableIsAllowed() {
		$this->subject->countRecords('pages');
	}

	/**
	 * @test
	 */
	public function countRecordsIgnoresNonDummyRecords() {
		Tx_Oelib_Db::insert(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$testResult = $this->subject->countRecords(
			OELIB_TESTTABLE, 'title = "foo"'
		);

		Tx_Oelib_Db::delete(
			OELIB_TESTTABLE,
			'title = "foo"'
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->subject->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertSame(
			0,
			$testResult
		);
	}


	/*
	 * Tests regarding existsRecord()
	 */

	/**
	 * @test
	 */
	public function existsRecordWithEmptyWhereClauseIsAllowed() {
		$this->subject->existsRecord(OELIB_TESTTABLE, '');
	}

	/**
	 * @test
	 */
	public function existsRecordWithMissingWhereClauseIsAllowed() {
		$this->subject->existsRecord(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function existsRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->existsRecord('');
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
		$this->subject->existsRecord($table);
	}

	/**
	 * @test
	 */
	public function existsRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			$this->subject->existsRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForOneMatchReturnsTrue() {
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->subject->existsRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForTwoMatchesReturnsTrue() {
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->subject->existsRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function existsRecordIgnoresNonDummyRecords() {
		Tx_Oelib_Db::insert(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$testResult = $this->subject->existsRecord(
			OELIB_TESTTABLE, 'title = "foo"'
		);

		Tx_Oelib_Db::delete(
			OELIB_TESTTABLE,
			'title = "foo"'
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->subject->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertFalse(
			$testResult
		);
	}


	/*
	 * Tests regarding existsRecordWithUid()
	 */

	/**
	 * @test
	 */
	public function existsRecordWithUidWithZeroUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->subject->existsRecordWithUid(OELIB_TESTTABLE, 0);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->subject->existsRecordWithUid(OELIB_TESTTABLE, -1);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->existsRecordWithUid('', 1);
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
		$this->subject->existsRecordWithUid($table, 1);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForNoMatcheReturnsFalse() {
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->deleteRecord(OELIB_TESTTABLE, $uid);

		$this->assertFalse(
			$this->subject->existsRecordWithUid(
				OELIB_TESTTABLE, $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForMatchReturnsTrue() {
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);

		$this->assertTrue(
			$this->subject->existsRecordWithUid(OELIB_TESTTABLE, $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidIgnoresNonDummyRecords() {
		$uid = Tx_Oelib_Db::insert(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$testResult = $this->subject->existsRecordWithUid(
			OELIB_TESTTABLE, $uid
		);

		Tx_Oelib_Db::delete(
			OELIB_TESTTABLE, 'uid = ' . $uid
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->subject->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertFalse(
			$testResult
		);
	}


	/*
	 * Tests regarding existsExactlyOneRecord()
	 */

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed() {
		$this->subject->existsExactlyOneRecord(OELIB_TESTTABLE, '');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed() {
		$this->subject->existsExactlyOneRecord(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->existsExactlyOneRecord('');
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
		$this->subject->existsExactlyOneRecord($table);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			$this->subject->existsExactlyOneRecord(
				OELIB_TESTTABLE, 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForOneMatchReturnsTrue() {
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->subject->existsExactlyOneRecord(
				OELIB_TESTTABLE, 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForTwoMatchesReturnsFalse() {
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$this->subject->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertFalse(
			$this->subject->existsExactlyOneRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordIgnoresNonDummyRecords() {
		Tx_Oelib_Db::insert(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$testResult = $this->subject->existsExactlyOneRecord(
			OELIB_TESTTABLE, 'title = "foo"'
		);

		Tx_Oelib_Db::delete(
			OELIB_TESTTABLE,
			'title = "foo"'
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->subject->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertFalse(
			$testResult
		);
	}


	/*
	 * Tests regarding resetAutoIncrement()
	 */

	/**
	 * @test
	 */
	public function resetAutoIncrementForTestTableSucceeds() {
		$latestUid = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->subject->resetAutoIncrement(OELIB_TESTTABLE);

		$this->assertSame(
			$latestUid,
			$this->subject->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForUnchangedTestTableCanBeRun() {
		$this->subject->resetAutoIncrement(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		// Creates and deletes a record and then resets the auto increment.
		$latestUid = $this->subject->createRecord('user_oelibtest_test');
		$this->subject->deleteRecord('user_oelibtest_test', $latestUid);
		$this->subject->resetAutoIncrement('user_oelibtest_test');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForTableWithoutUidIsAllowed() {
		$this->subject->resetAutoIncrement(OELIB_TESTTABLE_MM);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForFeUsersTableIsAllowed() {
		$this->subject->resetAutoIncrement('fe_users');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForPagesTableIsAllowed() {
		$this->subject->resetAutoIncrement('pages');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementForTtContentTableIsAllowed() {
		$this->subject->resetAutoIncrement('tt_content');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementWithOtherSystemTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->resetAutoIncrement('sys_domains');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementWithEmptyTableNameFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->resetAutoIncrement('');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementWithForeignTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->resetAutoIncrement('tx_seminars_seminars');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementWithInexistentTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->resetAutoIncrement('tx_oelib_DOESNOTEXIST');
	}


	/*
	 * Tests regarding resetAutoIncrementLazily() and
	 * setResetAutoIncrementThreshold
	 */

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForTestTableIsAllowed() {
		$this->subject->resetAutoIncrementLazily(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForTableWithoutUidIsAllowed() {
		$this->subject->resetAutoIncrementLazily(OELIB_TESTTABLE_MM);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForFeUsersTableIsAllowed() {
		$this->subject->resetAutoIncrementLazily('fe_users');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForPagesTableIsAllowed() {
		$this->subject->resetAutoIncrementLazily('pages');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyForTtContentTableIsAllowed() {
		$this->subject->resetAutoIncrementLazily('tt_content');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyWithOtherSystemTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->resetAutoIncrementLazily('sys_domains');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyWithEmptyTableNameFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->resetAutoIncrementLazily('');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyWithForeignTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->resetAutoIncrementLazily('tx_seminars_seminars');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyWithInexistentTableFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
		);

		$this->subject->resetAutoIncrementLazily('tx_oelib_DOESNOTEXIST');
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyDoesNothingAfterOneNewRecordByDefault() {
		$oldAutoIncrement = $this->subject->getAutoIncrement(OELIB_TESTTABLE);

		$latestUid = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->subject->resetAutoIncrementLazily(OELIB_TESTTABLE);

		$this->assertNotSame(
			$oldAutoIncrement,
			$this->subject->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyCleansUpsAfterOneNewRecordWithThreshholdOfOne() {
		$oldAutoIncrement = $this->subject->getAutoIncrement(OELIB_TESTTABLE);
		$this->subject->setResetAutoIncrementThreshold(1);

		$latestUid = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->subject->resetAutoIncrementLazily(OELIB_TESTTABLE);

		$this->assertSame(
			$oldAutoIncrement,
			$this->subject->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function resetAutoIncrementLazilyCleansUpsAfter100NewRecordsByDefault() {
		$oldAutoIncrement = $this->subject->getAutoIncrement(OELIB_TESTTABLE);

		for ($i = 0; $i < 100; $i++) {
			$latestUid = $this->subject->createRecord(OELIB_TESTTABLE);
			$this->subject->deleteRecord(OELIB_TESTTABLE, $latestUid);
		}

		$this->subject->resetAutoIncrementLazily(OELIB_TESTTABLE);

		$this->assertSame(
			$oldAutoIncrement,
			$this->subject->getAutoIncrement(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function setResetAutoIncrementThresholdForOneIsAllowed() {
		$this->subject->setResetAutoIncrementThreshold(1);
	}

	/**
	 * @test
	 */
	public function setResetAutoIncrementThresholdFor100IsAllowed() {
		$this->subject->setResetAutoIncrementThreshold(100);
	}

	/**
	 * @test
	 */
	public function setResetAutoIncrementThresholdForZeroFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$threshold must be > 0.'
		);

		$this->subject->setResetAutoIncrementThreshold(0);
	}

	/**
	 * @test
	 */
	public function setResetAutoIncrementThresholdForMinus1Fails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$threshold must be > 0.'
		);

		$this->subject->setResetAutoIncrementThreshold(-1);
	}


	/*
	 * Tests regarding createFrontEndPage()
	 */

	/**
	 * @test
	 */
	public function frontEndPageCanBeCreated() {
		$uid = $this->subject->createFrontEndPage();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->subject->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function createFrontEndPageSetsCorrectDocumentType() {
		$uid = $this->subject->createFrontEndPage();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$uid = $this->subject->createFrontEndPage();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$parent = $this->subject->createFrontEndPage();
		$uid = $this->subject->createFrontEndPage($parent);

		$this->assertNotSame(
			0,
			$uid
		);

		$row = Tx_Oelib_Db::selectSingle(
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
			count($this->subject->getListOfDirtySystemTables())
		);
		$uid = $this->subject->createFrontEndPage();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->subject->getListOfDirtySystemTables())
		);
	}

	/**
	 * @test
	 */
	public function frontEndPageWillBeCleanedUp() {
		$uid = $this->subject->createFrontEndPage();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function frontEndPageHasNoTitleByDefault() {
		$uid = $this->subject->createFrontEndPage();

		$row = Tx_Oelib_Db::selectSingle(
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
		$uid = $this->subject->createFrontEndPage(
			0,
			array('title' => 'Test title')
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$this->subject->createFrontEndPage(0, array('pid' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->subject->createFrontEndPage(0, array('pid' => 99999));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->subject->createFrontEndPage(0, array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->subject->createFrontEndPage(0, array('uid' => 99999));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoZeroDocumentType() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->subject->createFrontEndPage(0, array('doktype' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndPageMustHaveNoNonZeroDocumentType() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->subject->createFrontEndPage(0, array('doktype' => 99999));
	}


	/*
	 * Tests regarding createSystemFolder()
	 */

	/**
	 * @test
	 */
	public function systemFolderCanBeCreated() {
		$uid = $this->subject->createSystemFolder();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->subject->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function createSystemFolderSetsCorrectDocumentType() {
		$uid = $this->subject->createSystemFolder();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$uid = $this->subject->createSystemFolder();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$parent = $this->subject->createSystemFolder();
		$uid = $this->subject->createSystemFolder($parent);

		$this->assertNotSame(
			0,
			$uid
		);

		$row = Tx_Oelib_Db::selectSingle(
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
			count($this->subject->getListOfDirtySystemTables())
		);
		$uid = $this->subject->createSystemFolder();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->subject->getListOfDirtySystemTables())
		);
	}

	/**
	 * @test
	 */
	public function systemFolderWillBeCleanedUp() {
		$uid = $this->subject->createSystemFolder();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function systemFolderHasNoTitleByDefault() {
		$uid = $this->subject->createSystemFolder();

		$row = Tx_Oelib_Db::selectSingle(
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
		$uid = $this->subject->createSystemFolder(
			0,
			array('title' => 'Test title')
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$this->subject->createSystemFolder(0, array('pid' => 0));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->subject->createSystemFolder(0, array('pid' => 99999));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->subject->createSystemFolder(0, array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->subject->createSystemFolder(0, array('uid' => 99999));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoZeroDoktype() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->subject->createSystemFolder(0, array('doktype' => 0));
	}

	/**
	 * @test
	 */
	public function systemFolderMustHaveNoNonZeroDoktype() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "doktype" must not be set in $recordData.'
		);
		$this->subject->createSystemFolder(0, array('doktype' => 99999));
	}


	/*
	 * Tests regarding createContentElement()
	 */

	/**
	 * @test
	 */
	public function contentElementCanBeCreated() {
		$uid = $this->subject->createContentElement();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->subject->countRecords(
				'tt_content', 'uid=' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function contentElementWillBeCreatedOnRootPage() {
		$uid = $this->subject->createContentElement();

		$this->assertNotSame(
			0,
			$uid
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$parent = $this->subject->createSystemFolder();
		$uid = $this->subject->createContentElement($parent);

		$this->assertNotSame(
			0,
			$uid
		);

		$row = Tx_Oelib_Db::selectSingle(
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
			count($this->subject->getListOfDirtySystemTables())
		);
		$uid = $this->subject->createContentElement();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->subject->getListOfDirtySystemTables())
		);
	}

	/**
	 * @test
	 */
	public function contentElementWillBeCleanedUp() {
		$uid = $this->subject->createContentElement();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords(
				'tt_content', 'uid=' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function contentElementHasNoHeaderByDefault() {
		$uid = $this->subject->createContentElement();

		$row = Tx_Oelib_Db::selectSingle(
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
		$uid = $this->subject->createContentElement(
			0,
			array('header' => 'Test header')
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$uid = $this->subject->createContentElement();

		$row = Tx_Oelib_Db::selectSingle(
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
		$uid = $this->subject->createContentElement(
			0,
			array('CType' => 'list')
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$this->subject->createContentElement(0, array('pid' => 0));
	}

	/**
	 * @test
	 */
	public function contentElementMustHaveNoNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->subject->createContentElement(0, array('pid' => 99999));
	}

	/**
	 * @test
	 */
	public function contentElementMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->subject->createContentElement(0, array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function contentElementMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->subject->createContentElement(0, array('uid' => 99999));
	}


	/*
	 * Tests regarding createTemplate()
	 */

	/**
	 * @test
	 */
	public function templateCanBeCreatedOnNonRootPage() {
		$pageId = $this->subject->createFrontEndPage();
		$uid = $this->subject->createTemplate($pageId);

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->subject->countRecords(
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

		$this->subject->createTemplate(0);
	}

	/**
	 * @test
	 */
	public function templateCannotBeCreatedWithNegativePageNumber() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$pageId must be > 0.'
		);

		$this->subject->createTemplate(-1);
	}

	/**
	 * @test
	 */
	public function templateCanBeDirty() {
		$this->assertSame(
			0,
			count($this->subject->getListOfDirtySystemTables())
		);

		$pageId = $this->subject->createFrontEndPage();
		$uid = $this->subject->createTemplate($pageId);
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->subject->getListOfDirtySystemTables())
		);
	}

	/**
	 * @test
	 */
	public function templateWillBeCleanedUp() {
		$pageId = $this->subject->createFrontEndPage();
		$uid = $this->subject->createTemplate($pageId);
		$this->assertNotSame(
			0,
			$uid
		);

		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords(
				'sys_template', 'uid=' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function templateInitiallyHasNoConfig() {
		$pageId = $this->subject->createFrontEndPage();
		$uid = $this->subject->createTemplate($pageId);
		$row = Tx_Oelib_Db::selectSingle(
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
		$pageId = $this->subject->createFrontEndPage();
		$uid = $this->subject->createTemplate(
			$pageId,
			array('config' => 'plugin.tx_oelib.test = 1')
		);
		$row = Tx_Oelib_Db::selectSingle(
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
		$pageId = $this->subject->createFrontEndPage();
		$this->subject->createTemplate(
			$pageId,
			array('config' => 'plugin.tx_oelib.test = 42')
		);
		$templateHelper = new Tx_Oelib_TestingTemplateHelper(array());
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
		$pageId = $this->subject->createFrontEndPage();
		$uid = $this->subject->createTemplate($pageId);
		$row = Tx_Oelib_Db::selectSingle(
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
		$pageId = $this->subject->createFrontEndPage();
		$uid = $this->subject->createTemplate(
			$pageId,
			array('constants' => 'plugin.tx_oelib.test = 1')
		);
		$row = Tx_Oelib_Db::selectSingle(
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
		$pageId = $this->subject->createFrontEndPage();
		$this->subject->createTemplate(
			$pageId,
			array(
				'constants' => 'plugin.tx_oelib.test = 42',
				'config' => 'plugin.tx_oelib.test = {$plugin.tx_oelib.test}'
			)
		);
		$templateHelper = new Tx_Oelib_TestingTemplateHelper(array());
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
	public function templateMustNotHaveZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->subject->createTemplate(42, array('pid' => 0));
	}

	/**
	 * @test
	 */
	public function templateMustNotHaveNonZeroPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "pid" must not be set in $recordData.'
		);
		$this->subject->createTemplate(42, array('pid' => 99999));
	}

	/**
	 * @test
	 */
	public function templateMustHaveNoZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->subject->createTemplate(42, array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function templateMustNotHaveNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);
		$this->subject->createTemplate(42, array('uid' => 99999));
	}


	/*
	 * Tests regarding createDummyFile()
	 */

	/**
	 * @test
	 */
	public function createDummyFileCreatesFile() {
		$dummyFile = $this->subject->createDummyFile();

		$this->assertTrue(file_exists($dummyFile));
	}

	/**
	 * @test
	 */
	public function createDummyFileCreatesFileInSubfolder() {
		$dummyFolder = $this->subject->createDummyFolder('test_folder');
		$dummyFile = $this->subject->createDummyFile(
			$this->subject->getPathRelativeToUploadDirectory($dummyFolder) .
				'/test.txt'
		);

		$this->assertTrue(file_exists($dummyFile));
	}

	/**
	 * @test
	 */
	public function createDummyFileCreatesFileWithTheProvidedContent() {
		$dummyFile = $this->subject->createDummyFile('test.txt', 'Hello world!');

		$this->assertSame('Hello world!', file_get_contents($dummyFile));
	}

	/**
	 * @test
	 */
	public function createDummyFileForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->subject->createDummyFile();

		$this->assertTrue(is_dir($this->subject->getUploadFolderPath()));
	}

	/**
	 * @test
	 */
	public function createDummyFileForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->subject->createDummyFile();

		$this->assertTrue(file_exists($dummyFile));
	}


	/*
	 * Tests regarding createDummyZipArchive()
	 */

	/**
	 * @test
	 */
	public function createDummyZipArchiveForNoContentProvidedCreatesZipArchive() {
		$this->markAsSkippedForNoZipArchive();

		$dummyFile = $this->subject->createDummyZipArchive();

		$this->assertTrue(file_exists($dummyFile));
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForFileNameInSubFolderProvidedCreatesZipArchiveInSubFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFolder = $this->subject->getPathRelativeToUploadDirectory(
			$this->subject->createDummyFolder('sub-folder')
		);
		$this->subject->createDummyZipArchive($dummyFolder . 'foo.zip');

		$this->assertTrue(
			file_exists($this->subject->getUploadFolderPath() . $dummyFolder . 'foo.zip')
		);
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForNoContentProvidedCreatesZipArchiveWithDummyFile() {
		$this->markAsSkippedForNoZipArchive();

		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->subject->createDummyZipArchive();
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->subject->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->subject->getUploadFolderPath() . 'test.txt')
		);
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForFileProvidedCreatesZipArchiveWithThatFile() {
		$this->markAsSkippedForNoZipArchive();

		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->subject->createDummyZipArchive(
			'foo.zip', array($this->subject->createDummyFile('bar.txt'))
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->subject->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->subject->getUploadFolderPath() . 'bar.txt')
		);
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForFileProvidedWithContentCreatesZipArchiveWithThatFileAndContentInIt() {
		$this->markAsSkippedForNoZipArchive();

		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->subject->createDummyZipArchive(
			'foo.zip', array($this->subject->createDummyFile('bar.txt', 'foo bar'))
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->subject->getUploadFolderPath());
		$zip->close();

		$this->assertSame(
			'foo bar',
			file_get_contents($this->subject->getUploadFolderPath() . 'bar.txt')
		);
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForTwoFilesProvidedCreatesZipArchiveWithTheseFiles() {
		$this->markAsSkippedForNoZipArchive();

		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->subject->createDummyZipArchive(
			'foo.zip', array(
				$this->subject->createDummyFile('foo.txt'),
				$this->subject->createDummyFile('bar.txt'),
			)
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->subject->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->subject->getUploadFolderPath() . 'foo.txt')
		);
		$this->assertTrue(
			file_exists($this->subject->getUploadFolderPath() . 'bar.txt')
		);
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForFileInSubFolderOfUploadFolderProvidedCreatesZipArchiveWithFileInSubFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->subject->createDummyFolder('sub-folder');
		$dummyFile = $this->subject->createDummyZipArchive(
			'foo.zip', array($this->subject->createDummyFile('sub-folder/foo.txt'))
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->subject->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->subject->getUploadFolderPath() . 'sub-folder/foo.txt')
		);
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->subject->createDummyZipArchive();

		$this->assertTrue(is_dir($this->subject->getUploadFolderPath()));
	}

	/**
	 * @test
	 */
	public function createDummyZipArchiveForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFile = $this->subject->createDummyZipArchive();

		$this->assertTrue(file_exists($dummyFile));
	}


	/*
	 * Tests regarding deleteDummyFile()
	 */

	/**
	 * @test
	 */
	public function deleteDummyFileDeletesCreatedDummyFile() {
		$dummyFile = $this->subject->createDummyFile();
		$this->subject->deleteDummyFile(basename($dummyFile));

		$this->assertFalse(file_exists($dummyFile));
	}

	/**
	 * @test
	 */
	public function deleteDummyFileWithAlreadyDeletedFileThrowsNoException() {
		$dummyFile = $this->subject->createDummyFile();
		unlink($dummyFile);

		$this->subject->deleteDummyFile(basename($dummyFile));
	}

	/**
	 * @test
	 */
	public function deleteDummyFileWithInexistentFileThrowsException() {
		$uniqueFileName = $this->subject->getUniqueFileOrFolderPath('test.txt');

		$this->setExpectedException(
			'InvalidArgumentException',
			'The file "' . $uniqueFileName . '" which you are ' .
				'trying to delete does not exist and has never been created by this instance of the testing framework.'
		);

		$this->subject->deleteDummyFile(basename($uniqueFileName));
	}

	/**
	 * @test
	 */
	public function deleteDummyFileWithForeignFileThrowsException() {
		$uniqueFileName = $this->subject->getUniqueFileOrFolderPath('test.txt');
		t3lib_div::writeFile($uniqueFileName, '');
		$this->foreignFileToDelete = $uniqueFileName;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The file "' . $uniqueFileName . '" which you are ' .
				'trying to delete was not created by this instance of the testing framework.'
		);

		$this->subject->deleteDummyFile(basename($uniqueFileName));
	}


	/*
	 * Tests regarding createDummyFolder()
	 */

	/**
	 * @test
	 */
	public function createDummyFolderCreatesFolder() {
		$dummyFolder = $this->subject->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($dummyFolder));
	}

	/**
	 * @test
	 */
	public function createDummyFolderCanCreateFolderInDummyFolder() {
		$outerDummyFolder = $this->subject->createDummyFolder('test_folder');
		$innerDummyFolder = $this->subject->createDummyFolder(
			$this->subject->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->assertTrue(is_dir($innerDummyFolder));
	}

	/**
	 * @test
	 */
	public function createDummyFolderForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$this->subject->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($this->subject->getUploadFolderPath()));
	}

	/**
	 * @test
	 */
	public function createDummyFolderForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
		$dummyFolder = $this->subject->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($dummyFolder));
	}


	/*
	 * Tests regarding deleteDummyFolder()
	 */

	/**
	 * @test
	 */
	public function deleteDummyFolderDeletesCreatedDummyFolder() {
		$dummyFolder = $this->subject->createDummyFolder('test_folder');
		$this->subject->deleteDummyFolder(
			$this->subject->getPathRelativeToUploadDirectory($dummyFolder)
		);

		$this->assertFalse(is_dir($dummyFolder));
	}

	/**
	 * @test
	 */
	public function deleteDummyFolderWithInexistentFolderThrowsException() {
		$uniqueFolderName = $this->subject->getUniqueFileOrFolderPath('test_folder');

		$this->setExpectedException(
			'InvalidArgumentException',
			'The folder "' . $uniqueFolderName . '" which you are trying to delete does not exist.'
		);

		$this->subject->deleteDummyFolder(
			$this->subject->getPathRelativeToUploadDirectory($uniqueFolderName)
		);
	}

	/**
	 * @test
	 */
	public function deleteDummyFolderWithForeignFolderThrowsException() {
		$uniqueFolderName = $this->subject->getUniqueFileOrFolderPath('test_folder');
		t3lib_div::mkdir($uniqueFolderName);
		$this->foreignFolderToDelete = $uniqueFolderName;

		$this->setExpectedException(
			'InvalidArgumentException',
			'The folder "' . $uniqueFolderName . '" which you are ' .
				'trying to delete was not created by this instance of the testing framework.'
		);

		$this->subject->deleteDummyFolder(basename($uniqueFolderName));
	}

	/**
	 * @test
	 */
	public function deleteDummyFolderCanDeleteCreatedDummyFolderInDummyFolder() {
		$outerDummyFolder = $this->subject->createDummyFolder('test_folder');
		$innerDummyFolder = $this->subject->createDummyFolder(
			$this->subject->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->subject->deleteDummyFolder(
			$this->subject->getPathRelativeToUploadDirectory($innerDummyFolder)
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

		$dummyFolder = $this->subject->createDummyFolder('test_folder');
		$this->subject->createDummyFile(
			$this->subject->getPathRelativeToUploadDirectory($dummyFolder) .
			'/test.txt'
		);

		$this->subject->deleteDummyFolder(
			$this->subject->getPathRelativeToUploadDirectory($dummyFolder)
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

		$dummyFolder = $this->subject->createDummyFolder('test_folder');
		$this->subject->createDummyFile(
			$this->subject->getPathRelativeToUploadDirectory($dummyFolder) .
			'/test.txt'
		);

		$this->subject->deleteDummyFolder(
			$this->subject->getPathRelativeToUploadDirectory($dummyFolder)
		);
	}

	/**
	 * @test
	 */
	public function deleteDummyFolderWithFolderNameConsistingOnlyOfNumbersDoesNotThrowAnException() {
		$dummyFolder = $this->subject->createDummyFolder('123');

		$this->subject->deleteDummyFolder(
			$this->subject->getPathRelativeToUploadDirectory($dummyFolder)
		);
	}


	/*
	 * Tests regarding set- and getUploadFolderPath()
	 */

	/**
	 * @test
	 */
	public function getUploadFolderPathReturnsUploadFolderPathIncludingTablePrefix() {
		$this->assertRegExp(
			'/\/uploads\/tx_oelib\/$/',
			$this->subject->getUploadFolderPath()
		);
	}

	/**
	 * @test
	 */
	public function getUploadFolderPathAfterSetReturnsSetUploadFolderPath() {
		$this->subject->setUploadFolderPath('/foo/bar/');

		$this->assertSame(
			'/foo/bar/',
			$this->subject->getUploadFolderPath()
		);
	}

	/**
	 * @test
	 */
	public function setUploadFolderPathAfterCreatingDummyFileThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The upload folder path must not be changed if there are already dummy files or folders.'
		);

		$this->subject->createDummyFile();
		$this->subject->setUploadFolderPath('/foo/bar/');
	}


	/*
	 * Tests regarding getPathRelativeToUploadDirectory()
	 */

	/**
	 * @test
	 */
	public function getPathRelativeToUploadDirectoryWithPathOutsideUploadDirectoryThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The first parameter $absolutePath is not within the calling extension\'s upload directory.'
		);

		$this->subject->getPathRelativeToUploadDirectory(PATH_site);
	}


	/*
	 * Tests regarding getUniqueFileOrFolderPath()
	 */

	/**
	 * @test
	 */
	public function getUniqueFileOrFolderPathWithEmptyPathThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The first parameter $path must not be empty.'
		);

		$this->subject->getUniqueFileOrFolderPath('');
	}


	/*
	 * Tests regarding createFrontEndUserGroup()
	 */

	/**
	 * @test
	 */
	public function frontEndUserGroupCanBeCreated() {
		$uid = $this->subject->createFrontEndUserGroup();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->subject->countRecords(
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
			count($this->subject->getListOfDirtySystemTables())
		);
		$uid = $this->subject->createFrontEndUserGroup();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertNotSame(
			0,
			count($this->subject->getListOfDirtySystemTables())
		);
	}

	/**
	 * @test
	 */
	public function frontEndUserGroupTableWillBeCleanedUp() {
		$uid = $this->subject->createFrontEndUserGroup();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords(
				'fe_groups', 'uid=' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function frontEndUserGroupHasNoTitleByDefault() {
		$uid = $this->subject->createFrontEndUserGroup();

		$row = Tx_Oelib_Db::selectSingle(
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
	public function frontEndUserGroupCanHaveTitle() {
		$uid = $this->subject->createFrontEndUserGroup(
			array('title' => 'Test title')
		);

		$row = Tx_Oelib_Db::selectSingle(
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

		$this->subject->createFrontEndUserGroup(array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndUserGroupMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->subject->createFrontEndUserGroup(array('uid' => 99999));
	}


	/*
	 * Tests regarding createFrontEndUser()
	 */

	/**
	 * @test
	 */
	public function frontEndUserCanBeCreated() {
		$uid = $this->subject->createFrontEndUser();

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->subject->countRecords(
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
			count($this->subject->getListOfDirtySystemTables())
		);
		$uid = $this->subject->createFrontEndUser();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->greaterThan(
			1,
			count($this->subject->getListOfDirtySystemTables())
		);
	}

	/**
	 * @test
	 */
	public function frontEndUserTableWillBeCleanedUp() {
		$uid = $this->subject->createFrontEndUser();
		$this->assertNotSame(
			0,
			$uid
		);

		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords(
				'fe_users', 'uid=' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function frontEndUserHasNoUserNameByDefault() {
		$uid = $this->subject->createFrontEndUser();

		$row = Tx_Oelib_Db::selectSingle(
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
	public function frontEndUserCanHaveUserName() {
		$uid = $this->subject->createFrontEndUser(
			'',
			array('username' => 'Test name')
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$feUserGroupUidOne = $this->subject->createFrontEndUserGroup();
		$feUserGroupUidTwo = $this->subject->createFrontEndUserGroup();
		$feUserGroupUidThree = $this->subject->createFrontEndUserGroup();
		$uid = $this->subject->createFrontEndUser(
			$feUserGroupUidOne.', '.$feUserGroupUidTwo.', '.$feUserGroupUidThree
		);

		$this->assertNotSame(
			0,
			$uid
		);

		$this->assertSame(
			1,
			$this->subject->countRecords(
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

		$this->subject->createFrontEndUser('', array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoNonZeroUid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->subject->createFrontEndUser('', array('uid' => 99999));
	}

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoZeroUserGroupInTheDataArray() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "usergroup" must not be set in $recordData.'
		);

		$this->subject->createFrontEndUser('', array('usergroup' => 0));
	}

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoNonZeroUserGroupInTheDataArray() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "usergroup" must not be set in $recordData.'
		);

		$this->subject->createFrontEndUser('', array('usergroup' => 99999));
	}

	/**
	 * @test
	 */
	public function frontEndUserMustHaveNoUserGroupListInTheDataArray() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "usergroup" must not be set in $recordData.'
		);

		$this->subject->createFrontEndUser(
			'', array('usergroup' => '1,2,4,5')
		);
	}

	/**
	 * @test
	 */
	public function createFrontEndUserWithEmptyGroupCreatesGroup() {
		$this->subject->createFrontEndUser('');

		$this->assertTrue(
			$this->subject->existsExactlyOneRecord('fe_groups')
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

		$feUserGroupUidOne = $this->subject->createFrontEndUserGroup();
		$feUserGroupUidTwo = $this->subject->createFrontEndUserGroup();
		$feUserGroupUidThree = $this->subject->createFrontEndUserGroup();

		$this->subject->createFrontEndUser(
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

		$feUserGroupUid = $this->subject->createFrontEndUserGroup();

		$this->subject->createFrontEndUser(
			$feUserGroupUid.', abc'
		);
	}


	/*
	 * Tests regarding createBackEndUser()
	 */

	/**
	 * @test
	 */
	public function createBackEndUserReturnsUidGreaterZero() {
		$this->assertNotSame(
			0,
			$this->subject->createBackEndUser()
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserCreatesBackEndUserRecordInTheDatabase() {
		$this->assertSame(
			1,
			$this->subject->countRecords(
				'be_users', 'uid=' . $this->subject->createBackEndUser()
			)
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserMarksBackEndUserTableAsDirty() {
		$this->assertSame(
			0,
			count($this->subject->getListOfDirtySystemTables())
		);
		$this->subject->createBackEndUser();

		$this->greaterThan(
			1,
			count($this->subject->getListOfDirtySystemTables())
		);
	}

	/**
	 * @test
	 */
	public function cleanUpCleansUpDirtyBackEndUserTable() {
		$uid = $this->subject->createBackEndUser();

		$this->subject->cleanUp();
		$this->assertSame(
			0,
			$this->subject->countRecords('be_users', 'uid=' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserCreatesRecordWithoutUserNameByDefault() {
		$uid = $this->subject->createBackEndUser();

		$row = Tx_Oelib_Db::selectSingle('username', 'be_users', 'uid = ' . $uid);

		$this->assertSame(
			'',
			$row['username']
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserForUserNameProvidedCreatesRecordWithUserName() {
		$uid = $this->subject->createBackEndUser(array('username' => 'Test name'));

		$row = Tx_Oelib_Db::selectSingle('username', 'be_users', 'uid = ' . $uid);

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

		$this->subject->createBackEndUser(array('uid' => 0));
	}

	/**
	 * @test
	 */
	public function createBackEndUserWithNonZeroUidProvidedInRecordDataThrowsExeption() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The column "uid" must not be set in $recordData.'
		);

		$this->subject->createBackEndUser(array('uid' => 999999));
	}


	/*
	 * Tests concerning fakeFrontend
	 */

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesGlobalFrontEnd() {
		$GLOBALS['TSFE'] = NULL;
		$this->subject->createFakeFrontEnd();

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
			$this->subject->createFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndReturnsCurrentFrontEndPageUid() {
		$GLOBALS['TSFE'] = NULL;
		$result = $this->subject->createFakeFrontEnd();

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
		$this->subject->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TT'] instanceof t3lib_timeTrackNull
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesSysPage() {
		$GLOBALS['TSFE'] = NULL;
		$this->subject->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->sys_page instanceof t3lib_pageSelect
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesFrontEndUser() {
		$GLOBALS['TSFE'] = NULL;
		$this->subject->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->fe_user instanceof tslib_feUserAuth
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesContentObject() {
		$GLOBALS['TSFE'] = NULL;
		$this->subject->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->cObj instanceof tslib_cObj
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesTemplate() {
		$GLOBALS['TSFE'] = NULL;
		$this->subject->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->tmpl instanceof t3lib_TStemplate
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndReadsTypoScriptSetupFromPage() {
		$pageUid = $this->subject->createFrontEndPage();
		$this->subject->createTemplate(
			$pageUid,
			array('config' => 'foo = bar')
		);

		$this->subject->createFakeFrontEnd($pageUid);

		$this->assertSame(
			'bar',
			$GLOBALS['TSFE']->tmpl->setup['foo']
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndWithTemplateRecordMarksTemplateAsLoaded() {
		$pageUid = $this->subject->createFrontEndPage();
		$this->subject->createTemplate(
			$pageUid,
			array('config' => 'foo = 42')
		);

		$this->subject->createFakeFrontEnd($pageUid);

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
		$this->subject->createFakeFrontEnd();

		$this->assertTrue(
			is_array($GLOBALS['TSFE']->config)
		);
	}

	/**
	 * @test
	 */
	public function loginUserIsZeroAfterCreateFakeFrontEnd() {
		$this->subject->createFakeFrontEnd();

		$this->assertSame(
			0,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndSetsDefaultGroupList() {
		$this->subject->createFakeFrontEnd();

		$this->assertSame(
			'0,-1',
			$GLOBALS['TSFE']->gr_list
		);
	}

	/**
	 * @test
	 */
	public function discardFakeFrontEndNullsOutGlobalFrontEnd() {
		$this->subject->createFakeFrontEnd();
		$this->subject->discardFakeFrontEnd();

		$this->assertNull(
			$GLOBALS['TSFE']
		);
	}

	/**
	 * @test
	 */
	public function discardFakeFrontEndNullsOutGlobalTimeTrack() {
		$this->subject->createFakeFrontEnd();
		$this->subject->discardFakeFrontEnd();

		$this->assertNull(
			$GLOBALS['TT']
		);
	}

	/**
	 * @test
	 */
	public function discardFakeFrontEndCanBeCalledTwoTimes() {
		$this->subject->discardFakeFrontEnd();
		$this->subject->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function hasFakeFrontEndInitiallyIsFalse() {
		$this->assertFalse(
			$this->subject->hasFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function hasFakeFrontEndIsTrueAfterCreateFakeFrontEnd() {
		$this->subject->createFakeFrontEnd();

		$this->assertTrue(
			$this->subject->hasFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function hasFakeFrontEndIsFalseAfterCreateAndDiscardFakeFrontEnd() {
		$this->subject->createFakeFrontEnd();
		$this->subject->discardFakeFrontEnd();

		$this->assertFalse(
			$this->subject->hasFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function cleanUpDiscardsFakeFrontEnd() {
		$this->subject->createFakeFrontEnd();
		$this->subject->cleanUp();

		$this->assertFalse(
			$this->subject->hasFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndReturnsProvidedPageUid() {
		$pageUid = $this->subject->createFrontEndPage();

		$this->assertSame(
			$pageUid,
			$this->subject->createFakeFrontEnd($pageUid)
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndUsesProvidedPageUidAsFrontEndId() {
		$pageUid = $this->subject->createFrontEndPage();
		$this->subject->createFakeFrontEnd($pageUid);

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

		$this->subject->createFakeFrontEnd(-1);
	}

	// Note: In the unit tests, the src attribute of the generated image tag
	// will be empty because the IMAGE handles does not accept absolute paths
	// and handles relative paths and EXT: paths inconsistently:

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
	public function fakeFrontEndCobjImageCreatesImageTagForExistingImageFile() {
		$this->subject->createFakeFrontEnd();

		$this->assertContains(
			'<img ',
			$GLOBALS['TSFE']->cObj->IMAGE(
				array('file' => 'typo3conf/ext/oelib/Tests/Unit/Fixtures/test.png')
			)
		);
	}


	/*
	 * Tests regarding user login and logout
	 */

	/**
	 * @test
	 */
	public function isLoggedInInitiallyIsFalse() {
		$this->subject->createFakeFrontEnd();

		$this->assertFalse(
			$this->subject->isLoggedIn()
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

		$this->subject->isLoggedIn();
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserSwitchesToLoggedIn() {
		$this->subject->createFakeFrontEnd();

		$feUserId = $this->subject->createFrontEndUser();
		$this->subject->loginFrontEndUser($feUserId);

		$this->assertTrue(
			$this->subject->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserSwitchesLoginManagerToLoggedIn() {
		$this->subject->createFakeFrontEnd();

		$feUserId = $this->subject->createFrontEndUser();
		$this->subject->loginFrontEndUser($feUserId);

		$this->assertTrue(
			Tx_Oelib_FrontEndLoginManager::getInstance()->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserSetsLoginUserToOne() {
		$this->subject->createFakeFrontEnd();

		$feUserId = $this->subject->createFrontEndUser();
		$this->subject->loginFrontEndUser($feUserId);

		$this->assertSame(
			1,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserRetrievesNameOfUser() {
		$this->subject->createFakeFrontEnd();

		$feUserId = $this->subject->createFrontEndUser(
			'', array('name' => 'John Doe')
		);
		$this->subject->loginFrontEndUser($feUserId);

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

		$this->subject->createFakeFrontEnd();

		$this->subject->loginFrontEndUser(0);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserWithoutFrontEndThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please create a front end before calling loginFrontEndUser.'
		);

		$feUserId = $this->subject->createFrontEndUser();
		$this->subject->loginFrontEndUser($feUserId);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserSetsGroupDataOfUser() {
		$this->subject->createFakeFrontEnd();

		$feUserGroupUid = $this->subject->createFrontEndUserGroup(
			array('title' => 'foo')
		);
		$feUserId = $this->subject->createFrontEndUser($feUserGroupUid);
		$this->subject->loginFrontEndUser($feUserId);

		$this->assertSame(
			array($feUserGroupUid => 'foo'),
			$GLOBALS['TSFE']->fe_user->groupData['title']
		);
	}

	/**
	 * @test
	 */
	public function logoutFrontEndUserAfterLoginSwitchesToNotLoggedIn() {
		$this->subject->createFakeFrontEnd();

		$feUserId = $this->subject->createFrontEndUser();
		$this->subject->loginFrontEndUser($feUserId);
		$this->subject->logoutFrontEndUser();

		$this->assertFalse(
			$this->subject->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function logoutFrontEndUserAfterLoginSwitchesLoginManagerToNotLoggedIn() {
		$this->subject->createFakeFrontEnd();

		$feUserId = $this->subject->createFrontEndUser();
		$this->subject->loginFrontEndUser($feUserId);
		$this->subject->logoutFrontEndUser();

		$this->assertFalse(
			Tx_Oelib_FrontEndLoginManager::getInstance()->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function logoutFrontEndUserSetsLoginUserToZero() {
		$this->subject->createFakeFrontEnd();

		$this->subject->logoutFrontEndUser();

		$this->assertSame(
			0,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserNotInDatabaseSwitchesToLoggedIn() {
		$this->subject->createFakeFrontEnd();

		$feUser = Tx_Oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array());
		$this->subject->loginFrontEndUser($feUser->getUid());

		$this->assertTrue(
			$this->subject->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserNotInDatabaseSetsLoginUserToOne() {
		$this->subject->createFakeFrontEnd();

		$feUser = Tx_Oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array());
		$this->subject->loginFrontEndUser($feUser->getUid());

		$this->assertSame(
			1,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserNotInDatabaseRetrievesNameOfUser() {
		$this->subject->createFakeFrontEnd();

		$feUser = Tx_Oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array('name' => 'John Doe'));
		$this->subject->loginFrontEndUser($feUser->getUid());

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

		$feUser = Tx_Oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$feUser->setData(array());
		$this->subject->loginFrontEndUser($feUser->getUid());
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserMappedAsGhostAndInDatabaseSetsGroupDataOfUser() {
		$this->subject->createFakeFrontEnd();

		$feUserGroupUid = $this->subject->createFrontEndUserGroup(
			array('title' => 'foo')
		);
		$feUser = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUser')
			->find($this->subject->createFrontEndUser($feUserGroupUid));
		$this->subject->loginFrontEndUser($feUser->getUid());

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

		$this->subject->logoutFrontEndUser();
	}

	/**
	 * @test
	 */
	public function logoutFrontEndUserCanBeCalledTwoTimes() {
		$this->subject->createFakeFrontEnd();

		$this->subject->logoutFrontEndUser();
		$this->subject->logoutFrontEndUser();
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserCreatesFrontEndUser() {
		$this->subject->createFakeFrontEnd();
		$this->subject->createAndLogInFrontEndUser();

		$this->assertSame(
			1,
			$this->subject->countRecords('fe_users')
		);
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserWithRecordDataCreatesFrontEndUserWithThatData() {
		$this->subject->createFakeFrontEnd();
		$this->subject->createAndLogInFrontEndUser(
			'', array('name' => 'John Doe')
		);

		$this->assertSame(
			1,
			$this->subject->countRecords('fe_users', 'name = "John Doe"')
		);
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserLogsInFrontEndUser() {
		$this->subject->createFakeFrontEnd();
		$this->subject->createAndLogInFrontEndUser();

		$this->assertTrue(
			$this->subject->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserWithFrontEndUserGroupCreatesFrontEndUser() {
		$this->subject->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
		$this->subject->createAndLogInFrontEndUser($frontEndUserGroupUid);

		$this->assertSame(
			1,
			$this->subject->countRecords('fe_users')
		);
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserWithFrontEndUserGroupCreatesFrontEndUserWithGivenGroup() {
		$this->subject->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
		$frontEndUserUid = $this->subject->createAndLogInFrontEndUser(
			$frontEndUserGroupUid
		);

		$dbResultRow = Tx_Oelib_Db::selectSingle(
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
	public function createAndLogInFrontEndUserWithFrontEndUserGroupDoesNotCreateFrontEndUserGroup() {
		$this->subject->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
		$this->subject->createAndLogInFrontEndUser(
			$frontEndUserGroupUid
		);

		$this->assertSame(
			1,
			$this->subject->countRecords('fe_groups')
		);
	}

	/**
	 * @test
	 */
	public function createAndLogInFrontEndUserWithFrontEndUserGroupLogsInFrontEndUser() {
		$this->subject->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
		$this->subject->createAndLogInFrontEndUser($frontEndUserGroupUid);

		$this->assertTrue(
			$this->subject->isLoggedIn()
		);
	}


	/*
	 * Tests regarding increaseRelationCounter()
	 */

	/**
	 * @test
	 */
	public function increaseRelationCounterIncreasesNonZeroFieldValueByOne() {
		$uid = $this->subject->createRecord(
			OELIB_TESTTABLE,
			array('related_records' => 41)
		);

		$this->subject->increaseRelationCounter(
			OELIB_TESTTABLE,
			$uid,
			'related_records'
		);

		$row = Tx_Oelib_Db::selectSingle(
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
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);
		$invalidUid = $uid + 1;

		$this->setExpectedException(
			'BadMethodCallException',
			'The table ' . OELIB_TESTTABLE . ' does not contain a record with UID ' . $invalidUid . '.'
		);
		$this->subject->increaseRelationCounter(
			OELIB_TESTTABLE,
			$invalidUid,
			'related_records'
		);
	}

	/**
	 * @test
	 */
	public function increaseRelationCounterThrowsExceptionOnInvalidTableName() {
		$uid = $this->subject->createRecord(OELIB_TESTTABLE);

		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name "tx_oelib_inexistent" is invalid. This means it is either empty or not in the list of allowed tables.'
		);
		$this->subject->increaseRelationCounter(
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

		$uid = $this->subject->createRecord(OELIB_TESTTABLE);
		$this->subject->increaseRelationCounter(
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
			$this->subject->getDummyColumnName('tx_oelib_test')
		);
	}

	/**
	 * @test
	 */
	public function getDummyColumnNameForSystemTableReturnsOelibPrefixedColumnName() {
		$this->assertSame(
			'tx_oelib_is_dummy_record',
			$this->subject->getDummyColumnName('fe_users')
		);
	}

	/**
	 * @test
	 */
	public function getDummyColumnNameForThirdPartyExtensionTableReturnsPrefixedColumnName() {
		$this->checkIfExtensionUserOelibtestIsLoaded();

		$testingFramework = new Tx_Oelib_TestingFramework(
			'user_oelibtest', array('user_oelibtest2')
		);
		$this->assertSame(
			'user_oelibtest_is_dummy_record',
			$testingFramework->getDummyColumnName('user_oelibtest2_test')
		);
	}


	/*
	 * Tests concerning createBackEndUserGroup
	 */

	/**
	 * @test
	 */
	public function createBackEndUserGroupForNoDataGivenCreatesBackEndGroup() {
		$this->subject->createBackEndUserGroup(array());

		$this->assertTrue(
			$this->subject->existsRecord('be_groups')
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserGroupForNoDataGivenReturnsUidOfCreatedBackEndGroup() {
		$backendGroupUid = $this->subject->createBackEndUserGroup(array());

		$this->assertTrue(
			$this->subject->existsRecord(
				'be_groups', 'uid = ' . $backendGroupUid
			)
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserGroupForTitleGivenStoresTitleInGroupRecord() {
		$this->subject->createBackEndUserGroup(
			array('title' => 'foo group')
		);

		$this->assertTrue(
			$this->subject->existsRecord(
				'be_groups', 'title = "foo group"'
			)
		);
	}
}