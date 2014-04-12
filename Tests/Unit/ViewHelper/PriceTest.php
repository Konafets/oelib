<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2014 Niels Pardon (mail@niels-pardon.de)
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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ViewHelper_PriceTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_ViewHelper_Price
	 */
	private $subject;

	public function setUp() {
		$this->subject = new tx_oelib_ViewHelper_Price();
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function renderWithoutSettingValueOrCurrencyFirstRendersZeroWithTwoDigits() {
		$this->assertSame(
			'0.00',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithValueWithoutSettingCurrencyUsesDecimalPointAndTwoRoundedDecimalDigits() {
		$this->subject->setValue(12345.678);

		$this->assertSame(
			'12345.68',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderAfterSettingAnInvalidCurrencyUsesDecimalPointAndTwoRoundedDecimalDigits() {
		$this->subject->setValue(12345.678);
		$this->subject->setCurrencyFromIsoAlpha3Code('FOO');

		$this->assertSame(
			'12345.68',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithLeftSymbolRendersCurrencySymbolLeftOfPrice() {
		$this->subject->setValue(123.45);
		$this->subject->setCurrencyFromIsoAlpha3Code('EUR');

		$this->assertSame(
			'€ 123,45',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithRightSymbolRendersCurrencySymbolRightOfPrice() {
		$this->subject->setValue(123.45);
		$this->subject->setCurrencyFromIsoAlpha3Code('CZK');

		$this->assertSame(
			'123,45 Kč',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithoutDecimalDigitsReturnsPriceWithoutDecimalDigits() {
		$this->subject->setValue(123.45);
		$this->subject->setCurrencyFromIsoAlpha3Code('CLP');

		$this->assertSame(
			'$ 123',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithOneDecimalDigitReturnsPriceWithOneDecimalDigit() {
		$this->subject->setValue(123.45);
		$this->subject->setCurrencyFromIsoAlpha3Code('MGA');

		$this->assertSame(
			'123,5',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithTwoDecimalDigitsReturnsPriceWithTwoDecimalDigits() {
		$this->subject->setValue(123.45);
		$this->subject->setCurrencyFromIsoAlpha3Code('EUR');

		$this->assertSame(
			'€ 123,45',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithThreeDecimalDigitsReturnsPriceWithThreeDecimalDigits() {
		$this->subject->setValue(123.456);
		$this->subject->setCurrencyFromIsoAlpha3Code('KWD');

		$this->assertSame(
			'KD 123,456',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithCommaAsDecimalSeparatorReturnsPriceWithCommaAsDecimalSeparator() {
		$this->subject->setValue(123.45);
		$this->subject->setCurrencyFromIsoAlpha3Code('EUR');

		$this->assertSame(
			'€ 123,45',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithPointAsDecimalSeparatorReturnsPriceWithPointAsDecimalSeparator() {
		$this->subject->setValue(123.45);
		$this->subject->setCurrencyFromIsoAlpha3Code('USD');

		$this->assertSame(
			'$ 123.45',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithPointAsThousandsSeparatorReturnsPriceWithPointAsThousandsSeparator() {
		$this->subject->setValue(1234.56);
		$this->subject->setCurrencyFromIsoAlpha3Code('EUR');

		$this->assertSame(
			'€ 1.234,56',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithCommaAsThousandsSeparatorReturnsPriceWithCommaAsThousandsSeparator() {
		$this->subject->setValue(1234.56);
		$this->subject->setCurrencyFromIsoAlpha3Code('USD');

		$this->assertSame(
			'$ 1,234.56',
			$this->subject->render()
		);
	}
}