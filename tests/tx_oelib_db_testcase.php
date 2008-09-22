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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_db.php');

/**
 * Testcase for the tx_oelib_db class in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_db_testcase extends tx_phpunit_testcase {
	public function setUp() {
	}

	public function tearDown() {
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
	 * @see	https://bugs.oliverklee.com/show_bug.cgi?id=2180
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
}
?>