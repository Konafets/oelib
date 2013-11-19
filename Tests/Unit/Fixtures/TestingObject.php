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
 * This class represents an object for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class Tx_Oelib_testingObject extends Tx_Oelib_PublicObject {
	/**
	 * @var array the data for this object
	 */
	private $data = array();

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->data);
	}

	/**
	 * Sets the data of this object.
	 *
	 * @param array $data the data to set, may be empty
	 *
	 * @return void
	 */
	public function setData(array $data) {
		$this->data = $data;
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param string $key
	 *        the key of the data item to get, must not be empty
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
	 * @param string $key
	 *        the key of the data item to get, must not be empty
	 * @param mixed $value
	 *        the data for the key $key
	 *
	 * @return void
	 */
	protected function set($key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * Checks that $key is not empty.
	 *
	 * @throws InvalidArgumentException if $key is empty
	 *
	 * @param string $key
	 *        a key to check
	 *
	 * @return void
	 */
	public function checkForNonEmptyKey($key) {
		parent::checkForNonEmptyKey($key);
	}
}
?>