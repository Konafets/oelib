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
class Tx_Oelib_Tests_Unit_Double3ValidatorTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Double3Validator
	 */
	private $subject;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Double3Validator();
	}

	/**
	 * @test
	 */
	public function returnFieldJSReturnsNonEmptyString() {
		self::assertTrue(
			$this->subject->returnFieldJS() !== ''
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForIntegerReturnsFloatWithThreeDecimals() {
		self::assertSame(
			'42.000',
			$this->subject->evaluateFieldValue('42')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForFloatWithCommaReturnsFloatWithPoint() {
		self::assertSame(
			'42.123',
			$this->subject->evaluateFieldValue('42,123')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForFloatWithOneDecimalDigitReturnsFloatWithThreeDecimalDigits() {
		self::assertSame(
			'42.100',
			$this->subject->evaluateFieldValue('42.1')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForFloatWithTwoDecimalDigitsReturnsFloatWithThreeDecimalDigits() {
		self::assertSame(
			'42.120',
			$this->subject->evaluateFieldValue('42.12')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForFloatWithThreeDecimalsReturnsFloatWithThreeDecimals() {
		self::assertSame(
			'42.123',
			$this->subject->evaluateFieldValue('42.123')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueWithNegativeValueReturnsNegativeValue() {
		self::assertSame(
			'-42.123',
			$this->subject->evaluateFieldValue('-42.123')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForStringReturnsZeroWithThreeDecimalDigits() {
		self::assertSame(
			'0.000',
			$this->subject->evaluateFieldValue('foo bar')
		);
	}
}