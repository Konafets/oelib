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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_ViewHelper_PriceTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_ViewHelper_Price
	 */
	private $subject;

	protected function setUp() {
		$this->subject = new tx_oelib_ViewHelper_Price();
	}

	/**
	 * @test
	 */
	public function renderWithoutSettingValueOrCurrencyFirstRendersZeroWithTwoDigits() {
		self::assertSame(
			'0.00',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWithValueWithoutSettingCurrencyUsesDecimalPointAndTwoRoundedDecimalDigits() {
		$this->subject->setValue(12345.678);

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
			'$ 1,234.56',
			$this->subject->render()
		);
	}
}