<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_DbTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}

	/*
	 * Utility functions
	 */

	/**
	 * Explodes a comma-separated list of integer values and sorts them
	 * numerically.
	 *
	 * @param string $valueList
	 *        comma-separated list of values, may be empty
	 *
	 * @return int[] the separate values, sorted numerically, may be empty
	 */
	private function sortExplode($valueList) {
		if ($valueList === '') {
			return array();
		}

		$numbers = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $valueList);
		sort($numbers, SORT_NUMERIC);

		return $numbers;
	}


	/*
	 * Tests for the utility functions
	 */

	/**
	 * @test
	 */
	public function sortExplodeWithEmptyStringReturnsEmptyArray() {
		self::assertSame(
			array(),
			$this->sortExplode('')
		);
	}

	/**
	 * @test
	 */
	public function sortExplodeWithOneNumberReturnsArrayWithNumber() {
		self::assertSame(
			array(42),
			$this->sortExplode('42')
		);
	}

	/**
	 * @test
	 */
	public function sortExplodeWithTwoAscendingNumbersReturnsArrayWithBothNumbers() {
		self::assertSame(
			array(1, 2),
			$this->sortExplode('1,2')
		);
	}

	/**
	 * @test
	 */
	public function sortExplodeWithTwoDescendingNumbersReturnsSortedArrayWithBothNumbers() {
		self::assertSame(
			array(1, 2),
			$this->sortExplode('2,1')
		);
	}


	/*
	 * Tests for enableFields
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function enableFieldsThrowsExceptionForTooSmallShowHidden() {
		Tx_Oelib_Db::enableFields('tx_oelib_test', -2);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function enableFieldsThrowsExceptionForTooBigShowHidden() {
		Tx_Oelib_Db::enableFields('tx_oelib_test', 2);
	}

	/**
	 * @test
	 */
	public function enableFieldsIsDifferentForDifferentTables() {
		self::assertNotSame(
			Tx_Oelib_Db::enableFields('tx_oelib_test'),
			Tx_Oelib_Db::enableFields('pages')
		);
	}

	/**
	 * @test
	 */
	public function enableFieldsCanBeDifferentForShowHiddenZeroAndOne() {
		self::assertNotSame(
			Tx_Oelib_Db::enableFields('tx_oelib_test', 0),
			Tx_Oelib_Db::enableFields('tx_oelib_test', 1)
		);
	}

	/**
	 * @test
	 */
	public function enableFieldsAreTheSameForShowHiddenZeroAndMinusOne() {
		self::assertSame(
			Tx_Oelib_Db::enableFields('tx_oelib_test', 0),
			Tx_Oelib_Db::enableFields('tx_oelib_test', -1)
		);
	}

	/**
	 * @test
	 */
	public function enableFieldsCanBeDifferentForShowHiddenOneAndMinusOne() {
		self::assertNotSame(
			Tx_Oelib_Db::enableFields('tx_oelib_test', 1),
			Tx_Oelib_Db::enableFields('tx_oelib_test', -1)
		);
	}

	/**
	 * @test
	 */
	public function enableFieldsCanBeDifferentForDifferentIgnores() {
		self::assertNotSame(
			Tx_Oelib_Db::enableFields('tx_oelib_test', 0, array()),
			Tx_Oelib_Db::enableFields(
				'tx_oelib_test', 0, array('endtime' => TRUE)
			)
		);
	}


	/*
	 * Tests concerning createRecursivePageList
	 */

	/**
	 * @test
	 */
	public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithDefaultRecursion() {
		self::assertSame(
			'',
			Tx_Oelib_Db::createRecursivePageList('')
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithZeroRecursion() {
		self::assertSame(
			'',
			Tx_Oelib_Db::createRecursivePageList('', 0)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithNonZeroRecursion() {
		self::assertSame(
			'',
			Tx_Oelib_Db::createRecursivePageList('', 1)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function createRecursivePageListThrowsWithNegativeRecursion() {
		Tx_Oelib_Db::createRecursivePageList('', -1);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListDoesNotContainSubpagesForOnePageWithZeroRecursion() {
		$uid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder($uid);

		self::assertSame(
			(string) $uid,
			Tx_Oelib_Db::createRecursivePageList((string) $uid, 0)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListDoesNotContainSubpagesForTwoPagesWithZeroRecursion() {
		$uid1 = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder($uid1);
		$uid2 = $this->testingFramework->createSystemFolder();

		self::assertSame(
			$this->sortExplode($uid1 . ',' . $uid2),
			$this->sortExplode(
				Tx_Oelib_Db::createRecursivePageList($uid1.','.$uid2, 0)
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

		self::assertSame(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(Tx_Oelib_Db::createRecursivePageList($uid, 1))
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListDoesNotContainUnrelatedPages() {
		$uid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder();

		self::assertSame(
			(string) $uid,
			Tx_Oelib_Db::createRecursivePageList($uid, 0)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListCanContainTwoSubpagesOfOnePage() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid1 = $this->testingFramework->createSystemFolder($uid);
		$subFolderUid2 = $this->testingFramework->createSystemFolder($uid);

		self::assertSame(
			$this->sortExplode($uid.','.$subFolderUid1.','.$subFolderUid2),
			$this->sortExplode(Tx_Oelib_Db::createRecursivePageList($uid, 1))
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

		self::assertSame(
			$this->sortExplode(
				$uid1.','.$uid2.','.$subFolderUid1.','.$subFolderUid2
			),
			$this->sortExplode(
				Tx_Oelib_Db::createRecursivePageList($uid1.','.$uid2, 1)
			)
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListHeedsIncreasingRecursionDepthOnSubsequentCalls() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);

		self::assertSame(
			(string) $uid,
			Tx_Oelib_Db::createRecursivePageList($uid, 0)
		);
		self::assertSame(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(Tx_Oelib_Db::createRecursivePageList($uid, 1))
		);
	}

	/**
	 * @test
	 */
	public function createRecursivePageListHeedsDecreasingRecursionDepthOnSubsequentCalls() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);

		self::assertSame(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(Tx_Oelib_Db::createRecursivePageList($uid, 1))
		);
		self::assertSame(
			(string) $uid,
			Tx_Oelib_Db::createRecursivePageList($uid, 0)
		);
	}


	/*
	 * Tests concerning getColumnsInTable
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function getColumnsInTableForEmptyTableNameThrowsException() {
		Tx_Oelib_Db::getColumnsInTable('');
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function getColumnsInTableForInexistentTableNameThrowsException() {
		Tx_Oelib_Db::getColumnsInTable('tx_oelib_doesnotexist');
	}

	/**
	 * @test
	 */
	public function getColumnsInTableReturnsArrayThatContainsExistingColumn() {
		$columns = Tx_Oelib_Db::getColumnsInTable('tx_oelib_test');

		self::assertTrue(
			isset($columns['title'])
		);
	}

	/**
	 * @test
	 */
	public function getColumnsInTableReturnsArrayThatNotContainsInexistentColumn() {
		$columns = Tx_Oelib_Db::getColumnsInTable('tx_oelib_test');

		self::assertFalse(
			isset($columns['does_not_exist'])
		);
	}


	/*
	 * Tests concerning getColumnDefinition
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function getColumnDefinitionForEmptyTableNameThrowsException() {
		Tx_Oelib_Db::getColumnDefinition('', 'uid');
	}

	/**
	 * @test
	 */
	public function getColumnDefinitionReturnsArrayThatContainsFieldName() {
		$definition = Tx_Oelib_Db::getColumnDefinition('tx_oelib_test', 'title');

		self::assertSame(
			'title',
			$definition['Field']
		);
	}


	/*
	 * Tests regarding tableHasColumnUid()
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function tableHasColumnUidForEmptyTableNameThrowsException() {
		Tx_Oelib_Db::tableHasColumnUid('');
	}

	/**
	 * @test
	 */
	public function tableHasColumnUidIsTrueOnTableWithColumnUid() {
		self::assertTrue(
			Tx_Oelib_Db::tableHasColumnUid('tx_oelib_test')
		);
	}

	/**
	 * @test
	 */
	public function tableHasColumnUidIsFalseOnTableWithoutColumnUid() {
		self::assertFalse(
			Tx_Oelib_Db::tableHasColumnUid('tx_oelib_test_article_mm')
		);
	}

	/**
	 * @test
	 */
	public function tableHasColumnUidCanReturnDifferentResultsForDifferentTables() {
		self::assertNotSame(
			Tx_Oelib_Db::tableHasColumnUid('tx_oelib_test'),
			Tx_Oelib_Db::tableHasColumnUid('tx_oelib_test_article_mm')
		);
	}


	/*
	 * Tests regarding tableHasColumn()
	 */

	/**
	 * @test
	 */
	public function tableHasColumnReturnsTrueOnTableWithColumn() {
		self::assertTrue(
			Tx_Oelib_Db::tableHasColumn(
				'tx_oelib_test', 'title'
			)
		);
	}

	/**
	 * @test
	 */
	public function tableHasColumnReturnsFalseOnTableWithoutColumn() {
		self::assertFalse(
			Tx_Oelib_Db::tableHasColumn(
				'tx_oelib_test', 'inexistent_column'
			)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function tableHasColumnThrowsExceptionOnEmptyTableName() {
		Tx_Oelib_Db::tableHasColumn(
			'', 'title'
		);
	}

	/**
	 * @test
	 */
	public function tableHasColumnReturnsFalseOnEmptyColumnName() {
		self::assertFalse(
			Tx_Oelib_Db::tableHasColumn(
				'tx_oelib_test', ''
			)
		);
	}


	/*
	 * Tests for delete
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function deleteForEmptyTableNameThrowsException() {
		Tx_Oelib_Db::delete(
			'', 'uid = 0'
		);
	}

	/**
	 * @test
	 */
	public function deleteDeletesRecord() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		Tx_Oelib_Db::delete(
			'tx_oelib_test', 'uid = ' . $uid
		);

		self::assertFalse(
			$this->testingFramework->existsRecordWithUid(
				'tx_oelib_test', $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function deleteForNoDeletedRecordReturnsZero() {
		self::assertSame(
			0,
			Tx_Oelib_Db::delete(
				'tx_oelib_test', 'uid = 0'
			)
		);
	}

	/**
	 * @test
	 */
	public function deleteForOneDeletedRecordReturnsOne() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		self::assertSame(
			1,
			Tx_Oelib_Db::delete(
				'tx_oelib_test', 'uid = ' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function deleteForTwoDeletedRecordsReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid2 = $this->testingFramework->createRecord('tx_oelib_test');

		self::assertSame(
			2,
			Tx_Oelib_Db::delete(
				'tx_oelib_test',
				'uid IN(' . $uid1 . ',' . $uid2 . ')'
			)
		);
	}


	/*
	 * Tests for update
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function updateForEmptyTableNameThrowsException() {
		Tx_Oelib_Db::update(
			'', 'uid = 0', array()
		);
	}

	/**
	 * @test
	 */
	public function updateChangesRecord() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		Tx_Oelib_Db::update(
			'tx_oelib_test', 'uid = ' . $uid, array('title' => 'foo')
		);

		self::assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function updateForNoChangedRecordReturnsZero() {
		self::assertSame(
			0,
			Tx_Oelib_Db::update(
				'tx_oelib_test', 'uid = 0', array('title' => 'foo')
			)
		);
	}

	/**
	 * @test
	 */
	public function updateForOneChangedRecordReturnsOne() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		self::assertSame(
			1,
			Tx_Oelib_Db::update(
				'tx_oelib_test', 'uid = ' . $uid, array('title' => 'foo')
			)
		);
	}

	/**
	 * @test
	 */
	public function updateForTwoChangedRecordsReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid2 = $this->testingFramework->createRecord('tx_oelib_test');

		self::assertSame(
			2,
			Tx_Oelib_Db::update(
				'tx_oelib_test',
				'uid IN(' . $uid1 . ',' . $uid2 . ')',
				array('title' => 'foo')
			)
		);
	}


	/*
	 * Tests for insert
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function insertForEmptyTableNameThrowsException() {
		Tx_Oelib_Db::insert(
			'', array('is_dummy_record' => 1)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function insertForEmptyRecordDataThrowsException() {
		Tx_Oelib_Db::insert(
			'tx_oelib_test', array()
		);
	}

	/**
	 * @test
	 */
	public function insertInsertsRecord() {
		Tx_Oelib_Db::insert(
			'tx_oelib_test', array('title' => 'foo', 'is_dummy_record' => 1)
		);
		$this->testingFramework->markTableAsDirty('tx_oelib_test');

		self::assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function insertForTableWithUidReturnsUidOfCreatedRecord() {
		$uid = Tx_Oelib_Db::insert(
			'tx_oelib_test', array('is_dummy_record' => 1)
		);
		$this->testingFramework->markTableAsDirty('tx_oelib_test');

		self::assertTrue(
			$this->testingFramework->existsRecordWithUid(
				'tx_oelib_test', $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function insertForTableWithoutUidReturnsZero() {
		$this->testingFramework->markTableAsDirty('tx_oelib_test_article_mm');

		self::assertSame(
			0,
			Tx_Oelib_Db::insert(
				'tx_oelib_test_article_mm', array('is_dummy_record' => 1)
			)
		);
	}


	/*
	 * Tests concerning select, selectSingle, selectMultiple
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function selectForEmptyTableNameThrowsException() {
		Tx_Oelib_Db::select('*', '');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function selectForEmptyFieldListThrowsException() {
		Tx_Oelib_Db::select('', 'tx_oelib_test');
	}

	/**
	 * @test
	 */
	public function selectReturnsResource() {
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 6001000) {
			self::markTestSkipped('This test only applies to TYPO3 CMS < 6.1.');
		}

		self::assertTrue(
			is_resource(Tx_Phpunit_Service_Database::select('title', 'tx_phpunit_test'))
		);
	}

	/**
	 * @test
	 */
	public function selectReturnsMySqliResult() {
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 6001000) {
			self::markTestSkipped('This test is available in TYPO3 6.1 and above.');
		}

		self::assertInstanceOf(
			'mysqli_result',
			Tx_Phpunit_Service_Database::select('title', 'tx_phpunit_test')
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function selectSingleForEmptyTableNameThrowsException() {
		Tx_Oelib_Db::selectSingle('*', '');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function selectSingleForEmptyFieldListThrowsException() {
		Tx_Oelib_Db::selectSingle('', 'tx_oelib_test');
	}

	/**
	 * @test
	 */
	public function selectSingleCanFindOneRow() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test'
		);

		self::assertSame(
			array('uid' => (string) $uid),
			Tx_Oelib_Db::selectSingle('uid', 'tx_oelib_test', 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException tx_oelib_Exception_EmptyQueryResult
	 */
	public function selectSingleForNoResultsThrowsEmptyQueryResultException() {
		Tx_Oelib_Db::selectSingle('uid', 'tx_oelib_test', 'title = "nothing"');
	}

	/**
	 * @test
	 */
	public function selectSingleCanOrderTheResults() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Title A')
		);
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Title B')
		);

		self::assertSame(
			array('uid' => (string) $uid),
			Tx_Oelib_Db::selectSingle('uid', 'tx_oelib_test', '', '', 'title DESC')
		);
	}

	/**
	 * @test
	 */
	public function selectSingleCanUseOffset() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Title A')
		);
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Title B')
		);

		self::assertSame(
			array('uid' => (string) $uid),
			Tx_Oelib_Db::selectSingle('uid', 'tx_oelib_test', '', '', 'title', 1)
		);
	}


	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function selectMultipleForEmptyTableNameThrowsException() {
		Tx_Oelib_Db::selectMultiple('*', '');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function selectMultipleForEmptyFieldListThrowsException() {
		Tx_Oelib_Db::selectMultiple('', 'tx_oelib_test');
	}

	/**
	 * @test
	 */
	public function selectMultipleForNoResultsReturnsEmptyArray() {
		self::assertSame(
			array(),
			Tx_Oelib_Db::selectMultiple(
				'uid', 'tx_oelib_test', 'title = "nothing"'
			)
		);
	}

	/**
	 * @test
	 */
	public function selectMultipleCanFindOneRow() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test'
		);

		self::assertSame(
			array(array('uid' => (string) $uid)),
			Tx_Oelib_Db::selectMultiple('uid', 'tx_oelib_test', 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function selectMultipleCanFindTwoRows() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		self::assertSame(
			array(
				array('title' => 'foo'),
				array('title' => 'foo'),
			),
			Tx_Oelib_Db::selectMultiple(
				'title', 'tx_oelib_test', 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function selectColumnForMultipleForNoMatchesReturnsEmptyArray() {
		self::assertSame(
			array(),
			Tx_Oelib_Db::selectColumnForMultiple(
				'title', 'tx_oelib_test', 'title = "nothing"'
			)
		);
	}

	/**
	 * @test
	 */
	public function selectColumnForMultipleForOneMatchReturnsArrayWithColumnContent() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		self::assertSame(
			array('foo'),
			Tx_Oelib_Db::selectColumnForMultiple(
				'title', 'tx_oelib_test', 'uid = ' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function selectColumnForMultipleForTwoMatchReturnsArrayWithColumnContents() {
		$uid1 = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$uid2 = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);

		$result = Tx_Oelib_Db::selectColumnForMultiple(
			'title', 'tx_oelib_test', 'uid = ' . $uid1 . ' OR uid = ' . $uid2
		);
		sort($result);
		self::assertSame(
			array('bar', 'foo'),
			$result
		);
	}


	/*
	 * Tests concerning getAllTableNames
	 */

	/**
	 * @test
	 */
	public function getAllTableNamesContainsExistingTable() {
		self::assertTrue(
			in_array('tx_oelib_test', Tx_Oelib_Db::getAllTableNames(), TRUE)
		);
	}

	/**
	 * @test
	 */
	public function getAllTableNamesNotContainsInexistentTable() {
		self::assertFalse(
			in_array('tx_oelib_doesnotexist', Tx_Oelib_Db::getAllTableNames(), TRUE)
		);
	}


	/*
	 * Tests concerning existsTable
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function existsTableWithEmptyTableNameThrowsException() {
		Tx_Oelib_Db::existsTable('');
	}

	/**
	 * @test
	 */
	public function existsTableForExistingTableReturnsTrue() {
		self::assertTrue(
			Tx_Oelib_Db::existsTable('tx_oelib_test')
		);
	}

	/**
	 * @test
	 */
	public function existsTableForInexistentTableReturnsFalse() {
		self::assertFalse(
			Tx_Oelib_Db::existsTable('tx_oelib_doesnotexist')
		);
	}


	/*
	 * Tests concerning getTcaForTable
	 */

	/**
	 * @test
	 */
	public function getTcaForTableReturnsValidTcaArray() {
		$tca = Tx_Oelib_Db::getTcaForTable('tx_oelib_test');

		self::assertTrue(is_array($tca['ctrl']));
		self::assertTrue(is_array($tca['interface']));
		self::assertTrue(is_array($tca['columns']));
		self::assertTrue(is_array($tca['types']));
		self::assertTrue(is_array($tca['palettes']));
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function getTcaForTableWithEmptyTableNameThrowsExceptionTca() {
		Tx_Oelib_Db::getTcaForTable('');
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function getTcaForTableWithInexistentTableNameThrowsExceptionTca() {
		Tx_Oelib_Db::getTcaForTable('tx_oelib_doesnotexist');
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function getTcaForTableThrowsExceptionOnTableWithoutTca() {
		Tx_Oelib_Db::getTcaForTable('tx_oelib_test_article_mm');
	}

	/**
	 * @test
	 */
	public function getTcaForTableCanLoadFieldsAddedByExtensions() {
		$tca = Tx_Oelib_Db::getTcaForTable('fe_users');

		self::assertTrue(
			isset($tca['columns']['tx_oelib_is_dummy_record'])
		);
	}


	/*
	 * Tests concerning count
	 */

	/**
	 * @test
	 */
	public function countCanBeCalledWithEmptyWhereClause() {
		Tx_Oelib_Db::count('tx_oelib_test', '');
	}

	/**
	 * @test
	 */
	public function countCanBeCalledWithMissingWhereClause() {
		Tx_Oelib_Db::count('tx_oelib_test');
	}

	/**
	 * @test
	 */
	public function countForNoMatchesReturnsZero() {
		self::assertSame(
			0,
			Tx_Oelib_Db::count(
				'tx_oelib_test',
				'uid = 42'
			)
		);
	}

	/**
	 * @test
	 */
	public function countForOneMatchReturnsOne() {
		self::assertSame(
			1,
			Tx_Oelib_Db::count(
				'tx_oelib_test',
				'uid = ' . $this->testingFramework->createRecord('tx_oelib_test')
			)
		);
	}

	/**
	 * @test
	 */
	public function countForTwoMatchesReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid2 = $this->testingFramework->createRecord('tx_oelib_test');

		self::assertSame(
			2,
			Tx_Oelib_Db::count(
				'tx_oelib_test',
				'uid IN(' . $uid1 . ',' . $uid2 . ')'
			)
		);
	}

	/**
	 * @test
	 */
	public function countCanBeCalledForTableWithoutUid() {
		Tx_Oelib_Db::count('tx_oelib_test_article_mm');
	}

	/**
	 * @test
	 */
	public function countCanBeCalledWithMultipleTables() {
		Tx_Oelib_Db::count('tx_oelib_test, tx_oelib_testchild');
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function countWithInvalidTableNameThrowsException() {
		Tx_Oelib_Db::count('tx_oelib_doesnotexist', 'uid = 42');
	}

	/**
	 * @test
	 */
	public function countCanBeCalledWithJoinedTables() {
		Tx_Oelib_Db::count('tx_oelib_test JOIN tx_oelib_testchild');
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function countDoesNotAllowJoinWithoutTables() {
		Tx_Oelib_Db::count('JOIN');
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function countDoesNotAllowJoinWithOnlyOneTableOnTheLeft() {
		Tx_Oelib_Db::count('tx_oelib_test JOIN ');
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function countDoesNotAllowJoinWithOnlyOneTableOnTheRight() {
		Tx_Oelib_Db::count('JOIN tx_oelib_test');
	}


	/*
	 * Tests regarding existsRecord
	 */

	/**
	 * @test
	 */
	public function existsRecordWithEmptyWhereClauseIsAllowed() {
		Tx_Oelib_Db::existsRecord('tx_oelib_test', '');
	}

	/**
	 * @test
	 */
	public function existsRecordWithMissingWhereClauseIsAllowed() {
		Tx_Oelib_Db::existsRecord('tx_oelib_test');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function existsRecordWithEmptyTableNameThrowsException() {
		Tx_Oelib_Db::existsRecord('');
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function existsRecordWithInvalidTableNameThrowsException() {
		Tx_Oelib_Db::existsRecord('tx_oelib_doesnotexist');
	}

	/**
	 * @test
	 */
	public function existsRecordForNoMatchesReturnsFalse() {
		self::assertFalse(
			Tx_Oelib_Db::existsRecord('tx_oelib_test', 'uid = 42')
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForOneMatchReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test'
		);

		self::assertTrue(
			Tx_Oelib_Db::existsRecord('tx_oelib_test', 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForTwoMatchesReturnsTrue() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		self::assertTrue(
			Tx_Oelib_Db::existsRecord('tx_oelib_test', 'title = "foo"')
		);
	}


	/*
	 * Tests regarding existsExactlyOneRecord
	 */

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed() {
		Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test', '');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed() {
		Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function existsExactlyOneRecordWithEmptyTableNameThrowsException() {
		Tx_Oelib_Db::existsExactlyOneRecord('');
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function existsExactlyOneRecordWithInvalidTableNameThrowsException() {
		Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_doesnotexist');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForNoMatchesReturnsFalse() {
		self::assertFalse(
			Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test', 'uid = 42')
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForOneMatchReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test'
		);

		self::assertTrue(
			Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test', 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForTwoMatchesReturnsFalse() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		self::assertFalse(
			Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test', 'title = "foo"')
		);
	}


	/*
	 * Tests regarding existsRecordWithUid
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function existsRecordWithUidWithZeroUidThrowsException() {
		Tx_Oelib_Db::existsRecordWithUid('tx_oelib_test', 0);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function existsRecordWithUidWithNegativeUidThrowsException() {
		Tx_Oelib_Db::existsRecordWithUid('tx_oelib_test', -1);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function existsRecordWithUidWithEmptyTableNameThrowsException() {
		Tx_Oelib_Db::existsRecordWithUid('', 42);
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function existsRecordWithUidWithInvalidTableNameThrowsException() {
		Tx_Oelib_Db::existsRecordWithUid('tx_oelib_doesnotexist', 42);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForNoMatchReturnsFalse() {
		self::assertFalse(
			Tx_Oelib_Db::existsRecordWithUid('tx_oelib_test', 42)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForMatchReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test'
		);

		self::assertTrue(
			Tx_Oelib_Db::existsRecordWithUid('tx_oelib_test', $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidUsesAdditionalNonEmptyWhereClause() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('deleted' => 1)
		);

		self::assertFalse(
			Tx_Oelib_Db::existsRecordWithUid(
				'tx_oelib_test', $uid, ' AND deleted = 0'
			)
		);
	}

	/**
	 * @test
	 */
	public function getDatabaseConnectionReturnsGlobalsDatabaseConnection() {
		self::assertSame(
			$GLOBALS['TYPO3_DB'],
			Tx_Oelib_Db::getDatabaseConnection()
		);
	}
}