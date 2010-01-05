<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_oelib_Model_Country' for the 'oelib' extension.
 *
 * This class represents a country.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_Model_Country extends tx_oelib_Model {
	/**
	 * @var boolean whether this model is read-only
	 */
	protected $readOnly = true;

	/**
	 * Returns the country's local short name.
	 *
	 * @return string the country's local short name, will not be empty
	 */
	public function getLocalShortName() {
		return $this->getAsString('cn_short_local');
	}

	/**
	 * Returns the ISO 3166-1 alpha-2 code for this country.
	 *
	 * @return string the ISO 3166-1 alpha-2 code of this country, will not be empty
	 */
	public function getIsoAlpha2Code() {
		return $this->getAsString('cn_iso_2');
	}

	/**
	 * Returns the ISO 3166-1 alpha-3 code for this country.
	 *
	 * @return string the ISO 3166-1 alpha-3 code of this country, will not be empty
	 */
	public function getIsoAlpha3Code() {
		return $this->getAsString('cn_iso_3');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_Country.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_Country.php']);
}
?>