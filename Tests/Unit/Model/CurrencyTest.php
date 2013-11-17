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
class Tx_Oelib_Model_CurrencyTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Model_Currency
	 */
	private $subject;

	public function setUp() {
		$this->subject = new tx_oelib_Model_Currency();
	}

	public function tearDown() {
		$this->subject->__destruct();
		unset($this->subject);
	}


	////////////////////////////////
	// Tests concerning isReadOnly
	////////////////////////////////

	/**
	 * @test
	 */
	public function isReadOnlyIsTrue() {
		$this->assertTrue(
			$this->subject->isReadOnly()
		);
	}


	//////////////////////////////////////////////////
	// Tests regarding getting the ISO alpha-3 code.
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getIsoAlpha3CodeCanReturnIsoAlpha3CodeOfEuro() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertSame(
			'EUR',
			$subject->getIsoAlpha3Code()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlpha3CodeCanReturnIsoAlpha3CodeOfUsDollars() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(155);

		$this->assertSame(
			'USD',
			$subject->getIsoAlpha3Code()
		);
	}


	/////////////////////////////////////
	// Tests regarding the left symbol.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function hasLeftSymbolForCurrencyWithLeftSymbolReturnsTrue() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertTrue(
			$subject->hasLeftSymbol()
		);
	}

	/**
	 * @test
	 */
	public function hasLeftSymbolForCurrencyWithoutLeftSymbolReturnsFalse() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertFalse(
			$subject->hasLeftSymbol()
		);
	}

	/**
	 * @test
	 */
	public function getLeftSymbolForCurrencyWithLeftSymbolReturnsLeftSymbol() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertSame(
			'€',
			$subject->getLeftSymbol()
		);
	}

	/**
	 * @test
	 */
	public function getLeftSymbolForCurrencyWithoutLeftSymbolReturnsEmptyString() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertSame(
			'',
			$subject->getLeftSymbol()
		);
	}


	//////////////////////////////////////
	// Tests regarding the right symbol.
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function hasRightSymbolForCurrencyWithRightSymbolReturnsTrue() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertTrue(
			$subject->hasRightSymbol()
		);
	}

	/**
	 * @test
	 */
	public function hasRightSymbolForCurrencyWithoutRightSymbolReturnsFalse() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertFalse(
			$subject->hasRightSymbol()
		);
	}

	/**
	 * @test
	 */
	public function getRightSymbolForCurrencyWithRightSymbolReturnsRightSymbol() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertSame(
			'Kč',
			$subject->getRightSymbol()
		);
	}

	/**
	 * @test
	 */
	public function getRightSymbolForCurrencyWithoutRightSymbolReturnsEmptyString() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertSame(
			'',
			$subject->getRightSymbol()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding the thousands separator.
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getThousandsSeparatorForEuroReturnsPoint() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertSame(
			'.',
			$subject->getThousandsSeparator()
		);
	}

	/**
	 * @test
	 */
	public function getThousandsSeparatorForUsDollarReturnsComma() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(155);

		$this->assertSame(
			',',
			$subject->getThousandsSeparator()
		);
	}


	///////////////////////////////////////////
	// Tests regarding the decimal separator.
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getDecimalSeparatorForEuroReturnsComma() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertSame(
			',',
			$subject->getDecimalSeparator()
		);
	}

	/**
	 * @test
	 */
	public function getDecimalSeparatorForUsDollarReturnsPoint() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(155);

		$this->assertSame(
			'.',
			$subject->getDecimalSeparator()
		);
	}


	////////////////////////////////////////
	// Tests regarding the decimal digits.
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getDecimalDigitsForSudaneseDinarReturnsZero() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(130);

		$this->assertSame(
			0,
			$subject->getDecimalDigits()
		);
	}

	/**
	 * @test
	 */
	public function getDecimalDigitsForMalagasyAriaryReturnsOne() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(173);

		$this->assertSame(
			1,
			$subject->getDecimalDigits()
		);
	}

	/**
	 * @test
	 */
	public function getDecimalDigitsForEuroReturnsTwo() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertSame(
			2,
			$subject->getDecimalDigits()
		);
	}

	/**
	 * @test
	 */
	public function getDecimalDigitsForKuwaitiDinarReturnsThree() {
		$subject = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(81);

		$this->assertSame(
			3,
			$subject->getDecimalDigits()
		);
	}
}
?>