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
 * Testcase for the testing framework in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Mario Rimann <typo3-coding@rimann.org>
 */

require_once(t3lib_extMgm::extPath('oelib')
	.'tests/fixtures/class.tx_oelib_testingframework.php');

define('OELIB_TESTTABLE', 'tx_oelib_test');
define('OELIB_TESTTABLE_MM', 'tx_oelib_test_article_mm');

class tx_oelib_testingframework_testcase extends tx_phpunit_testcase {
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_oelib_testingframework('tx_oelib');
	}

	protected function tearDown() {
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
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'sorting',
			OELIB_TESTTABLE_MM,
			'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
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

		return $row['sorting'];
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

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title',
			OELIB_TESTTABLE,
			'uid='.$uid
		);

		if (!$dbResult) {
			$this->fail('There was an error with the database query.');
		}

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		if (!$row) {
			$this->fail('There was an error with the result of the database query.');
		}
		$this->assertEquals(
			$title,
			$row['title']
		);
	}

	public function testCreateRecordOnInvalidTable() {
		$this->assertEquals(
			0,
			$this->fixture->createRecord('tx_oelib_DOESNOTEXIST', array())
		);
	}

	public function testCreateRecordWithEmptyTableName() {
		$this->assertEquals(
			0,
			$this->fixture->createRecord('', array())
		);
	}

	public function testCreateRecordWithUidFails() {
		$this->assertEquals(
			0,
			$this->fixture->createRecord(
				OELIB_TESTTABLE, array('uid' => 10000)
			)
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
		$this->assertEquals(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid='.$uid)
		);
	}

	public function testDeleteRecordOnInexistentRecord() {
		$uid = 10000;

		// Checks that the record is inexistent before testing on it.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(OELIB_TESTTABLE, 'uid='.$uid)
		);

		// Runs our delete function - it should run through and result true even
		// when it can't delete a record.
		$this->assertTrue(
			$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid)
		);
	}

	public function testDeleteRecordOnForeignTable() {
		$table = 'tx_seminars_seminars';
		$uid = 10000;

		$this->assertFalse(
			$this->fixture->deleteRecord($table, $uid)
		);
	}

	public function testDeleteRecordOnInexistentTable() {
		$table = 'tx_oelib_DOESNOTEXIST';
		$uid = 10000;

		$this->assertFalse(
			$this->fixture->deleteRecord($table, $uid)
		);
	}

	public function testDeleteRecordWithEmptyTableName() {
		$table = '';
		$uid = 10000;

		$this->assertFalse(
			$this->fixture->deleteRecord($table, $uid)
		);
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
			$this->fail('There was an error with the database query.');
		}

		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();

		// Checks whether the creation of the record was successful.
		$this->assertNotEquals(
			0,
			$uid
		);

		// Runs our delete method which should NOT affect the record created above.
		$this->assertTrue(
			$this->fixture->deleteRecord(OELIB_TESTTABLE, $uid)
		);

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
			$this->fail('There was an error with the database query.');
		}
	}



	// ---------------------------------------------------------------------
	// Tests regarding createRelation()
	// ---------------------------------------------------------------------

	public function testCreateRelationWithValidData() {
		$uidLocal = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);

		$this->assertTrue(
			$this->fixture->createRelation(
				OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
			)
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
		$uidLocal = 55;
		$uidForeign = 2000;

		$this->assertFalse(
			$this->fixture->createRelation($table, $uidLocal, $uidForeign)
		);
	}

	public function testCreateRelationWithEmptyTableName() {
		$this->assertFalse(
			$this->fixture->createRelation('', 200, 500)
		);
	}

	public function testCreateRelationWithInvalidData() {
		$this->assertFalse(
			$this->fixture->createRelation(OELIB_TESTTABLE_MM, 0, 50)
		);

		$this->assertFalse(
			$this->fixture->createRelation(OELIB_TESTTABLE_MM, 50, 0)
		);
	}

	public function testCreateRelationWithAutomaticSorting() {
		$uidLocal = $this->fixture->createRecord(OELIB_TESTTABLE);
		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->assertTrue(
			$this->fixture->createRelation(
				OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
			)
		);
		$previousSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
		$this->assertGreaterThan(
			0,
			$previousSorting
		);


		$uidForeign = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->assertTrue(
			$this->fixture->createRelation(
				OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
			)
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
		$sorting = 42;

		$this->assertTrue(
			$this->fixture->createRelation(
				OELIB_TESTTABLE_MM, $uidLocal, $uidForeign, $sorting
			)
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
		$uidLocal = 10000;
		$uidForeign = 20000;

		// Checks that the record is inexistent before testing on it.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				OELIB_TESTTABLE_MM,
				'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
			)
		);

		// Runs our delete function - it should run through and result true even
		// when it can't delete a record.
		$this->assertTrue(
			$this->fixture->removeRelation(
				OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
			)
		);
	}

	public function testRemoveRelationOnForeignTable() {
		$table = 'tx_seminars_seminars_places_mm';
		$uidLocal = 10000;
		$uidForeign = 20000;

		$this->assertFalse(
			$this->fixture->removeRelation($table, $uidLocal, $uidForeign)
		);
	}

	public function testRemoveRelationOnInexistentTable() {
		$table = 'tx_oelib_DOESNOTEXIST';
		$uidLocal = 10000;
		$uidForeign = 20000;

		$this->assertFalse(
			$this->fixture->removeRelation($table, $uidLocal, $uidForeign)
		);
	}

	public function testRemoveRelationWithEmptyTableName() {
		$table = '';
		$uidLocal = 10000;
		$uidForeign = 20000;

		$this->assertFalse(
			$this->fixture->removeRelation($table, $uidLocal, $uidForeign)
		);
	}

	public function testRemoveRelationOnRealRecord() {
		$uidLocal = 10000;
		$uidForeign = 20000;

		// Create a new record that looks like a real record, i.e. the is_dummy_record
		// flag is set to 0.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			OELIB_TESTTABLE_MM,
			array(
				'uid_local' => $uidLocal,
				'uid_foreign' => $uidForeign,
				'is_dummy_record' => 0
			)
		);

		if (!$dbResult) {
			$this->fail('There was an error with the database query.');
		}

		// Runs our delete method which should NOT affect the record created above.
		$this->assertTrue(
			$this->fixture->removeRelation(
				OELIB_TESTTABLE_MM, $uidLocal, $uidForeign
			)
		);

		// Checks whether the record still exists.
		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				OELIB_TESTTABLE_MM,
				'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
				)
		);

		// Deletes the record as it will not be caught by the clean up function.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			OELIB_TESTTABLE_MM,
			'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
				.' AND is_dummy_record=0'
		);

		if (!$dbResult) {
			$this->fail('There was an error with the database query.');
		}
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
			$this->fail('There was an error with the database query.');
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
			$this->fail('There was an error with the database query.');
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
		$this->fail('The expected exception was not caught!');
	}

	public function testCountRecordsWithForeignTableName() {
		$table = 'tx_seminars_seminars';
		$whereClause = 'is_dummy_record=1';

		try {
			$this->fixture->countRecords($table, $whereClause);
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail('The expected exception was not caught!');
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



	// ---------------------------------------------------------------------
	// Tests regarding resetAutoIncrement()
	// ---------------------------------------------------------------------

	public function testResetAutoIncrement() {
		// Creates and deletes a record and then resets the auto increment.
		$latestUid = $this->fixture->createRecord(OELIB_TESTTABLE);
		$this->fixture->deleteRecord(OELIB_TESTTABLE, $latestUid);
		$this->fixture->resetAutoIncrement(OELIB_TESTTABLE);

		// Checks whether the reset of the auto increment value worked as it
		// should. After the reset, the auto increment index should be equal
		// to the UID of the record we created and deleted before.
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'SHOW TABLE STATUS WHERE Name=\''.OELIB_TESTTABLE.'\';'
		);
		if (!$dbResult) {
			$this->fail('There was an error with the database query.');
		}

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		if (!$row) {
			$this->fail('There was an error with the result of the database query.');
		}
		$this->assertEquals(
			$latestUid,
			$row['Auto_increment']
		);
	}

	public function testResetAutoIncrementWithEmptyTableName() {
		try {
			$this->fixture->resetAutoIncrement('');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail('The expected exception was not caught!');
	}

	public function testResetAutoIncrementWithForeignTable() {
		try {
			$this->fixture->resetAutoIncrement('tx_seminars_seminars');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail('The expected exception was not caught!');
	}

	public function testResetAutoIncrementWithInexistentTable() {
		try {
			$this->fixture->resetAutoIncrement('tx_oelib_DOESNOTEXIST');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail('The expected exception was not caught!');
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
}

?>
