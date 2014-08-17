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
 * This is just a dummy class that implements the MapPoint interface and the
 * Identity interface.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Fixtures_TestingMapPoint implements tx_oelib_Interface_MapPoint, tx_oelib_Interface_Identity {
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
	 * @return float[]
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