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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_AutoloaderTest extends tx_phpunit_testcase {
	/**
	 * @test
	 */
	public function loadWithEmptyStringDoesNotFail() {
		tx_oelib_Autoloader::load('');
	}

	/**
	 * @test
	 */
	public function loadWithNameInOtherFormatReturnsFalse() {
		$this->assertFalse(
			tx_oelib_Autoloader::load('asdfkj12k_jh234')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfInexistentExtensionReturnsFalse() {
		$this->assertFalse(
			tx_oelib_Autoloader::load('tx_foo_Nothing')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfInexistentClassReturnsFalse() {
		$this->assertFalse(
			tx_oelib_Autoloader::load('tx_oelib_tests_fixtures_CatchMe')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfLoadedClassReturnsTrue() {
		$this->assertTrue(
			tx_oelib_Autoloader::load('tx_oelib_AutoloaderTest')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfExistingNotLoadedClassReturnsTrue() {
		$this->assertTrue(
			tx_oelib_Autoloader::load('tx_oelib_tests_fixtures_NotIncluded')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfExistingClassWithDigitsInPathReturnsTrue() {
		$this->assertTrue(
			tx_oelib_Autoloader::load('tx_oelib_tests_fixtures_pi1_NotIncluded1')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfExistingNotLoadedClassLoadsClass() {
		tx_oelib_Autoloader::load('tx_oelib_tests_fixtures_NotIncluded');

		$this->assertTrue(
			class_exists('tx_oelib_tests_fixtures_NotIncluded', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function newWithNotIncludedClassDoesNotFail() {
		new tx_oelib_tests_fixtures_NotIncludedEither();
	}
}
?>