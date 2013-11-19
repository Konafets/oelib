<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
class Tx_Oelib_ConfigurationTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Configuration the model to test
	 */
	private $subject;

	public function setUp() {
		$this->subject = new Tx_Oelib_Configuration();
	}

	public function tearDown() {
		$this->subject->__destruct();
		unset($this->subject);
	}


	//////////////////////////////////////
	// Tests for the basic functionality
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function setWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);


		$this->subject->set('', 'foo');
	}

	/**
	 * @test
	 */
	public function setDataWithEmptyArrayIsAllowed() {
		$this->subject->setData(array());
	}

	/**
	 * @test
	 */
	public function getAfterSetReturnsTheSetValue() {
		$this->subject->set('foo', 'bar');

		$this->assertSame(
			'bar',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAfterSetDataReturnsTheSetValue() {
		$this->subject->setData(
			array('foo' => 'bar')
		);

		$this->assertSame(
			'bar',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function setDataCalledTwoTimesDoesNotFail() {
		$this->subject->setData(
			array('title' => 'bar')
		);
		$this->subject->setData(
			array('title' => 'bar')
		);
	}


	////////////////////////////////////
	// Tests regarding getArrayKeys().
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getArrayKeysWithEmptyKeyReturnsKeysOfDataArray() {
		$this->subject->setData(array('first' => 'test', 'second' => 'test'));

		$this->assertSame(
			array('first', 'second'),
			$this->subject->getArrayKeys()
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForInexistentKeyReturnEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForKeyOfStringDataItemReturnsEmptyArray() {
		$this->subject->setData(array('key' => 'blub'));

		$this->assertSame(
			array(),
			$this->subject->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForKeyOfDataItemWithOneArrayElementReturnsKeyOfArrayElement() {
		$this->subject->setData(array('key' => array('test' => 'child')));

		$this->assertSame(
			array('test'),
			$this->subject->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForKeyOfDataItemWithTwoArrayElementsReturnsKeysOfArrayElements() {
		$this->subject->setData(
			array('key' => array('first' => 'child', 'second' => 'child'))
		);

		$this->assertSame(
			array('first', 'second'),
			$this->subject->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayReturnsMultidimensionalArray() {
		$this->subject->setData(
			array('1' => array('1.1' => array('1.1.1' => 'child')))
		);

		$this->assertSame(
			array('1.1' => array('1.1.1' => 'child')),
			$this->subject->getAsMultidimensionalArray('1')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayForInexistentKeyReturnsEmptyArray() {
		$this->subject->setData(array());

		$this->assertSame(
			array(),
			$this->subject->getAsMultidimensionalArray('1')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayForStringReturnsEmptyArray() {
		$this->subject->setData(
			array('1' => 'child')
		);

		$this->assertSame(
			array(),
			$this->subject->getAsMultidimensionalArray('1')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayForIntegerReturnsEmptyArray() {
		$this->subject->setData(
			array('1' => 42)
		);

		$this->assertSame(
			array(),
			$this->subject->getAsMultidimensionalArray('1')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayForFloatReturnsEmptyArray() {
		$this->subject->setData(
			array('1' => 42.42)
		);

		$this->assertSame(
			array(),
			$this->subject->getAsMultidimensionalArray('1')
		);
	}
}
?>