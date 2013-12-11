<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon (mail@niels-pardon.de)
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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Model_CountryTest extends Tx_Phpunit_TestCase {
	public function setUp() {
	}

	public function tearDown() {
		Tx_Oelib_MapperRegistry::purgeInstance();
	}


	//////////////////////////////////////////////////
	// Tests regarding getting the local short name.
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getLocalShortNameReturnsLocalShortNameOfGermany() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->
			find(54);

		$this->assertSame(
			'Deutschland',
			$subject->getLocalShortName()
		);
	}

	/**
	 * @test
	 */
	public function getLocalShortNameReturnsLocalShortNameOfUnitedKingdomOfGreatBritain() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->
			find(74);

		$this->assertSame(
			'United Kingdom',
			$subject->getLocalShortName()
		);
	}


	//////////////////////////////////////////////////
	// Tests regarding getting the ISO alpha-2 code.
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfGermany() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->
			find(54);

		$this->assertSame(
			'DE',
			$subject->getIsoAlpha2Code()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfUnitedKingdomOfGreatBritain() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->
			find(74);

		$this->assertSame(
			'GB',
			$subject->getIsoAlpha2Code()
		);
	}


	//////////////////////////////////////////////////
	// Tests regarding getting the ISO alpha-3 code.
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getIsoAlpha3CodeReturnsIsoAlpha3CodeOfGermany() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->
			find(54);

		$this->assertSame(
			'DEU',
			$subject->getIsoAlpha3Code()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlpha3CodeReturnsIsoAlpha3CodeOfUnitedKingdomOfGreatBritain() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->
			find(74);

		$this->assertSame(
			'GBR',
			$subject->getIsoAlpha3Code()
		);
	}


	////////////////////////////////
	// Tests concerning isReadOnly
	////////////////////////////////

	/**
	 * @test
	 */
	public function isReadOnlyIsTrue() {
		$model = new Tx_Oelib_Model_Country();

		$this->assertTrue(
			$model->isReadOnly()
		);

		$model->__destruct();
	}
}