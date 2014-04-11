<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class represents a manager for back-end logins, providing access to the logged-in user.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_BackEndLoginManager implements tx_oelib_Interface_LoginManager {
	/**
	 * @var Tx_Oelib_BackEndLoginManager the Singleton instance
	 */
	private static $instance = NULL;

	/**
	 * @var Tx_Oelib_Model_BackEndUser a fake logged-in back-end user
	 */
	private $loggedInUser = NULL;

	/**
	 * The constructor. Use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		$this->loggedInUser = NULL;
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return Tx_Oelib_BackEndLoginManager the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new Tx_Oelib_BackEndLoginManager();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 *
	 * @return void
	 */
	public static function purgeInstance() {
		self::$instance = NULL;
	}

	/**
	 * Checks whether a back-end user is logged in.
	 *
	 * @return boolean TRUE if a back-end user is logged in, FALSE otherwise
	 */
	public function isLoggedIn() {
		if($this->loggedInUser) {
			return TRUE;
		}

		return isset($GLOBALS['BE_USER']) && is_object($GLOBALS['BE_USER']);
	}

	/**
	 * Gets the currently logged-in back-end user.
	 *
	 * @param string $mapperName
	 *        the name of the mapper to use for getting the back-end user model, must not be empty
	 *
	 * @return Tx_Oelib_Model_BackEndUser the logged-in back-end user, will
	 *                                    be NULL if no user is logged in
	 */
	public function getLoggedInUser($mapperName = 'tx_oelib_Mapper_BackEndUser') {
		if ($mapperName == '') {
			throw new InvalidArgumentException('$mapperName must not be empty.', 1331318483);
		}
		if (!$this->isLoggedIn()) {
			return NULL;
		}
		if ($this->loggedInUser) {
			return $this->loggedInUser;
		}

		return Tx_Oelib_MapperRegistry::get($mapperName)
			->find($GLOBALS['BE_USER']->user['uid']);
	}

	/**
	 * Sets the currently logged-in back-end user.
	 *
	 * This function is for testing purposes only!
	 *
	 * @param Tx_Oelib_Model_BackEndUser $loggedInUser
	 *        the fake logged-in back-end user
	 *
	 * @return void
	 */
	public function setLoggedInUser(Tx_Oelib_Model_BackEndUser $loggedInUser) {
		$this->loggedInUser = $loggedInUser;
	}
}