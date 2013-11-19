<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
class Tx_Oelib_Geocoding_CalculatorTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Geocoding_Calculator
	 */
	private $subject;

	public function setUp() {
		$this->subject = new tx_oelib_Geocoding_Calculator();
	}

	public function tearDown() {
		unset($this->subject);
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
		$noCoordinates = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$noCoordinates->clearGeoCoordinates();
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);

		$this->subject->calculateDistanceInKilometers($noCoordinates, $bonn);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function calculateDistanceInKilometersForSecondObjectWithoutCoordinatesThrowsException() {
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$noCoordinates = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$noCoordinates->clearGeoCoordinates();

		$this->subject->calculateDistanceInKilometers($bonn, $noCoordinates);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function calculateDistanceInKilometersForFirstObjectWithGeoErrorThrowsException() {
		$brokenBonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$brokenBonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$brokenBonn->setGeoError();
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);

		$this->subject->calculateDistanceInKilometers($brokenBonn, $bonn);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function calculateDistanceInKilometersForSecondObjectWithGeoErrorThrowsException() {
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$brokenBonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$brokenBonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$brokenBonn->setGeoError();

		$this->subject->calculateDistanceInKilometers($bonn, $brokenBonn);
	}

	/**
	 * @test
	 */
	public function calculateDistanceInKilometersForSameObjectsReturnsZero() {
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);

		$this->assertSame(
			0.0,
			$this->subject->calculateDistanceInKilometers($bonn, $bonn)
		);
	}

	/**
	 * @test
	 */
	public function calculateDistanceInKilometersForBonnAndCologneReturnsActualDistance() {
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$this->assertEquals(
			26.0,
			$this->subject->calculateDistanceInKilometers($bonn, $cologne),
			'',
			2.0
		);
	}

	/**
	 * @test
	 */
	public function calculateDistanceInKilometersReturnsSameDistanceForSwappedArguments() {
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$this->assertSame(
			$this->subject->calculateDistanceInKilometers($bonn, $cologne),
			$this->subject->calculateDistanceInKilometers($cologne, $bonn)
		);
	}


	//////////////////////////////////////
	// Tests concerning filterByDistance
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function filterByDistanceKeepsElementWithinDistance() {
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$list = new Tx_Oelib_List();
		$list->add($bonn);

		$filteredList = $this->subject->filterByDistance(
			$list, $cologne, 27.0
		);

		$this->assertSame(
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
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$list = new Tx_Oelib_List();
		$list->add($bonn);

		$filteredList = $this->subject->filterByDistance(
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
		$bonn = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$bonn->setGeoCoordinates(
			array('latitude' => 50.72254683, 'longitude' => 7.07519531)
		);
		$cologne = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$cologne->setGeoCoordinates(
			array('latitude' => 50.94458443, 'longitude' => 6.9543457)
		);

		$list = new Tx_Oelib_List();
		$list->add($bonn);
		$list->add($cologne);

		$filteredList = $this->subject->filterByDistance(
			$list, $cologne, 27.0
		);

		$this->assertSame(
			2,
			$filteredList->count()
		);
	}

}
?>