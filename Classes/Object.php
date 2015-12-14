<?php
/*
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents an object that allows getting and setting its data,
 * but only via protected methods so that encapsulation is retained.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Oelib_Object {
	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param string $key the key of the data item to get, must not be empty
	 *
	 * @return mixed the data for the key $key, will be an empty string
	 *               if the key has not been set yet
	 */
	abstract protected function get($key);

	/**
	 * Sets the value of the data item for the key $key.
	 *
	 * @param string $key the key of the data item to get, must not be empty
	 * @param mixed $value the data for the key $key
	 *
	 * @return void
	 */
	abstract protected function set($key, $value);

	/**
	 * Checks that $key is not empty.
	 *
	 * @throws InvalidArgumentException if $key is empty
	 *
	 * @param string $key the key to check
	 *
	 * @return void
	 */
	protected function checkForNonEmptyKey($key) {
		if ($key === '') {
			throw new InvalidArgumentException('$key must not be empty.', 1331488963);
		}
	}

	/**
	 * Gets the value stored in under the key $key, converted to a string.
	 *
	 * @param string $key the key of the element to retrieve, must not be empty
	 *
	 * @return string the string value of the given key, may be empty
	 */
	protected function getAsString($key) {
		$this->checkForNonEmptyKey($key);

		return trim((string) $this->get($key));
	}

	/**
	 * Checks whether a non-empty string is stored under the key $key.
	 *
	 * @param string $key the key of the element to check, must not be empty
	 *
	 * @return bool TRUE if the value for the given key is non-empty,
	 *                 FALSE otherwise
	 */
	protected function hasString($key) {
		return ($this->getAsString($key) !== '');
	}

	/**
	 * Sets a value for the key $key (and converts it to a string).
	 *
	 * @param string $key the key of the element to set, must not be empty
	 * @param mixed $value the value to set, may be empty
	 *
	 * @return void
	 */
	protected function setAsString($key, $value) {
		$this->checkForNonEmptyKey($key);

		$this->set($key, (string) $value);
	}

	/**
	 * Gets the value stored in under the key $key, converted to an integer.
	 *
	 * @param string $key the key of the element to retrieve, must not be empty
	 *
	 * @return int the integer value of the given key, may be positive,
	 *                 negative or zero
	 */
	protected function getAsInteger($key) {
		$this->checkForNonEmptyKey($key);

		return (int)$this->get($key);
	}

	/**
	 * Checks whether a non-zero integer is stored under the key $key.
	 *
	 * @param string $key the key of the element to check, must not be empty
	 *
	 * @return bool TRUE if the value for the given key is non-zero,
	 *                 FALSE otherwise
	 */
	protected function hasInteger($key) {
		return ($this->getAsInteger($key) !== 0);
	}

	/**
	 * Sets a value for the key $key (and converts it to an integer).
	 *
	 * @param string $key the key of the element to set, must not be empty
	 * @param mixed $value the value to set, may be empty
	 *
	 * @return void
	 */
	protected function setAsInteger($key, $value) {
		$this->checkForNonEmptyKey($key);

		$this->set($key, (int)$value);
	}

	/**
	 * Gets the value stored in under the key $key, converted to an array of
	 * trimmed strings.
	 *
	 * @param string $key the key of the element to retrieve, must not be empty
	 *
	 * @return string[] the array value of the given key, may be empty
	 */
	protected function getAsTrimmedArray($key) {
		return GeneralUtility::trimExplode(',', $this->getAsString($key), TRUE);
	}

	/**
	 * Gets the value stored under the key $key, converted to an array of
	 * integers.
	 *
	 * @param string $key the key of the element to retrieve, must not be empty
	 *
	 * @return int[] the array value of the given key, may be empty
	 */
	protected function getAsIntegerArray($key) {
		$stringValue = $this->getAsString($key);

		if ($stringValue === '') {
			return array();
		}

		return GeneralUtility::intExplode(',', $stringValue);
	}

	/**
	 * Sets an array value for the key $key.
	 *
	 * Note: This function is intended for data that does not contain any
	 * commas. Commas in the array elements cause getAsTrimmedArray and
	 * getAsIntegerArray to split that element at the comma. This is a known
	 * limitation.
	 *
	 * @param string $key the key of the element to set, must not be empty
	 * @param array $value the value to set, may be empty
	 *
	 * @see getAsTrimmedArray
	 * @see getAsIntegerArray
	 *
	 * @return void
	 */
	protected function setAsArray($key, array $value) {
		$this->setAsString($key, implode(',', $value));
	}

	/**
	 * Gets the value stored in under the key $key, converted to a boolean.
	 *
	 * @param string $key the key of the element to retrieve, must not be empty
	 *
	 * @return bool the boolean value of the given key
	 */
	protected function getAsBoolean($key) {
		$this->checkForNonEmptyKey($key);

		return (bool)$this->get($key);
	}

	/**
	 * Sets a value for the key $key (and converts it to a boolean).
	 *
	 * @param string $key the key of the element to set, must not be empty
	 * @param mixed $value the value to set, may be empty
	 *
	 * @return void
	 */
	protected function setAsBoolean($key, $value) {
		$this->checkForNonEmptyKey($key);

		$this->set($key, (bool)$value);
	}

	/**
	 * Gets the value stored in under the key $key, converted to a float.
	 *
	 * @param string $key the key of the element to retrieve, must not be empty
	 *
	 * @return float the float value of the given key, may be positive,
	 *               negative or zero
	 */
	protected function getAsFloat($key) {
		$this->checkForNonEmptyKey($key);

		return (float) $this->get($key);
	}

	/**
	 * Checks whether a non-zero float is stored under the key $key.
	 *
	 * @param string $key the key of the element to check, must not be empty
	 *
	 * @return bool TRUE if the value for the given key is non-zero,
	 *                 FALSE otherwise
	 */
	protected function hasFloat($key) {
		return ($this->getAsFloat($key) !== 0.00);
	}

	/**
	 * Sets a value for the key $key (and converts it to a float).
	 *
	 * @param string $key the key of the element to set, must not be empty
	 * @param mixed $value the value to set, may be empty
	 *
	 * @return void
	 */
	protected function setAsFloat($key, $value) {
		$this->checkForNonEmptyKey($key);

		$this->set($key, (float) $value);
	}
}