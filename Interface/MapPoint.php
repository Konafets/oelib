<?php
/***************************************************************
* Copyright notice
*
* (c) 2011-2012 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This interface represents an object that can be positioned on a map, e.g.,
 * on a Google Map.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_oelib_Interface_MapPoint {
	/**
	 * Returns this object's coordinates.
	 *
	 * @return array<float>
	 *         this object's geo coordinates using the keys "latitude" and
	 *         "longitude", will be empty if this object has no coordinates
	 */
	public function getGeoCoordinates();

	/**
	 * Checks whether this object has non-empty coordinates.
	 *
	 * @return boolean
	 *         TRUE if this object has both a non-empty longitude and a
	 *         non-empty latitude, FALSE otherwise
	 */
	public function hasGeoCoordinates();

	/**
	 * Gets the title for the tooltip of this object.
	 *
	 * @return string the tooltip title (plain text), might be empty
	 */
	public function getTooltipTitle();

	/**
	 * Checks whether this object has a non-empty tooltip title.
	 *
	 * @return boolean
	 *         TRUE if this object has a non-empty tooltip title, FALSE otherwise
	 */
	public function hasTooltipTitle();

	/**
	 * Gets the info window content of this object.
	 *
	 * @return string the info window content (HTML), might be empty
	 */
	public function getInfoWindowContent();

	/**
	 * Checks whether this object has a non-empty info window content.
	 *
	 * @return boolean
	 *         TRUE if this object has a non-empty info window content, FALSE otherwise
	 */
	public function hasInfoWindowContent();
}
?>