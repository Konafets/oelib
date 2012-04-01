<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'tests/fixtures/class.tx_oelib_testingObject.php');

/**
 * Testcase for the tx_oelib_Object class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Object_testcase extends tx_phpunit_testcase {
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

	public function testCheckForNonEmptyKeyWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->checkForNonEmptyKey('');
	}

	public function testCheckForNonEmptyKeyWithNonEmptyKeyIsAllowed() {
		$this->fixture->checkForNonEmptyKey('foo');
	}


	//////////////////////////////////////////
	// Tests for setAsString and getAsString
	//////////////////////////////////////////

	public function testGetAsStringWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->getAsString('');
	}

	public function testSetAsStringWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsString('', 'bar');
	}

	public function testGetAsStringWithInexistentKeyReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getAsString('foo')
		);
	}

	public function testGetAsStringReturnsNonEmptyStringSetViaSetAsString() {
		$this->fixture->setAsString('foo', 'bar');

		$this->assertEquals(
			'bar',
			$this->fixture->getAsString('foo')
		);
	}

	public function testGetAsStringReturnsTrimmedValue() {
		$this->fixture->setAsString('foo', ' bar ');

		$this->assertEquals(
			'bar',
			$this->fixture->getAsString('foo')
		);
	}

	public function testGetAsStringReturnsEmptyStringSetViaSetAsString() {
		$this->fixture->setAsString('foo', '');

		$this->assertEquals(
			'',
			$this->fixture->getAsString('foo')
		);
	}


	////////////////////////////////////////////
	// Tests for setAsInteger and getAsInteger
	////////////////////////////////////////////

	public function testGetAsIntegerWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->getAsInteger('');
	}

	public function testSetAsIntegerWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsInteger('', 42);
	}

	public function testGetAsIntegerWithInexistentKeyReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getAsInteger('foo')
		);
	}

	public function testGetAsIntegerReturnsPositiveIntegerSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 42);

		$this->assertEquals(
			42,
			$this->fixture->getAsInteger('foo')
		);
	}

	public function testGetAsIntegerReturnsNegativeIntegerSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', -42);

		$this->assertEquals(
			-42,
			$this->fixture->getAsInteger('foo')
		);
	}

	public function testGetAsIntegerReturnsZeroSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 0);

		$this->assertEquals(
			0,
			$this->fixture->getAsInteger('foo')
		);
	}

	public function testGetAsIntegerReturnsZeroForStringSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 'bar');

		$this->assertEquals(
			0,
			$this->fixture->getAsInteger('foo')
		);
	}

	public function testGetAsIntegerReturnsRoundedValueForFloatSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 12.34);

		$this->assertEquals(
			12,
			$this->fixture->getAsInteger('foo')
		);
	}


	//////////////////////////////////////////////////////////////////
	// Tests for setAsArray, getAsTrimmedArray and getAsIntegerArray
	//////////////////////////////////////////////////////////////////

	public function testGetAsTrimmedArrayWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->getAsTrimmedArray('');
	}

	public function testGetAsIntegerArrayWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->getAsIntegerArray('');
	}

	public function testSetAsArrayWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsArray('', array('bar'));
	}

	public function testGetAsTrimmedArrayWithInexistentKeyReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getAsTrimmedArray('foo')
		);
	}

	public function testGetAsIntegerArrayWithInexistentKeyReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getAsIntegerArray('foo')
		);
	}

	public function testGetAsTrimmedArrayReturnsNonEmptyArraySetViaSetAsArray() {
		$this->fixture->setAsArray('foo', array('foo', 'bar'));

		$this->assertEquals(
			array('foo', 'bar'),
			$this->fixture->getAsTrimmedArray('foo')
		);
	}

	public function testGetAsIntegerArrayReturnsNonEmptyArraySetViaSetAsArray() {
		$this->fixture->setAsArray('foo', array(1, -2));

		$this->assertEquals(
			array(1, -2),
			$this->fixture->getAsIntegerArray('foo')
		);
	}

	public function testGetAsTrimmedArrayReturnsEmptyArraySetViaSetAsArray() {
		$this->fixture->setAsArray('foo', array());

		$this->assertEquals(
			array(),
			$this->fixture->getAsTrimmedArray('foo')
		);
	}

	public function testGetAsIntegerArrayReturnsEmptyArraySetViaSetAsArray() {
		$this->fixture->setAsArray('foo', array());

		$this->assertEquals(
			array(),
			$this->fixture->getAsIntegerArray('foo')
		);
	}

	public function testGetAsTrimmedArrayReturnsTrimmedValues() {
		$this->fixture->setAsArray('foo', array(' foo '));

		$this->assertEquals(
			array('foo'),
			$this->fixture->getAsTrimmedArray('foo')
		);
	}

	public function testGetAsIntegerArrayReturnsIntvaledValues() {
		$this->fixture->setAsArray('foo', array('asdf'));

		$this->assertEquals(
			array(0),
			$this->fixture->getAsIntegerArray('foo')
		);
	}


	////////////////////////////////////////////
	// Tests for setAsBoolean and getAsBoolean
	////////////////////////////////////////////

	public function testGetAsBooleanWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->getAsBoolean('');
	}

	public function testSetAsBooleanWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsBoolean('', FALSE);
	}

	public function testGetAsBooleanWithInexistentKeyReturnsFalse() {
		$this->assertEquals(
			FALSE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	public function testGetAsBooleanReturnsTrueSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', TRUE);

		$this->assertEquals(
			TRUE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	public function testGetAsBooleanReturnsFalseSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', FALSE);

		$this->assertEquals(
			FALSE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	public function testGetAsBooleanReturnsTrueForNonEmptyStringSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', 'bar');

		$this->assertEquals(
			TRUE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	public function testGetAsBooleanReturnsFalseForEmptyStringSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', '');

		$this->assertEquals(
			FALSE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	public function testGetAsIntegerReturnsOneForTrueSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', TRUE);

		$this->assertEquals(
			1,
			$this->fixture->getAsInteger('foo')
		);
	}

	public function testGetAsIntegerReturnsZeroForFalseSetViaSetAsBoolean() {
		$this->fixture->setAsBoolean('foo', FALSE);

		$this->assertEquals(
			0,
			$this->fixture->getAsInteger('foo')
		);
	}

	public function testGetAsBooleanReturnsTrueForPositiveIntegerSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 42);

		$this->assertEquals(
			TRUE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	public function testGetAsBooleanReturnsTrueForNegativeIntegerSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', -42);

		$this->assertEquals(
			TRUE,
			$this->fixture->getAsBoolean('foo')
		);
	}

	public function testGetAsBooleanReturnsFalseForZeroSetViaSetAsInteger() {
		$this->fixture->setAsInteger('foo', 0);

		$this->assertEquals(
			FALSE,
			$this->fixture->getAsBoolean('foo')
		);
	}


	////////////////////////////////////////
	// Tests for setAsFloat and getAsFloat
	////////////////////////////////////////

	public function testGetAsFloatWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->getAsFloat('');
	}

	public function testSetAsFloatWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setAsFloat('', 42.5);
	}

	public function testGetAsFloatWithInexistentKeyReturnsZero() {
		$this->assertEquals(
			0.0,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatCanReturnPositiveFloatFromFloat() {
		$this->fixture->setData(array('foo' => 42.5));

		$this->assertEquals(
			42.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsPositiveFloatSetViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', 42.5);

		$this->assertEquals(
			42.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsPositiveFloatSetAsStringViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', '42.5');

		$this->assertEquals(
			42.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	public function testGetAsFloatReturnsNegativeFloatSetViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', -42.5);

		$this->assertEquals(
			-42.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	public function testGetAsFloatReturnsZeroSetViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', 0.5);

		$this->assertEquals(
			0.5,
			$this->fixture->getAsFloat('foo')
		);
	}

	public function testGetAsFloatReturnsZeroForStringSetViaSetAsFloat() {
		$this->fixture->setAsFloat('foo', 'bar');

		$this->assertEquals(
			0.0,
			$this->fixture->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatCanReturnPositiveFloatFromString() {
		$this->fixture->setData(array('foo' => '42.5'));

		$this->assertEquals(
			42.5,
			$this->fixture->getAsFloat('foo')
		);
	}



	/////////////////////////////////////////////////
	// Tests for hasString, hasInteger and hasFloat
	/////////////////////////////////////////////////

	public function testHasStringForNonEmptyStringReturnsTrue() {
		$this->fixture->setAsString('foo', 'bar');

		$this->assertTrue(
			$this->fixture->hasString('foo')
		);
	}

	public function testHasStringForEmptyStringReturnsFalse() {
		$this->fixture->setAsString('foo', '');

		$this->assertFalse(
			$this->fixture->hasString('foo')
		);
	}

	public function testHasIntegerForPositiveIntegerReturnsTrue() {
		$this->fixture->setAsInteger('foo', 42);

		$this->assertTrue(
			$this->fixture->hasInteger('foo')
		);
	}

	public function testHasIntegerForNegativeIntegerReturnsTrue() {
		$this->fixture->setAsInteger('foo', -42);

		$this->assertTrue(
			$this->fixture->hasInteger('foo')
		);
	}

	public function testHasIntegerForZeroReturnsFalse() {
		$this->fixture->setAsInteger('foo', 0);

		$this->assertFalse(
			$this->fixture->hasInteger('foo')
		);
	}

	public function testHasFloatForPositiveFloatReturnsTrue() {
		$this->fixture->setAsFloat('foo', 42.00);

		$this->assertTrue(
			$this->fixture->hasFloat('foo')
		);
	}

	public function testHasFloatForNegativeFloatReturnsTrue() {
		$this->fixture->setAsFloat('foo', -42.00);

		$this->assertTrue(
			$this->fixture->hasFloat('foo')
		);
	}

	public function testHasFloatForZeroReturnsFalse() {
		$this->fixture->setAsFloat('foo', 0.00);

		$this->assertFalse(
			$this->fixture->hasFloat('foo')
		);
	}
}
?>