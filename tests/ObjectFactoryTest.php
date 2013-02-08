<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
class tx_oelib_ObjectFactoryTest extends tx_phpunit_testcase {
	public function setUp() {
	}

	public function tearDown() {
	}


	/**
	 * @test
	 */
	public function canCreateInstanceOfClassWithConstructorWithoutParameters() {
		$this->assertTrue(
			tx_oelib_ObjectFactory::make('tx_oelib_tests_fixtures_TestingModel')
				instanceof tx_oelib_tests_fixtures_TestingModel
		);
	}

	/**
	 * @test
	 */
	public function canCreateInstanceOfClassWithConstructorWithParameters() {
		$object = tx_oelib_ObjectFactory::make(
			'tx_oelib_Translator', 'de', '', array()
		);

		$this->assertTrue(
			$object instanceof tx_oelib_Translator
		);

		$this->assertSame(
			'de',
			$object->getLanguageKey()
		);

		$object->__destruct();
	}

	/**
	 * @test
	 */
	public function makeInstantiatesSubclassIfXclassIsAvailable() {
		$object = tx_oelib_ObjectFactory::make('tx_oelib_tests_fixtures_Empty');

		$this->assertSame(
			'ux_tx_oelib_tests_fixtures_Empty',
			get_class($object)
		);
	}
}
?>