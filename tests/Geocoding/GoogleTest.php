<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Unit tests for the tx_oelib_Geocoding_Google class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Geocoding_GoogleTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Geocoding_Google
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = tx_oelib_Geocoding_Google::getInstance();
	}

	public function tearDown() {
		tx_oelib_Geocoding_Google::purgeInstance();
		unset($this->fixture);
	}


	//////////////////////////////////////
	// Tests for the basic functionality
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceCreatesGoogleMapsLookupInstance() {
		$this->assertInstanceOf(
			'tx_oelib_Geocoding_Google',
			tx_oelib_Geocoding_Google::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function setInstanceSetsInstance() {
		tx_oelib_Geocoding_Google::purgeInstance();

		$instance = new tx_oelib_Geocoding_Dummy();
		tx_oelib_Geocoding_Google::setInstance($instance);

		$this->assertSame(
			$instance,
			tx_oelib_Geocoding_Google::getInstance()
		);
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
	public function lookUpForEmptyAddressWithErrorSendsNoRequest() {
		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoError();

		$fixture = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->never())->method('sendRequest');

		$fixture->lookUp($geo);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressSetsCoordinatesOfAddress() {
		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->fixture->lookUp($geo);
		$coordinates = $geo->getGeoCoordinates();

		$this->assertEquals(
			50.7335500,
			$coordinates['latitude'],
			'', 0.1
		);
		$this->assertEquals(
			7.1014300,
			$coordinates['longitude'],
			'', 0.1
		);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithCoordinatesSendsNoRequest() {
		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
		$geo->setGeoCoordinates(
			array('latitude' => 50.7335500, 'longitude' => 7.1014300)
		);

		$fixture = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->never())->method('sendRequest');

		$fixture->lookUp($geo);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithErrorSendsNoRequest() {
		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
		$geo->setGeoError();

		$fixture = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->never())->method('sendRequest');

		$fixture->lookUp($geo);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithServerErrorSetsGeoProblem() {
		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$fixture = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->any())->method('sendRequest')
			->will($this->returnValue('500'));

		$fixture->lookUp($geo);

		$this->assertTrue(
			$geo->hasGeoError()
		);
	}

	/**
	 * @test
	 */
	public function lookUpSetsCoordinatesFromSendRequest() {
		$jsonResult = '{ "results" : [ { "address_components" : [ { "long_name" : "1", "short_name" : "1", ' .
			'"types" : [ "street_number" ] }, { "long_name" : "Am Hof", "short_name" : "Am Hof", ' .
			'"types" : [ "route" ] }, { "long_name" : "Bonn", "short_name" : "Bonn", ' .
			'"types" : [ "sublocality", "political" ] }, { "long_name" : "Bonn", "short_name" : "Bonn", ' .
			'"types" : [ "locality", "political" ] }, { "long_name" : "Bonn", "short_name" : "BN", ' .
			'"types" : [ "administrative_area_level_2", "political" ] }, { "long_name" : "Nordrhein-Westfalen", ' .
			'"short_name" : "Nordrhein-Westfalen", "types" : [ "administrative_area_level_1", "political" ] }, ' .
			'{ "long_name" : "Germany", "short_name" : "DE", "types" : [ "country", "political" ] }, ' .
			'{ "long_name" : "53113", "short_name" : "53113", "types" : [ "postal_code" ] } ], ' .
			'"formatted_address" : "Am Hof 1, 53113 Bonn, Germany", "geometry" : { "location" : ' .
			'{ "lat" : 50.733550, "lng" : 7.101430 }, "location_type" : "ROOFTOP", ' .
			'"viewport" : { "northeast" : { "lat" : 50.73489898029150, "lng" : 7.102778980291502 }, ' .
			'"southwest" : { "lat" : 50.73220101970850, "lng" : 7.100081019708497 } } }, ' .
			'"types" : [ "street_address" ] } ], "status" : "OK"}';

		$geo = new tx_oelib_tests_fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$fixture = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->any())->method('sendRequest')->will($this->returnValue($jsonResult));

		$fixture->lookUp($geo);

		$this->assertEquals(
			array(
				'latitude' => 50.7335500,
				'longitude' => 7.1014300,
			),
			$geo->getGeoCoordinates()
		);
	}

	/**
	 * @test
	 */
	public function lookUpThrottlesRequestsByAtLeast1Dot73Seconds() {
		$geo1 = new tx_oelib_tests_fixtures_TestingGeo();
		$geo1->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
		$geo2 = new tx_oelib_tests_fixtures_TestingGeo();
		$geo2->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$fixture = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest'),
			array(),
			'',
			FALSE
		);
		$fixture->expects($this->any())->method('sendRequest')
			->will($this->returnValue('200,8,50.7335500,7.1014300'));

		$startTime = microtime(TRUE);
		$fixture->lookUp($geo1);
		$fixture->lookUp($geo2);
		$endTime = microtime(TRUE);

		$timePassed = $endTime - $startTime;
		$this->assertGreaterThan(
			1.73,
			$timePassed
		);
	}
}
?>