<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class represents an identity map that stores and retrieves model instances by their UIDs.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_IdentityMap {
	/**
	 * @var array the items in this map with their UIDs as keys
	 */
	protected $items = array();

	/**
	 * @var integer the highest used UID
	 */
	private $highestUid = 0;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		$this->highestUid = 0;
		unset($this->items);
	}

	/**
	 * Adds a model to the identity map.
	 *
	 * @param Tx_Oelib_Model $model the model to add, must have a UID
	 *
	 * @return void
	 */
	public function add(Tx_Oelib_Model $model) {
		if (!$model->hasUid()) {
			throw new InvalidArgumentException('Add() requires a model that has a UID.', 1331488748);
		}

		$this->items[$model->getUid()] = $model;
		$this->highestUid = max($this->highestUid, $model->getUid());
	}

	/**
	 * Retrieves a model from the map by UID.
	 *
	 * @throws tx_oelib_Exception_NotFound if this map does not have a model
	 *                                     with that particular UID
	 *
	 * @param integer $uid the UID of the model to retrieve, must be > 0
	 *
	 * @return Tx_Oelib_Model the stored model with the UID $uid
	 */
	public function get($uid) {
		if ($uid <= 0) {
			throw new InvalidArgumentException('$uid must be > 0.', 1331488761);
		}

		if (!isset($this->items[$uid])) {
			throw new tx_oelib_Exception_NotFound(
				'This map currently does not contain a model with the UID ' .
					$uid . '.'
			);
		}

		return $this->items[$uid];
	}

	/**
	 * Gets a UID that has not been used in the map before and that is greater
	 * than the greatest used UID.
	 *
	 * @return integer a new UID, will be > 0
	 */
	public function getNewUid() {
		return $this->highestUid + 1;
	}
}