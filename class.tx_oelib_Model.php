<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_Model' for the 'oelib' extension.
 *
 * This class represents a general domain model which is capable of lazy loading
 * (using ghosts).
 *
 * A model can have one of the following states: empty, ghost, loading, loaded.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_oelib_Model extends tx_oelib_Object {
	/**
	 * @var integer a status indicating that this model has neither data nur UID
	 *              yet
	 */
	const STATUS_EMPTY = 0;
	/**
	 * @var integer a status indicating that this model's data has not been
	 *              loaded yet (lazily), but that the model already has a UID
	 */
	const STATUS_GHOST = 1;
	/**
	 * @var integer a status indicating that this model's data currently is
	 *              being loaded
	 */
	const STATUS_LOADING = 2;
	/**
	 * @var integer a status indicating that this model's data has already been
	 *              loaded (with or without UID)
	 */
	const STATUS_LOADED = 3;
	/**
	 * @var integer a status indicating that this model's data could not be
	 *              retrieved from the DB
	 */
	const STATUS_DEAD = 4;

	/**
	 * @var integer this model's UID, will be 0 if this model has been created
	 *              in memory
	 */
	private $uid = 0;

	/**
	 * @var array the data for this object (without the UID column)
	 */
	private $data = array();

	/**
	 * @var integer this model's load status, will be STATUS_EMTPY,
	 *              STATUS_GHOST, STATUS_LOADING or STATUS_LOADED
	 */
	private $loadStatus = self::STATUS_EMPTY;

	/**
	 * @var array the callback function that fills this model with data
	 */
	private $loadCallback = array();

	/**
	 * The (empty) constructor.
	 *
	 * After instantiation, this model's data can be set via via setData() or
	 * set().
	 *
	 * @see setData
	 * @see set
	 */
	public function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->data);
	}

	/**
	 * Sets the complete data for this model.
	 *
	 * This function should be called directly after instantiation and must only
	 * be called once.
	 *
	 * @param array the data for this model, may be empty
	 */
	public function setData(array $data) {
		if ($this->isLoaded()) {
			throw new Exception(
				'setData must only be called once per model instance.'
			);
		}

		$this->data = $data;
		if (isset($this->data['uid'])) {
			if (!$this->hasUid()) {
				$this->setUid($this->data['uid']);
			}
			unset($this->data['uid']);
		}

		$this->loadStatus = self::STATUS_LOADED;
	}

	/**
	 * Marks this model as "dead", ie. that retrieving its data from the DB has
	 * failed.
	 */
	public function markAsDead() {
		$this->loadStatus = self::STATUS_DEAD;
	}

	/**
	 * Sets this model's UID.
	 *
	 * This function may only be called on models that do not have a UID yet.
	 *
	 * If this function is called on an empty model, the model state is changed
	 * to ghost.
	 *
	 * @param integer the UID to set, must be > 0
	 */
	public function setUid($uid) {
		if ($this->hasUid()) {
			throw new Exception(
				'The UID of a model cannot be set a second time.'
			);
		}
		if ($this->isEmpty()) {
			$this->loadStatus = self::STATUS_GHOST;
		}

		$this->uid = $uid;
	}

	/**
	 * Sets the value of the data item for the key $key.
	 *
	 * @param string the key of the data item to get, must not be empty
	 * @param mixed the data for the key $key
	 */
	protected function set($key, $value) {
		$this->data[$key] = $value;

		$this->loadStatus = self::STATUS_LOADED;
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * Before this function may be called, setData() or set() must have been
	 * called once.
	 *
	 * @param string the key of the data item to get, must not be empty
	 *
	 * @return mixed the data for the key $key, will be an empty string
	 *               if the key has not been set yet
	 */
	protected function get($key) {
		if ($key == 'uid') {
			throw new Exception(
				'The UID column needs to be accessed using the getUid function.'
			);
		}

		$this->load();
		if (!isset($this->data[$key])) {
			return '';
		}

		return $this->data[$key];
	}

	/**
	 * Makes sure this model has some data by loading the data for ghost models.
	 */
	private function load() {
		if ($this->isEmpty()) {
			throw new Exception(
				'Please call setData() directly after instantiation first.'
			);
		}

		if ($this->isGhost()) {
			if (!$this->hasLoadCallBack()) {
				throw new Exception(
					'Ghosts need a load callback function before their data ' .
						'can be accessed.'
				);
			}

			$this->loadStatus = self::STATUS_LOADING;
			call_user_func($this->loadCallback, $this);
		}
	}

	/**
	 * Gets this model's UIDs.
	 *
	 * @return integer this model's UID, will be zero if this model does
	 *                 not have a UID yet
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 * Checks whether this model has a UID.
	 *
	 * @return boolean true if this model has a non-zero UID, false otherwise
	 */
	public function hasUid() {
		return ($this->uid > 0);
	}

	/**
	 * Checks whether this model is a empty (has neither data nor UID).
	 *
	 * @return boolean true if this model is empty, false otherwise
	 */
	public function isEmpty() {
		return ($this->loadStatus == self::STATUS_EMPTY);
	}

	/**
	 * Checks whether this model is a ghost (has a UID, but is not fully loaded
	 * yet).
	 *
	 * @return boolean true if this model is a ghost, false otherwise
	 */
	public function isGhost() {
		return ($this->loadStatus == self::STATUS_GHOST);
	}

	/**
	 * Checks whether this model is fully loaded (has data).
	 *
	 * @return boolean true if this model is fully loaded, false otherwise
	 */
	public function isLoaded() {
		return ($this->loadStatus == self::STATUS_LOADED);
	}

	/**
	 * Checks whether this model is dead (retrieving its data from the DB has
	 * failed).
	 *
	 * @return boolean true if this model is dead, false otherwise
	 */
	public function isDead() {
		return ($this->loadStatus == self::STATUS_DEAD);
	}

	/**
	 * Sets the callback function for loading this model with data.
	 *
	 * @param array the callback function for loading this model with data
	 */
	public function setLoadCallback(array $callback) {
		$this->loadCallback = $callback;
	}

	/**
	 * Checks whether this model has a callback function set for loading its
	 * data.
	 *
	 * @return boolean true if this model has a loading callback function set,
	 *                 false otherwise
	 */
	private function hasLoadCallBack() {
		return !empty($this->loadCallback);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Model.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Model.php']);
}
?>