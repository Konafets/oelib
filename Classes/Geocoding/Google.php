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
	private static $instance = NULL;

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
	 *
	 * @return void
	 */
	public static function setInstance(tx_oelib_Interface_GeocodingLookup $instance) {
		self::$instance = $instance;
	}

	/**
	 * Purges the current GoogleMaps look-up instance.
	 *
	 * @return void
	 */
	public static function purgeInstance() {
		self::$instance = NULL;
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
			throw new RuntimeException('There was an error connecting to the Google geocoding server.', 1331488446);
		}

		$resultParts = json_decode($rawResult, TRUE);
		$status = $resultParts['status'];

		if ($status === self::STATUS_OK) {
			$coordinates = $resultParts['results'][0]['geometry']['location'];
			$geoObject->setGeoCoordinates(
				array(
					'latitude' => (float)$coordinates['lat'],
					'longitude' => (float)$coordinates['lng'],
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

		return \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($baseUrlWithAddress . urlencode($address));
	}

	/**
	 * Makes sure the necessary amount of time has passed since the last
	 * geocoding request.
	 *
	 * @return void
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