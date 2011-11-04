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
 * Class 'tx_oelib_PublicObject' for the 'oelib' extension.
 *
 * This class represents an object that allows getting and setting its data
 * via public methods.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_oelib_PublicObject extends tx_oelib_Object {
	/**
	 * Gets the value stored in under the key $key, converted to a string.
	 *
	 * @param string the key of the element to retrieve, must not be empty
	 *
	 * @return string the string value of the given key, may be empty
	 */
	public function getAsString($key) {
		return parent::getAsString($key);
	}

	/**
	 * Checks whether a non-empty string is stored under the key $key.
	 *
	 * @param string the key of the element to check, must not be empty
	 *
	 * @return boolean TRUE if the value for the given key is non-empty,
	 *                 FALSE otherwise
	 */
	public function hasString($key) {
		return parent::hasString($key);
	}

	/**
	 * Sets a value for the key $key (and converts it to a string).
	 *
	 * @param string the key of the element to set, must not be empty
	 * @param mixed the value to set, may be empty
	 */
	public function setAsString($key, $value) {
		parent::setAsString($key, $value);
	}

	/**
	 * Gets the value stored in under the key $key, converted to an integer.
	 *
	 * @param string the key of the element to retrieve, must not be empty
	 *
	 * @return integer the integer value of the given key, may be positive,
	 *                 negative or zero
	 */
	public function getAsInteger($key) {
		return parent::getAsInteger($key);
	}

	/**
	 * Checks whether a non-zero integer is stored under the key $key.
	 *
	 * @param string the key of the element to check, must not be empty
	 *
	 * @return boolean TRUE if the value for the given key is non-zero,
	 *                 FALSE otherwise
	 */
	public function hasInteger($key) {
		return parent::hasInteger($key);
	}

	/**
	 * Sets a value for the key $key (and converts it to an integer).
	 *
	 * @param string the key of the element to set, must not be empty
	 * @param mixed the value to set, may be empty
	 */
	public function setAsInteger($key, $value) {
		parent::setAsInteger($key, $value);
	}

	/**
	 * Gets the value stored in under the key $key, converted to an array of
	 * trimmed strings.
	 *
	 * @param string the key of the element to retrieve, must not be empty
	 *
	 * @return array the array value of the given key, may be empty
	 */
	public function getAsTrimmedArray($key) {
		return parent::getAsTrimmedArray($key);
	}

	/**
	 * Gets the value stored under the key $key, converted to an array of
	 * integers.
	 *
	 * @param string the key of the element to retrieve, must not be empty
	 *
	 * @return array the array value of the given key, may be empty
	 */
	public function getAsIntegerArray($key) {
		return parent::getAsIntegerArray($key);
	}

	/**
	 * Sets an array value for the key $key.
	 *
	 * Note: This function is intended for data that does not contain any
	 * commas. Commas in the array elements cause getAsTrimmedArray and
	 * getAsIntegerArray to split that element at the comma. This is a known
	 * limitation.
	 *
	 * @param string the key of the element to set, must not be empty
	 * @param array the value to set, may be empty
	 *
	 * @see getAsTrimmedArray
	 * @see getAsIntegerArray
	 */
	public function setAsArray($key, array $value) {
		parent::setAsArray($key, $value);
	}

	/**
	 * Gets the value stored in under the key $key, converted to a boolean.
	 *
	 * @param string the key of the element to retrieve, must not be empty
	 *
	 * @return boolean the boolean value of the given key
	 */
	public function getAsBoolean($key) {
		return parent::getAsBoolean($key);
	}

	/**
	 * Sets a value for the key $key (and converts it to a boolean).
	 *
	 * @param string the key of the element to set, must not be empty
	 * @param mixed the value to set, may be empty
	 */
	public function setAsBoolean($key, $value) {
		parent::setAsBoolean($key, $value);
	}

	/**
	 * Gets the value stored in under the key $key, converted to a float.
	 *
	 * @param string the key of the element to retrieve, must not be empty
	 *
	 * @return float the float value of the given key, may be positive,
	 *               negative or zero
	 */
	public function getAsFloat($key) {
		return parent::getAsFloat($key);
	}

	/**
	 * Checks whether a non-zero float is stored under the key $key.
	 *
	 * @param string the key of the element to check, must not be empty
	 *
	 * @return boolean TRUE if the value for the given key is non-zero,
	 *                 FALSE otherwise
	 */
	public function hasFloat($key) {
		return parent::hasFloat($key);
	}

	/**
	 * Sets a value for the key $key (and converts it to a float).
	 *
	 * @param string the key of the element to set, must not be empty
	 * @param mixed the value to set, may be empty
	 */
	public function setAsFloat($key, $value) {
		parent::setAsFloat($key, $value);
	}
}
?>