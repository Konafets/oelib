<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_Configuration class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Configuration_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_tests_fixtures_TestingConfiguration the model to test
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
			'Exception', '$key must not be empty.'
		);


		$this->fixture->set('', 'foo');
	}

	public function testSetDataWithEmptyArrayIsAllowed() {
		$this->fixture->setData(array());
	}

	public function testGetAfterSetReturnsTheSetValue() {
		$this->fixture->set('foo', 'bar');

		$this->assertEquals(
			'bar',
			$this->fixture->getAsString('foo')
		);
	}

	public function testGetAfterSetDataReturnsTheSetValue() {
		$this->fixture->setData(
			array('foo' => 'bar')
		);

		$this->assertEquals(
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

		$this->assertEquals(
			array('first', 'second'),
			$this->fixture->getArrayKeys()
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForInexistentKeyReturnEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForKeyOfStringDataItemReturnsEmptyArray() {
		$this->fixture->setData(array('key' => 'blub'));

		$this->assertEquals(
			array(),
			$this->fixture->getArrayKeys('key')
		);
	}

	/**
	 * @test
	 */
	public function getArrayKeysForKeyOfDataItemWithOneArrayElementReturnsKeyOfArrayElement() {
		$this->fixture->setData(array('key' => array('test' => 'child')));

		$this->assertEquals(
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

		$this->assertEquals(
			array('first', 'second'),
			$this->fixture->getArrayKeys('key')
		);
	}
}
?>