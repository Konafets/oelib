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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_object.php');

/**
 * Class 'tx_oelib_model' for the 'oelib' extension.
 *
 * This class represents a general domain model.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_oelib_model extends tx_oelib_object {
	/**
	 * @var	array	the data for this object
	 */
	private $data = array();

	/**
	 * @var	boolean		whether this model has any data set
	 */
	private $hasData = false;

	/**
	 * The (empty) constructor.
	 *
	 * After instantiation, this model's data can be set via via setData() or
	 * set().
	 *
	 * @see	setData
	 * @see	set
	 */
	public function __construct() {
	}

	/**
	 * Sets the complete data for this model.
	 *
	 * This function should be called directly after instantiation and must only
	 * be called once.
	 *
	 * @param	array		the data for this model, may be empty
	 */
	public function setData(array $data) {
		if ($this->hasData) {
			throw new Exception(
				'setData must only be called once per model instance.'
			);
		}

		$this->data = $data;

		$this->hasData = true;
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * Before this function may be called, setData() or set() must have been
	 * called once.
	 *
	 * @param	string		the key of the data item to get, must not be empty
	 *
	 * @return	mixed		the data for the key $key, will be an empty string
	 * 						if the key has not been set yet
	 */
	protected function get($key) {
		if (!$this->hasData) {
			throw new Exception(
				'Please call setData() directly after instantiation first.'
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
	 * @param	string		the key of the data item to get, must not be empty
	 * @param	mixed		the data for the key $key
	 */
	protected function set($key, $value) {
		$this->data[$key] = $value;

		$this->hasData = true;
	}

	/**
	 * Gets this model's UIDs.
	 *
	 * @return	integer		this model's UID, will be zero if this model does
	 * 						not have a UID yet
	 */
	public function getUid() {
		return $this->getAsInteger('uid');
	}

	/**
	 * Checks whether this model has a UID.
	 *
	 * @return	boolean		true if this model has a non-zero UID, false
	 * 						otherwise
	 */
	public function hasUid() {
		return $this->hasInteger('uid');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_model.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_model.php']);
}
?>