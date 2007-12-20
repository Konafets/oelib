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
	// Tests regarding createRecord()
	// ---------------------------------------------------------------------

	public function testCreateRecordOnValidTableWithNoData() {
		$this->assertNotEquals(
			0,
			$this->fixture->createRecord('tx_oelib_test', array())
		);
	}

	public function testCreateRecordWithValidData() {
		$table = 'tx_oelib_test';
		$title = 'TEST record';
		$uid = $this->fixture->createRecord(
			$table,
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
			$table,
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



	// ---------------------------------------------------------------------
	// Tests regarding deleteRecord()
	// ---------------------------------------------------------------------

	public function testDeleteRecordOnValidDummyRecord() {
		$table = 'tx_oelib_test';

		// Creates and directly destroys a dummy record.
		$uid = $this->fixture->createRecord($table, array());
		$this->fixture->deleteRecord($table, $uid);

		// Checks whether the record really was removed from the database.
		$this->assertEquals(
			0,
			$this->fixture->countRecords($table, 'uid='.$uid)
		);
	}

	public function testDeleteRecordOnInexistentRecord() {
		$table = 'tx_oelib_test';
		$uid = 10000;

		// Checks that the record is inexistent before testing on it.
		$this->assertEquals(
			0,
			$this->fixture->countRecords($table, 'uid='.$uid)
		);

		// Runs our delete function - it should run through and result true even
		// when it can't delete a record.
		$this->assertTrue(
			$this->fixture->deleteRecord($table, $uid)
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
		$table = 'tx_oelib_test';

		// Create a new record that looks like a real record, i.e. the is_dummy_record
		// flag is set to 0.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			$table,
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
			$this->fixture->deleteRecord($table, $uid)
		);

		// Checks whether the record still exists.
		$this->assertEquals(
			1,
			$this->fixture->countRecords($table, 'uid='.$uid)
		);

		// Deletes the record as it will not be caught by the clean up function.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$table,
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
		$table = 'tx_oelib_test_article_mm';
		$uidLocal = 55;
		$uidForeign = 2000;

		$this->assertTrue(
			$this->fixture->createRelation($table, $uidLocal, $uidForeign)
		);

		// Checks whether the record really exists.
		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				$table,
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
		$table = 'tx_oelib_test_article_mm';

		$this->assertFalse(
			$this->fixture->createRelation($table, 0, 50)
		);

		$this->assertFalse(
			$this->fixture->createRelation($table, 50, 0)
		);
	}



	// ---------------------------------------------------------------------
	// Tests regarding removeRelation()
	// ---------------------------------------------------------------------

	public function testRemoveRelationOnValidDummyRecord() {
		$table = 'tx_oelib_test_article_mm';
		$uidLocal = 55;
		$uidForeign = 77;

		// Creates and directly destroys a dummy record.
		$this->fixture->createRelation($table, $uidLocal, $uidForeign);
		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);

		// Checks whether the record really was removed from the database.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				$table,
				'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
			)
		);
	}

	public function testRemoveRelationOnInexistentRecord() {
		$table = 'tx_oelib_test_article_mm';
		$uidLocal = 10000;
		$uidForeign = 20000;

		// Checks that the record is inexistent before testing on it.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				$table,
				'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
			)
		);

		// Runs our delete function - it should run through and result true even
		// when it can't delete a record.
		$this->assertTrue(
			$this->fixture->removeRelation($table, $uidLocal, $uidForeign)
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
		$table = 'tx_oelib_test_article_mm';
		$uidLocal = 10000;
		$uidForeign = 20000;

		// Create a new record that looks like a real record, i.e. the is_dummy_record
		// flag is set to 0.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			$table,
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
			$this->fixture->removeRelation($table, $uidLocal, $uidForeign)
		);

		// Checks whether the record still exists.
		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				$table,
				'uid_local='.$uidLocal.' AND uid_foreign='.$uidForeign
				)
		);

		// Deletes the record as it will not be caught by the clean up function.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$table,
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
		$this->fixture->createRecord('tx_oelib_test');

		// Creates a dummy record directly in the database, without putting this
		// table name to the list of dirty tables.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_oelib_test_article_mm',
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
			$this->fixture->countRecords('tx_oelib_test', 'is_dummy_record=1'),
			'Some test records were not deleted from table "tx_oelib_test"'
		);

		// Checks whether the second dummy record still exists.
		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'tx_oelib_test_article_mm',
				'is_dummy_record=1'
			)
		);

		// Runs a deep clean up to delete all dummy records.
		$this->fixture->cleanUp(true);
	}

	public function testCleanUpWithDeepCleanup() {
		// Creates a dummy record (and marks that table as dirty).
		$this->fixture->createRecord('tx_oelib_test');

		// Creates a dummy record directly in the database, without putting this
		// table name to the list of dirty tables.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_oelib_test_article_mm',
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
			'tx_oelib_test',
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
		$table = 'tx_oelib_test';

		$this->assertEquals(
			0,
			$this->fixture->countRecords($table, '')
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
		$table = 'tx_oelib_test';
		$whereClause = 'is_dummy_record=1';

		$this->assertEquals(
			0,
			$this->fixture->countRecords($table, $whereClause)
		);
		$this->fixture->createRecord($table);
		$this->assertEquals(
			1,
			$this->fixture->countRecords($table, $whereClause)
		);
	}



	// ---------------------------------------------------------------------
	// Tests regarding resetAutoIncrement()
	// ---------------------------------------------------------------------

	public function testResetAutoIncrement() {
		$table = 'tx_oelib_test';

		// Creates and deletes a record and then resets the auto increment.
		$latestUid = $this->fixture->createRecord($table);
		$this->fixture->deleteRecord($table, $latestUid);
		$this->fixture->resetAutoIncrement($table);

		// Checks whether the reset of the auto increment value worked as it
		// should. After the reset, the auto increment index should be equal
		// to the UID of the record we created and deleted before.
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'SHOW TABLE STATUS WHERE Name=\''.$table.'\';'
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
		$table = 'tx_oelib_test';
		$this->assertTrue($this->fixture->hasTableColumnUid($table));	
	}

	public function testHasTableColumnUidOnTableWithoutColumnUid() {
		$table = 'tx_oelib_test_article_mm';
		$this->assertFalse($this->fixture->hasTableColumnUid($table));	
	}



	// ---------------------------------------------------------------------
	// Tests regarding markTableAsDirty()
	// ---------------------------------------------------------------------
	
	public function testMarkTableAsDirty() {
		$table = 'tx_oelib_test';
		$this->assertEquals(
			array(),
			$this->fixture->getListOfDirtyTables()
		);

		$this->fixture->createRecord($table, array());
		$this->assertEquals(
			array(
				$table => $table
			),
			$this->fixture->getListOfDirtyTables()
		);
	}
}

?>
