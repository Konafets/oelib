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
class Tx_Oelib_FakeSessionTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_FakeSession the object to test
	 */
	private $subject;

	public function setUp() {
		$this->subject = new Tx_Oelib_FakeSession();
	}

	public function tearDown() {
		unset($this->subject);
	}


	/////////////////////////////////////////////////////////
	// Tests for the basic functions
	/////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function fakeSessionCanBeInstantiatedDirectly() {
		new Tx_Oelib_FakeSession();
	}


	////////////////////////////////////////
	// Tests that the setters/getters work
	////////////////////////////////////////

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
	public function getAsStringReturnsEmptyStringSetViaSetAsString() {
		$this->subject->setAsString('foo', '');

		$this->assertSame(
			'',
			$this->subject->getAsString('foo')
		);
	}
}