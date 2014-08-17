<?php
/**
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
	}
}