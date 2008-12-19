<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee (typo3-coding@oliverklee.de)
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

/**
 * Testcase for the tx_oelib_db class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_db_testcase extends tx_phpunit_testcase {
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
		tx_oelib_db::enableFields('tx_oelib_test', -2);
	}

	public function testEnableFieldsThrowsExceptionForTooBigShowHidden() {
		$this->setExpectedException('Exception', '$showHidden may only be -1, 0 or 1, but actually is 2');
		tx_oelib_db::enableFields('tx_oelib_test', 2);
	}

	public function testEnableFieldsIsDifferentForDifferentTables() {
		$this->assertNotEquals(
			tx_oelib_db::enableFields('tx_oelib_test'),
			tx_oelib_db::enableFields('pages')
		);
	}

	public function testEnableFieldsCanBeDifferentForShowHiddenZeroAndOne() {
		$this->assertNotEquals(
			tx_oelib_db::enableFields('tx_oelib_test', 0),
			tx_oelib_db::enableFields('tx_oelib_test', 1)
		);
	}

	public function testEnableFieldsAreTheSameForShowHiddenZeroAndMinusOne() {
		$this->assertEquals(
			tx_oelib_db::enableFields('tx_oelib_test', 0),
			tx_oelib_db::enableFields('tx_oelib_test', -1)
		);
	}

	public function testEnableFieldsCanBeDifferentForShowHiddenOneAndMinusOne() {
		$this->assertNotEquals(
			tx_oelib_db::enableFields('tx_oelib_test', 1),
			tx_oelib_db::enableFields('tx_oelib_test', -1)
		);
	}

	public function testEnableFieldsCanBeDifferentForDifferentIgnores() {
		$this->assertNotEquals(
			tx_oelib_db::enableFields('tx_oelib_test', 0, array()),
			tx_oelib_db::enableFields(
				'tx_oelib_test', 0, array('endtime' => true)
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
				'tx_oelib_test', 0, array(), false
			),
			tx_oelib_db::enableFields(
				'tx_oelib_test', 0, array(), true
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
}
?>