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
 * Testcase for the testing framework in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Mario Rimann <typo3-coding@rimann.org>
 */

require_once(t3lib_extMgm::extPath('oelib').'tx_oelib_commonConstants.php');
require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');
require_once(t3lib_extMgm::extPath('oelib').'tests/fixtures/class.tx_oelib_templatehelperchild.php');

define('OELIB_TESTTABLE', 'tx_oelib_test');
define('OELIB_TESTTABLE_MM', 'tx_oelib_test_article_mm');

class tx_oelib_testingFramework_testcase extends tx_phpunit_testcase {
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_testingFramework('tx_oelib');
	}

	public function tearDown() {
		$this->fixture->cleanUp();
		unset($this->fixture);
	}


	// ---------------------------------------------------------------------
	// Utility functions.
	// ---------------------------------------------------------------------

	/**
	 * Returns the sorting value of the relation between the local UID given by
	 * the first parameter $uidLocal and the foreign UID given by the second
	 * parameter $uidForeign.
	 *
	 * @param	integer		the UID of the local record, must be > 0
	 * @param	integer		the UID of the foreign record, must be > 0
	 *
	 * @return	integer		the sorting value of the relation
	 */
	private function getSortingOfRelation($uidLocal, $uidForeign) {
		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'sorting',
				OELIB_TESTTABLE_MM,
				'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
			)
		);

		return $row['sorting'];
	}


	// ---------------------------------------------------------------------
	// Tests regarding markTableAsDirty()
	// ---------------------------------------------------------------------

	public function testMarkTableAsDirtyWillCleanUpANonSystemTable() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			OELIB_TESTTABLE,
			array(
				'is_dummy_record' => 1
			)
		);

		if (!$dbResult) {
			$this->fail(DATABASE_QUERY_ERROR);
		}

		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->markTableAsDirty(OELIB_TESTTABLE);
		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid='.$uid)
		);
	}

	public function testMarkTableAsDirtyWillCleanUpASystemTable() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'pages',
			array(
				'tx_oelib_is_dummy_record' => 1
			)
		);

		if (!$dbResult) {
			$this->fail(DATABASE_QUERY_ERROR);
		}

		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->markTableAsDirty('pages');
		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords('pages', 'uid='.$uid)
		);
	}

	public function testMarkTableAsDirtyFailsOnInexistentTable() {
		try {
			$this->fixture->markTableAsDirty('tx_oelib_DOESNOTEXIST');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testMarkTableAsDirtyFailsOnNotAllowedSystemTable() {
		try {
			$this->fixture->markTableAsDirty('sys_domain');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testMarkTableAsDirtyFailsOnForeignTable() {
		try {
			$this->fixture->markTableAsDirty('tx_seminars_seminars');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testMarkTableAsDirtyFailsWithEmptyTableName() {
		try {
			$this->fixture->markTableAsDirty('');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createRecord()
	// ---------------------------------------------------------------------

	public function testCreateRecordOnValidTableWithNoData() {
		$this->assertNotEquals(
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
		$this->assertNotEquals(
			0,
			$uid
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				OELIB_TESTTABLE,
				'uid='.$uid
			)
		);

		$this->assertEquals(
			$title,
			$row['title']
		);
	}

	public function testCreateRecordOnInvalidTable() {
		try {
			$this->fixture->createRecord('tx_oelib_DOESNOTEXIST', array());
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testCreateRecordWithEmptyTableName() {
		try {
			$this->fixture->createRecord('', array());
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testCreateRecordWithUidFails() {
		try {
			$this->fixture->createRecord(
				OELIB_TESTTABLE, array('uid' => 99999)
			);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
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

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				OELIB_TESTTABLE,
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'bar',
			$row['title']
		);
	}

	public function testChangeRecordFailsOnForeignTable() {
		try {
			$this->fixture->changeRecord(
				'tx_seminars_seminars',
				99999,
				array('title' => 'foo')
			);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testChangeRecordFailsOnInexistentTable() {
		try {
			$this->fixture->changeRecord(
				'tx_oelib_DOESNOTEXIST',
				99999,
				array('title' => 'foo')
			);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testChangeRecordOnAllowedSystemTableForPages() {
		$pid = $this->fixture->createFrontEndPage(0, array('title' => 'foo'));

		$this->fixture->changeRecord(
			'pages',
			$pid,
			array('title' => 'bar')
		);

		$this->assertEquals(
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

		$this->assertEquals(
			1,
			$this->fixture->countRecords('tt_content', 'uid='.$uid.' AND titleText="bar"')
		);
	}

	public function testChangeRecordFailsOnOtherSystemTable() {
		try {
			$this->fixture->changeRecord(
				'sys_domain',
				1,
				array('title' => 'bar')
			);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testChangeRecordFailsWithUidZero() {
		try {
			$this->fixture->changeRecord(OELIB_TESTTABLE, 0, array('title' => 'foo'));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testChangeRecordFailsWithEmptyData() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());

		try {
			$this->fixture->changeRecord(
				OELIB_TESTTABLE, $uid, array()
			);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testChangeRecordFailsWithUidFieldInRecordData() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());

		try {
			$this->fixture->changeRecord(
				OELIB_TESTTABLE, $uid, array('uid' => '55742')
			);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testChangeRecordFailsWithDummyRecordFieldInRecordData() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());

		try {
			$this->fixture->changeRecord(
				OELIB_TESTTABLE, $uid, array('is_dummy_record' => 0)
			);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testChangeRecordFailsOnInexistentRecord() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());

		try {
			$this->fixture->changeRecord(
				OELIB_TESTTABLE, $uid + 1, array('title' => 'foo')
			);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}


	// ---------------------------------------------------------------------
	// Tests regarding deleteRecord()
	// ---------------------------------------------------------------------

	public function testDeleteRecordOnValidDummyRecord() {
		// Creates and directly destroys a dummy record.
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE, array());
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid);

		// Checks whether the record really was removed from the database.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid='.$uid)
		);
	}

	public function testDeleteRecordOnInexistentRecord() {
		$uid = 99999;

		// Checks that the record is inexistent before testing on it.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid='.$uid)
		);

		// Runs our delete function - it should run through even when it can't
		// delete a record.
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid);
	}

	public function testDeleteRecordOnForeignTable() {
		$table = 'tx_seminars_seminars';
		$uid = 99999;

		try {
			$this->fixture->deleteRecord($table, $uid);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testDeleteRecordOnInexistentTable() {
		$table = 'tx_oelib_DOESNOTEXIST';
		$uid = 99999;

		try {
			$this->fixture->deleteRecord($table, $uid);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testDeleteRecordWithEmptyTableName() {
		$table = '';
		$uid = 99999;

		try {
			$this->fixture->deleteRecord($table, $uid);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testDeleteRecordOnNonTestRecord() {
		// Create a new record that looks like a real record, i.e. the
		// is_dummy_record flag is set to 0.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			OELIB_TESTTABLE,
			array(
				'title' => 'TEST',
				'is_dummy_record' => 0
			)
		);

		if (!$dbResult) {
			$this->fail(DATABASE_QUERY_ERROR);
		}

		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();

		// Checks whether the creation of the record was successful.
		$this->assertNotEquals(
			0,
			$uid
		);

		// Runs our delete method which should NOT affect the record created above.
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid);

		// Checks whether the record still exists.
		$this->assertEquals(
			1,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid='.$uid)
		);

		// Deletes the record as it will not be caught by the clean up function.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			OELIB_TESTTABLE,
			'uid='.$uid.' AND is_dummy_record=0'
		);

		if (!$dbResult) {
			$this->fail(DATABASE_QUERY_ERROR);
		}
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
		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				OELIB_TESTTABLE_MM,
				'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
			)
		);
	}

	public function testCreateRelationWithInvalidTable() {
		$table = 'tx_oelib_test_DOESNOTEXIST_mm';
		$uidLocal = 99999;
		$uidForeign = 199999;

		try {
			$this->fixture->createRelation($table, $uidLocal, $uidForeign);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testCreateRelationWithEmptyTableName() {
		try {
			$this->fixture->createRelation('', 99999, 199999);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testCreateRelationWithZeroFirstUid() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		try {
			$this->fixture->createRelation(OELIB_TESTTABLE_MM, 0, $uid);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testCreateRelationWithZeroSecondUid() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		try {
			$this->fixture->createRelation(OELIB_TESTTABLE_MM, $uid, 0);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
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
		$this->assertEquals(
			($previousSorting + 1),
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

		$this->assertEquals(
			$sorting,
			$this->getSortingOfRelation($uidLocal, $uidForeign)
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
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				OELIB_TESTTABLE_MM,
				'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
			)
		);
	}

	public function testRemoveRelationOnInexistentRecord() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidLocal = $uid + 1;
		$uidForeign = $uid + 2;

		// Checks that the record is inexistent before testing on it.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				OELIB_TESTTABLE_MM,
				'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
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

		try {
			$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testRemoveRelationOnInexistentTable() {
		$table = 'tx_oelib_DOESNOTEXIST';
		$uidLocal = 99999;
		$uidForeign = 199999;

		try {
			$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testRemoveRelationWithEmptyTableName() {
		$table = '';
		$uidLocal = 99999;
		$uidForeign = 199999;

		try {
			$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testRemoveRelationOnRealRecord() {
		$uidLocal = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);;

		// Create a new record that looks like a real record, i.e. the
		// is_dummy_record flag is set to 0.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			OELIB_TESTTABLE_MM,
			array(
				'uid_local' => $uidLocal,
				'uid_foreign' => $uidForeign,
				'is_dummy_record' => 0
			)
		);

		if (!$dbResult) {
			$this->fail(DATABASE_QUERY_ERROR);
		}

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
		$numberOfCreatedRelations = $this->fixture->countRecords(
			OELIB_TESTTABLE_MM,
			'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
		);

		// Deletes the record as it will not be caught by the clean up function.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			OELIB_TESTTABLE_MM,
			'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
				.' AND is_dummy_record=0'
		);

		if (!$dbResult) {
			$this->fail(DATABASE_QUERY_ERROR);
		}

		// Checks whether the relation had been created further up.
		$this->assertEquals(
			1,
			$numberOfCreatedRelations
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding dropAllDummyRecords()
	// ---------------------------------------------------------------------

	public function testCleanUpWithRegularCleanUp() {
		// Creates a dummy record (and marks that table as dirty).
		$this->fixture->createRecord(OELIB_TESTTABLE);

		// Creates a dummy record directly in the database, without putting this
		// table name to the list of dirty tables.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			OELIB_TESTTABLE_MM,
			array(
				'is_dummy_record' => 1
			)
		);
		if (!$dbResult) {
			$this->fail(DATABASE_QUERY_ERROR);
		}

		// Runs a regular clean up. This should now delete only the first record
		// which was created through the testing framework and thus that table
		// is on the list of dirty tables. The second record was directly put
		// into the database and it's table is not on this list and will not be
		// removed by a regular clean up run.
		$this->fixture->cleanUp();

		// Checks whether the first dummy record is deleted.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'is_dummy_record=1'),
			'Some test records were not deleted from table "tx_oelib_test"'
		);

		// Checks whether the second dummy record still exists.
		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				OELIB_TESTTABLE_MM,
				'is_dummy_record=1'
			)
		);

		// Runs a deep clean up to delete all dummy records.
		$this->fixture->cleanUp(true);
	}

	public function testCleanUpWithDeepCleanup() {
		// Creates a dummy record (and marks that table as dirty).
		$this->fixture->createRecord(OELIB_TESTTABLE);

		// Creates a dummy record directly in the database, without putting this
		// table name to the list of dirty tables.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			OELIB_TESTTABLE_MM,
			array(
				'is_dummy_record' => 1
			)
		);
		if (!$dbResult) {
			$this->fail(DATABASE_QUERY_ERROR);
		}

		// Deletes all dummy records.
		$this->fixture->cleanUp(true);

		// Checks whether ALL dummy records were deleted (independent of the
		// list of dirty tables).
		$allowedTables = $this->fixture->getListOfDirtyTables();
		foreach ($allowedTables as $currentTable) {
			$this->assertEquals(
				0,
				$this->fixture->countRecords($currentTable, 'is_dummy_record=1'),
				'Some test records were not deleted from table "'.$currentTable.'"'
			);
		}
	}


	// ---------------------------------------------------------------------
	// Tests regarding createListOfAllowedTables()
	//
	// The method is called in the constructor of the fixture.
	// ---------------------------------------------------------------------

	public function testCreateListOfAllowedTablesContainsOurTestTable() {
		$allowedTables = $this->fixture->getListOfAllowedTableNames();
		$this->assertContains(
			OELIB_TESTTABLE,
			$allowedTables
		);
	}

	public function testCreateListOfAllowedTablesDoesNotContainForeignTables() {
		$allowedTables = $this->fixture->getListOfAllowedTableNames();
		$this->assertNotContains(
			'be_users',
			$allowedTables
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding getAssociativeDatabaseResult()
	// ---------------------------------------------------------------------

	public function testGetAssociativeDatabaseResultFailsIfResourceIsFalse() {
		try {
			$this->fixture->getAssociativeDatabaseResult(false);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testGetAssociativeDatabaseResultFailsIfDataBaseResultIsEmpty() {
		$uid = $this->fixture->createRecord(
			OELIB_TESTTABLE, array('title' => '')
		);
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title',
			OELIB_TESTTABLE,
			'uid='.$uid.' AND title="foo"'
		);

		try {
			$this->fixture->getAssociativeDatabaseResult($dbResult);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testGetAssociativeDatabaseResultSucceedsForNonEmptyResults() {
		$uid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid',
			OELIB_TESTTABLE,
			'uid='.$uid
		);

		$this->assertEquals(
			array('uid' => $uid),
			$this->fixture->getAssociativeDatabaseResult($dbResult)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding countRecords()
	// ---------------------------------------------------------------------

	public function testCountRecordsWithEmptyWhereClause() {
		$this->assertEquals(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, '')
		);
	}

	public function testCountRecordsWithEmptyTableName() {
		$whereClause = 'is_dummy_record=1';

		try {
			$this->fixture->countRecords('', $whereClause);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testCountRecordsWithInvalidTableNameRaisesException() {
		$table = 'foo_bar';

		try {
			$this->fixture->countRecords($table);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
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

	public function testCountRecordsWithOtherTableIsAllowed() {
		$table = 'sys_domain';
		$this->fixture->countRecords($table);
	}

	public function testCountRecords() {
		$whereClause = 'is_dummy_record=1';

		$this->assertEquals(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, $whereClause)
		);
		$this->fixture->createRecord(OELIB_TESTTABLE);
		$this->assertEquals(
			1,
			$this->fixture->countRecords(OELIB_TESTTABLE, $whereClause)
		);
	}

	public function testCountRecordsForPagesTableIsAllowed() {
		$table = 'pages';

		try {
			$this->fixture->countRecords($table);
		} catch (Exception $expected) {
			$this->fail('countRecords should not have thrown an exception.');
		}
	}


	// ---------------------------------------------------------------------
	// Tests regarding resetAutoIncrement()
	// ---------------------------------------------------------------------

	public function testResetAutoIncrementForTestTableSucceeds() {
		// Creates and deletes a record and then resets the auto increment.
		$latestUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);

		// Checks whether the reset of the auto increment value worked as it
		// should. After the reset, the auto increment index should be equal
		// to the UID of the record we created and deleted before.
		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->sql_query(
				'SHOW TABLE STATUS WHERE Name=\''.OELIB_TESTTABLE.'\';'
			)
		);

		$this->assertEquals(
			$latestUid,
			$row['Auto_increment']
		);
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
		try {
			$this->fixture->resetAutoIncrement('sys_domains');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testResetAutoIncrementWithEmptyTableNameFails() {
		try {
			$this->fixture->resetAutoIncrement('');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testResetAutoIncrementWithForeignTableFails() {
		try {
			$this->fixture->resetAutoIncrement('tx_seminars_seminars');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testResetAutoIncrementWithInexistentTableFails() {
		try {
			$this->fixture->resetAutoIncrement('tx_oelib_DOESNOTEXIST');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}


	// ---------------------------------------------------------------------
	// Tests regarding hasTableColumnUid()
	// ---------------------------------------------------------------------

	public function testHasTableColumnUidOnTableWithColumnUid() {
		$this->assertTrue(
			$this->fixture->hasTableColumnUid(OELIB_TESTTABLE)
		);
	}

	public function testHasTableColumnUidOnTableWithoutColumnUid() {
		$this->assertFalse(
			$this->fixture->hasTableColumnUid(OELIB_TESTTABLE_MM)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding markTableAsDirty()
	// ---------------------------------------------------------------------

	public function testMarkTableAsDirty() {
		$this->assertEquals(
			array(),
			$this->fixture->getListOfDirtyTables()
		);

		$this->fixture->createRecord(OELIB_TESTTABLE, array());
		$this->assertEquals(
			array(
				OELIB_TESTTABLE => OELIB_TESTTABLE
			),
			$this->fixture->getListOfDirtyTables()
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndPage()
	// ---------------------------------------------------------------------

	public function testFrontEndPageCanBeCreated() {
		$uid = $this->fixture->createFrontEndPage();

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'pages', 'uid='.$uid
			)
		);
	}

	public function testCreateFrontEndPageSetsCorrectDocumentType() {
		$uid = $this->fixture->createFrontEndPage();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'doktype',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			1,
			$row['doktype']
		);
	}

	public function testFrontEndPageWillBeCreatedOnRootPage() {
		$uid = $this->fixture->createFrontEndPage();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'pid',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			0,
			$row['pid']
		);
	}

	public function testFrontEndPageCanBeCreatedOnOtherPage() {
		$parent = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createFrontEndPage($parent);

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'pid',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			$parent,
			$row['pid']
		);
	}

	public function testFrontEndPageCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createFrontEndPage();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testFrontEndPageWillBeCleanedUp() {
		$uid = $this->fixture->createFrontEndPage();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'pages', 'uid='.$uid
			)
		);
	}

	public function testFrontEndPageHasNoTitleByDefault() {
		$uid = $this->fixture->createFrontEndPage();

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'',
			$row['title']
		);
	}

	public function testFrontEndPageCanHaveTitle() {
		$uid = $this->fixture->createFrontEndPage(
			0,
			array('title' => 'Test title')
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'Test title',
			$row['title']
		);
	}

	public function testFrontEndPageMustHaveNoZeroPid() {
		try {
			$this->fixture->createFrontEndPage(0, array('pid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndPageMustHaveNoNonZeroPid() {
		try {
			$this->fixture->createFrontEndPage(0, array('pid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndPageMustHaveNoZeroUid() {
		try {
			$this->fixture->createFrontEndPage(0, array('uid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndPageMustHaveNoNonZeroUid() {
		try {
			$this->fixture->createFrontEndPage(0, array('uid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndPageMustHaveNoZeroDoktype() {
		try {
			$this->fixture->createFrontEndPage(0, array('doktype' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndPageMustHaveNoNonZeroDoktype() {
		try {
			$this->fixture->createFrontEndPage(0, array('doktype' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}



	// ---------------------------------------------------------------------
	// Tests regarding createSystemFolder()
	// ---------------------------------------------------------------------

	public function testSystemFolderCanBeCreated() {
		$uid = $this->fixture->createSystemFolder();

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'pages', 'uid='.$uid
			)
		);
	}

	public function testCreateSystemFolderSetsCorrectDocumentType() {
		$uid = $this->fixture->createSystemFolder();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'doktype',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			254,
			$row['doktype']
		);
	}

	public function testSystemFolderWillBeCreatedOnRootPage() {
		$uid = $this->fixture->createSystemFolder();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'pid',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			0,
			$row['pid']
		);
	}

	public function testSystemFolderCanBeCreatedOnOtherPage() {
		$parent = $this->fixture->createSystemFolder();
		$uid = $this->fixture->createSystemFolder($parent);

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'pid',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			$parent,
			$row['pid']
		);
	}

	public function testSystemFolderCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createSystemFolder();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testSystemFolderWillBeCleanedUp() {
		$uid = $this->fixture->createSystemFolder();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'pages', 'uid='.$uid
			)
		);
	}

	public function testSystemFolderHasNoTitleByDefault() {
		$uid = $this->fixture->createSystemFolder();

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'',
			$row['title']
		);
	}

	public function testSystemFolderCanHaveTitle() {
		$uid = $this->fixture->createSystemFolder(
			0,
			array('title' => 'Test title')
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				'pages',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'Test title',
			$row['title']
		);
	}

	public function testSystemFolderMustHaveNoZeroPid() {
		try {
			$this->fixture->createSystemFolder(0, array('pid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testSystemFolderMustHaveNoNonZeroPid() {
		try {
			$this->fixture->createSystemFolder(0, array('pid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testSystemFolderMustHaveNoZeroUid() {
		try {
			$this->fixture->createSystemFolder(0, array('uid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testSystemFolderMustHaveNoNonZeroUid() {
		try {
			$this->fixture->createSystemFolder(0, array('uid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testSystemFolderMustHaveNoZeroDoktype() {
		try {
			$this->fixture->createSystemFolder(0, array('doktype' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testSystemFolderMustHaveNoNonZeroDoktype() {
		try {
			$this->fixture->createSystemFolder(0, array('doktype' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}



	// ---------------------------------------------------------------------
	// Tests regarding createContentElement()
	// ---------------------------------------------------------------------

	public function testContentElementCanBeCreated() {
		$uid = $this->fixture->createContentElement();

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'tt_content', 'uid='.$uid
			)
		);
	}

	public function testContentElementWillBeCreatedOnRootPage() {
		$uid = $this->fixture->createContentElement();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'pid',
				'tt_content',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			0,
			$row['pid']
		);
	}

	public function testContentElementCanBeCreatedOnNonRootPage() {
		$parent = $this->fixture->createSystemFolder();
		$uid = $this->fixture->createContentElement($parent);

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'pid',
				'tt_content',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			$parent,
			$row['pid']
		);
	}

	public function testContentElementCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createContentElement();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testContentElementWillBeCleanedUp() {
		$uid = $this->fixture->createContentElement();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'tt_content', 'uid='.$uid
			)
		);
	}

	public function testContentElementHasNoHeaderByDefault() {
		$uid = $this->fixture->createContentElement();

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'header',
				'tt_content',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'',
			$row['header']
		);
	}

	public function testContentElementCanHaveHeader() {
		$uid = $this->fixture->createContentElement(
			0,
			array('header' => 'Test header')
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'header',
				'tt_content',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'Test header',
			$row['header']
		);
	}

	public function testContentElementIsTextElementByDefault() {
		$uid = $this->fixture->createContentElement();

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'CType',
				'tt_content',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'text',
			$row['CType']
		);
	}

	public function testContentElementCanHaveOtherType() {
		$uid = $this->fixture->createContentElement(
			0,
			array('CType' => 'list')
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'CType',
				'tt_content',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'list',
			$row['CType']
		);
	}

	public function testContentElementMustHaveNoZeroPid() {
		try {
			$this->fixture->createContentElement(0, array('pid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testContentElementMustHaveNoNonZeroPid() {
		try {
			$this->fixture->createContentElement(0, array('pid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testContentElementMustHaveNoZeroUid() {
		try {
			$this->fixture->createContentElement(0, array('uid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testContentElementMustHaveNoNonZeroUid() {
		try {
			$this->fixture->createContentElement(0, array('uid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createCachePageEntry()
	// ---------------------------------------------------------------------

	public function testPageCacheEntryCanBeCreated() {
		$id = $this->fixture->createPageCacheEntry();

		$this->assertNotEquals(
			0,
			$id
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'cache_pages', 'id='.$id
			)
		);
	}

	public function testPageCacheEntryCanBeCreatedForACertainFrontEndPage() {
		$parentUid = $this->fixture->createFrontEndPage();
		$id = $this->fixture->createPageCacheEntry($parentUid);

		$this->assertNotEquals(
			0,
			$id
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'page_id',
				'cache_pages',
				'id='.$id
			)
		);

		$this->assertEquals(
			$parentUid,
			$row['page_id']
		);
	}

	public function testCachePagesCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$id = $this->fixture->createPageCacheEntry();
		$this->assertNotEquals(
			0,
			$id
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testCachePagesWillBeCleanedUp() {
		$id = $this->fixture->createPageCacheEntry();
		$this->assertNotEquals(
			0,
			$id
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'cache_pages', 'id='.$id
			)
		);
	}

	public function testPageCacheEntryMustHaveNoZeroId() {
		try {
			$this->fixture->createPageCacheEntry(0, array('id' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testPageCacheEntryMustHaveNoNonZeroId() {
		try {
			$this->fixture->createPageCacheEntry(0, array('id' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testPageCacheEntryMustHaveNoZeroPageId() {
		try {
			$this->fixture->createPageCacheEntry(0, array('page_id' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testPageCacheEntryMustHaveNoNonZeroPageId() {
		try {
			$this->fixture->createPageCacheEntry(0, array('page_id' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createTemplate()
	// ---------------------------------------------------------------------

	public function testTemplateCanBeCreatedOnNonRootPage() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'sys_template', 'uid='.$uid
			)
		);
	}

	public function testTemplateCannotBeCreatedOnRootPage() {
		try {
			$this->fixture->createTemplate(0);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testTemplateCannotBeCreatedWithNegativePageNumber() {
		try {
			$this->fixture->createTemplate(-1);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testTemplateCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);

		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testTemplateWillBeCleanedUp() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'sys_template', 'uid='.$uid
			)
		);
	}

	public function testTemplateInitiallyHasNoConfig() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'config',
				'sys_template',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'',
			$row['config']
		);
	}

	public function testTemplateCanHaveConfig() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate(
			$pageId,
			array('config' => 'plugin.tx_oelib.test = 1')
		);
		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'config',
				'sys_template',
				'uid='.$uid
			)
		);

		$this->assertEquals(
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
		$this->assertEquals(
			'42',
			$configuration['test']
		);
	}

	public function testTemplateInitiallyHasNoConstants() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'constants',
				'sys_template',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'',
			$row['constants']
		);
	}

	public function testTemplateCanHaveConstants() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate(
			$pageId,
			array('constants' => 'plugin.tx_oelib.test = 1')
		);
		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'constants',
				'sys_template',
				'uid='.$uid
			)
		);

		$this->assertEquals(
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
		$this->assertEquals(
			'42',
			$configuration['test']
		);
	}

	public function testTemplateMustNotHaveAZeroPid() {
		try {
			$this->fixture->createTemplate(0, array('pid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testTemplateMustNotHaveANonZeroPid() {
		try {
			$this->fixture->createTemplate(0, array('pid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testTemplateMustHaveNoZeroUid() {
		try {
			$this->fixture->createTemplate(0, array('uid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testTemplateMustNotHaveANonZeroUid() {
		try {
			$this->fixture->createTemplate(0, array('uid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndUserGroup()
	// ---------------------------------------------------------------------

	public function testFrontEndUserGroupCanBeCreated() {
		$uid = $this->fixture->createFrontEndUserGroup();

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'fe_groups', 'uid='.$uid
			)
		);
	}

	public function testFrontEndUserGroupTableCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createFrontEndUserGroup();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testFrontEndUserGroupTableWillBeCleanedUp() {
		$uid = $this->fixture->createFrontEndUserGroup();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'fe_groups', 'uid='.$uid
			)
		);
	}

	public function testFrontEndUserGroupHasNoTitleByDefault() {
		$uid = $this->fixture->createFrontEndUserGroup();

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				'fe_groups',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'',
			$row['title']
		);
	}

	public function testFrontEndUserGroupCanHaveATitle() {
		$uid = $this->fixture->createFrontEndUserGroup(
			array('title' => 'Test title')
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				'fe_groups',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'Test title',
			$row['title']
		);
	}

	public function testFrontEndUserGroupMustHaveNoZeroUid() {
		try {
			$this->fixture->createFrontEndUserGroup(array('uid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserGroupMustHaveNoNonZeroUid() {
		try {
			$this->fixture->createFrontEndUserGroup(array('uid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndUser()
	// ---------------------------------------------------------------------

	public function testFrontEndUserCanBeCreated() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$uid = $this->fixture->createFrontEndUser($feUserGroupUid);

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'fe_users', 'uid='.$uid
			)
		);
	}

	public function testFrontEndUserTableCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$uid = $this->fixture->createFrontEndUser($feUserGroupUid);
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->greaterThan(
			1,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	public function testFrontEndUserTableWillBeCleanedUp() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$uid = $this->fixture->createFrontEndUser($feUserGroupUid);
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'fe_users', 'uid='.$uid
			)
		);
	}

	public function testFrontEndUserHasNoUserNameByDefault() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$uid = $this->fixture->createFrontEndUser($feUserGroupUid);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'username',
				'fe_users',
				'uid='.$uid
			)
		);

		$this->assertEquals(
			'',
			$row['username']
		);
	}

	public function testFrontEndUserCanHaveAUserName() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$uid = $this->fixture->createFrontEndUser(
			$feUserGroupUid,
			array('username' => 'Test name')
		);

		$row = $this->fixture->getAssociativeDatabaseResult(
			$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'username',
				'fe_users',
				'uid='.$uid
			)
		);

		$this->assertEquals(
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

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'fe_users', 'uid='.$uid
			)
		);
	}

	public function testFrontEndUserMustHaveNoZeroUid() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		try {
			$this->fixture->createFrontEndUser($feUserGroupUid, array('uid' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserMustHaveNoNonZeroUid() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		try {
			$this->fixture->createFrontEndUser($feUserGroupUid, array('uid' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserMustHaveNoZeroUserGroupInTheDataArray() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		try {
			$this->fixture->createFrontEndUser($feUserGroupUid, array('usergroup' => 0));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserMustHaveNoNonZeroUserGroupInTheDataArray() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		try {
			$this->fixture->createFrontEndUser($feUserGroupUid, array('usergroup' => 99999));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserMustHaveNoUserGroupListInTheDataArray() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		try {
			$this->fixture->createFrontEndUser($feUserGroupUid, array('usergroup' => '1,2,4,5'));
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserMustHaveANonZeroUserGroup() {
		try {
			$this->fixture->createFrontEndUser(0);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserMustHaveANonEmptyUserGroup() {
		try {
			$this->fixture->createFrontEndUser('');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserMustHaveNotOnlyASpaceAsValueForTheUserGroup() {
		try {
			$this->fixture->createFrontEndUser(' ');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserMustHaveNoZeroUserGroupEvenIfSeveralGroupsAreProvided() {
		$feUserGroupUidOne = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidTwo = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidThree = $this->fixture->createFrontEndUserGroup();

		try {
			$this->fixture->createFrontEndUser(
			$feUserGroupUidOne.', '.$feUserGroupUidTwo.', 0, '.$feUserGroupUidThree
		);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testFrontEndUserMustHaveNoAlphabeticalCharactersInTheUserGroupList() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();

		try {
			$this->fixture->createFrontEndUser(
			$feUserGroupUid.', abc'
		);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}


	// ---------------------------------------------------------------------
	// Tests regarding user login and logout
	// ---------------------------------------------------------------------

	public function testLoginFrontEndUser() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$feUserId = $this->fixture->createFrontEndUser($feUserGroupUid);
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	public function testLogoutFrontEndUser() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$feUserId = $this->fixture->createFrontEndUser($feUserGroupUid);
		$this->fixture->loginFrontEndUser($feUserId);
		$this->fixture->logoutFrontEndUser();

		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}

	public function testLoginFrontEndUserWithAZeroUid() {
		try {
			$this->fixture->loginFrontEndUser(0);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}
}

?>
