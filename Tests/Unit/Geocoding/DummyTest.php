<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
class Tx_Oelib_Geocoding_DummyTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Geocoding_Dummy
	 */
	private $subject;

	public function setUp() {
		$this->subject = new tx_oelib_Geocoding_Dummy();
	}

	public function tearDown() {
		unset($this->subject);
	}


	/////////////////////
	// Tests for lookUp
	/////////////////////

	/**
	 * @test
	 */
	public function lookUpForEmptyAddressSetsCoordinatesError() {
		$geo = $this->getMock(
			'Tx_Oelib_Tests_Unit_Fixtures_TestingGeo',
			array('setGeoError')
		);
		$geo->expects($this->once())->method('setGeoError');

		$this->subject->lookUp($geo);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressSetsCoordinatesFromSetCoordinates() {
		$coordinates = array('latitude' => 50.7335500, 'longitude' => 7.1014300);
		$this->subject->setCoordinates(
			$coordinates['latitude'], $coordinates['longitude']
		);

		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->subject->lookUp($geo);

		$this->assertSame(
			$coordinates,
			$geo->getGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithoutSetCoordinatesSetsNoCoordinates() {
		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->subject->lookUp($geo);

		$this->assertFalse(
			$geo->hasGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithoutSetCoordinatesNotClearsExistingCoordinates() {
		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->subject->lookUp($geo);

		$this->assertFalse(
			$geo->hasGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithoutSetCoordinatesSetsGeoError() {
		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->subject->lookUp($geo);

		$this->assertTrue(
			$geo->hasGeoError()
		);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithCoordinatesNotOverwritesCoordinates() {
		$this->subject->setCoordinates(42.0, 42.0);

		$coordinates = array('latitude' => 50.7335500, 'longitude' => 7.1014300);
		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
		$geo->setGeoCoordinates($coordinates);

		$this->subject->lookUp($geo);

		$this->assertSame(
			$coordinates,
			$geo->getGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function lookUpAfterClearCoordinatesSetsNoCoordinates() {
		$this->subject->setCoordinates(42.0, 42.0);
		$this->subject->clearCoordinates();

		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->subject->lookUp($geo);

		$this->assertFalse(
			$geo->hasGeoCoordinates()
		);
	}
}