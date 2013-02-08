<?php
/***************************************************************
* Copyright notice
*
* (c) 2011-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This is just a dummy class that implements the MapPoint interface and the
 * Identity interface.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_tests_fixtures_TestingMapPoint implements tx_oelib_Interface_MapPoint, tx_oelib_Interface_Identity {
	/**
	 * @var integer
	 */
	private $uid = 0;

	/**
	 * Gets this object's UID.
	 *
	 * @return integer
	 *         this object's UID, will be zero if this object does not have a UID yet
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 * Checks whether this object has a UID.
	 *
	 * @return boolean TRUE if this object has a non-zero UID, FALSE otherwise
	 */
	public function hasUid() {
		return ($this->getUid() !== 0);
	}

	/**
	 * Sets this object's UID.
	 *
	 * This function may only be called on objects that do not have a UID yet.
	 *
	 * @param integer $uid the UID to set, must be > 0
	 *
	 * @return void
	 */
	public function setUid($uid) {
		$this->uid = $uid;
	}

	/**
	 * Returns this object's coordinates.
	 *
	 * @return array<float>
	 *         this object's geo coordinates using the keys "latitude" and
	 *         "longitude", will be empty if this object has no coordinates
	 */
	public function getGeoCoordinates() {
		return array('latitude' => 11.2, 'longitude' => 4.9);
	}

	/**
	 * Checks whether this object has non-empty coordinates.
	 *
	 * @return boolean
	 *         TRUE if this object has both a non-empty longitude and a
	 *         non-empty latitude, FALSE otherwise
	 */
	public function hasGeoCoordinates() {
		return TRUE;
	}

	/**
	 * Gets the title for the tooltip of this object.
	 *
	 * @return string the tooltip title (plain text), might be empty
	 */
	public function getTooltipTitle() {
		return '';
	}

	/**
	 * Checks whether this object has a non-empty tooltip title.
	 *
	 * @return boolean
	 *         TRUE if this object has a non-empty tooltip title, FALSE otherwise
	 */
	public function hasTooltipTitle() {
		return FALSE;
	}

	/**
	 * Gets the info window content of this object.
	 *
	 * @return string the info window content (HTML), might be empty
	 */
	public function getInfoWindowContent() {
		return '';
	}

	/**
	 * Checks whether this object has a non-empty info window content.
	 *
	 * @return boolean
	 *         TRUE if this object has a non-empty info window content, FALSE otherwise
	 */
	public function hasInfoWindowContent() {
		return FALSE;
	}
}
?>