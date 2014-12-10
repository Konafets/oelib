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

/**
 * This interface represents a manager for logins, providing access to the logged-in user.
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
	 *
	 * @return void
	 */
	public static function purgeInstance();

	/**
	 * Checks whether a user is logged in.
	 *
	 * @return boolean TRUE if a user is logged in, FALSE otherwise
	 */
	public function isLoggedIn();

	/**
	 * Gets the currently logged-in user.
	 *
	 * @param string $mapperName
	 *        the name of the mapper to use for getting the user model, must not be empty
	 *
	 * @return Tx_Oelib_Model the logged-in user, will be NULL if no user is
	 *                        logged in
	 */
	public function getLoggedInUser($mapperName = '');
}