<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_FakeSession class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_FakeSession_testcase  extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_FakeSession the object to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_FakeSession();
	}

	public function tearDown() {
		unset($this->fixture);
	}


	/////////////////////////////////////////////////////////
	// Tests for the basic functions
	/////////////////////////////////////////////////////////

	public function testFakeSessionCanBeInstantiatedDirectly() {
		new tx_oelib_FakeSession();
	}


	////////////////////////////////////////
	// Tests that the setters/getters work
	////////////////////////////////////////

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

	public function testGetAsStringReturnsEmptyStringSetViaSetAsString() {
		$this->fixture->setAsString('foo', '');

		$this->assertEquals(
			'',
			$this->fixture->getAsString('foo')
		);
	}
}
?>