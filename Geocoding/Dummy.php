<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2012 Saskia Metzler <saskia@merlin.owl.de>
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
 * Class tx_oelib_Geocoding_Dummy for the "oelib" extension.
 *
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

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Geocoding/Dummy.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Geocoding/Dummy.php']);
}
?>