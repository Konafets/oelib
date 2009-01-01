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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Class 'tx_oelib_Model' for the 'oelib' extension.
 *
 * This class represents a general domain model.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_oelib_Model extends tx_oelib_Object {
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
	 * @var boolean whether this model has any data set
	 */
	private $dataHasBeenSet = false;

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
		if ($this->dataHasBeenSet) {
			throw new Exception(
				'setData must only be called once per model instance.'
			);
		}

		$this->data = $data;
		if (isset($data['uid'])) {
			if ($data['uid'] > 0) {
				$this->setUid($data['uid']);
			}
			unset($data['uid']);
		}

		$this->dataHasBeenSet = true;
	}

	/**
	 * Sets this model's UID.
	 *
	 * @param integer the UID to set, must be > 0
	 */
	protected function setUid($uid) {
		if ($this->hasUid()) {
			throw new Exception(
				'The UID of a model cannot be set a second time.'
			);
		}

		$this->uid = $uid;
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
		if (!$this->dataHasBeenSet) {
			throw new Exception(
				'Please call setData() directly after instantiation first.'
			);
		}
		if ($key == 'uid') {
			throw new Exception(
				'The UID column needs to be accessed using the getUid function.'
			);
		}

		if (!isset($this->data[$key])) {
			return '';
		}

		return $this->data[$key];
	}

	/**
	 * Sets the value of the data item for the key $key.
	 *
	 * @param string the key of the data item to get, must not be empty
	 * @param mixed the data for the key $key
	 */
	protected function set($key, $value) {
		$this->data[$key] = $value;

		$this->dataHasBeenSet = true;
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Model.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Model.php']);
}
?>