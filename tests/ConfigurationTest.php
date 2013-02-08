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
class tx_oelib_ConfigurationTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Configuration the model to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Configuration();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	//////////////////////////////////////
	// Tests for the basic functionality
	//////////////////////////////////////

	public function testSetWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);


		$this->fixture->set('', 'foo');
	}

	public function testSetDataWithEmptyArrayIsAllowed() {
		$this->fixture->setData(array());
	}

	public function testGetAfterSetReturnsTheSetValue() {
		$this->fixture->set('foo', 'bar');

		$this->assertSame(
			'bar',
			$this->fixture->getAsString('foo')
		);
	}

	public function testGetAfterSetDataReturnsTheSetValue() {
		$this->fixture->setData(
			array('foo' => 'bar')
		);

		$this->assertSame(
			'bar',
			$this->fixture->getAsString('foo')
		);
	}

	public function testSetDataCalledTwoTimesDoesNotFail() {
		$this->fixture->setData(
			array('title' => 'bar')
		);
		$this->fixture->setData(
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
		$this->fixture->setData(array('first' => 'test', 'second' => 'test'));

		$this->assertSame(
			array('first', 'second'),
			$this->fixture->getArrayKeys()
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForInexistentKeyReturnEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForKeyOfStringDataItemReturnsEmptyArray() {
		$this->fixture->setData(array('key' => 'blub'));

		$this->assertSame(
			array(),
			$this->fixture->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForKeyOfDataItemWithOneArrayElementReturnsKeyOfArrayElement() {
		$this->fixture->setData(array('key' => array('test' => 'child')));

		$this->assertSame(
			array('test'),
			$this->fixture->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForKeyOfDataItemWithTwoArrayElementsReturnsKeysOfArrayElements() {
		$this->fixture->setData(
			array('key' => array('first' => 'child', 'second' => 'child'))
		);

		$this->assertSame(
			array('first', 'second'),
			$this->fixture->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayReturnsMultidimensionalArray() {
		$this->fixture->setData(
			array('1' => array('1.1' => array('1.1.1' => 'child')))
		);

		$this->assertSame(
			array('1.1' => array('1.1.1' => 'child')),
			$this->fixture->getAsMultidimensionalArray('1')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayForInexistentKeyReturnsEmptyArray() {
		$this->fixture->setData(array());

		$this->assertSame(
			array(),
			$this->fixture->getAsMultidimensionalArray('1')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayForStringReturnsEmptyArray() {
		$this->fixture->setData(
			array('1' => 'child')
		);

		$this->assertSame(
			array(),
			$this->fixture->getAsMultidimensionalArray('1')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayForIntegerReturnsEmptyArray() {
		$this->fixture->setData(
			array('1' => 42)
		);

		$this->assertSame(
			array(),
			$this->fixture->getAsMultidimensionalArray('1')
		);
	}

	/**
	 * @test
	 */
	public function getAsMultidimensionalArrayForFloatReturnsEmptyArray() {
		$this->fixture->setData(
			array('1' => 42.42)
		);

		$this->assertSame(
			array(),
			$this->fixture->getAsMultidimensionalArray('1')
		);
	}
}
?>