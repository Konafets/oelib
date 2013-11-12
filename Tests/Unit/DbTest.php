<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_DbTest extends Tx_Phpunit_TestCase {
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
	 * @param string $valueList
	 *        comma-separated list of values, may be empty
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

	/**
	 * @test
	 */
	public function sortExplodeWithEmptyStringReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->sortExplode('')
		);
	}

	/**
	 * @test
	 */
	public function sortExplodeWithOneNumberReturnsArrayWithNumber() {
		$this->assertSame(
			array(42),
			$this->sortExplode('42')
		);
	}

	/**
	 * @test
	 */
	public function sortExplodeWithTwoAscendingNumbersReturnsArrayWithBothNumbers() {
		$this->assertSame(
			array(1, 2),
			$this->sortExplode('1,2')
		);
	}

	/**
	 * @test
	 */
	public function sortExplodeWithTwoDescendingNumbersReturnsSortedArrayWithBothNumbers() {
		$this->assertSame(
			array(1, 2),
			$this->sortExplode('2,1')
		);
	}


	//////////////////////////////////
	// Tests for enableFields
	//////////////////////////////////

	/**
	 * @test
	 */
	public function enableFieldsThrowsExceptionForTooSmallShowHidden() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$showHidden may only be -1, 0 or 1, but actually is -2'
		);

		tx_oelib_db::enableFields(OELIB_TESTTABLE, -2);
	}

	/**
	 * @test
	 */
	public function enableFieldsThrowsExceptionForTooBigShowHidden() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$showHidden may only be -1, 0 or 1, but actually is 2'
		);

		tx_oelib_db::enableFields(OELIB_TESTTABLE, 2);
	}

	/**
	 * @test
	 */
	public function enableFieldsIsDifferentForDifferentTables() {
		$this->assertNotSame(
			tx_oelib_db::enableFields(OELIB_TESTTABLE),
			tx_oelib_db::enableFields('pages')
		);
	}

	/**
	 * @test
	 */
	public function enableFieldsCanBeDifferentForShowHiddenZeroAndOne() {
		$this->assertNotSame(
			tx_oelib_db::enableFields(OELIB_TESTTABLE, 0),
			tx_oelib_db::enableFields(OELIB_TESTTABLE, 1)
		);
	}

	/**
	 * @test
	 */
	public function enableFieldsAreTheSameForShowHiddenZeroAndMinusOne() {
		$this->assertSame(
			tx_oelib_db::enableFields(OELIB_TESTTABLE, 0),
			tx_oelib_db::enableFields(OELIB_TESTTABLE, -1)
		);
	}

	/**
	 * @test
	 */
	public function enableFieldsCanBeDifferentForShowHiddenOneAndMinusOne() {
		$this->assertNotSame(
			tx_oelib_db::enableFields(OELIB_TESTTABLE, 1),
			tx_oelib_db::enableFields(OELIB_TESTTABLE, -1)
		);
	}

	/**
	 * @test
	 */
	public function enableFieldsCanBeDifferentForDifferentIgnores() {
		$this->assertNotSame(
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
	 *
	 * @test
	 */
	public function enableFieldsCanBeDifferentForDifferentVersionParameters() {
		$this->markTestSkipped(
			'This test does not work until the full versioning feature is ' .
				'implemented in oelib. See ' .
				'https://bugs.oliverklee.com/show_bug.cgi?id=2180'
		);
		tx_oelib_db::enableVersioningPreviewForCachedPage();

		$this->assertNotSame(
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

	/**
	 * @test
	 */
	public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithDefaultRecursion() {
		$this->assertSame(
			'',
			tx_oelib_db::createRecursivePageList('')
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithZeroRecursion() {
		$this->assertSame(
			'',
			tx_oelib_db::createRecursivePageList('', 0)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithNonZeroRecursion() {
		$this->assertSame(
			'',
			tx_oelib_db::createRecursivePageList('', 1)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListThrowsWithNegativeRecursion() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$recursionDepth must be >= 0.'
		);

		tx_oelib_db::createRecursivePageList('', -1);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListDoesNotContainSubpagesForOnePageWithZeroRecursion() {
		$uid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder($uid);

		$this->assertSame(
			(string) $uid,
			tx_oelib_db::createRecursivePageList((string) $uid, 0)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListDoesNotContainSubpagesForTwoPagesWithZeroRecursion() {
		$uid1 = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder($uid1);
		$uid2 = $this->testingFramework->createSystemFolder();

		$this->assertSame(
			$this->sortExplode($uid1 . ',' . $uid2),
			$this->sortExplode(
				tx_oelib_db::createRecursivePageList($uid1.','.$uid2, 0)
			)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListDoesNotContainSubsubpagesForRecursionOfOne() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);
		$this->testingFramework->createSystemFolder($subFolderUid);

		$this->assertSame(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(tx_oelib_db::createRecursivePageList($uid, 1))
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListDoesNotContainUnrelatedPages() {
		$uid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder();

		$this->assertSame(
			(string) $uid,
			tx_oelib_db::createRecursivePageList($uid, 0)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListCanContainTwoSubpagesOfOnePage() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid1 = $this->testingFramework->createSystemFolder($uid);
		$subFolderUid2 = $this->testingFramework->createSystemFolder($uid);

		$this->assertSame(
			$this->sortExplode($uid.','.$subFolderUid1.','.$subFolderUid2),
			$this->sortExplode(tx_oelib_db::createRecursivePageList($uid, 1))
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListCanContainSubpagesOfTwoPages() {
		$uid1 = $this->testingFramework->createSystemFolder();
		$uid2 = $this->testingFramework->createSystemFolder();
		$subFolderUid1 = $this->testingFramework->createSystemFolder($uid1);
		$subFolderUid2 = $this->testingFramework->createSystemFolder($uid2);

		$this->assertSame(
			$this->sortExplode(
				$uid1.','.$uid2.','.$subFolderUid1.','.$subFolderUid2
			),
			$this->sortExplode(
				tx_oelib_db::createRecursivePageList($uid1.','.$uid2, 1)
			)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListHeedsIncreasingRecursionDepthOnSubsequentCalls() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);

		$this->assertSame(
			(string) $uid,
			tx_oelib_db::createRecursivePageList($uid, 0)
		);
		$this->assertSame(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(tx_oelib_db::createRecursivePageList($uid, 1))
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListHeedsDecreasingRecursionDepthOnSubsequentCalls() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);

		$this->assertSame(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(tx_oelib_db::createRecursivePageList($uid, 1))
		);
		$this->assertSame(
			(string) $uid,
			tx_oelib_db::createRecursivePageList($uid, 0)
		);
	}


	///////////////////////////////////////
	// Tests concerning getColumnsInTable
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getColumnsInTableForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::getColumnsInTable('');
	}

	/**
	 * @test
	 */
	public function getColumnsInTableForInexistentTableNameThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The table "tx_oelib_doesnotexist" does not exist.'
		);

		tx_oelib_db::getColumnsInTable('tx_oelib_doesnotexist');
	}

	/**
	 * @test
	 */
	public function getColumnsInTableReturnsArrayThatContainsExistingColumn() {
		$columns = tx_oelib_db::getColumnsInTable(OELIB_TESTTABLE);

		$this->assertTrue(
			isset($columns['title'])
		);
	}

	/**
	 * @test
	 */
	public function getColumnsInTableReturnsArrayThatNotContainsInexistentColumn() {
		$columns = tx_oelib_db::getColumnsInTable(OELIB_TESTTABLE);

		$this->assertFalse(
			isset($columns['does_not_exist'])
		);
	}


	//////////////////////////////////////////
	// Tests concerning getColumnDefinition
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function getColumnDefinitionForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::getColumnDefinition('', 'uid');
	}

	/**
	 * @test
	 */
	public function getColumnDefinitionReturnsArrayThatContainsFieldName() {
		$definition = tx_oelib_db::getColumnDefinition(OELIB_TESTTABLE, 'title');

		$this->assertTrue(
			$definition['Field'] == 'title'
		);
	}


	////////////////////////////////////////
	// Tests regarding tableHasColumnUid()
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function tableHasColumnUidForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::tableHasColumnUid('');
	}

	/**
	 * @test
	 */
	public function tableHasColumnUidIsTrueOnTableWithColumnUid() {
		$this->assertTrue(
			tx_oelib_db::tableHasColumnUid(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function tableHasColumnUidIsFalseOnTableWithoutColumnUid() {
		$this->assertFalse(
			tx_oelib_db::tableHasColumnUid(OELIB_TESTTABLE_MM)
		);
	}

	/**
	 * @test
	 */
	public function tableHasColumnUidCanReturnDifferentResultsForDifferentTables() {
		$this->assertNotSame(
			tx_oelib_db::tableHasColumnUid(OELIB_TESTTABLE),
			tx_oelib_db::tableHasColumnUid(OELIB_TESTTABLE_MM)
		);
	}


	/////////////////////////////////////
	// Tests regarding tableHasColumn()
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function tableHasColumnReturnsTrueOnTableWithColumn() {
		$this->assertTrue(
			tx_oelib_db::tableHasColumn(
				OELIB_TESTTABLE, 'title'
			)
		);
	}

	/**
	 * @test
	 */
	public function tableHasColumnReturnsFalseOnTableWithoutColumn() {
		$this->assertFalse(
			tx_oelib_db::tableHasColumn(
				OELIB_TESTTABLE, 'inexistent_column'
			)
		);
	}

	/**
	 * @test
	 */
	public function tableHasColumnThrowsExceptionOnEmptyTableName() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::tableHasColumn(
			'', 'title'
		);
	}

	/**
	 * @test
	 */
	public function tableHasColumnReturnsFalseOnEmptyColumnName() {
		$this->assertFalse(
			tx_oelib_db::tableHasColumn(
				OELIB_TESTTABLE, ''
			)
		);
	}


	/////////////////////
	// Tests for delete
	/////////////////////

	/**
	 * @test
	 */
	public function deleteForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::delete(
			'', 'uid = 0'
		);
	}

	/**
	 * @test
	 */
	public function deleteDeletesRecord() {
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

	/**
	 * @test
	 */
	public function deleteForNoDeletedRecordReturnsZero() {
		$this->assertSame(
			0,
			tx_oelib_db::delete(
				OELIB_TESTTABLE, 'uid = 0'
			)
		);
	}

	/**
	 * @test
	 */
	public function deleteForOneDeletedRecordReturnsOne() {
		$uid = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		$this->assertSame(
			1,
			tx_oelib_db::delete(
				OELIB_TESTTABLE, 'uid = ' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function deleteForTwoDeletedRecordsReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord(OELIB_TESTTABLE);
		$uid2 = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		$this->assertSame(
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

	/**
	 * @test
	 */
	public function updateForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::update(
			'', 'uid = 0', array()
		);
	}

	/**
	 * @test
	 */
	public function updateChangesRecord() {
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

	/**
	 * @test
	 */
	public function updateForNoChangedRecordReturnsZero() {
		$this->assertSame(
			0,
			tx_oelib_db::update(
				OELIB_TESTTABLE, 'uid = 0', array('title' => 'foo')
			)
		);
	}

	/**
	 * @test
	 */
	public function updateForOneChangedRecordReturnsOne() {
		$uid = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		$this->assertSame(
			1,
			tx_oelib_db::update(
				OELIB_TESTTABLE, 'uid = ' . $uid, array('title' => 'foo')
			)
		);
	}

	/**
	 * @test
	 */
	public function updateForTwoChangedRecordsReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord(OELIB_TESTTABLE);
		$uid2 = $this->testingFramework->createRecord(OELIB_TESTTABLE);

		$this->assertSame(
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

	/**
	 * @test
	 */
	public function insertForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::insert(
			'', array('is_dummy_record' => 1)
		);
	}

	/**
	 * @test
	 */
	public function insertForEmptyRecordDataThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$recordData must not be empty.'
		);

		tx_oelib_db::insert(
			OELIB_TESTTABLE, array()
		);
	}

	/**
	 * @test
	 */
	public function insertInsertsRecord() {
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

	/**
	 * @test
	 */
	public function insertForTableWithUidReturnsUidOfCreatedRecord() {
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

	/**
	 * @test
	 */
	public function insertForTableWithoutUidReturnsZero() {
		$this->testingFramework->markTableAsDirty(OELIB_TESTTABLE_MM);

		$this->assertSame(
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
			'InvalidArgumentException',
			'The table names must not be empty.'
		);

		tx_oelib_db::select('*', '');
	}

	/**
	 * @test
	 */
	public function selectForEmptyFieldListThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$fields must not be empty.'
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
			'InvalidArgumentException',
			'The table names must not be empty.'
		);

		tx_oelib_db::selectSingle('*', '');
	}

	/**
	 * @test
	 */
	public function selectSingleForEmptyFieldListThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$fields must not be empty.'
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

		$this->assertSame(
			array('uid' => (string) $uid),
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

		$this->assertSame(
			array('uid' => (string) $uid),
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

		$this->assertSame(
			array('uid' => (string) $uid),
			tx_oelib_db::selectSingle('uid', OELIB_TESTTABLE, '', '', 'title', 1)
		);
	}


	/**
	 * @test
	 */
	public function selectMultipleForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table names must not be empty.'
		);

		tx_oelib_db::selectMultiple('*', '');
	}

	/**
	 * @test
	 */
	public function selectMultipleForEmptyFieldListThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$fields must not be empty.'
		);

		tx_oelib_db::selectMultiple('', OELIB_TESTTABLE);
	}

	/**
	 * @test
	 */
	public function selectMultipleForNoResultsReturnsEmptyArray() {
		$this->assertSame(
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

		$this->assertSame(
			array(array('uid' => (string) $uid)),
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

		$this->assertSame(
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
		$this->assertSame(
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

		$this->assertSame(
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
		$this->assertSame(
			array('bar', 'foo'),
			$result
		);
	}


	//////////////////////////////////////
	// Tests concerning getAllTableNames
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getAllTableNamesContainsExistingTable() {
		$this->assertTrue(
			in_array(OELIB_TESTTABLE, tx_oelib_db::getAllTableNames())
		);
	}

	/**
	 * @test
	 */
	public function getAllTableNamesNotContainsInexistentTable() {
		$this->assertFalse(
			in_array('tx_oelib_doesnotexist', tx_oelib_db::getAllTableNames())
		);
	}


	/////////////////////////////////
	// Tests concerning existsTable
	/////////////////////////////////

	/**
	 * @test
	 */
	public function existsTableWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::existsTable('');
	}

	/**
	 * @test
	 */
	public function existsTableForExistingTableReturnsTrue() {
		$this->assertTrue(
			tx_oelib_db::existsTable(OELIB_TESTTABLE)
		);
	}

	/**
	 * @test
	 */
	public function existsTableForInexistentTableReturnsFalse() {
		$this->assertFalse(
			tx_oelib_db::existsTable('tx_oelib_doesnotexist')
		);
	}


	////////////////////////////////////
	// Tests concerning getTcaForTable
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getTcaForTableReturnsValidTcaArray() {
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
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::getTcaForTable('');
	}

	/**
	 * @test
	 */
	public function getTcaForTableWithInexistentTableNameThrowsExceptionTca() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The table "tx_oelib_doesnotexist" does not exist.'
		);

		tx_oelib_db::getTcaForTable('tx_oelib_doesnotexist');
	}

	/**
	 * @test
	 */
	public function getTcaForTableThrowsExceptionOnTableWithoutTca() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The table "' . OELIB_TESTTABLE_MM . '" has no TCA.'
		);

		tx_oelib_db::getTcaForTable(OELIB_TESTTABLE_MM);
	}

	/**
	 * @test
	 */
	public function getTcaForTableCanLoadFieldsAddedByExtensions() {
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
			'BadMethodCallException',
			'The table "tx_oelib_doesnotexist" does not exist.'
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
			'BadMethodCallException',
			'The table "JOIN" does not exist.'
		);

		tx_oelib_db::count('JOIN');
	}

	/**
	 * @test
	 */
	public function countDoesNotAllowJoinWithOnlyOneTableOnTheLeft() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The table "tx_oelib_test JOIN " does not exist.'
		);

		tx_oelib_db::count('tx_oelib_test JOIN ');
	}

	/**
	 * @test
	 */
	public function countDoesNotAllowJoinWithOnlyOneTableOnTheRight() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The table "JOIN tx_oelib_test" does not exist.'
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
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::existsRecord('');
	}

	/**
	 * @test
	 */
	public function existsRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The table "tx_oelib_doesnotexist" does not exist.'
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
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::existsExactlyOneRecord('');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The table "tx_oelib_doesnotexist" does not exist.'
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
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		tx_oelib_db::existsRecordWithUid(OELIB_TESTTABLE, 0);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		tx_oelib_db::existsRecordWithUid(OELIB_TESTTABLE, -1);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The table name must not be empty.'
		);

		tx_oelib_db::existsRecordWithUid('', 42);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The table "tx_oelib_doesnotexist" does not exist.'
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