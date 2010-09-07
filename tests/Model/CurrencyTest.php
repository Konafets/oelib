<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_oelib_Model_Currency class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_Model_CurrencyTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Model_Currency
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Model_Currency();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	////////////////////////////////
	// Tests concerning isReadOnly
	////////////////////////////////

	/**
	 * @test
	 */
	public function isReadOnlyIsTrue() {
		$this->assertTrue(
			$this->fixture->isReadOnly()
		);
	}


	//////////////////////////////////////////////////
	// Tests regarding getting the ISO alpha-3 code.
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getIsoAlpha3CodeCanReturnIsoAlpha3CodeOfEuro() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertEquals(
			'EUR',
			$fixture->getIsoAlpha3Code()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlpha3CodeCanReturnIsoAlpha3CodeOfUsDollars() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(155);

		$this->assertEquals(
			'USD',
			$fixture->getIsoAlpha3Code()
		);
	}


	/////////////////////////////////////
	// Tests regarding the left symbol.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function hasLeftSymbolForCurrencyWithLeftSymbolReturnsTrue() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertTrue(
			$fixture->hasLeftSymbol()
		);
	}

	/**
	 * @test
	 */
	public function hasLeftSymbolForCurrencyWithoutLeftSymbolReturnsFalse() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertFalse(
			$fixture->hasLeftSymbol()
		);
	}

	/**
	 * @ŧest
	 */
	public function getLeftSymbolForCurrencyWithLeftSymbolReturnsLeftSymbol() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertEquals(
			'€',
			$fixture->getLeftSymbol()
		);
	}

	/**
	 * @test
	 */
	public function getLeftSymbolForCurrencyWithoutLeftSymbolReturnsEmptyString() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertEquals(
			'',
			$fixture->getLeftSymbol()
		);
	}


	//////////////////////////////////////
	// Tests regarding the right symbol.
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function hasRightSymbolForCurrencyWithRightSymbolReturnsTrue() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertTrue(
			$fixture->hasRightSymbol()
		);
	}

	/**
	 * @test
	 */
	public function hasRightSymbolForCurrencyWithoutRightSymbolReturnsFalse() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertFalse(
			$fixture->hasRightSymbol()
		);
	}

	/**
	 * @ŧest
	 */
	public function getRightSymbolForCurrencyWithRightSymbolReturnsRightSymbol() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertEquals(
			'Kč',
			$fixture->getRightSymbol()
		);
	}

	/**
	 * @test
	 */
	public function getRightSymbolForCurrencyWithoutRightSymbolReturnsEmptyString() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertEquals(
			'',
			$fixture->getRightSymbol()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding the thousands separator.
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getThousandsSeparatorForEuroReturnsPoint() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertEquals(
			'.',
			$fixture->getThousandsSeparator()
		);
	}

	/**
	 * @test
	 */
	public function getThousandsSeparatorForUsDollarReturnsComma() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(155);

		$this->assertEquals(
			',',
			$fixture->getThousandsSeparator()
		);
	}


	///////////////////////////////////////////
	// Tests regarding the decimal separator.
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getDecimalSeparatorForEuroReturnsComma() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertEquals(
			',',
			$fixture->getDecimalSeparator()
		);
	}

	/**
	 * @test
	 */
	public function getDecimalSeparatorForUsDollarReturnsPoint() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(155);

		$this->assertEquals(
			'.',
			$fixture->getDecimalSeparator()
		);
	}


	////////////////////////////////////////
	// Tests regarding the decimal digits.
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getDecimalDigitsForSudaneseDinarReturnsZero() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(130);

		$this->assertEquals(
			0,
			$fixture->getDecimalDigits()
		);
	}

	/**
	 * @test
	 */
	public function getDecimalDigitsForMalagasyAriaryReturnsOne() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(173);

		$this->assertEquals(
			1,
			$fixture->getDecimalDigits()
		);
	}

	/**
	 * @test
	 */
	public function getDecimalDigitsForEuroReturnsTwo() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertEquals(
			2,
			$fixture->getDecimalDigits()
		);
	}

	/**
	 * @test
	 */
	public function getDecimalDigitsForKuwaitiDinarReturnsThree() {
		$fixture = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(81);

		$this->assertEquals(
			3,
			$fixture->getDecimalDigits()
		);
	}
}
?>