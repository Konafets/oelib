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
 * Testcase for the Tx_Oelib_Model_FederalState class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Model_FederalStateTest extends Tx_Phpunit_TestCase {
	public function tearDown() {
		Tx_Oelib_MapperRegistry::purgeInstance();
	}


	/*
	 * Tests regarding getting the local name
	 */

	/**
	 * @test
	 */
	public function getLocalNameReturnsLocalNameOfNorthRhineWestphalia() {
		$model = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FederalState')->find(88);

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
		$model = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FederalState')->find(88);

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
		$model = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FederalState')->find(88);

		$this->assertSame(
			'DE',
			$model->getIsoAlpha2Code()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlpha2ZoneCodeReturnsIsoAlpha2ZoneCodeOfNorthRhineWestphalia() {
		$model = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FederalState')->find(88);

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
		$model = new Tx_Oelib_Model_FederalState();

		$this->assertTrue(
			$model->isReadOnly()
		);
	}
}