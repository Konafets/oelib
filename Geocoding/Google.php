<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2011 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class tx_oelib_Geocoding_Lookup for the "oelib" extension.
 *
 * This class represents a service to look up geo coordinates via Google Maps.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Geocoding_Google implements tx_oelib_Interface_GeocodingLookup {
	/**
	 * status code for: okay, address was parsed
	 *
	 * @var string
	 */
	const STATUS_OK = 'OK';

	/**
	 * the base URL of the Google Maps geo coding service
	 *
	 * @var string
	 */
	const BASE_URL = 'http://maps.google.com/maps/api/geocode/json?sensor=false';

	/**
	 * the Singleton instance
	 *
	 * @var tx_oelib_Interface_GeocodingLookup
	 */
	private static $instance = null;

	/**
	 * the amount of time (in seconds) that need to pass between subsequent
	 * geocoding requests
	 *
	 * @var float
	 */
	const GEOCODING_THROTTLING = 35.0;

	/**
	 * the timestamp of the last geocoding request (will be 0.00 before the
	 * first request)
	 *
	 * @var float
	 */
	static private $lastGeocodingTimestamp = 0.00;

	/**
	 * The constructor. Do not call this constructor directly. Use getInstance()
	 * instead.
	 */
	protected function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		self::$lastGeocodingTimestamp = 0.00;
	}

	/**
	 * Retrieves the Singleton instance of the GoogleMaps look-up.
	 *
	 * Note: There will always be only one instance, even if this function is
	 * called with different parameters.
	 *
	 * @return tx_oelib_Interface_GeocodingLookup the Singleton GoogleMaps look-up
	 */
	public static function getInstance() {
		if (!is_object(self::$instance)) {
			self::$instance = new tx_oelib_Geocoding_Google();
		}

		return self::$instance;
	}

	/**
	 * Sets the Singleton GoogleMaps look-up instance.
	 *
	 * Note: This function is to be used for testing only.
	 *
	 * @param tx_oelib_Interface_GeocodingLookup $instance
	 *        the instance which getInstance() should return
	 */
	public static function setInstance(tx_oelib_Interface_GeocodingLookup $instance) {
		self::$instance = $instance;
	}

	/**
	 * Purges the current GoogleMaps look-up instance.
	 */
	public static function purgeInstance() {
		if (is_object(self::$instance)) {
			self::$instance->__destruct();
		}

		self::$instance = null;
	}

	/**
	 * Looks up the geo coordinates of the address of an object and sets its
	 * geo coordinates.
	 *
	 * @param tx_oelib_Interface_Geo $geoObject
	 *        the object for which the geo coordinates will be looked up and set
	 *
	 * @return void
	 */
	public function lookUp(tx_oelib_Interface_Geo $geoObject) {
		if ($geoObject->hasGeoError() || $geoObject->hasGeoCoordinates()) {
			return;
		}
		if (!$geoObject->hasGeoAddress()) {
			$geoObject->setGeoError();
			return;
		}

		$address = $geoObject->getGeoAddress();

		$this->throttle();
		$rawResult = $this->sendRequest($address);
		if ($rawResult === FALSE) {
			throw new Exception(
				'There was an error connecting to the Google Maps server.'
			);
		}

		$resultParts = json_decode($rawResult, TRUE);
		$status = $resultParts['status'];

		if ($status === self::STATUS_OK) {
			$coordinates = $resultParts['results'][0]['geometry']['location'];
			$geoObject->setGeoCoordinates(
				array(
					'latitude' => floatval($coordinates['lat']),
					'longitude' => floatval($coordinates['lng']),
				)
			);
		} else {
			$geoObject->setGeoError();
		}
	}

	/**
	 * Sends a geocoding request to the Google Maps server.
	 *
	 * @param string $address the address to look up, must not be empty
	 *
	 * @return mixed a string with the CSV result from the Google Maps server,
	 *               or FALSE if an error has occurred
	 */
	protected function sendRequest($address) {
		$baseUrlWithAddress = self::BASE_URL . '&address=';

		return t3lib_div::getURL($baseUrlWithAddress . urlencode($address));
	}

	/**
	 * Makes sure the necessary amount of time has passed since the last
	 * geocoding request.
	 */
	protected function throttle() {
		if (self::$lastGeocodingTimestamp > 0.00) {
			$secondsSinceLastRequest = microtime(TRUE) - self::$lastGeocodingTimestamp;
			if ($secondsSinceLastRequest < self::GEOCODING_THROTTLING) {
				usleep(1000000 * (self::GEOCODING_THROTTLING - $secondsSinceLastRequest));
			}
		}

		self::$lastGeocodingTimestamp = microtime(TRUE);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Geocoding/Google.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Geocoding/Google.php']);
}
?>