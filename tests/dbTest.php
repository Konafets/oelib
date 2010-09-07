<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_db class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_dbTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->testingFramework);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Explodes a comma-separated list of integer values and sorts them
	 * numerically.
	 *
	 * @param string comma-separated list of values, may be empty
	 *
	 * @return array the separate values, sorted numerically, may be empty
	 */
	private function sortExplode($valueList) {
		if ($valueList == '') {
			return array();
		}

		$numbers = t3lib_div::intExplode(',', $valueList);
		sort($numbers, SORT_NUMERIC);

		return ($numbers);
	}


	////////////////////////////////////
	// Tests for the utility functions
	////////////////////////////////////

	public function testSortExplodeWithEmptyStringReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->sortExplode('')
		);
	}

	public function testSortExplodeWithOneNumberReturnsArrayWithNumber() {
		$this->assertEquals(
			array(42),
			$this->sortExplode('42')
		);
	}

	public function testSortExplodeWithTwoAscendingNumbersReturnsArrayWithBothNumbers() {
		$this->assertEquals(
			array(1, 2),
			$this->sortExplode('1,2')
		);
	}

	public function testSortExplodeWithTwoDescendingNumbersReturnsSortedArrayWithBothNumbers() {
		$this->assertEquals(
			array(1, 2),
			$this->sortExplode('2,1')
		);
	}


	//////////////////////////////////
	// Tests for enableFields
	//////////////////////////////////

	public function testEnableFieldsThrowsExceptionForTooSmallShowHidden() {
		$this->setExpectedException('Exception', '$showHidden may only be -1, 0 or 1, but actually is -2');
		tx_oelib_db::enableFields(OELIB_TESTTABLE, -2);
	}

	public function testEnableFieldsThrowsExceptionForTooBigShowHidden() {
		$this->setExpectedException('Exception', '$showHidden may only be -1, 0 or 1, but actually is 2');
		tx_oelib_db::enableFields(OELIB_TESTTABLE, 2);
	}

	public function testEnableFieldsIsDifferentForDifferentTables() {
		$this->assertNotEquals(
			tx_oelib_db::enableFields(OELIB_TESTTABLE),
			tx_oelib_db::enableFields('pages')
		);
	}

	public function testEnableFieldsCanBeDifferentForShowHiddenZeroAndOne() {
		$this->assertNotEquals(
			tx_oelib_db::enableFields(OELIB_TESTTABLE, 0),
			tx_oelib_db::enableFields(OELIB_TESTTABLE, 1)
		);
	}

	public function testEnableFieldsAreTheSameForShowHiddenZeroAndMinusOne() {
		$this->assertEquals(
			tx_oelib_db::enableFields(OELIB_TESTTABLE, 0),
			tx_oelib_db::enableFields(OELIB_TESTTABLE, -1)
		);
	}

	public function testEnableFieldsCanBeDifferentForShowHiddenOneAndMinusOne() {
		$this->assertNotEquals(
			tx_oelib_db::enableFields(OELIB_TESTTABLE, 1),
			tx_oelib_db::enableFields(OELIB_TESTTABLE, -1)
		);
	}

	public function testEnableFieldsCanBeDifferentForDifferentIgnores() {
		$this->assertNotEquals(
			tx_oelib_db::enableFields(OELIB_TESTTABLE, 0, array()),
			tx_oelib_db::enableFields(
				OELIB_TESTTABLE, 0, array('endtime' => TRUE)
			)
		);
	}

	/**
	 * TODO: This test does not work until the full versioning feature is
	 * implemented in oelib.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=2180
	 */
	public function testEnableFieldsCanBeDifferentForDifferentVersionParameters() {
		$this->markTestSkipped(
			'This test does not work until the full versioning feature is ' .
				'implemented in oelib. See ' .
				'https://bugs.oliverklee.com/show_bug.cgi?id=2180'
		);
		tx_oelib_db::enableVersioningPreviewForCachedPage();

		$this->assertNotEquals(
			tx_oelib_db::enableFields(
				OELIB_TESTTABLE, 0, array(), FALSE
			),
			tx_oelib_db::enableFields(
				OELIB_TESTTABLE, 0, array(), TRUE
			)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning createRecursivePageList
	/////////////////////////////////////////////

	public function testCreateRecursivePageListReturnsAnEmptyStringForNoPagesWithDefaultRecursion() {
		$this->assertEquals(
			'',
			tx_oelib_db::createRecursivePageList('')
		);
	}

	public function testCreateRecursivePageListReturnsAnEmptyStringForNoPagesWithZeroRecursion() {
		$this->assertEquals(
			'',
			tx_oelib_db::createRecursivePageList('', 0)
		);
	}

	public function testCreateRecursivePageListReturnsAnEmptyStringForNoPagesWithNonZeroRecursion() {
		$this->assertEquals(
			'',
			tx_oelib_db::createRecursivePageList('', 1)
		);
	}

	public function testCreateRecursivePageListThrowsWithNegativeRecursion() {
		$this->setExpectedException('Exception', '$recursionDepth must be >= 0.');

		tx_oelib_db::createRecursivePageList('', -1);
	}

	public function testCreateRecursivePageListDoesNotContainSubpagesForOnePageWithZeroRecursion() {
		$uid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder($uid);

		$this->assertEquals(
			(string) $uid,
			tx_oelib_db::createRecursivePageList((string) $uid, 0)
		);
	}

	public function testCreateRecursivePageListDoesNotContainSubpagesForTwoPagesWithZeroRecursion() {
		$uid1 = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder($uid1);
		$uid2 = $this->testingFramework->createSystemFolder();

		$this->assertEquals(
			$this->sortExplode($uid1 . ',' . $uid2),
			$this->sortExplode(
				tx_oelib_db::createRecursivePageList($uid1.','.$uid2, 0)
			)
		);
	}

	public function testCreateRecursivePageListDoesNotContainSubsubpagesForRecursionOfOne() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);
		$this->testingFramework->createSystemFolder($subFolderUid);

		$this->assertEquals(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(tx_oelib_db::createRecursivePageList($uid, 1))
		);
	}

	public function testCreateRecursivePageListDoesNotContainUnrelatedPages() {
		$uid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder();

		$this->assertEquals(
			(string) $uid,
			tx_oelib_db::createRecursivePageList($uid, 0)
		);
	}

	public function testCreateRecursivePageListCanContainTwoSubpagesOfOnePage() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid1 = $this->testingFramework->createSystemFolder($uid);
		$subFolderUid2 = $this->testingFramework->createSystemFolder($uid);

		$this->assertEquals(
			$this->sortExplode($uid.','.$subFolderUid1.','.$subFolderUid2),
			$this->sortExplode(tx_oelib_db::createRecursivePageList($uid, 1))
		);
	}

	public function testCreateRecursivePageListCanContainSubpagesOfTwoPages() {
		$uid1 = $this->testingFramework->createSystemFolder();
		$uid2 = $this->testingFramework->createSystemFolder();
		$subFolderUid1 = $this->testingFramework->createSystemFolder($uid1);
		$subFolderUid2 = $this->testingFramework->createSystemFolder($uid2);

		$this->assertEquals(
			$this->sortExplode(
				$uid1.','.$uid2.','.$subFolderUid1.','.$subFolderUid2
			),
			$this->sortExplode(
				tx_oelib_db::createRecursivePageList($uid1.','.$uid2, 1)
			)
		);
	}

	public function testCreateRecursivePageListHeedsIncreasingRecursionDepthOnSubsequentCalls() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);

		$this->assertEquals(
			(string) $uid,
			tx_oelib_db::createRecursivePageList($uid, 0)
		);
		$this->assertEquals(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(tx_oelib_db::createRecursivePageList($uid, 1))
		);
	}

	public function testCreateRecursivePageListHeedsDecreasingRecursionDepthOnSubsequentCalls() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);

		$this->assertEquals(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(tx_oelib_db::createRecursivePageList($uid, 1))
		);
		$this->assertEquals(
			(string) $uid,
			tx_oelib_db::createRecursivePageList($uid, 0)
		);
	}


	///////////////////////////////////////
	// Tests concerning getColumnsInTable
	///////////////////////////////////////

	public function testGetColumnsInTableForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		tx_oelib_db::getColumnsInTable('');
	}

	/**
	 * @test
	 */
	public function getColumnsInTableForInexistentTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_oelib_doesnotexist" does not exist.'
		);

		tx_oelib_db::getColumnsInTable('tx_oelib_doesnotexist');
	}

	public function testGetColumnsInTableReturnsArrayThatContainsExistingColumn() {
		$columns = tx_oelib_db::getColumnsInTable(OELIB_TESTTABLE);

		$this->assertTrue(
			isset($columns['title'])
		);
	}

	public function testGetColumnsInTableReturnsArrayThatNotContainsInexistentColumn() {
		$columns = tx_oelib_db::getColumnsInTable(OELIB_TESTTABLE);

		$this->assertFalse(
			isset($columns['does_not_exist'])
		);
	}


	//////////////////////////////////////////
	// Tests concerning getColumnDefinition
	//////////////////////////////////////////

	public function testGetColumnDefinitionForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		tx_oelib_db::getColumnDefinition('', 'uid');
	}

	public function testGetColumnDefinitionReturnsArrayThatContainsFieldName() {
		$definition = tx_oelib_db::getColumnDefinition(OELIB_TESTTABLE, 'title');

		$this->assertTrue(
			$definition['Field'] == 'title'
		);
	}


	////////////////////////////////////////
	// Tests regarding tableHasColumnUid()
	////////////////////////////////////////

	public function testTableHasColumnUidForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		tx_oelib_db::tableHasColumnUid('');
	}

	public function testTableHasColumnUidIsTrueOnTableWithColumnUid() {
		$this->assertTrue(
			tx_oelib_db::tableHasColumnUid(OELIB_TESTTABLE)
		);
	}

	public function testTableHasColumnUidIsFalseOnTableWithoutColumnUid() {
		$this->assertFalse(
			tx_oelib_db::tableHasColumnUid(OELIB_TESTTABLE_MM)
		);
	}

	public function testTableHasColumnUidCanReturnDifferentResultsForDifferentTables() {
		$this->assertNotEquals(
			tx_oelib_db::tableHasColumnUid(OELIB_TESTTABLE),
			tx_oelib_db::tableHasColumnUid(OELIB_TESTTABLE_MM)
		);
	}


	/////////////////////////////////////
	// Tests regarding tableHasColumn()
	/////////////////////////////////////

	public function testTableHasColumnReturnsTrueOnTableWithColumn() {
		$this->assertTrue(
			tx_oelib_db::tableHasColumn(
				OELIB_TESTTABLE, 'title'
			)
		);
	}

	public function testTableHasColumnReturnsFalseOnTableWithoutColumn() {
		$this->assertFalse(
			tx_oelib_db::tableHasColumn(
				OELIB_TESTTABLE, 'inexistent_column'
			)
		);
	}

	public function testTableHasColumnThrowsExceptionOnEmptyTableName() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		tx_oelib_db::tableHasColumn(
			'', 'title'
		);
	}

	public function testTableHasColumnReturnsFalseOnEmptyColumnName() {
		$this->assertFalse(
			tx_oelib_db::tableHasColumn(
				OELIB_TESTTABLE, ''
			)
		);
	}


	/////////////////////
	// Tests for delete
	/////////////////////

	public function testDeleteForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		tx_oelib_db::delete(
			'', 'uid = 0'
		);
	}

	public function testDeleteDeletesRecord() {
		$uid = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		tx_oelib_db::delete(
			OELIB_TESTTABLE, 'uid = ' . $uid
		);

		$this->assertFalse(
			$this->testingFramework->existsRecordWithUid(
				OELIB_TESTTABLE, $uid
			)
		);
	}

	public function testDeleteForNoDeletedRecordReturnsZero() {
		$this->assertEquals(
			0,
			tx_oelib_db::delete(
				OELIB_TESTTABLE, 'uid = 0'
			)
		);
	}

	public function testDeleteForOneDeletedRecordReturnsOne() {
		$uid = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		$this->assertEquals(
			1,
			tx_oelib_db::delete(
				OELIB_TESTTABLE, 'uid = ' . $uid
			)
		);
	}

	public function testDeleteForTwoDeletedRecordsReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord(OELIB_TESTTABLE);
		$uid2 = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		$this->assertEquals(
			2,
			tx_oelib_db::delete(
				OELIB_TESTTABLE,
				'uid IN(' . $uid1 . ',' . $uid2 . ')'
			)
		);
	}


	/////////////////////
	// Tests for update
	/////////////////////

	public function testUpdateForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		tx_oelib_db::update(
			'', 'uid = 0', array()
		);
	}

	public function testUpdateChangesRecord() {
		$uid = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		tx_oelib_db::update(
			OELIB_TESTTABLE, 'uid = ' . $uid, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				OELIB_TESTTABLE, 'title = "foo"'
			)
		);
	}

	public function testUpdateForNoChangedRecordReturnsZero() {
		$this->assertEquals(
			0,
			tx_oelib_db::update(
				OELIB_TESTTABLE, 'uid = 0', array('title' => 'foo')
			)
		);
	}

	public function testUpdateForOneChangedRecordReturnsOne() {
		$uid = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		$this->assertEquals(
			1,
			tx_oelib_db::update(
				OELIB_TESTTABLE, 'uid = ' . $uid, array('title' => 'foo')
			)
		);
	}

	public function testUpdateForTwoChangedRecordsReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord(OELIB_TESTTABLE);
		$uid2 = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		$this->assertEquals(
			2,
			tx_oelib_db::update(
				OELIB_TESTTABLE,
				'uid IN(' . $uid1 . ',' . $uid2 . ')',
				array('title' => 'foo')
			)
		);
	}


	/////////////////////
	// Tests for insert
	/////////////////////

	public function testInsertForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		tx_oelib_db::insert(
			'', array('is_dummy_record' => 1)
		);
	}

	public function testInsertForEmptyRecordDataThrowsException() {
		$this->setExpectedException(
			'Exception', '$recordData must not be empty.'
		);

		tx_oelib_db::insert(
			OELIB_TESTTABLE, array()
		);
	}

	public function testInsertInsertsRecord() {
		tx_oelib_db::insert(
			OELIB_TESTTABLE, array('title' => 'foo', 'is_dummy_record' => 1)
		);
		$this->testingFramework->markTableAsDirty(OELIB_TESTTABLE);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				OELIB_TESTTABLE, 'title = "foo"'
			)
		);
	}

	public function testInsertForTableWithUidReturnsUidOfCreatedRecord() {
		$uid = tx_oelib_db::insert(
			OELIB_TESTTABLE, array('is_dummy_record' => 1)
		);
		$this->testingFramework->markTableAsDirty(OELIB_TESTTABLE);

		$this->assertTrue(
			$this->testingFramework->existsRecordWithUid(
				OELIB_TESTTABLE, $uid
			)
		);
	}

	public function testInsertForTableWithoutUidReturnsZero() {
		$this->testingFramework->markTableAsDirty(OELIB_TESTTABLE_MM);

		$this->assertEquals(
			0,
			tx_oelib_db::insert(
				OELIB_TESTTABLE_MM, array('is_dummy_record' => 1)
			)
		);
	}


	//////////////////////////////////////////////////////////
	// Tests concerning select, selectSingle, selectMultiple
	//////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function selectForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table names must not be empty.'
		);

		tx_oelib_db::select('*', '');
	}

	/**
	 * @test
	 */
	public function selectForEmptyFieldListThrowsException() {
		$this->setExpectedException(
			'Exception', '$fields must not be empty.'
		);

		tx_oelib_db::select('', OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function selectReturnsRessource() {
		$this->assertTrue(
			is_resource(tx_oelib_db::select('title', OELIB_TESTTABLE))
		);
	}

	/**
	 * @test
	 */
	public function selectSingleForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table names must not be empty.'
		);

		tx_oelib_db::selectSingle('*', '');
	}

	/**
	 * @test
	 */
	public function selectSingleForEmptyFieldListThrowsException() {
		$this->setExpectedException(
			'Exception', '$fields must not be empty.'
		);

		tx_oelib_db::selectSingle('', OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function selectSingleCanFindOneRow() {
		$uid = $this->testingFramework->createRecord(
			OELIB_TESTTABLE
		);

		$this->assertEquals(
			array('uid' => $uid),
			tx_oelib_db::selectSingle('uid', OELIB_TESTTABLE, 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function selectSingleForNoResultsThrowsEmptyQueryResultException() {
		$this->setExpectedException(
			'tx_oelib_Exception_EmptyQueryResult'
		);

		tx_oelib_db::selectSingle('uid', OELIB_TESTTABLE, 'title = "nothing"');
	}

	/**
	 * @test
	 */
	public function selectSingleCanOrderTheResults() {
		$this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'Title A')
		);
		$uid = $this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'Title B')
		);

		$this->assertEquals(
			array('uid' => $uid),
			tx_oelib_db::selectSingle('uid', OELIB_TESTTABLE, '', '', 'title DESC')
		);
	}

	/**
	 * @test
	 */
	public function selectSingleCanUseOffset() {
		$this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'Title A')
		);
		$uid = $this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'Title B')
		);

		$this->assertEquals(
			array('uid' => $uid),
			tx_oelib_db::selectSingle('uid', OELIB_TESTTABLE, '', '', 'title', 1)
		);
	}


	/**
	 * @test
	 */
	public function selectMultipleForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table names must not be empty.'
		);

		tx_oelib_db::selectMultiple('*', '');
	}

	/**
	 * @test
	 */
	public function selectMultipleForEmptyFieldListThrowsException() {
		$this->setExpectedException(
			'Exception', '$fields must not be empty.'
		);

		tx_oelib_db::selectMultiple('', OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function selectMultipleForNoResultsReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			tx_oelib_db::selectMultiple(
				'uid', OELIB_TESTTABLE, 'title = "nothing"'
			)
		);
	}

	/**
	 * @test
	 */
	public function selectMultipleCanFindOneRow() {
		$uid = $this->testingFramework->createRecord(
			OELIB_TESTTABLE
		);

		$this->assertEquals(
			array(array('uid' => $uid)),
			tx_oelib_db::selectMultiple('uid', OELIB_TESTTABLE, 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function selectMultipleCanFindTwoRows() {
		$this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertEquals(
			array(
				array('title' => 'foo'),
				array('title' => 'foo'),
			),
			tx_oelib_db::selectMultiple(
				'title', OELIB_TESTTABLE, 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function selectColumnForMultipleForNoMatchesReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			tx_oelib_db::selectColumnForMultiple(
				'title', OELIB_TESTTABLE, 'title = "nothing"'
			)
		);
	}

	/**
	 * @test
	 */
	public function selectColumnForMultipleForOneMatchReturnsArrayWithColumnContent() {
		$uid = $this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertEquals(
			array('foo'),
			tx_oelib_db::selectColumnForMultiple(
				'title', OELIB_TESTTABLE, 'uid = ' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function selectColumnForMultipleForTwoMatchReturnsArrayWithColumnContents() {
		$uid1 = $this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$uid2 = $this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'bar')
		);

		$result = tx_oelib_db::selectColumnForMultiple(
			'title', OELIB_TESTTABLE, 'uid = ' . $uid1 . ' OR uid = ' . $uid2
		);
		sort($result);
		$this->assertEquals(
			array('bar', 'foo'),
			$result
		);
	}


	//////////////////////////////////////
	// Tests concerning getAllTableNames
	//////////////////////////////////////

	public function testGetAllTableNamesContainsExistingTable() {
		$this->assertTrue(
			in_array(OELIB_TESTTABLE, tx_oelib_db::getAllTableNames())
		);
	}

	public function testGetAllTableNamesNotContainsInexistentTable() {
		$this->assertFalse(
			in_array('tx_oelib_doesnotexist', tx_oelib_db::getAllTableNames())
		);
	}


	/////////////////////////////////
	// Tests concerning existsTable
	/////////////////////////////////

	public function testExistsTableWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		tx_oelib_db::existsTable('');
	}

	public function testExistsTableForExistingTableReturnsTrue() {
		$this->assertTrue(
			tx_oelib_db::existsTable(OELIB_TESTTABLE)
		);
	}

	public function testExistsTableForInexistentTableReturnsFalse() {
		$this->assertFalse(
			tx_oelib_db::existsTable('tx_oelib_doesnotexist')
		);
	}


	////////////////////////////////////
	// Tests concerning getTcaForTable
	////////////////////////////////////

	public function testGetTcaForTableReturnsValidTcaArray() {
		$tca = tx_oelib_db::getTcaForTable(OELIB_TESTTABLE);

		$this->assertTrue(is_array($tca['ctrl']));
		$this->assertTrue(is_array($tca['interface']));
		$this->assertTrue(is_array($tca['columns']));
		$this->assertTrue(is_array($tca['types']));
		$this->assertTrue(is_array($tca['palettes']));
	}

	/**
	 * @test
	 */
	public function getTcaForTableWithEmptyTableNameThrowsExceptionTca() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		tx_oelib_db::getTcaForTable('');
	}

	/**
	 * @test
	 */
	public function getTcaForTableWithInexistentTableNameThrowsExceptionTca() {
		$this->setExpectedException(
			'Exception', 'The table "tx_oelib_doesnotexist" does not exist.'
		);

		tx_oelib_db::getTcaForTable('tx_oelib_doesnotexist');
	}

	public function testGetTcaForTableThrowsExceptionOnTableWithoutTca() {
		$this->setExpectedException(
			'Exception', 'The table "' . OELIB_TESTTABLE_MM . '" has no TCA.'
		);

		tx_oelib_db::getTcaForTable(OELIB_TESTTABLE_MM);
	}

	public function test_getTcaForTableCanLoadFieldsAddedByExtensions() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register is loaded.'
			);
		}
		$tca = tx_oelib_db::getTcaForTable('fe_users');

		$this->assertTrue(isset($tca['columns']['gender']));
	}


	///////////////////////////
	// Tests concerning count
	///////////////////////////

	/**
	 * @test
	 */
	public function countCanBeCalledWithEmptyWhereClause() {
		tx_oelib_db::count(OELIB_TESTTABLE, '');
	}

	/**
	 * @test
	 */
	public function countCanBeCalledWithMissingWhereClause() {
		tx_oelib_db::count(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function countForNoMatchesReturnsZero() {
		$this->assertSame(
			0,
			tx_oelib_db::count(
				OELIB_TESTTABLE,
				'uid = 42'
			)
		);
	}

	/**
	 * @test
	 */
	public function countForOneMatchReturnsOne() {
		$this->assertSame(
			1,
			tx_oelib_db::count(
				OELIB_TESTTABLE,
				'uid = ' . $this->testingFramework->createRecord(OELIB_TESTTABLE)
			)
		);
	}

	/**
	 * @test
	 */
	public function countForTwoMatchesReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord(OELIB_TESTTABLE);
		$uid2 = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		$this->assertSame(
			2,
			tx_oelib_db::count(
				OELIB_TESTTABLE,
				'uid IN(' . $uid1 . ',' . $uid2 . ')'
			)
		);
	}

	/**
	 * @test
	 */
	public function countCanBeCalledForTableWithoutUid() {
		tx_oelib_db::count(OELIB_TESTTABLE_MM);
	}

	/**
	 * @test
	 */
	public function countCanBeCalledWithMultipleTables() {
		tx_oelib_db::count('tx_oelib_test, tx_oelib_testchild');
	}

	/**
	 * @test
	 */
	public function countWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_oelib_doesnotexist" does not exist.'
		);

		tx_oelib_db::count('tx_oelib_doesnotexist', 'uid = 42');
	}

	/**
	 * @test
	 */
	public function countCanBeCalledWithJoinedTables() {
		tx_oelib_db::count('tx_oelib_test JOIN tx_oelib_testchild');
	}

	/**
	 * @test
	 */
	public function countDoesNotAllowJoinWithoutTables() {
		$this->setExpectedException(
			'Exception', 'The table "JOIN" does not exist.'
		);

		tx_oelib_db::count('JOIN');
	}

	/**
	 * @test
	 */
	public function countDoesNotAllowJoinWithOnlyOneTableOnTheLeft() {
		$this->setExpectedException(
			'Exception', 'The table "tx_oelib_test JOIN " does not exist.'
		);

		tx_oelib_db::count('tx_oelib_test JOIN ');
	}

	/**
	 * @test
	 */
	public function countDoesNotAllowJoinWithOnlyOneTableOnTheRight() {
		$this->setExpectedException(
			'Exception', 'The table "JOIN tx_oelib_test" does not exist.'
		);

		tx_oelib_db::count('JOIN tx_oelib_test');
	}


	/////////////////////////////////
	// Tests regarding existsRecord
	/////////////////////////////////

	/**
	 * @test
	 */
	public function existsRecordWithEmptyWhereClauseIsAllowed() {
		tx_oelib_db::existsRecord(OELIB_TESTTABLE, '');
	}

	/**
	 * @test
	 */
	public function existsRecordWithMissingWhereClauseIsAllowed() {
		tx_oelib_db::existsRecord(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function existsRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The table name must not be empty.'
		);

		tx_oelib_db::existsRecord('');
	}

	/**
	 * @test
	 */
	public function existsRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_oelib_doesnotexist" does not exist.'
		);

		tx_oelib_db::existsRecord('tx_oelib_doesnotexist');
	}

	/**
	 * @test
	 */
	public function existsRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			tx_oelib_db::existsRecord(OELIB_TESTTABLE, 'uid = 42')
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForOneMatchReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			OELIB_TESTTABLE
		);

		$this->assertTrue(
			tx_oelib_db::existsRecord(OELIB_TESTTABLE, 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForTwoMatchesReturnsTrue() {
		$this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertTrue(
			tx_oelib_db::existsRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}


	///////////////////////////////////////////
	// Tests regarding existsExactlyOneRecord
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed() {
		tx_oelib_db::existsExactlyOneRecord(OELIB_TESTTABLE, '');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed() {
		tx_oelib_db::existsExactlyOneRecord(OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The table name must not be empty.'
		);

		tx_oelib_db::existsExactlyOneRecord('');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_oelib_doesnotexist" does not exist.'
		);

		tx_oelib_db::existsExactlyOneRecord('tx_oelib_doesnotexist');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			tx_oelib_db::existsExactlyOneRecord(OELIB_TESTTABLE, 'uid = 42')
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForOneMatchReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			OELIB_TESTTABLE
		);

		$this->assertTrue(
			tx_oelib_db::existsExactlyOneRecord(OELIB_TESTTABLE, 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForTwoMatchesReturnsFalse() {
		$this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('title' => 'foo')
		);

		$this->assertFalse(
			tx_oelib_db::existsExactlyOneRecord(OELIB_TESTTABLE, 'title = "foo"')
		);
	}


	////////////////////////////////////////
	// Tests regarding existsRecordWithUid
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function existsRecordWithUidWithZeroUidThrowsException() {
		$this->setExpectedException(
			'Exception', '$uid must be > 0.'
		);

		tx_oelib_db::existsRecordWithUid(OELIB_TESTTABLE, 0);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'Exception', '$uid must be > 0.'
		);

		tx_oelib_db::existsRecordWithUid(OELIB_TESTTABLE, -1);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The table name must not be empty.'
		);

		tx_oelib_db::existsRecordWithUid('', 42);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_oelib_doesnotexist" does not exist.'
		);

		tx_oelib_db::existsRecordWithUid('tx_oelib_doesnotexist', 42);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForNoMatchReturnsFalse() {
		$this->assertFalse(
			tx_oelib_db::existsRecordWithUid(OELIB_TESTTABLE, 42)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForMatchReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			OELIB_TESTTABLE
		);

		$this->assertTrue(
			tx_oelib_db::existsRecordWithUid(OELIB_TESTTABLE, $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidUsesAdditionalNonEmptyWhereClause() {
		$uid = $this->testingFramework->createRecord(
			OELIB_TESTTABLE, array('deleted' => 1)
		);

		$this->assertFalse(
			tx_oelib_db::existsRecordWithUid(
				OELIB_TESTTABLE, $uid, ' AND deleted = 0'
			)
		);
	}
}
?>