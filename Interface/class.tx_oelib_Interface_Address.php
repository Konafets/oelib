<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Bernd Schönbach <bernd@oliverklee.de>
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
 * Interface 'tx_oelib_Interface_Address' for the 'oelib' extension.
 *
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
?>