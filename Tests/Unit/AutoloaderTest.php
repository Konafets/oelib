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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_AutoloaderTest extends Tx_Phpunit_TestCase {
	/**
	 * @test
	 */
	public function loadWithEmptyStringDoesNotFail() {
		Tx_Oelib_Autoloader::load('');
	}

	/**
	 * @test
	 */
	public function loadWithNameInOtherFormatReturnsFalse() {
		self::assertFalse(
			Tx_Oelib_Autoloader::load('asdfkj12k_jh234')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfInexistentExtensionReturnsFalse() {
		self::assertFalse(
			Tx_Oelib_Autoloader::load('tx_foo_Nothing')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfInexistentClassReturnsFalse() {
		self::assertFalse(
			Tx_Oelib_Autoloader::load('Tx_Oelib_Tests_Unit_Fixtures_CatchMe')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfLoadedClassReturnsTrue() {
		self::assertTrue(
			Tx_Oelib_Autoloader::load('Tx_Oelib_Tests_Unit_AutoloaderTest')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfExistingNotLoadedClassReturnsTrue() {
		self::assertTrue(
			Tx_Oelib_Autoloader::load('tx_oelib_Tests_Unit_Fixtures_NotIncluded')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfExistingNotLoadedClassWithUppercaseTxReturnsTrue() {
		self::assertTrue(
			Tx_Oelib_Autoloader::load('Tx_oelib_Tests_Unit_Fixtures_NotIncludedFirstUppercase')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfExistingNotLoadedClassWithUppercaseExtensionKeyReturnsTrue() {
		self::assertTrue(
			Tx_Oelib_Autoloader::load('Tx_Oelib_Tests_Unit_Fixtures_NotIncludedUppercaseExtensionKey')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfExistingClassWithDigitsInPathReturnsTrue() {
		self::assertTrue(
			Tx_Oelib_Autoloader::load('Tx_Oelib_Tests_Unit_Fixtures_pi1_NotIncluded1')
		);
	}

	/**
	 * @test
	 */
	public function loadWithNameOfExistingNotLoadedClassLoadsClass() {
		Tx_Oelib_Autoloader::load('Tx_Oelib_Tests_Unit_Fixtures_NotIncluded');

		self::assertTrue(
			class_exists('Tx_Oelib_Tests_Unit_Fixtures_NotIncluded', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function newWithNotIncludedClassDoesNotFail() {
		Tx_Oelib_Autoloader::load('Tx_Oelib_Tests_Unit_Fixtures_NotIncludedEither');
		new tx_oelib_Tests_Unit_Fixtures_NotIncludedEither();
	}
}