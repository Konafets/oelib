<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2011 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class tx_oelib_Geocoding_Calculator for the "oelib" extension.
 *
 * This class provides functions for calculating the distance between geo
 * objects.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Geocoding_Calculator {
	/**
	 * the earth radius in kilometers
	 *
	 * @var float
	 */
	const EARTH_RADIUS_IN_KILOMETERS = 6378.7;

	/**
	 * Calculates the great-circle distance in kilometers between two geo
	 * objects using the haversine formula.
	 *
	 * @param tx_oelib_Interface_Geo $object1
	 *        the first object, must have geo coordinates
	 * @param tx_oelib_Interface_Geo $object2
	 *        the second object, must have geo coordinates
	 *
	 * @return float the distance between $object1 and $object2 in kilometers,
	 *               will be >= 0.0
	 */
	public function calculateDistanceInKilometers(
		tx_oelib_Interface_Geo $object1, tx_oelib_Interface_Geo $object2
	) {
		if ($object1->hasGeoError()) {
			throw new InvalidArgumentException('$object1 has a geo error.');
		}
		if ($object2->hasGeoError()) {
			throw new InvalidArgumentException('$object2 has a geo error.');
		}
		if (!$object1->hasGeoCoordinates()) {
			throw new InvalidArgumentException(
				'$object1 needs to have coordinates, but has none.'
			);
		}
		if (!$object2->hasGeoCoordinates()) {
			throw new InvalidArgumentException(
				'$object2 needs to have coordinates, but has none.'
			);
		}

		$coordinates1 = $object1->getGeoCoordinates();
		$latitude1 = deg2rad($coordinates1['latitude']);
		$longitude1 = deg2rad($coordinates1['longitude']);
		$coordinates2 = $object2->getGeoCoordinates();
		$latitude2 = deg2rad($coordinates2['latitude']);
		$longitude2 = deg2rad($coordinates2['longitude']);

		return acos(sin($latitude1) * sin($latitude2)
			+ cos($latitude1) * cos($latitude2) * cos($longitude2 - $longitude1)
		) * self::EARTH_RADIUS_IN_KILOMETERS;
	}

	/**
	 * Filters a list of geo objects by distance around another geo object.
	 *
	 * The returned list will only contain objects that are within $distance of
	 * $center, including objects that are located at a distance of exactly
	 * $distance.
	 *
	 * @param tx_oelib_List $unfilteredObjects
	 *        the list to filter, may be empty
	 * @param tx_oelib_Interface_Geo $center
	 *        the center to which $distance related
	 * @param float $distance
	 *        the distance in kilometers within which the returned objects must
	 *        be located
	 *
	 * @return tx_oelib_List<tx_oelib_Interface_Geo>
	 *         a copy of $unfilteredObjects with only those objects that are
	 *         located within $distance kilometers of $center
	 */
	public function filterByDistance(
		tx_oelib_List $unfilteredObjects, tx_oelib_Interface_Geo $center,
		$distance
	) {
		$objectsWithinDistance = tx_oelib_ObjectFactory::make('tx_oelib_List');

		foreach ($unfilteredObjects as $object) {
			if ($this->calculateDistanceInKilometers($center, $object)
				<= $distance
			) {
				$objectsWithinDistance->add($object);
			}
		}

		return $objectsWithinDistance;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Geocoding/Calculator.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Geocoding/Calculator.php']);
}
?>