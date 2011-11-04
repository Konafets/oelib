<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_oelib_Double3Validator class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_Double3ValidatorTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Double3Validator
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Double3Validator();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function returnFieldJSReturnsNonEmptyString() {
		$this->assertTrue(
			$this->fixture->returnFieldJS() != ''
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForIntegerReturnsFloatWithThreeDecimals() {
		$this->assertEquals(
			'42.000',
			$this->fixture->evaluateFieldValue('42')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForFloatWithCommaReturnsFloatWithPoint() {
		$this->assertEquals(
			'42.123',
			$this->fixture->evaluateFieldValue('42,123')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForFloatWithOneDecimalDigitReturnsFloatWithThreeDecimalDigits() {
		$this->assertEquals(
			'42.100',
			$this->fixture->evaluateFieldValue('42.1')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForFloatWithTwoDecimalDigitsReturnsFloatWithThreeDecimalDigits() {
		$this->assertEquals(
			'42.120',
			$this->fixture->evaluateFieldValue('42.12')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForFloatWithThreeDecimalsReturnsFloatWithThreeDecimals() {
		$this->assertEquals(
			'42.123',
			$this->fixture->evaluateFieldValue('42.123')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueWithNegativeValueReturnsNegativeValue() {
		$this->assertEquals(
			'-42.123',
			$this->fixture->evaluateFieldValue('-42.123')
		);
	}

	/**
	 * @test
	 */
	public function evaluateFieldValueForStringReturnsZeroWithThreeDecimalDigits() {
		$this->assertEquals(
			'0.000',
			$this->fixture->evaluateFieldValue('foo bar')
		);
	}
}
?>