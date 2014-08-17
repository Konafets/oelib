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
 * This interfaces represents a postal address.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
interface tx_oelib_Interface_Address {
	/**
	 * Returns the city of the current address.
	 *
	 * @return string the city of the current address, will be empty if no city
	 *                was set
	 */
	public function getCity();

	/**
	 * Returns the street of the current address.
	 *
	 * @return string the street of the current address, may be multi-line,
	 *                will be empty if no street was set
	 */
	public function getStreet();

	/**
	 * Returns the ZIP code of the current address
	 *
	 * @return string the ZIP code of the current address, will be empty if no
	 *                ZIP code was set
	 */
	public function getZip();

	/**
	 * Returns the homepage of the current address.
	 *
	 * @return string the homepage of the current address, will be empty if no
	 *                homepage was set
	 */
	public function getHomepage();

	/**
	 * Returns the telephone number of the current address.
	 *
	 * @return string the telephone number of the current address, will be empty
	 *                if no telephone number was set
	 */
	public function getPhoneNumber();
}