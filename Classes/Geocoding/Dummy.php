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
 * This class represents a faked service to look up geo coordinates.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Geocoding_Dummy implements tx_oelib_Interface_GeocodingLookup {
	/**
	 * faked coordinates with the keys "latitude" and "longitude" or empty if
	 * there are none
	 *
	 * @var array
	 */
	private $coordinates = array();

	/**
	 * The constructor.
	 */
	public function __construct() {}

	/**
	 * The destructor.
	 */
	public function __destruct() {}

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

		if (!empty($this->coordinates)) {
			$geoObject->setGeoCoordinates($this->coordinates);
		} else {
			$geoObject->setGeoError();
		}
	}

	/**
	 * Sets the coordinates lookUp() is supposed to return.
	 *
	 * @param float $latitude latitude coordinate
	 * @param float $longitude longitude coordinate
	 *
	 * @return void
	 */
	public function setCoordinates($latitude, $longitude) {
		$this->coordinates = array(
			'latitude' => $latitude, 'longitude' => $longitude,
		);
	}

	/**
	 * Resets the fake coordinates.
	 *
	 * @return void
	 */
	public function clearCoordinates() {
		$this->coordinates = array();
	}
}