<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2012 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_Autoloader class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_AutoloaderTest extends tx_phpunit_testcase {
	public function testLoadWithEmptyStringDoesNotFail() {
		tx_oelib_Autoloader::load('');
	}

	public function testLoadWithNameInOtherFormatReturnsFalse() {
		$this->assertFalse(
			tx_oelib_Autoloader::load('asdfkj12k_jh234')
		);
	}

	public function testLoadWithNameOfInexistentExtensionReturnsFalse() {
		$this->assertFalse(
			tx_oelib_Autoloader::load('tx_foo_Nothing')
		);
	}

	public function testLoadWithNameOfInexistentClassReturnsFalse() {
		$this->assertFalse(
			tx_oelib_Autoloader::load('tx_oelib_tests_fixtures_CatchMe')
		);
	}

	public function testLoadWithNameOfLoadedClassReturnsTrue() {
		$this->assertTrue(
			tx_oelib_Autoloader::load('tx_oelib_AutoloaderTest')
		);
	}

	public function testLoadWithNameOfExistingNotLoadedClassReturnsTrue() {
		$this->assertTrue(
			tx_oelib_Autoloader::load('tx_oelib_tests_fixtures_NotIncluded')
		);
	}

	public function testLoadWithNameOfExistingClassWithDigitsInPathReturnsTrue() {
		$this->assertTrue(
			tx_oelib_Autoloader::load('tx_oelib_tests_fixtures_pi1_NotIncluded1')
		);
	}

	public function testLoadWithNameOfExistingNotLoadedClassLoadsClass() {
		tx_oelib_Autoloader::load('tx_oelib_tests_fixtures_NotIncluded');

		$this->assertTrue(
			class_exists('tx_oelib_tests_fixtures_NotIncluded', FALSE)
		);
	}

	public function testNewWithNotIncludedClassDoesNotFail() {
		new tx_oelib_tests_fixtures_NotIncludedEither();
	}
}
?>