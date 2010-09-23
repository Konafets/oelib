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
	 * a valid Google Maps API key for localhost
	 *
	 * @var string
	 */
	const GOOGLE_MAPS_API_KEY = 'ABQIAAAAbDm1mvIP78sIsBcIbMgOPRT2yXp_ZAY8_ufC3CFXhHIE1NvwkxTwV0FqSWhHhsXRyGQ_btfZ1hNR7g';

	/**
	 * @var tx_oelib_Geocoding_Google
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = tx_oelib_Geocoding_Google
			::getInstance(self::GOOGLE_MAPS_API_KEY);
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
		$this->assertTrue(
			tx_oelib_Geocoding_Google::getInstance(self::GOOGLE_MAPS_API_KEY)
				instanceof tx_oelib_Geocoding_Google
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
			tx_oelib_Geocoding_Google::getInstance(self::GOOGLE_MAPS_API_KEY)
		);
	}

	/**
	 * @test
	 */
	public function constructorWithEmptyApiKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$apiKey must not be empty.'
		);

		tx_oelib_Geocoding_Google::getInstance('');
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

		$fixture->__destruct();
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

		$fixture->__destruct();
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

		$fixture->__destruct();
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

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function lookUpSetsCoordinatesFromSendRequest() {
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
			->will($this->returnValue('200,8,50.7335500,7.1014300'));

		$fixture->lookUp($geo);

		$this->assertEquals(
			array(
				'latitude' => 50.7335500,
				'longitude' => 7.1014300,
			),
			$geo->getGeoCoordinates()
		);

		$fixture->__destruct();
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

		$fixture->__destruct();
	}
}
?>