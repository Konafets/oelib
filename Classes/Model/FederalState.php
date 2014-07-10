<?php
/***************************************************************
* Copyright notice
*
* (c) 2012 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This model represents a federal state, e.g., Nordrhein-Westfalen (in Germany).
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Model_FederalState extends Tx_Oelib_Model {
	/**
	 * @var boolean
	 */
	protected $readOnly = TRUE;

	/**
	 * Returns the local name, e.g., "Nordrhein-Westfalen".
	 *
	 * @return string the local name, will not be empty
	 */
	public function getLocalName() {
		return $this->getAsString('zn_name_local');
	}

	/**
	 * Returns the English name, e.g., "North Rhine-Westphalia".
	 *
	 * @return string the English name, will not be empty
	 */
	public function getEnglishName() {
		return $this->getAsString('zn_name_en');
	}

	/**
	 * Returns the ISO 3166-1 alpha-2 code, e.g., "DE".
	 *
	 * @return string the ISO 3166-1 alpha-2 code, will not be empty
	 */
	public function getIsoAlpha2Code() {
		return $this->getAsString('zn_country_iso_2');
	}

	/**
	 * Returns the ISO 3166-2 alpha-2 code, e.g., "NW".
	 *
	 * @return string the ISO 3166-2 alpha-2 code, will not be empty
	 */
	public function getIsoAlpha2ZoneCode() {
		return $this->getAsString('zn_code');
	}
}