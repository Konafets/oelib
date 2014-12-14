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
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Domain_Model_FederalState();
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