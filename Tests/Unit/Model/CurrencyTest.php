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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Model_CurrencyTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Model_Currency
	 */
	private $subject;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Model_Currency();
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertTrue(
			$subject->hasLeftSymbol()
		);
	}

	/**
	 * @test
	 */
	public function hasLeftSymbolForCurrencyWithoutLeftSymbolReturnsFalse() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertFalse(
			$subject->hasLeftSymbol()
		);
	}

	/**
	 * @test
	 */
	public function getLeftSymbolForCurrencyWithLeftSymbolReturnsLeftSymbol() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(40);

		$this->assertTrue(
			$subject->hasRightSymbol()
		);
	}

	/**
	 * @test
	 */
	public function hasRightSymbolForCurrencyWithoutRightSymbolReturnsFalse() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(49);

		$this->assertFalse(
			$subject->hasRightSymbol()
		);
	}

	/**
	 * @test
	 */
	public function getRightSymbolForCurrencyWithRightSymbolReturnsRightSymbol() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(155);

		$this->assertSame(
			'.',
			$subject->getDecimalSeparator()
		);
	}


	/*
	 * Tests regarding the decimal digits.
	 */

	/**
	 * @test
	 */
	public function getDecimalDigitsForChileanPesoReturnsZero() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->find(33);

		$this->assertSame(
			0,
			$subject->getDecimalDigits()
		);
	}

	/**
	 * @test
	 */
	public function getDecimalDigitsForMalagasyAriaryReturnsOne() {
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
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
		$subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Currency')->
			find(81);

		$this->assertSame(
			3,
			$subject->getDecimalDigits()
		);
	}
}