<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee <typo3-coding@oliverklee.de>
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
 * A model can have one of the following states: dead, ghost, loading, loaded,
 * virgin.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class tx_oelib_Model extends tx_oelib_Object {
	/**
	 * @var integer a status indicating that this model has neither data nor UID
	 *              yet
	 */
	const STATUS_VIRGIN = 0;
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
	 * @var boolean whether this model is read-only
	 */
	protected $readOnly = FALSE;

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
	 * @var integer this model's load status, will be STATUS_VIRGIN,
	 *              STATUS_GHOST, STATUS_DEAD, STATUS_LOADING or STATUS_LOADED
	 */
	private $loadStatus = self::STATUS_VIRGIN;

	/**
	 * @var boolean whether this model's initial data has changed
	 */
	private $isDirty = FALSE;

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
		// avoids infinite loops for two models in a circle
		if ($this->isDead()) {
			return;
		}

		$this->markAsDead();

		foreach ($this->data as $key => $item) {
			if ($item instanceof tx_oelib_List) {
				$item->__destruct();
			} elseif ($item instanceof tx_oelib_Model) {
				// Models without UIDs are not registered at a mapper and thus
				// will not be destructed by the mapper.
				// Still, we need to avoid infinite loops if this model has a
				// relation to itself.
				if (!$item->hasUid() && ($item !== $this)) {
					$item->__destruct();
				}
			}
			unset($this->data[$key]);
		}
		$this->loadCallback = array();
	}

	/**
	 * Sets the complete data for this model.
	 *
	 * The data which is set via this function is considered to be the initial
	 * data. Fields with relations must already be filled with the constituted
	 * models/lists, not just with the UIDs (unlike the format that
	 * tx_oelib_DataMapper::getLoadedTestingModel takes).
	 *
	 * This function should be called directly after instantiation and must only
	 * be called once. Usually, this function is called on only a few occasions:
	 *
	 * 1. when the data mapper loads a model
	 * 2. when a new model is created in some unit tests
	 * 3. before a new model should be saved to the database
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
		if ($this->existsKey('uid')) {
			if (!$this->hasUid()) {
				$this->setUid($this->data['uid']);
			}
			unset($this->data['uid']);
		}

		$this->markAsLoaded();
		if ($this->hasUid()) {
			$this->markAsClean();
		} else {
			$this->markAsDirty();
		}
	}

	/**
	 * Returns the complete data for this model.
	 *
	 * This function may only be called by the mapper.
	 *
	 * @return array this model's complete data, will be empty if a model has
	 *               no data
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Marks this model as "loaded", ie. that it has some real data.
	 */
	protected function markAsLoaded() {
		$this->loadStatus = self::STATUS_LOADED;
	}

	/**
	 * Marks this model as "dead", ie. that retrieving its data from the DB has
	 * failed.
	 */
	public function markAsDead() {
		$this->loadStatus = self::STATUS_DEAD;
		$this->markAsClean();
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
		if ($this->isVirgin()) {
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
		if ($key == 'deleted') {
			throw new Exception(
				'$key must not be "deleted". Please use setToDeleted() instead.'
			);
		}
		if ($this->isReadOnly()) {
			throw new Exception(
				'set() must not be called on a read-only model.'
			);
		}

		if ($this->isGhost()) {
			$this->load();
		}
		$this->data[$key] = $value;

		$this->markAsLoaded();
		$this->markAsDirty();
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * Before this function may be called, setData() or set() must have been
	 * called once.
	 *
	 * @throws tx_oelib_Exception_NotFound if this model is dead
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
		if ($this->isDead()) {
			throw new tx_oelib_Exception_NotFound(
				'The ' . get_class($this) . ' with the UID ' . $this->getUid() .
					' either has been deleted (or has never existed), but still is accessed.',
				1332446332
			);
		}

		if (!$this->existsKey($key)) {
			return '';
		}

		return $this->data[$key];
	}

	/**
	 * Checks whether a data item with a certain key exists.
	 *
	 * @param string the key of the data item to check, must not be empty
	 *
	 * @return boolean TRUE if a data item with the key $key exists, FALSE
	 *                 otherwise
	 */
	protected function existsKey($key) {
		return array_key_exists($key, $this->data);
	}

	/**
	 * Gets the value stored in under the key $key as a model.
	 *
	 * @throws Exception if there is a data item stored for the key $key that
	 *                   is not a model instance
	 *
	 * @param string the key of the element to retrieve, must not be empty
	 *
	 * @return tx_oelib_Model the data item for the given key, will be null if
	 *                        it has not been set
	 */
	protected function getAsModel($key) {
		$this->checkForNonEmptyKey($key);

		$result = $this->get($key);
		if (!$this->existsKey($key) || ($result === null)) {
			return null;
		}

		if (!$result instanceof tx_oelib_Model) {
			throw new Exception(
				'The data item for the key "' . $key . '" is no model instance.'
			);
		}

		return $result;
	}

	/**
	 * Gets the value stored in under the key $key as a list of models.
	 *
	 * @throws Exception if there is a data item stored for the key $key that
	 *                   is not a list instance or if that item has not been
	 *                   set yet
	 *
	 * @param string the key of the element to retrieve, must not be empty
	 *
	 * @return tx_oelib_List the data item for the given key
	 */
	public function getAsList($key) {
		$this->checkForNonEmptyKey($key);

		$result = $this->get($key);
		if (!$result instanceof tx_oelib_List) {
			throw new Exception(
				'The data item for the key "' . $key . '" is no list instance.'
			);
		}

		return $result;
	}

	/**
	 * Makes sure this model has some data by loading the data for ghost models.
	 */
	private function load() {
		if ($this->isVirgin()) {
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
		return intval($this->uid);
	}

	/**
	 * Checks whether this model has a UID.
	 *
	 * @return boolean TRUE if this model has a non-zero UID, FALSE otherwise
	 */
	public function hasUid() {
		return ($this->uid > 0);
	}

	/**
	 * Checks whether this is a virgin model (which has neither data nor UID).
	 *
	 * @return boolean TRUE if this is a virgin model, FALSE otherwise
	 */
	public function isVirgin() {
		return ($this->loadStatus == self::STATUS_VIRGIN);
	}

	/**
	 * Checks whether this model is a ghost (has a UID, but is not fully loaded
	 * yet).
	 *
	 * @return boolean TRUE if this model is a ghost, FALSE otherwise
	 */
	public function isGhost() {
		return ($this->loadStatus == self::STATUS_GHOST);
	}

	/**
	 * Checks whether this model is fully loaded (has data).
	 *
	 * @return boolean TRUE if this model is fully loaded, FALSE otherwise
	 */
	public function isLoaded() {
		return ($this->loadStatus == self::STATUS_LOADED);
	}

	/**
	 * Checks whether this model is dead (retrieving its data from the DB has
	 * failed).
	 *
	 * @return boolean TRUE if this model is dead, FALSE otherwise
	 */
	public function isDead() {
		return ($this->loadStatus == self::STATUS_DEAD);
	}

	/**
	 * Checks whether this model is hidden.
	 *
	 * @return boolean TRUE if this model is hidden, FALSE otherwise
	 */
	public function isHidden() {
		return $this->getAsBoolean('hidden');
	}

	/**
	 * Marks this model as hidden.
	 */
	public function markAsHidden() {
		$this->setAsBoolean('hidden', TRUE);
	}

	/**
	 * Marks this model as visible (= not hidden).
	 */
	public function markAsVisible() {
		$this->setAsBoolean('hidden', FALSE);
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
	 * @return boolean TRUE if this model has a loading callback function set,
	 *                 FALSE otherwise
	 */
	private function hasLoadCallBack() {
		return !empty($this->loadCallback);
	}

	/**
	 * Marks this model's data as clean.
	 */
	public function markAsClean() {
		$this->isDirty = FALSE;
	}

	/**
	 * Marks this model's data as dirty.
	 */
	public function markAsDirty() {
		$this->isDirty = TRUE;
	}

	/**
	 * Checks whether this model has been marked as dirty which means that this
	 * model's data has changed compared to the initial state.
	 *
	 * @return boolean TRUE if this model has been marked as dirty
	 */
	public function isDirty() {
		return $this->isDirty;
	}

	/**
	 * Sets the "deleted" property for the current model.
	 */
	protected function setToDeleted() {
		if ($this->isLoaded()) {
			$this->data['deleted'] = TRUE;
			$this->markAsDirty();
		} else {
			$this->markAsDead();
		}
	}

	/**
	 * Checks whether this model is set to deleted.
	 *
	 * @return boolean TRUE if this model is set to deleted, FALSE otherwise
	 */
	public function isDeleted() {
		return $this->getAsBoolean('deleted');
	}

	/**
	 * Checks whether this model is read-only.
	 *
	 * @return boolean TRUE if this model is read-only, FALSE if it is writable
	 */
	public function isReadOnly() {
		return $this->readOnly;
	}

	/**
	 * Sets the the modification date and time.
	 */
	public function setTimestamp() {
		$this->setAsInteger('tstamp', $GLOBALS['SIM_EXEC_TIME']);
	}

	/**
	 * Sets the the creation date and time.
	 */
	public function setCreationDate() {
		if ($this->hasUid()) {
			throw new Exception(
				'Only new objects (without UID) may receive "crdate".'
			);
		}

		$this->setAsInteger('crdate', $GLOBALS['SIM_EXEC_TIME']);
	}

	/**
	 * Returns the page UID of this model.
	 *
	 * @return integer the page UID of this model, will be >= 0
	 */
	public function getPageUid() {
		return $this->getAsInteger('pid');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Model.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Model.php']);
}
?>