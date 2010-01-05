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
 * Interface 'tx_oelib_Interface_LoginManager' for the 'oelib' extension.
 *
 * This interface represents a manager for logins, providing access to the
 * logged-in user.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_oelib_Interface_LoginManager {
	/**
	 * Returns an instance of this class.
	 *
	 * @return tx_oelib_Interface_LoginManager the current Singleton instance
	 */
	public static function getInstance();

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 */
	public static function purgeInstance();

	/**
	 * Checks whether a user is logged in.
	 *
	 * @return boolean true if a user is logged in, false otherwise
	 */
	public function isLoggedIn();

	/**
	 * Gets the currently logged-in user.
	 *
	 * @param string the name of the mapper to use for getting the user model,
	 *               must not be empty
	 *
	 * @return tx_oelib_Model the logged-in user, will be null if no user is
	 *                        logged in
	 */
	public function getLoggedInUser($mapperName = '');
}
?>