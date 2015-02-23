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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Geocoding_CalculatorTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Geocoding_Calculator
	 */
	protected $subject = null;

	/**
	 * @var Tx_Oelib_Tests_Unit_Fixtures_TestingGeo
	 */
	protected $geoObject = null;

	protected function setUp() {
		$this->subject = new tx_oelib_Geocoding_Calculator();

		$this->geoObject = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$this->geoObject->setGeoCoordinates(array('latitude' => 50.733585499999997, 'longitude' => 7.1012733999999993));
	}

	/**
	 * @test
	 */
	public function classIsSingleton() {
		$this->assertInstanceOf(
			't3lib_Singleton',
			$this->subject
		);
	}

	/*
	 * Tests concerning calculateDistanceInKilometers
	 */

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


	/*
	 * Tests concerning filterByDistance
	 */

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

	/**
	 * @test
	 */
	public function moveWithEastDirectionNotChangesLatitude() {
		$otherGeoObject = clone $this->geoObject;
		$distance = 100;
		$this->subject->move($otherGeoObject, 0, $distance);

		$originalCoordinates = $this->geoObject->getGeoCoordinates();
		$changedCoordinates = $otherGeoObject->getGeoCoordinates();

		$this->assertSame(
			$originalCoordinates['latitude'],
			$changedCoordinates['latitude']
		);
	}

	/**
	 * @test
	 */
	public function moveWithWestDirectionNotChangesLatitude() {
		$otherGeoObject = clone $this->geoObject;
		$distance = 100;
		$this->subject->move($otherGeoObject, 180, $distance);

		$originalCoordinates = $this->geoObject->getGeoCoordinates();
		$changedCoordinates = $otherGeoObject->getGeoCoordinates();

		$this->assertSame(
			$originalCoordinates['latitude'],
			$changedCoordinates['latitude']
		);
	}

	/**
	 * @test
	 */
	public function moveWithSouthDirectionNotChangesLongitude() {
		$otherGeoObject = clone $this->geoObject;
		$distance = 100;
		$this->subject->move($otherGeoObject, 270, $distance);

		$originalCoordinates = $this->geoObject->getGeoCoordinates();
		$changedCoordinates = $otherGeoObject->getGeoCoordinates();

		$this->assertSame(
			$originalCoordinates['longitude'],
			$changedCoordinates['longitude']
		);
	}

	/**
	 * @test
	 */
	public function moveWithNorthDirectionNotChangesLongitude() {
		$otherGeoObject = clone $this->geoObject;
		$distance = 100;
		$this->subject->move($otherGeoObject, 90, $distance);

		$originalCoordinates = $this->geoObject->getGeoCoordinates();
		$changedCoordinates = $otherGeoObject->getGeoCoordinates();

		$this->assertSame(
			$originalCoordinates['longitude'],
			$changedCoordinates['longitude']
		);
	}

	/**
	 * @return array[]
	 */
	public function directionDataProvider() {
		return array(
			'E' => array(0),
			'NE' => array(45),
			'N' => array(90),
			'NW' => array(135),
			'W' => array(180),
			'SW' => array(225),
			'S' => array(270),
			'SE' => array(315),
		);
	}

	/**
	 * @test
	 * @dataProvider directionDataProvider
	 *
	 * @param int
	 */
	public function moveMovesByGivenDistanceWithPositiveDistance($direction) {
		$distance = 100.0;
		$otherGeoObject = clone $this->geoObject;
		$this->subject->move($otherGeoObject, $direction, $distance);

		$this->assertEquals(
			$distance,
			$this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject),
			'The distance is not as expected.',
			$distance / 10
		);
	}

	/**
	 * @test
	 * @dataProvider directionDataProvider
	 *
	 * @param int
	 */
	public function moveMovesByGivenDistanceWithNegativeDistance($direction) {
		$distance = -100.0;
		$otherGeoObject = clone $this->geoObject;
		$this->subject->move($otherGeoObject, $direction, $distance);

		$this->assertEquals(
			abs($distance),
			$this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject),
			'The distance is not as expected.',
			abs($distance) / 10
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function moveByRandomDistanceWithNegativeNumberThrowsException() {
		$this->subject->moveByRandomDistance($this->geoObject, 0, -1);
	}

	/**
	 * @test
	 */
	public function moveByRandomDistanceWithZeroNotThrowsException() {
		$this->subject->moveByRandomDistance($this->geoObject, 0, 0);
	}

	/**
	 * @test
	 * @dataProvider directionDataProvider
	 *
	 * @param int
	 */
	public function moveByRandomDistanceChangesCoordinates($direction) {
		$originalCoordinates = $this->geoObject->getGeoCoordinates();

		$maximumDistance = 100.0;
		$this->subject->moveByRandomDistance($this->geoObject, $direction, $maximumDistance);

		$this->assertNotSame(
			$originalCoordinates,
			$this->geoObject->getGeoCoordinates()
		);
	}

	/**
	 * @test
	 * @dataProvider directionDataProvider
	 *
	 * @param int
	 */
	public function moveByRandomDistanceMovesAtMostByGivenDistanceWithPositiveDistance($direction) {
		$maximumDistance = 100.0;
		$otherGeoObject = clone $this->geoObject;
		$this->subject->moveByRandomDistance($otherGeoObject, $direction, $maximumDistance);

		$this->assertLessThanOrEqual(
			$maximumDistance,
			$this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject)
		);
	}

	/**
	 * @test
	 */
	public function moveByRandomDistanceCalledTwiceCreatesDifferentCoordinates() {
		$maximumDistance = 100.0;
		$this->subject->moveByRandomDistance($this->geoObject, 0, $maximumDistance);
		$firstCoordinates = $this->geoObject->getGeoCoordinates();

		$this->subject->moveByRandomDistance($this->geoObject, 0, $maximumDistance);

		$this->assertNotSame(
			$firstCoordinates,
			$this->geoObject->getGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function moveInRandomDirectionChangesCoordinates() {
		$originalCoordinates = $this->geoObject->getGeoCoordinates();

		$distance = 100.0;
		$this->subject->moveInRandomDirection($this->geoObject, $distance);

		$this->assertNotSame(
			$originalCoordinates,
			$this->geoObject->getGeoCoordinates()
		);
	}


	/**
	 * @test
	 */
	public function moveInRandomDirectionCalledTwiceCreatesDifferentCoordinates() {
		$distance = 100.0;
		$this->subject->moveInRandomDirection($this->geoObject, $distance);
		$firstCoordinates = $this->geoObject->getGeoCoordinates();

		$this->subject->moveInRandomDirection($this->geoObject, $distance);

		$this->assertNotSame(
			$firstCoordinates,
			$this->geoObject->getGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function moveInRandomDirectionMovesByGivenDistanceWithPositiveDistance() {
		$distance = 100.0;
		$otherGeoObject = clone $this->geoObject;
		$this->subject->moveInRandomDirection($otherGeoObject, $distance);

		$this->assertEquals(
			$distance,
			$this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject),
			'The distance is not as expected.',
			$distance / 10
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function moveInRandomDirectionAndDistanceWithNegativeNumberThrowsException() {
		$this->subject->moveInRandomDirectionAndDistance($this->geoObject, -1);
	}

	/**
	 * @test
	 */
	public function moveInRandomDirectionAndDistanceWithZeroNotThrowsException() {
		$this->subject->moveInRandomDirectionAndDistance($this->geoObject, 0);
	}

	/**
	 * @test
	 */
	public function moveInRandomDirectionAndDistanceChangesCoordinates() {
		$originalCoordinates = $this->geoObject->getGeoCoordinates();

		$maximumDistance = 100.0;
		$this->subject->moveInRandomDirectionAndDistance($this->geoObject, $maximumDistance);

		$this->assertNotSame(
			$originalCoordinates,
			$this->geoObject->getGeoCoordinates()
		);
	}


	/**
	 * @test
	 */
	public function moveInRandomDirectionAndDistanceCalledTwiceCreatesDifferentCoordinates() {
		$maximumDistance = 100.0;
		$this->subject->moveInRandomDirectionAndDistance($this->geoObject, $maximumDistance);
		$firstCoordinates = $this->geoObject->getGeoCoordinates();

		$this->subject->moveInRandomDirectionAndDistance($this->geoObject, $maximumDistance);

		$this->assertNotSame(
			$firstCoordinates,
			$this->geoObject->getGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function moveInRandomDirectionAndDistanceMovesAtMostByGivenDistanceWithPositiveDistance() {
		$maximumDistance = 100.0;
		$otherGeoObject = clone $this->geoObject;
		$this->subject->moveInRandomDirectionAndDistance($otherGeoObject, $maximumDistance);

		$this->assertLessThanOrEqual(
			$maximumDistance,
			$this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject)
		);
	}

}