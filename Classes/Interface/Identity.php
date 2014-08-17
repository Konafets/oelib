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
 * This interface represents something that has an identity, i.e., a UID.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_oelib_Interface_Identity {
	/**
	 * Gets this object's UID.
	 *
	 * @return integer
	 *         this object's UID, will be zero if this object does not have a UID yet
	 */
	public function getUid();

	/**
	 * Checks whether this object has a UID.
	 *
	 * @return boolean TRUE if this object has a non-zero UID, FALSE otherwise
	 */
	public function hasUid();

	/**
	 * Sets this object's UID.
	 *
	 * This function may only be called on objects that do not have a UID yet.
	 *
	 * @param integer $uid the UID to set, must be > 0
	 *
	 * @return void
	 */
	public function setUid($uid);
}