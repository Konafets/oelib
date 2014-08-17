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