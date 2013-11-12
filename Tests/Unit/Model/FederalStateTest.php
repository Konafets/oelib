<?php
/***************************************************************
* Copyright notice
*
* (c) 2012 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Testcase for the tx_oelib_Model_FederalState class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Model_FederalStateTest extends Tx_Phpunit_TestCase {
	public function tearDown() {
		tx_oelib_MapperRegistry::purgeInstance();
	}


	/*
	 * Tests regarding getting the local name
	 */

	/**
	 * @test
	 */
	public function getLocalNameReturnsLocalNameOfNorthRhineWestphalia() {
		$model = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FederalState')->find(88);

		$this->assertSame(
			'Nordrhein-Westfalen',
			$model->getLocalName()
		);
	}


	/*
	 * Tests regarding getting the English name
	 */

	/**
	 * @test
	 */
	public function getEnglishNameReturnsLocalNameOfNorthRhineWestphalia() {
		$model = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FederalState')->find(88);

		$this->assertSame(
			'North Rhine-Westphalia',
			$model->getEnglishName()
		);
	}


	/*
	 * Tests regarding getting the ISO alpha-2 code
	 */

	/**
	 * @test
	 */
	public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfNorthRhineWestphalia() {
		$model = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FederalState')->find(88);

		$this->assertSame(
			'DE',
			$model->getIsoAlpha2Code()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlpha2ZoneCodeReturnsIsoAlpha2ZoneCodeOfNorthRhineWestphalia() {
		$model = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FederalState')->find(88);

		$this->assertSame(
			'NW',
			$model->getIsoAlpha2ZoneCode()
		);
	}


	/*
	 * Tests concerning isReadOnly
	 */

	/**
	 * @test
	 */
	public function isReadOnlyIsTrue() {
		$model = new tx_oelib_Model_FederalState();

		$this->assertTrue(
			$model->isReadOnly()
		);
	}
}
?>