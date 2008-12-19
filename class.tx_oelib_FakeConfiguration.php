<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon <mail@niels-pardon.de>
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_PublicObject.php');

/**
 * Class 'tx_oelib_FakeConfiguration' for the 'oelib' extension.
 *
 * This class represents the fake configuration.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_FakeConfiguration extends tx_oelib_PublicObject {
	/**
	 * @var array the configuration data
	 */
	private $data = array();

	/**
	 * The constructor. Does nothing.
	 */
	public function __construct() {
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->data);
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param string the key of the data item to get, must not be empty
	 *
	 * @return mixed the data for the key $key, will be an empty string
	 *               if the key has not been set yet
	 */
	protected function get($key) {
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
	}

	/**
	 * Sets the complete data for this configuration.
	 *
	 * @param array the data for this configuration, may be empty
	 */
	public function setData(array $data) {
		$this->data = $data;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_FakeConfiguration.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_FakeConfiguration.php']);
}
?>