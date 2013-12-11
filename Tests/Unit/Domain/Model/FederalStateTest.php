<?php
/***************************************************************
* Copyright notice
*
* (c) 2012-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Testcase for the Tx_Oelib_Domain_Model_FederalState class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Domain_Model_FederalStateTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Oelib_Domain_Model_FederalState
	 */
	private $subject = NULL;

	public function setUp() {
		$this->subject = new Tx_Oelib_Domain_Model_FederalState();
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getLocalNameInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getLocalName()
		);
	}

	/**
	 * @test
	 */
	public function setLocalNameSetsLocalShortName() {
		$this->subject->setLocalName('Nordrhein-Westfalen');

		$this->assertSame(
			'Nordrhein-Westfalen',
			$this->subject->getLocalName()
		);
	}

	/**
	 * @test
	 */
	public function getEnglishNameInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getEnglishName()
		);
	}

	/**
	 * @test
	 */
	public function setEnglishNameSetsEnglishShortName() {
		$this->subject->setEnglishName('North Rhine-Westphalia');

		$this->assertSame(
			'North Rhine-Westphalia',
			$this->subject->getEnglishName()
		);
	}

	/**
	 * @test
	 */
	public function getIsoCountryCodeInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getIsoCountryCode()
		);
	}

	/**
	 * @test
	 */
	public function setIsoCountryCodeSetsIsoAlphaTwoCode() {
		$this->subject->setIsoCountryCode('DE');

		$this->assertSame(
			'DE',
			$this->subject->getIsoCountryCode()
		);
	}

	/**
	 * @test
	 */
	public function getIsoZoneCodeInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getIsoZoneCode()
		);
	}

	/**
	 * @test
	 */
	public function setIsoZoneCodeSetsIsoAlphaTwoCode() {
		$this->subject->setIsoZoneCode('NW');

		$this->assertSame(
			'NW',
			$this->subject->getIsoZoneCode()
		);
	}
}