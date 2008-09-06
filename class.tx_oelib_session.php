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

/**
 * Class 'tx_oelib_session' for the 'oelib' extension.
 *
 * This Singleton class represents a session and its data.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_session {
	/**
	 * @var	integer		session type for persistent data that is stored for the
	 * 					logged-in front-end user and will be available when the
	 * 					user logs in again
	 */
	const TYPE_USER = 1;

	/*
	 * @var	integer		session type for volatile data that will be deleted when
	 * 					the session cookie is dropped (when the browser is
	 * 					closed)
	 */
	const TYPE_TEMPORARY = 2;

	/** @var	array		available type codes for the FE session functions */
	private static $types = array(
		self::TYPE_USER => 'user',
		self::TYPE_TEMPORARY => 'ses',
	);

	/**
	 * @var	integer		the type of this session (::TYPE_USER or
	 * 					::TYPE_TEMPORARY)
	 */
	private $type;

	/** @var	array		the instances, using the type as key */
	private static $instances = array();

	/**
	 * The constructor.
	 *
	 * @throws	Exception	if there is no front end
	 *
	 * @param	integer		the type of the session to use; either ::TYPE_USER
	 * 						or ::TYPE_TEMPORARY
	 */
	protected function __construct($type) {
		if (!($GLOBALS['TSFE'] instanceof tslib_fe)) {
			throw new Exception(
				'This class must not be instantiated when there is no front ' .
				'end.'
			);
		}

		self::checkType($type);
		$this->type = $type;
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @param	integer		the type of the session to use; either TYPE_USER
	 * 						(persistent) or TYPE_TEMPORARY (only for the lifetime
	 * 						of the session cookie)
	 *
	 * @return	tx_oelib_session	the current Singleton instance for the given
	 * 								type
	 */
	public static function getInstance($type) {
		self::checkType($type);

		if (!isset(self::$instances[$type])) {
			self::$instances[$type] = new tx_oelib_session($type);
		}

		return self::$instances[$type];
	}

	/**
	 * Sets the instance for the given type.
	 *
	 * @param	integer				the type to set, must be either TYPE_USER or
	 * 								TYPE_TEMPORARY
	 * @param	tx_oelib_session	the instance to set
	 */
	public function setInstance($type, tx_oelib_session $instance) {
		self::checkType($type);

		self::$instances[$type] = $instance;
	}

	/**
	 * Checks that a type ID is valid.
	 *
	 * @throws	Exception	if $type is neither ::TYPE_USER nor ::TYPE_TEMPORARY
	 *
	 * @param	integer		the type ID to check
	 */
	protected static function checkType($type) {
		if (($type != self::TYPE_USER) && ($type != self::TYPE_TEMPORARY)) {
			throw new Exception(
				'Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.'
			);
		}
	}

	/**
	 * Purges the instances of all types so that getInstance will create new
	 * instances.
	 */
	public static function purgeInstances() {
		self::$instances = array();
	}

	/**
	 * Checks that $key is not empty.
	 *
	 * @throws	Exception	if $key is empty
	 *
	 * @param	string		a key to check
	 */
	protected function checkForNonEmptyKey($key) {
		if ($key == '') {
			throw new Exception('$key must not be empty.');
		}
	}

	/**
	 * Gets the value stored in this session under the key $key, converted to a
	 * string.
	 *
	 * @param	string		the key of the element to retrieve, must not be
	 * 						empty
	 *
	 * @return	string		the string value of the given key, may be empty
	 */
	public function getAsString($key) {
		return trim((string) $this->get($key));
	}

	/**
	 * Sets a string value for the key $key in this session.
	 *
	 * @param	string		the key of the element to set, must not be empty
	 * @param	string		the value to set, may be empty
	 */
	public function setAsString($key, $value) {
		$this->set($key, (string) $value);
	}

	/**
	 * Gets the value stored in this session under the key $key, converted to an
	 * array of trimmed strings.
	 *
	 * @param	string		the key of the element to retrieve, must not be
	 * 						empty
	 *
	 * @return	array		the array value of the given key, may be empty
	 */
	public function getAsTrimmedArray($key) {
		$stringValue = $this->getAsString($key);

		if ($stringValue == '') {
			return array();
		}

		return t3lib_div::trimExplode(',', $stringValue);
	}

	/**
	 * Gets the value stored in this session under the key $key, converted to an
	 * array of integer.
	 *
	 * @param	string		the key of the element to retrieve, must not be
	 * 						empty
	 *
	 * @return	array		the array value of the given key, may be empty
	 */
	public function getAsIntegerArray($key) {
		$stringValue = $this->getAsString($key);

		if ($stringValue == '') {
			return array();
		}

		return t3lib_div::intExplode(',', $stringValue);
	}

	/**
	 * Sets an array value for the key $key in this session.
	 *
	 * Note: This function is intended for data that does not contain any
	 * commas. Commas in the array elements cause getAsTrimmedArray and
	 * getAsIntegerArray to split that element at the comma. This is a known
	 * limitation.
	 *
	 * @param	string		the key of the element to set, must not be empty
	 * @param	array		the value to set, may be empty
	 *
	 * @see	getAsTrimmedArray
	 * @see	getAsIntegerArray
	 */
	public function setAsArray($key, array $value) {
		$this->setAsString($key, implode(',', $value));
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param	string		the key of the data item to get, must not be empty
	 *
	 * @return	mixed		the data for the key $key, will be an empty string
	 * 						if the key has not been set yet
	 */
	protected function get($key) {
		$this->checkForNonEmptyKey($key);

		return $GLOBALS['TSFE']->fe_user->getKey(
			self::$types[$this->type],
			$key
		);
	}

	/**
	 * Sets the value of the data item for the key $key.
	 *
	 * @param	string		the key of the data item to get, must not be empty
	 *
	 * @param	mixed		the data for the key $key
	 */
	protected function set($key, $value) {
		$this->checkForNonEmptyKey($key);

		$GLOBALS['TSFE']->fe_user->setKey(
			self::$types[$this->type],
			$key,
			$value
		);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_session.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_session.php']);
}
?>