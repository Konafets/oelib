<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_oelib_FakeConfiguration class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_FakeConfiguration_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_FakeConfiguration the fake configuration to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_FakeConfiguration();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	//////////////////////////////////////////
	// Tests for setAsString and getAsString
	//////////////////////////////////////////

	public function testGetAsStringWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '$key must not be empty.'
		);

		$this->fixture->getAsString('');
	}

	public function testSetAsStringWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '$key must not be empty.'
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


	//////////////////////
	// Tests for setData
	//////////////////////

	public function testSetDataWithNonEmptyArraySetsData() {
		$this->fixture->setData(array('foo' => 'bar'));

		$this->assertEquals(
			'bar',
			$this->fixture->getAsString('foo')
		);
	}

	public function testSetDataWithEmptyArrayIsAllowed() {
		$this->fixture->setData(array());
	}

	public function testSetDataCanBeCalledTwoTimesInARow() {
		$this->fixture->setData(array('foo' => 'bar'));
		$this->fixture->setData(array('bar' => 'foo'));
	}
}
?>