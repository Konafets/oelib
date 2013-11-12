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
class tx_oelib_ObjectTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingObject the object to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_testingObject();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
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

		$this->fixture->checkForNonEmptyKey('');
	}

	/**
	 * @test
	 */
	public function checkForNonEmptyKeyWithNonEmptyKeyIsAllowed() {
		$this->fixture->checkForNonEmptyKey('foo');
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

		$this->fixture->getAsString('');
	}

	/**
	 * @test
	 */
	public function setAsStringWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsString('', 'bar');
	}

	/**
	 * @test
	 */
	public function getAsStringWithInexistentKeyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsNonEmptyStringSetViaSetAsString() {
		$this->fixture->setAsString('foo', 'bar');

		$this->assertSame(
			'bar',
			$this->fixture->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsTrimmedValue() {
		$this->fixture->setAsString('foo', ' bar ');

		$this->assertSame(
			'bar',
			$this->fixture->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsEmptyStringSetViaSetAsString() {
		$this->fixture->setAsString('foo', '');

		$this->assertSame(
			'',
			$this->fixture->getAsString('foo')
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

		$this->fixture->getAsInteger('');
	}

	/**
	 * @test
	 */
	public function setAsIntegerWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsInteger('', 42);
	}

	/**
	 * @test
	 */
	public function getAsIntegerWithInexistentKeyReturnsZero() {
		$this->assertSame(
			0,
			$this->fixture->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsPositiveIntegerSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 42);

		$this->assertSame(
			42,
			$this->fixture->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsNegativeIntegerSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', -42);

		$this->assertSame(
			-42,
			$this->fixture->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsZeroSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 0);

		$this->assertSame(
			0,
			$this->fixture->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsZeroForStringSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 'bar');

		$this->assertSame(
			0,
			$this->fixture->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsRoundedValueForFloatSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 12.34);

		$this->assertSame(
			12,
			$this->fixture->getAsInteger('foo')
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

		$this->fixture->getAsTrimmedArray('');
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->getAsIntegerArray('');
	}

	/**
	 * @test
	 */
	public function setAsArrayWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsArray('', array('bar'));
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayWithInexistentKeyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayWithInexistentKeyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getAsIntegerArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayReturnsNonEmptyArraySetViaSetAsArray() {
		$this->fixture->setAsArray('foo', array('foo', 'bar'));

		$this->assertSame(
			array('foo', 'bar'),
			$this->fixture->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayReturnsNonEmptyArraySetViaSetAsArray() {
		$this->fixture->setAsArray('foo', array(1, -2));

		$this->assertSame(
			array(1, -2),
			$this->fixture->getAsIntegerArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayReturnsEmptyArraySetViaSetAsArray() {
		$this->fixture->setAsArray('foo', array());

		$this->assertSame(
			array(),
			$this->fixture->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayReturnsEmptyArraySetViaSetAsArray() {
		$this->fixture->setAsArray('foo', array());

		$this->assertSame(
			array(),
			$this->fixture->getAsIntegerArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayReturnsTrimmedValues() {
		$this->fixture->setAsArray('foo', array(' foo '));

		$this->assertSame(
			array('foo'),
			$this->fixture->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayReturnsIntvaledValues() {
		$this->fixture->setAsArray('foo', array('asdf'));

		$this->assertSame(
			array(0),
			$this->fixture->getAsIntegerArray('foo')
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

		$this->fixture->getAsBoolean('');
	}

	/**
	 * @test
	 */
	public function setAsBooleanWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsBoolean('', FALSE);
	}

	/**
	 * @test
	 */
	public function getAsBooleanWithInexistentKeyReturnsFalse() {
		$this->assertSame(
			FALSE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', TRUE);

		$this->assertSame(
			TRUE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsFalseSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', FALSE);

		$this->assertSame(
			FALSE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueForNonEmptyStringSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', 'bar');

		$this->assertSame(
			TRUE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsFalseForEmptyStringSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', '');

		$this->assertSame(
			FALSE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsOneForTrueSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', TRUE);

		$this->assertSame(
			1,
			$this->fixture->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsZeroForFalseSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', FALSE);

		$this->assertSame(
			0,
			$this->fixture->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueForPositiveIntegerSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 42);

		$this->assertSame(
			TRUE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueForNegativeIntegerSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', -42);

		$this->assertSame(
			TRUE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsFalseForZeroSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 0);

		$this->assertSame(
			FALSE,
			$this->fixture->getAsBoolean('foo')
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

		$this->fixture->getAsFloat('');
	}

	/**
	 * @test
	 */
	public function setAsFloatWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsFloat('', 42.5);
	}

	/**
	 * @test
	 */
	public function getAsFloatWithInexistentKeyReturnsZero() {
		$this->assertSame(
			0.0,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatCanReturnPositiveFloatFromFloat() {
		$this->fixture->setData(array('foo' => 42.5));

		$this->assertSame(
			42.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsPositiveFloatSetViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', 42.5);

		$this->assertSame(
			42.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsPositiveFloatSetAsStringViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', '42.5');

		$this->assertSame(
			42.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsNegativeFloatSetViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', -42.5);

		$this->assertSame(
			-42.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsZeroSetViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', 0.5);

		$this->assertSame(
			0.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsZeroForStringSetViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', 'bar');

		$this->assertSame(
			0.0,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatCanReturnPositiveFloatFromString() {
		$this->fixture->setData(array('foo' => '42.5'));

		$this->assertSame(
			42.5,
			$this->fixture->getAsFloat('foo')
		);
	}



	/////////////////////////////////////////////////
	// Tests for hasString, hasInteger and hasFloat
	/////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasStringForNonEmptyStringReturnsTrue() {
		$this->fixture->setAsString('foo', 'bar');

		$this->assertTrue(
			$this->fixture->hasString('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasStringForEmptyStringReturnsFalse() {
		$this->fixture->setAsString('foo', '');

		$this->assertFalse(
			$this->fixture->hasString('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForPositiveIntegerReturnsTrue() {
		$this->fixture->setAsInteger('foo', 42);

		$this->assertTrue(
			$this->fixture->hasInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForNegativeIntegerReturnsTrue() {
		$this->fixture->setAsInteger('foo', -42);

		$this->assertTrue(
			$this->fixture->hasInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForZeroReturnsFalse() {
		$this->fixture->setAsInteger('foo', 0);

		$this->assertFalse(
			$this->fixture->hasInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasFloatForPositiveFloatReturnsTrue() {
		$this->fixture->setAsFloat('foo', 42.00);

		$this->assertTrue(
			$this->fixture->hasFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasFloatForNegativeFloatReturnsTrue() {
		$this->fixture->setAsFloat('foo', -42.00);

		$this->assertTrue(
			$this->fixture->hasFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasFloatForZeroReturnsFalse() {
		$this->fixture->setAsFloat('foo', 0.00);

		$this->assertFalse(
			$this->fixture->hasFloat('foo')
		);
	}
}
?>