<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_model.php');
require_once(t3lib_extMgm::extPath('oelib') . 'exceptions/class.tx_oelib_notFoundException.php');

/**
 * Class 'tx_oelib_identityMap' for the 'oelib' extension.
 *
 * This class represents an identity map that stores and retrieves model
 * instances by their UIDs.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_identityMap {
	/**
	 * @var array the items in this map with their UIDs as keys
	 */
	protected $items = array();

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if (is_array($this->items)) {
			foreach (($this->items) as $key => $item) {
				$item->__destruct();
				unset($this->items[$key]);
			}

			unset($this->items);
		}
	}

	/**
	 * Adds a model to the identity map.
	 *
	 * @param tx_oelib_model model to add, must have a UID
	 */
	public function add(tx_oelib_model $model) {
		if (!$model->hasUid()) {
			throw new Exception('Add() requires a model that has a UID.');
		}

		$this->items[$model->getUid()] = $model;
	}

	/**
	 * Retrieves a model from the map by UID.
	 *
	 * @throws tx_oelib_notFoundException if this map does not have a model
	 *                                    with that particular UID
	 *
	 * @param integer the UID of the model to retrieve, must be > 0
	 *
	 * @return tx_oelib_model the stored model with the UID $uid
	 */
	public function get($uid) {
		if ($uid <= 0) {
			throw new Exception(
				'$uid must be > 0.'
			);
		}

		if (!isset($this->items[$uid])) {
			throw new tx_oelib_notFoundException(
				'This map currently does not contain a model with the UID ' .
					$uid . '.'
			);
		}

		return $this->items[$uid];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_identityMap.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_identityMap.php']);
}
?>