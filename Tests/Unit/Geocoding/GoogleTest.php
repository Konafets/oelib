<?php
/**
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
class Tx_Oelib_Geocoding_GoogleTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Geocoding_Google
	 */
	private $subject;

	public function setUp() {
		$this->subject = tx_oelib_Geocoding_Google::getInstance();
	}

	public function tearDown() {
		tx_oelib_Geocoding_Google::purgeInstance();
		unset($this->subject);
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
			'Tx_Oelib_Tests_Unit_Fixtures_TestingGeo',
			array('setGeoError')
		);
		$geo->expects($this->once())->method('setGeoError');

		$this->subject->lookUp($geo);
	}

	/**
	 * @test
	 */
	public function lookUpForEmptyAddressWithErrorSendsNoRequest() {
		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoError();

		$subject = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$subject->expects($this->never())->method('sendRequest');

		$subject->lookUp($geo);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressSetsCoordinatesOfAddress() {
		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$this->subject->lookUp($geo);
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
		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
		$geo->setGeoCoordinates(
			array('latitude' => 50.7335500, 'longitude' => 7.1014300)
		);

		$subject = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$subject->expects($this->never())->method('sendRequest');

		$subject->lookUp($geo);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithErrorSendsNoRequest() {
		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
		$geo->setGeoError();

		$subject = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$subject->expects($this->never())->method('sendRequest');

		$subject->lookUp($geo);
	}

	/**
	 * @test
	 */
	public function lookUpForAFullGermanAddressWithServerErrorSetsGeoProblem() {
		$jsonResult = '{ "status" : "ZERO_RESULTS"}';

		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$subject = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$subject->expects($this->any())->method('sendRequest')->will($this->returnValue($jsonResult));

		$subject->lookUp($geo);

		$this->assertTrue(
			$geo->hasGeoError()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException RuntimeException
	 */
	public function lookUpForAFullGermanAddressWithNetworkErrorThrowsException() {
		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$subject = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$subject->expects($this->any())->method('sendRequest')->will($this->returnValue(FALSE));

		$subject->lookUp($geo);
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

		$geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$subject = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest', 'throttle'),
			array(),
			'',
			FALSE
		);
		$subject->expects($this->any())->method('sendRequest')->will($this->returnValue($jsonResult));

		$subject->lookUp($geo);

		$this->assertSame(
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
	public function lookUpThrottlesRequestsByAtLeast35Seconds() {
		$this->markTestSkipped('This test usually is not executed because it takes more than 30 seconds to execute.');

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

		$geo1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo1->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
		$geo2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
		$geo2->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

		$subject = $this->getMock(
			'tx_oelib_Geocoding_Google',
			array('sendRequest'),
			array(),
			'',
			FALSE
		);
		$subject->expects($this->any())->method('sendRequest')
			->will($this->returnValue($jsonResult));

		$startTime = microtime(TRUE);
		$subject->lookUp($geo1);
		$subject->lookUp($geo2);
		$endTime = microtime(TRUE);

		$timePassed = $endTime - $startTime;
		$this->assertGreaterThan(
			35.0,
			$timePassed
		);
	}
}