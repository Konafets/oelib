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
 * This Singleton class represents a session and its data.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Session extends Tx_Oelib_PublicObject {
	/**
	 * @var integer session type for persistent data that is stored for the
	 *              logged-in front-end user and will be available when the
	 *              user logs in again
	 */
	const TYPE_USER = 1;

	/*
	 * @var integer session type for volatile data that will be deleted when
	 *              the session cookie is dropped (when the browser is closed)
	 */
	const TYPE_TEMPORARY = 2;

	/**
	 * @var array available type codes for the FE session functions
	 */
	private static $types = array(
		self::TYPE_USER => 'user',
		self::TYPE_TEMPORARY => 'ses',
	);

	/**
	 * @var integer the type of this session (::TYPE_USER or ::TYPE_TEMPORARY)
	 */
	private $type;

	/**
	 * @var array the instances, using the type as key
	 */
	private static $instances = array();

	/**
	 * The constructor. Use getInstance() instead.
	 *
	 * @throws BadMethodCallException if there is no front end
	 *
	 * @param integer $type the type of the session to use; either TYPE_USER or TYPE_TEMPORARY
	 */
	protected function __construct($type) {
		if (!($GLOBALS['TSFE'] instanceof tslib_fe)) {
			throw new BadMethodCallException('This class must not be instantiated when there is no front end.', 1331489053);
		}

		self::checkType($type);
		$this->type = $type;
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @param integer $type
	 *        the type of the session to use; either TYPE_USER (persistent)
	 *        or TYPE_TEMPORARY (only for the lifetime of the session cookie)
	 *
	 * @return Tx_Oelib_Session the current Singleton instance for the given
	 *                          type
	 */
	public static function getInstance($type) {
		self::checkType($type);

		if (!isset(self::$instances[$type])) {
			self::$instances[$type] = new Tx_Oelib_Session($type);
		}

		return self::$instances[$type];
	}

	/**
	 * Sets the instance for the given type.
	 *
	 * @param integer $type the type to set, must be either TYPE_USER or TYPE_TEMPORARY
	 * @param Tx_Oelib_Session $instance the instance to set
	 *
	 * @return void
	 */
	public static function setInstance($type, Tx_Oelib_Session $instance) {
		self::checkType($type);

		self::$instances[$type] = $instance;
	}

	/**
	 * Checks that a type ID is valid.
	 *
	 * @throws InvalidArgumentException if $type is neither ::TYPE_USER nor ::TYPE_TEMPORARY
	 *
	 * @param integer $type the type ID to check
	 *
	 * @return void
	 */
	protected static function checkType($type) {
		if (($type != self::TYPE_USER) && ($type != self::TYPE_TEMPORARY)) {
			throw new InvalidArgumentException('Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.', 1331489067);
		}
	}

	/**
	 * Purges the instances of all types so that getInstance will create new instances.
	 *
	 * @return void
	 */
	public static function purgeInstances() {
		foreach (self::$instances as $key => $instance) {
			$instance->__destruct();
			unset(self::$instances[$key]);
		}

		self::$instances = array();
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param string $key the key of the data item to get, must not be empty
	 *
	 * @return mixed the data for the key $key, will be an empty string
	 *               if the key has not been set yet
	 */
	protected function get($key) {
		return $GLOBALS['TSFE']->fe_user->getKey(
			self::$types[$this->type],
			$key
		);
	}

	/**
	 * Sets the value of the data item for the key $key.
	 *
	 * @param string $key the key of the data item to get, must not be empty
	 * @param mixed $value the data for the key $key
	 *
	 * @return void
	 */
	protected function set($key, $value) {
		$GLOBALS['TSFE']->fe_user->setKey(
			self::$types[$this->type],
			$key,
			$value
		);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}
}