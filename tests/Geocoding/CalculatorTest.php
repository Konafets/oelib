<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2011 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_Geocoding_Calculator class in the "oelib"
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Geocoding_CalculatorTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Geocoding_Calculator
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Geocoding_Calculator();
	}

	public function tearDown() {
		unset($this->fixture);
	}


	///////////////////////////////////////////////////
	// Tests concerning calculateDistanceInKilometers
	///////////////////////////////////////////////////

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function calculateDistanceInKilometersForFirstObjectWithoutCoordinatesThrowsException() {
		$noCoordinates = new tx_oelib_tests_fixtures_TestingGeo();
		$noCoordinates->clearGeoCoordinates();
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);

		$this->fixture->calculateDistanceInKilometers($noCoordinates, $bonn);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function calculateDistanceInKilometersForSecondObjectWithoutCoordinatesThrowsException() {
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$noCoordinates = new tx_oelib_tests_fixtures_TestingGeo();
		$noCoordinates->clearGeoCoordinates();

		$this->fixture->calculateDistanceInKilometers($bonn, $noCoordinates);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function calculateDistanceInKilometersForFirstObjectWithGeoErrorThrowsException() {
		$brokenBonn = new tx_oelib_tests_fixtures_TestingGeo();
		$brokenBonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$brokenBonn->setGeoError();
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);

		$this->fixture->calculateDistanceInKilometers($brokenBonn, $bonn);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function calculateDistanceInKilometersForSecondObjectWithGeoErrorThrowsException() {
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$brokenBonn = new tx_oelib_tests_fixtures_TestingGeo();
		$brokenBonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$brokenBonn->setGeoError();

		$this->fixture->calculateDistanceInKilometers($bonn, $brokenBonn);
	}

	/**
	 * @test
	 */
	public function calculateDistanceInKilometersForSameObjectsReturnsZero() {
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);

		$this->assertEquals(
			0.0,
			$this->fixture->calculateDistanceInKilometers($bonn, $bonn)
		);
	}

	/**
	 * @test
	 */
	public function calculateDistanceInKilometersForBonnAndCologneReturnsActualDistance() {
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new tx_oelib_tests_fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$this->assertEquals(
			26.0,
			$this->fixture->calculateDistanceInKilometers($bonn, $cologne),
			'',
			2.0
		);
	}

	/**
	 * @test
	 */
	public function calculateDistanceInKilometersReturnsSameDistanceForSwappedArguments() {
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new tx_oelib_tests_fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$this->assertEquals(
			$this->fixture->calculateDistanceInKilometers($bonn, $cologne),
			$this->fixture->calculateDistanceInKilometers($cologne, $bonn)
		);
	}


	//////////////////////////////////////
	// Tests concerning filterByDistance
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function filterByDistanceKeepsElementWithinDistance() {
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new tx_oelib_tests_fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$list = new tx_oelib_List();
		$list->add($bonn);

		$filteredList = $this->fixture->filterByDistance(
			$list, $cologne, 27.0
		);

		$this->assertEquals(
			1,
			$filteredList->count()
		);
		$this->assertSame(
			$bonn,
			$filteredList->first()
		);
	}

	/**
	 * @test
	 */
	public function filterByDistanceDropsElementOutOfDistance() {
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new tx_oelib_tests_fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$list = new tx_oelib_List();
		$list->add($bonn);

		$filteredList = $this->fixture->filterByDistance(
			$list, $cologne, 25.0
		);

		$this->assertTrue(
			$filteredList->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function filterByDistanceCanReturnTwoElements() {
		$bonn = new tx_oelib_tests_fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new tx_oelib_tests_fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$list = new tx_oelib_List();
		$list->add($bonn);
		$list->add($cologne);

		$filteredList = $this->fixture->filterByDistance(
			$list, $cologne, 27.0
		);

		$this->assertEquals(
			2,
			$filteredList->count()
		);
	}

}
?>