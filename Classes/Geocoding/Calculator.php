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
 * This class provides functions for calculating the distance between geo objects.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Geocoding_Calculator implements t3lib_Singleton {
	/**
	 * the earth radius in kilometers
	 *
	 * @var float
	 */
	const EARTH_RADIUS_IN_KILOMETERS = 6378.7;

	/**
	 * @var float
	 */
	const ONE_DEGREE_LATITUDE_IN_KILOMETERS = 111.2;

	/**
	 * Calculates the great-circle distance in kilometers between two geo
	 * objects using the haversine formula.
	 *
	 * @param tx_oelib_Interface_Geo $object1
	 *        the first object, must have geo coordinates
	 * @param tx_oelib_Interface_Geo $object2
	 *        the second object, must have geo coordinates
	 *
	 * @return float the distance between $object1 and $object2 in kilometers, will be >= 0.0
	 *
	 * @throws InvalidArgumentException
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
	 * @param Tx_Oelib_List<tx_oelib_Interface_Geo> $unfilteredObjects
	 *        the list to filter, may be empty
	 * @param tx_oelib_Interface_Geo $center
	 *        the center to which $distance related
	 * @param float $distance
	 *        the distance in kilometers within which the returned objects must
	 *        be located
	 *
	 * @return Tx_Oelib_List<tx_oelib_Interface_Geo>
	 *         a copy of $unfilteredObjects with only those objects that are
	 *         located within $distance kilometers of $center
	 */
	public function filterByDistance(
		Tx_Oelib_List $unfilteredObjects, tx_oelib_Interface_Geo $center,
		$distance
	) {
		$objectsWithinDistance = t3lib_div::makeInstance('Tx_Oelib_List');

		foreach ($unfilteredObjects as $object) {
			if ($this->calculateDistanceInKilometers($center, $object)
				<= $distance
			) {
				$objectsWithinDistance->add($object);
			}
		}

		return $objectsWithinDistance;
	}

	/**
	 * Moves $object by $distance kilometers in the direction of $direction.
	 *
	 * Note: This move is not very accurate.
	 *
	 * @param tx_oelib_Interface_Geo $object
	 * @param float $direction direction of the movement in degrees (0.0 is east)
	 * @param float $distance distance to move in kilometers, may be positive, zero or negative
	 *
	 * @return void
	 */
	public function move(tx_oelib_Interface_Geo $object, $direction, $distance) {
		$directionInRadians = deg2rad($direction);

		$originalCoordinates = $object->getGeoCoordinates();
		/** @var float $originalLatitude */
		$originalLatitude = $originalCoordinates['latitude'];
		/** @var float $originalLongitude */
		$originalLongitude = $originalCoordinates['longitude'];

		$xDeltaInKilometers = $distance * cos($directionInRadians);
		$yDeltaInKilometers = $distance * sin($directionInRadians);

		$oneDegreeLongitudeInKilometers = 2 * M_PI * self::EARTH_RADIUS_IN_KILOMETERS * cos($originalLongitude) / 360;

		$latitudeDelta = $yDeltaInKilometers / self::ONE_DEGREE_LATITUDE_IN_KILOMETERS;
		$longitudeDelta = $xDeltaInKilometers / $oneDegreeLongitudeInKilometers;

		$object->setGeoCoordinates(
			array(
				'latitude' => $originalLatitude + $latitudeDelta,
				'longitude' => $originalLongitude + $longitudeDelta,
			)
		);
	}

	/**
	 * Moves $object at most by $maximumDistance kilometers in the direction of $direction.
	 *
	 * Note: This move is not very accurate.
	 *
	 * @param tx_oelib_Interface_Geo $object
	 * @param float $direction direction of the movement in degrees (0.0 is east)
	 * @param float $maximumDistance maximum distance to move in kilometers, may be positive, zero or negative
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function moveByRandomDistance(tx_oelib_Interface_Geo $object, $direction, $maximumDistance) {
		if ($maximumDistance < 0) {
			throw new InvalidArgumentException('$distance must be >= 0, but actually is: ' . $maximumDistance, 1407432668);
		}

		$distanceMultiplier = 10000;

		$randomDistance = mt_rand(0, $maximumDistance * $distanceMultiplier) / $distanceMultiplier;
		$this->move($object, $direction, $randomDistance);
	}

	/**
	 * Moves $object by $distance kilometers in a random direction
	 *
	 * Note: This move is not very accurate.
	 *
	 * @param tx_oelib_Interface_Geo $object
	 * @param float $distance distance to move in kilometers, may be positive, zero or negative
	 *
	 * @return void
	 */
	public function moveInRandomDirection(tx_oelib_Interface_Geo $object, $distance) {
		$direction = mt_rand(0, 360);
		$this->move($object, $direction, $distance);
	}

	/**
	 * Moves $object by at most $maximumDistance kilometers in a random direction
	 *
	 * Note: This move is not very accurate.
	 *
	 * @param tx_oelib_Interface_Geo $object
	 * @param float $maximumDistance maximum distance to move in kilometers, must not be negative
	 *
	 * @return void
	 */
	public function moveInRandomDirectionAndDistance(tx_oelib_Interface_Geo $object, $maximumDistance) {
		$direction = mt_rand(0, 360);
		$this->moveByRandomDistance($object, $direction, $maximumDistance);
	}
}