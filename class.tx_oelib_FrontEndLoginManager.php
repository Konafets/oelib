<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_FrontEndLoginManager' for the 'oelib' extension.
 *
 * This class represents a manager for front-end logins, providing access to the
 * logged-in user.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_FrontEndLoginManager implements tx_oelib_Interface_LoginManager {
	/**
	 * @var tx_oelib_FrontEndLoginManager the Singleton instance
	 */
	private static $instance = null;

	/**
	 * The constructor. Use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return tx_oelib_FrontEndLoginManager the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_oelib_FrontEndLoginManager();
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
	 * Checks whether any front-end user is logged in (and whether a front end
	 * exists at all).
	 *
	 * @return boolean true if a front end exists and a front-end user is logged
	 *                 in, false otherwise
	 */
	public function isLoggedIn() {
		return isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE']) &&
			is_array($GLOBALS['TSFE']->fe_user->user);
	}

	/**
	 * Gets the currently logged-in front-end user.
	 *
	 * @param string the name of the mapper to use for getting the front-end
	 *               user model, must not be empty
	 *
	 * @return tx_oelib_Model_FrontEndUser the logged-in front-end user, will
	 *                                     be null if no user is logged in or
	 *                                     if there is no front end
	 */
	public function getLoggedInUser(
		$mapperName = 'tx_oelib_Mapper_FrontEndUser'
	) {
		if ($mapperName == '') {
			throw new Exception('$mapperName must not be empty.');
		}
		if (!$this->isLoggedIn()) {
			return null;
		}

		return tx_oelib_MapperRegistry::get($mapperName)
			->find($GLOBALS['TSFE']->fe_user->user['uid']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_FrontEndLoginManager.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_FrontEndLoginManager.php']);
}
?>