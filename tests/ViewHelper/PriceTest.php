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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_ViewHelper_PriceTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_ViewHelper_Price
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_ViewHelper_Price();
	}

	public function teardown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function renderWithoutSettingValueOrCurrencyFirstRendersZeroWithTwoDigits() {
		$this->assertSame(
			'0.00',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithValueWithoutSettingCurrencyUsesDecimalPointAndTwoRoundedDecimalDigits() {
		$this->fixture->setValue(12345.678);

		$this->assertSame(
			'12345.68',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderAfterSettingAnInvalidCurrencyUsesDecimalPointAndTwoRoundedDecimalDigits() {
		$this->fixture->setValue(12345.678);
		$this->fixture->setCurrencyFromIsoAlpha3Code('FOO');

		$this->assertSame(
			'12345.68',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithLeftSymbolRendersCurrencySymbolLeftOfPrice() {
		$this->fixture->setValue(123.45);
		$this->fixture->setCurrencyFromIsoAlpha3Code('EUR');

		$this->assertSame(
			'€ 123,45',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithRightSymbolRendersCurrencySymbolRightOfPrice() {
		$this->fixture->setValue(123.45);
		$this->fixture->setCurrencyFromIsoAlpha3Code('CZK');

		$this->assertSame(
			'123,45 Kč',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithoutDecimalDigitsReturnsPriceWithoutDecimalDigits() {
		$this->fixture->setValue(123.45);
		$this->fixture->setCurrencyFromIsoAlpha3Code('SDD');

		$this->assertSame(
			'sD 123',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithOneDecimalDigitReturnsPriceWithOneDecimalDigit() {
		$this->fixture->setValue(123.45);
		$this->fixture->setCurrencyFromIsoAlpha3Code('MGA');

		$this->assertSame(
			'123,5',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithTwoDecimalDigitsReturnsPriceWithTwoDecimalDigits() {
		$this->fixture->setValue(123.45);
		$this->fixture->setCurrencyFromIsoAlpha3Code('EUR');

		$this->assertSame(
			'€ 123,45',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithThreeDecimalDigitsReturnsPriceWithThreeDecimalDigits() {
		$this->fixture->setValue(123.456);
		$this->fixture->setCurrencyFromIsoAlpha3Code('KWD');

		$this->assertSame(
			'KD 123,456',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithCommaAsDecimalSeparatorReturnsPriceWithCommaAsDecimalSeparator() {
		$this->fixture->setValue(123.45);
		$this->fixture->setCurrencyFromIsoAlpha3Code('EUR');

		$this->assertSame(
			'€ 123,45',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithPointAsDecimalSeparatorReturnsPriceWithPointAsDecimalSeparator() {
		$this->fixture->setValue(123.45);
		$this->fixture->setCurrencyFromIsoAlpha3Code('USD');

		$this->assertSame(
			'$ 123.45',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithPointAsThousandsSeparatorReturnsPriceWithPointAsThousandsSeparator() {
		$this->fixture->setValue(1234.56);
		$this->fixture->setCurrencyFromIsoAlpha3Code('EUR');

		$this->assertSame(
			'€ 1.234,56',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForCurrencyWithCommaAsThousandsSeparatorReturnsPriceWithCommaAsThousandsSeparator() {
		$this->fixture->setValue(1234.56);
		$this->fixture->setCurrencyFromIsoAlpha3Code('USD');

		$this->assertSame(
			'$ 1,234.56',
			$this->fixture->render()
		);
	}
}
?>