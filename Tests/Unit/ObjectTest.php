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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_ObjectTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Tests_Unit_Fixtures_TestingObject
	 */
	private $subject;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Tests_Unit_Fixtures_TestingObject();
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
		self::assertSame(
			'',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsNonEmptyStringSetViaSetAsString() {
		$this->subject->setAsString('foo', 'bar');

		self::assertSame(
			'bar',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsTrimmedValue() {
		$this->subject->setAsString('foo', ' bar ');

		self::assertSame(
			'bar',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsEmptyStringSetViaSetAsString() {
		$this->subject->setAsString('foo', '');

		self::assertSame(
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
		self::assertSame(
			0,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsPositiveIntegerSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 42);

		self::assertSame(
			42,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsNegativeIntegerSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', -42);

		self::assertSame(
			-42,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsZeroSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 0);

		self::assertSame(
			0,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsZeroForStringSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 'bar');

		self::assertSame(
			0,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsRoundedValueForFloatSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 12.34);

		self::assertSame(
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
		self::assertSame(
			array(),
			$this->subject->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayWithInexistentKeyReturnsEmptyArray() {
		self::assertSame(
			array(),
			$this->subject->getAsIntegerArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayReturnsNonEmptyArraySetViaSetAsArray() {
		$this->subject->setAsArray('foo', array('foo', 'bar'));

		self::assertSame(
			array('foo', 'bar'),
			$this->subject->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayReturnsNonEmptyArraySetViaSetAsArray() {
		$this->subject->setAsArray('foo', array(1, -2));

		self::assertSame(
			array(1, -2),
			$this->subject->getAsIntegerArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayReturnsEmptyArraySetViaSetAsArray() {
		$this->subject->setAsArray('foo', array());

		self::assertSame(
			array(),
			$this->subject->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayReturnsEmptyArraySetViaSetAsArray() {
		$this->subject->setAsArray('foo', array());

		self::assertSame(
			array(),
			$this->subject->getAsIntegerArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsTrimmedArrayReturnsTrimmedValues() {
		$this->subject->setAsArray('foo', array(' foo '));

		self::assertSame(
			array('foo'),
			$this->subject->getAsTrimmedArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerArrayReturnsIntvaledValues() {
		$this->subject->setAsArray('foo', array('asdf'));

		self::assertSame(
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
		self::assertSame(
			FALSE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', TRUE);

		self::assertSame(
			TRUE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsFalseSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', FALSE);

		self::assertSame(
			FALSE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueForNonEmptyStringSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', 'bar');

		self::assertSame(
			TRUE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsFalseForEmptyStringSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', '');

		self::assertSame(
			FALSE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsOneForTrueSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', TRUE);

		self::assertSame(
			1,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerReturnsZeroForFalseSetViaSetAsBoolean() {
		$this->subject->setAsBoolean('foo', FALSE);

		self::assertSame(
			0,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueForPositiveIntegerSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 42);

		self::assertSame(
			TRUE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsTrueForNegativeIntegerSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', -42);

		self::assertSame(
			TRUE,
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanReturnsFalseForZeroSetViaSetAsInteger() {
		$this->subject->setAsInteger('foo', 0);

		self::assertSame(
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
		self::assertSame(
			0.0,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatCanReturnPositiveFloatFromFloat() {
		$this->subject->setData(array('foo' => 42.5));

		self::assertSame(
			42.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsPositiveFloatSetViaSetAsFloat() {
		$this->subject->setAsFloat('foo', 42.5);

		self::assertSame(
			42.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsPositiveFloatSetAsStringViaSetAsFloat() {
		$this->subject->setAsFloat('foo', '42.5');

		self::assertSame(
			42.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsNegativeFloatSetViaSetAsFloat() {
		$this->subject->setAsFloat('foo', -42.5);

		self::assertSame(
			-42.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsZeroSetViaSetAsFloat() {
		$this->subject->setAsFloat('foo', 0.5);

		self::assertSame(
			0.5,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatReturnsZeroForStringSetViaSetAsFloat() {
		$this->subject->setAsFloat('foo', 'bar');

		self::assertSame(
			0.0,
			$this->subject->getAsFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsFloatCanReturnPositiveFloatFromString() {
		$this->subject->setData(array('foo' => '42.5'));

		self::assertSame(
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

		self::assertTrue(
			$this->subject->hasString('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasStringForEmptyStringReturnsFalse() {
		$this->subject->setAsString('foo', '');

		self::assertFalse(
			$this->subject->hasString('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForPositiveIntegerReturnsTrue() {
		$this->subject->setAsInteger('foo', 42);

		self::assertTrue(
			$this->subject->hasInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForNegativeIntegerReturnsTrue() {
		$this->subject->setAsInteger('foo', -42);

		self::assertTrue(
			$this->subject->hasInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForZeroReturnsFalse() {
		$this->subject->setAsInteger('foo', 0);

		self::assertFalse(
			$this->subject->hasInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasFloatForPositiveFloatReturnsTrue() {
		$this->subject->setAsFloat('foo', 42.00);

		self::assertTrue(
			$this->subject->hasFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasFloatForNegativeFloatReturnsTrue() {
		$this->subject->setAsFloat('foo', -42.00);

		self::assertTrue(
			$this->subject->hasFloat('foo')
		);
	}

	/**
	 * @test
	 */
	public function hasFloatForZeroReturnsFalse() {
		$this->subject->setAsFloat('foo', 0.00);

		self::assertFalse(
			$this->subject->hasFloat('foo')
		);
	}
}