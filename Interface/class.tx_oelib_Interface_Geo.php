<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Interface tx_oelib_Interface_Geo for the "oelib" extension.
 *
 * This class represents an object that can have geo coordinates.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_oelib_Interface_Geo {
	/**
	 * Returns this object's address formatted for a geo lookup, for example
	 * "Pariser Str. 50, 53117 Auerberg, Bonn, DE". Any part of this address
	 * might be missing, though.
	 *
	 * @return string this object's address formatted for a geo lookup,
	 *                will be empty if this object has no address
	 */
	public function getGeoAddress();

	/**
	 * Checks whether this object has a non-empty address suitable for a geo
	 * lookup.
	 *
	 * @return boolean TRUE if this object has a non-empty address, FALSE
	 *                 otherwise
	 */
	public function hasGeoAddress();

	/**
	 * Retrieves this object's coordinates.
	 *
	 * @return array<float>
	 *         this object's geo coordinates using the keys "latitude" and
	 *         "longitude", will be empty if this object has no coordinates
	 */
	public function getGeoCoordinates();

	/**
	 * Sets this object's coordinates.
	 *
	 * @param array<float> $coordinates
	 *        the coordinates, using the keys "latitude" and "longitude",
	 *        the array values must not be empty
	 *
	 * @return void
	 */
	public function setGeoCoordinates(array $coordinates);

	/**
	 * Checks whether this object has non-empty coordinates.
	 *
	 * @return boolean TRUE if this object has both a non-empty longitude and
	 *                 a non-empty latitude, FALSE otherwise
	 */
	public function hasGeoCoordinates();

	/**
	 * Purges this object's geo coordinates.
	 *
	 * Note: Calling this function has no influence on this object's geo error
	 * status.
	 *
	 * @return void
	 */
	public function clearGeoCoordinates();

	/**
	 * Checks whether there has been a problem with this object's geo
	 * coordinates.
	 *
	 * Note: This function only checks whether there has been an error with the
	 * coordinates, not whether this object actually has coordinates.
	 *
	 * @return boolean TRUE if there has been an error, FALSE otherwise
	 */
	public function hasGeoError();

	/**
	 * Marks this object as having an error with the geo coordinates.
	 *
	 * @return void
	 */
	public function setGeoError();

	/**
	 * Marks this object as not having an error with the geo coordinates.
	 *
	 * @return void
	 */
	public function clearGeoError();
}
?>