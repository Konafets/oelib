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
 * Unit tests for the tx_oelib_Geocoding_Dummy class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Geocoding_DummyTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Geocoding_Dummy
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Geocoding_Dummy();
	}

	public function tearDown() {
		unset($this->fixture);
	}


	/////////////////////
	// Tests for lookUp
	/////////////////////

	/**
	 * @test
	 */
	public function lookUpForEmptyAddressSetsCoordinatesError() {
		$geo = $this->getMock(
			'tx_oelib_tests_fixtures_TestingGeo',
			array('setGeoError')
		);
		$geo->expects($this->once())->method('setGeoError');

		$this->fixture->lookUp($geo);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressSetsCoordinatesFromSetCoordinates() {
		$coordinates = array('latitude' => 50.7335500, 'longitude' => 7.1014300);
		$this->fixture->setCoordinates(
			$coordinates['latitude'], $coordinates['longitude']
		);

		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->fixture->lookUp($geo);

		$this->assertSame(
			$coordinates,
			$geo->getGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithoutSetCoordinatesSetsNoCoordinates() {
		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->fixture->lookUp($geo);

		$this->assertFalse(
			$geo->hasGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithoutSetCoordinatesNotClearsExistingCoordinates() {
		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->fixture->lookUp($geo);

		$this->assertFalse(
			$geo->hasGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithoutSetCoordinatesSetsGeoError() {
		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->fixture->lookUp($geo);

		$this->assertTrue(
			$geo->hasGeoError()
		);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithCoordinatesNotOverwritesCoordinates() {
		$this->fixture->setCoordinates(42.0, 42.0);

		$coordinates = array('latitude' => 50.7335500, 'longitude' => 7.1014300);
		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
		$geo->setGeoCoordinates($coordinates);

		$this->fixture->lookUp($geo);

		$this->assertSame(
			$coordinates,
			$geo->getGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function lookUpAfterClearCoordinatesSetsNoCoordinates() {
		$this->fixture->setCoordinates(42.0, 42.0);
		$this->fixture->clearCoordinates();

		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->fixture->lookUp($geo);

		$this->assertFalse(
			$geo->hasGeoCoordinates()
		);
	}
}
?>