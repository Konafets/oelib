<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_BackEndLoginManager' for the 'oelib' extension.
 *
 * This class represents a manager for back-end logins, providing access to the
 * logged-in user.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_BackEndLoginManager implements tx_oelib_Interface_LoginManager {
	/**
	 * @var tx_oelib_BackEndLoginManager the Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var tx_oelib_Model_BackEndUser a fake logged-in back-end user
	 */
	private $loggedInUser = null;

	/**
	 * The constructor. Use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		$this->loggedInUser = null;
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return tx_oelib_BackEndLoginManager the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_oelib_BackEndLoginManager();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 */
	public static function purgeInstance() {
		if (self::$instance) {
			self::$instance->__destruct();
		}
		self::$instance = null;
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
	 * @param string the name of the mapper to use for getting the back-end
	 *               user model, must not be empty
	 *
	 * @return tx_oelib_Model_BackEndUser the logged-in back-end user, will
	 *                                    be null if no user is logged in
	 */
	public function getLoggedInUser(
		$mapperName = 'tx_oelib_Mapper_BackEndUser'
	) {
		if ($mapperName == '') {
			throw new Exception('$mapperName must not be empty.');
		}
		if (!$this->isLoggedIn()) {
			return null;
		}
		if ($this->loggedInUser) {
			return $this->loggedInUser;
		}

		return tx_oelib_MapperRegistry::get($mapperName)
			->find($GLOBALS['BE_USER']->user['uid']);
	}

	/**
	 * Sets the currently logged-in back-end user.
	 *
	 * This function is for testing purposes only!
	 *
	 * @param tx_oelib_Model_BackEndUser $loggedInUser
	 *        the fake logged-in back-end user
	 */
	public function setLoggedInUser(tx_oelib_Model_BackEndUser $loggedInUser) {
		$this->loggedInUser = $loggedInUser;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_BackEndLoginManager.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_BackEndLoginManager.php']);
}
?>