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
	 * @return float[]
	 *         this object's geo coordinates using the keys "latitude" and
	 *         "longitude", will be empty if this object has no coordinates
	 */
	public function getGeoCoordinates();

	/**
	 * Checks whether this object has non-empty coordinates.
	 *
	 * @return bool
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
	 * @return bool
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
	 * @return bool
	 *         TRUE if this object has a non-empty info window content, FALSE otherwise
	 */
	public function hasInfoWindowContent();
}