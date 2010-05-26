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
class tx_oelib_Geocoding_Google {
	/**
	 * HTTP status code for: okay, address was parsed
	 *
	 * @var integer
	 */
	const G_GEO_SUCCESS = 200;
	/**
	 * HTTP status code for: request could not be parsed
	 *
	 * @var integer
	 */
	const G_GEO_BAD_REQUEST = 400;
	/**
	 * HTTP status code for: access forbidden (too many requests)
	 *
	 * @var integer
	 */
	const G_GEO_FORBIDDEN = 403;
	/**
	 * HTTP status code for: general server error
	 *
	 * @var integer
	 */
	const G_GEO_SERVER_ERROR = 500;
	/**
	 * HTTP status code for: address parameter missing or empty
	 *
	 * @var integer
	 */
	const G_GEO_MISSING_QUERY = 601;
	/**
	 * HTTP status code for: address parameter missing or empty
	 *
	 * @var integer
	 */
	const G_GEO_MISSING_ADDRESS = 601;
	/**
	 * HTTP status code for: address could not be found in the database
	 * (either because it is incorrect or very new)
	 *
	 * @var integer
	 */
	const G_GEO_UNKNOWN_ADDRESS = 602;
	/**
	 * HTTP status code for: address cannot be returned due to legal or
	 * contractual reasons
	 *
	 * @var integer
	 */
	const G_GEO_UNAVAILABLE_ADDRESS = 603;
	/**
	 * HTTP status code for: no route between the two points
	 *
	 * @var integer
	 */
	const G_GEO_UNKNOWN_DIRECTIONS = 604;
	/**
	 * HTTP status code for: API key invalid or does not match the domain
	 *
	 * @var integer
	 */
	const G_GEO_BAD_KEY = 610;
	/**
	 * HTTP status code for: query limit exceeded (either too many queries in a
	 * short amount of time or in the 24-hour limit)
	 *
	 * @var integer
	 */
	const G_GEO_TOO_MANY_QUERIES = 620;

	/**
	 * the base URL of the Google Maps geo coding service
	 *
	 * @var string
	 */
	const BASE_URL = 'http://maps.google.com/maps/geo?sensor=false&output=csv&key=';

	/**
	 * the Google Maps API key
	 *
	 * @var string
	 */
	private $apiKey = '';

	/**
	 * the Singleton instance
	 *
	 * @var tx_oelib_Geocoding_Google
	 */
	private static $instance = null;

	/**
	 * the amount of time (in seconds) that need to pass between subsequent
	 * geocoding requests
	 *
	 * @var float
	 */
	const GEOCODING_THROTTLING = 1.75;

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
	 *
	 * @param string $apiKey
	 *        the Google Maps API key to use, must not be empty
	 */
	protected function __construct($apiKey) {
		$this->apiKey = $apiKey;
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
	 * @param string $apiKey
	 *        the Google Maps API key to use, must not be empty
	 *
	 * @return tx_oelib_Geocoding_Google the Singleton GoogleMaps look-up
	 */
	public static function getInstance($apiKey) {
		if ($apiKey == '') {
			throw new InvalidArgumentException('$apiKey must not be empty.');
		}

		if (!is_object(self::$instance)) {
			self::$instance = new tx_oelib_Geocoding_Google($apiKey);
		}

		return self::$instance;
	}

	/**
	 * Sets the Singleton GoogleMaps look-up instance.
	 *
	 * Note: This function is to be used for testing only.
	 *
	 * @param tx_oelib_Geocoding_Google $geoFinder
	 *         the instance which getInstance() should return
	 */
	public static function setInstance(tx_oelib_Geocoding_Google $instance) {
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
	 */
	public function lookUp(tx_oelib_Interface_Geo $geoObject) {
		if ($geoObject->hasGeoError() || $geoObject->hasGeoCoordinates()) {
			return;
		}
		if (!$geoObject->hasGeoAddress()) {
			$geoObject->setGeoError();
			return;
		}

		$delay = 0;
		$address = $geoObject->getGeoAddress();

		do {
			if ($delay > 0) {
				usleep($delay);
			}
			$this->throttle();
			$rawResult = $this->sendRequest($address);
			if ($rawResult === FALSE) {
				throw new Exception(
					'There was an error connecting to the Google Maps server.'
				);
			}

			$delay += 100000;

			$resultParts = t3lib_div::trimExplode(',', $rawResult, TRUE);
			$status = $resultParts[0];
		} while (
			($status == self::G_GEO_TOO_MANY_QUERIES)
				|| ($status == self::G_GEO_FORBIDDEN)
		);

		if ($status == self::G_GEO_SUCCESS) {
			$geoObject->setGeoCoordinates(
				array(
					'latitude' => floatval($resultParts[2]),
					'longitude' => floatval($resultParts[3]),
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
		$baseUrlWithKey = self::BASE_URL . $this->getApiKey() . '&q=';

		return t3lib_div::getURL($baseUrlWithKey . urlencode($address));
	}

	/**
	 * Gets the Google Maps API key from the configuration.
	 *
	 * @return string the Google Maps API key, will be empty if no key has been set
	 */
	protected function getApiKey() {
		return $this->apiKey;
	}

	/**
	 * Makes sure the necessary amount of time has passed since the last
	 * geocoding request.
	 */
	protected function throttle() {
		if (self::$lastGeocodingTimestamp > 0.00) {
			$secondsSinceLastRequest
				= microtime(TRUE) - self::$lastGeocodingTimestamp;
			if ($secondsSinceLastRequest < self::GEOCODING_THROTTLING) {
				usleep(1000000 *
					(self::GEOCODING_THROTTLING - $secondsSinceLastRequest)
				);
			}
		}

		self::$lastGeocodingTimestamp = microtime(TRUE);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Geocoding/class.tx_oelib_Geocoding_Google.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Geocoding/class.tx_oelib_Geocoding_Google.php']);
}
?>