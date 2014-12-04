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
 * This class represents a set of configuration options within a certain
 * namespace.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Configuration extends Tx_Oelib_PublicObject {
	/**
	 * @var array the data for this configuration
	 */
	private $data = array();

	/**
	 * The (empty) constructor.
	 *
	 * After instantiation, this configuration's data can be set via via
	 * setData() or set().
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
	 * Sets the complete data for this configuration.
	 *
	 * This function can be called multiple times.
	 *
	 * @param array $data
	 *        the data for this configuration, may be empty
	 *
	 * @return void
	 */
	public function setData(array $data) {
		$this->data = $data;
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
	public function set($key, $value) {
		if ($key === '') {
			throw new InvalidArgumentException('$key must not be empty.', 1331318809);
		}

		$this->data[$key] = $value;
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
		if (!$this->existsKey($key)) {
			return '';
		}

		return $this->data[$key];
	}

	/**
	 * Checks whether a data item with a certain key exists.
	 *
	 * @param string $key
	 *        the key of the data item to check, must not be empty
	 *
	 * @return boolean TRUE if a data item with the key $key exists, FALSE
	 *                 otherwise
	 */
	protected function existsKey($key) {
		return isset($this->data[$key]);
	}

	/**
	 * Returns the array keys of the data item for the key $key.
	 *
	 * If $key is an empty string the array keys of $this->data are returned.
	 *
	 * @param string $key
	 *        the key of the data item to get the array keys for, may be empty
	 *
	 * @return string[] the array keys of the data item for the key $key, may be empty
	 */
	public function getArrayKeys($key = '') {
		if ($key === '') {
			return array_keys($this->data);
		}

		if (!$this->existsKey($key) || !is_array($this->data[$key])) {
			return array();
		}

		return array_keys($this->data[$key]);
	}

	/**
	 * Returns the data for the key $key as a multidimensional array.
	 *
	 * The return value will be an empty array:
	 * - if the data item is an empty array,
	 * - if the data item is not an array,
	 * - if the key does not exist in $this->data.
	 *
	 * @param string $key
	 *        the key of the data item to get as a multidimensional array, must
	 *        not be empty
	 *
	 * @return array the data for the key $key as a multidimensional array, may
	 *               be empty
	 */
	public function getAsMultidimensionalArray($key) {
		if (!isset($this->data[$key]) || !is_array($this->data[$key])) {
			return array();
		}

		return $this->data[$key];
	}
}