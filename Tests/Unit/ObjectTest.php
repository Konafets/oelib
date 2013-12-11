<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ObjectTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_testingObject the object to test
	 */
	private $subject;

	public function setUp() {
		$this->subject = new tx_oelib_testingObject();
	}

	public function tearDown() {
		$this->subject->__destruct();
		unset($this->subject);
	}


	//////////////////////////////////
	// Tests for checkForNonEmptyKey
	//////////////////////////////////

	/**
	 * @test
	 */
	public function checkForNonEmptyKeyWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->checkForNonEmptyKey('');
	}

	/**
	 * @test
	 */
	public function checkForNonEmptyKeyWithNonEmptyKeyIsAllowed() {
		$this->subject->checkForNonEmptyKey('foo');
	}


	//////////////////////////////////////////
	// Tests for setAsString and getAsString
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAsStringWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->getAsString('');
	}

	/**
	 * @test
	 */
	public function setAsStringWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->setAsString('', 'bar');
	}

	/**
	 * @test
	 */
	public function getAsStringWithInexistentKeyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsNonEmptyStringSetViaSetAsString() {
		$this->subject->setAsString('foo', 'bar');

		$this->assertSame(
			'bar',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsTrimmedValue() {
		$this->subject->setAsString('foo', ' bar ');

		$this->assertSame(
			'bar',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsEmptyStringSetViaSetAsString() {
		$this->subject->setAsString('foo', '');

		$this->assertSame(
			'',
			$this->subject->getAsString('foo')
		);
	}


	////////////////////////////////////////////
	// Tests for setAsInteger and getAsInteger
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAsIntegerWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->getAsInteger('');
	}

	/**
	 * @test
	 */
	public function setAsIntegerWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->setAsInteger('', 42);
	}

	/**
	 * @test
	 */
	public function getAsIntegerWithInexistentKeyReturnsZero() {
		$this->assertSame(
			0,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsPositiveIntegerSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 42);

		$this->assertSame(
			42,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsNegativeIntegerSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', -42);

		$this->assertSame(
			-42,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsZeroSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 0);

		$this->assertSame(
			0,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsZeroForStringSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 'bar');

		$this->assertSame(
			0,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsRoundedValueForFloatSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 12.34);

		$this->assertSame(
			12,
			$this->subject->getAsInteger('foo')
		);
	}


	//////////////////////////////////////////////////////////////////
	// Tests for setAsArray, getAsTrimmedArray and getAsIntegerArray
	//////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAsTrimmedArrayWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->getAsTrimmedArray('');
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->getAsIntegerArray('');
	}

	/**
	 * @test
	 */
	public function setAsArrayWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->setAsArray('', array('bar'));
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayWithInexistentKeyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayWithInexistentKeyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getAsIntegerArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayReturnsNonEmptyArraySetViaSetAsArray() {
		$this->subject->setAsArray('foo', array('foo', 'bar'));

		$this->assertSame(
			array('foo', 'bar'),
			$this->subject->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayReturnsNonEmptyArraySetViaSetAsArray() {
		$this->subject->setAsArray('foo', array(1, -2));

		$this->assertSame(
			array(1, -2),
			$this->subject->getAsIntegerArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayReturnsEmptyArraySetViaSetAsArray() {
		$this->subject->setAsArray('foo', array());

		$this->assertSame(
			array(),
			$this->subject->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayReturnsEmptyArraySetViaSetAsArray() {
		$this->subject->setAsArray('foo', array());

		$this->assertSame(
			array(),
			$this->subject->getAsIntegerArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayReturnsTrimmedValues() {
		$this->subject->setAsArray('foo', array(' foo '));

		$this->assertSame(
			array('foo'),
			$this->subject->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayReturnsIntvaledValues() {
		$this->subject->setAsArray('foo', array('asdf'));

		$this->assertSame(
			array(0),
			$this->subject->getAsIntegerArray('foo')
		);
	}


	////////////////////////////////////////////
	// Tests for setAsBoolean and getAsBoolean
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAsBooleanWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->getAsBoolean('');
	}

	/**
	 * @test
	 */
	public function setAsBooleanWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->setAsBoolean('', FALSE);
	}

	/**
	 * @test
	 */
	public function getAsBooleanWithInexistentKeyReturnsFalse() {
		$this->assertSame(
			FALSE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', TRUE);

		$this->assertSame(
			TRUE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsFalseSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', FALSE);

		$this->assertSame(
			FALSE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueForNonEmptyStringSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', 'bar');

		$this->assertSame(
			TRUE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsFalseForEmptyStringSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', '');

		$this->assertSame(
			FALSE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsOneForTrueSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', TRUE);

		$this->assertSame(
			1,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsZeroForFalseSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', FALSE);

		$this->assertSame(
			0,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueForPositiveIntegerSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 42);

		$this->assertSame(
			TRUE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueForNegativeIntegerSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', -42);

		$this->assertSame(
			TRUE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsFalseForZeroSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 0);

		$this->assertSame(
			FALSE,
			$this->subject->getAsBoolean('foo')
		);
	}


	////////////////////////////////////////
	// Tests for setAsFloat and getAsFloat
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAsFloatWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->getAsFloat('');
	}

	/**
	 * @test
	 */
	public function setAsFloatWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->setAsFloat('', 42.5);
	}

	/**
	 * @test
	 */
	public function getAsFloatWithInexistentKeyReturnsZero() {
		$this->assertSame(
			0.0,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatCanReturnPositiveFloatFromFloat() {
		$this->subject->setData(array('foo' => 42.5));

		$this->assertSame(
			42.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsPositiveFloatSetViaSetAsFloat() {
		$this->subject->setAsFloat('foo', 42.5);

		$this->assertSame(
			42.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsPositiveFloatSetAsStringViaSetAsFloat() {
		$this->subject->setAsFloat('foo', '42.5');

		$this->assertSame(
			42.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsNegativeFloatSetViaSetAsFloat() {
		$this->subject->setAsFloat('foo', -42.5);

		$this->assertSame(
			-42.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsZeroSetViaSetAsFloat() {
		$this->subject->setAsFloat('foo', 0.5);

		$this->assertSame(
			0.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsZeroForStringSetViaSetAsFloat() {
		$this->subject->setAsFloat('foo', 'bar');

		$this->assertSame(
			0.0,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatCanReturnPositiveFloatFromString() {
		$this->subject->setData(array('foo' => '42.5'));

		$this->assertSame(
			42.5,
			$this->subject->getAsFloat('foo')
		);
	}



	/////////////////////////////////////////////////
	// Tests for hasString, hasInteger and hasFloat
	/////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasStringForNonEmptyStringReturnsTrue() {
		$this->subject->setAsString('foo', 'bar');

		$this->assertTrue(
			$this->subject->hasString('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasStringForEmptyStringReturnsFalse() {
		$this->subject->setAsString('foo', '');

		$this->assertFalse(
			$this->subject->hasString('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForPositiveIntegerReturnsTrue() {
		$this->subject->setAsInteger('foo', 42);

		$this->assertTrue(
			$this->subject->hasInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForNegativeIntegerReturnsTrue() {
		$this->subject->setAsInteger('foo', -42);

		$this->assertTrue(
			$this->subject->hasInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForZeroReturnsFalse() {
		$this->subject->setAsInteger('foo', 0);

		$this->assertFalse(
			$this->subject->hasInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasFloatForPositiveFloatReturnsTrue() {
		$this->subject->setAsFloat('foo', 42.00);

		$this->assertTrue(
			$this->subject->hasFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasFloatForNegativeFloatReturnsTrue() {
		$this->subject->setAsFloat('foo', -42.00);

		$this->assertTrue(
			$this->subject->hasFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasFloatForZeroReturnsFalse() {
		$this->subject->setAsFloat('foo', 0.00);

		$this->assertFalse(
			$this->subject->hasFloat('foo')
		);
	}
}