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
 * This model represents a federal state, e.g., Nordrhein-Westfalen (in Germany).
 *
 * @deprecated Do not use this class. It will be copied to the static_info_tables extension and then removed.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Domain_Model_FederalState extends Tx_Extbase_DomainObject_AbstractEntity {
	/**
	 * @var string
	 */
	protected $localName = '';

	/**
	 * @var string
	 */
	protected $englishName = '';

	/**
	 * @var string
	 */
	protected $isoCountryCode = '';

	/**
	 * @var string
	 */
	protected $isoZoneCode = '';

	/**
	 * Gets the local name of this federal state, e.g., "Nordrhein-Westfalen".
	 *
	 * @return string
	 *         the local name, will not be empty for proper models from the database
	 */
	public function getLocalName() {
		return $this->localName;
	}

	/**
	 * Sets the local name, e.g., "Nordrhein-Westfalen".
	 *
	 * @param string $localName
	 *        the local name, must not be empty
	 *
	 * @return void
	 */
	public function setLocalName($localName) {
		$this->localName = $localName;
	}

	/**
	 * Gets the english name of this federal state, e.g., "North Rhine-Westphalia".
	 *
	 * @return string
	 *         the english name, will not be empty for proper models from the database
	 */
	public function getEnglishName() {
		return $this->englishName;
	}

	/**
	 * Sets the english name, e.g., "North Rhine-Westphalia".
	 *
	 * @param string $englishName
	 *        the english name, must not be empty
	 *
	 * @return void
	 */
	public function setEnglishName($englishName) {
		$this->englishName = $englishName;
	}

	/**
	 * Gets the ISO 3166-1 code (country code) of this federal state, e.g., "DE".
	 *
	 * @return string
	 *         the ISO 3166-1 code (country code), will not be empty for proper models from the database
	 */
	public function getIsoCountryCode() {
		return $this->isoCountryCode;
	}

	/**
	 * Sets the ISO 3166-1 code (country code) of this federal state, e.g., "DE".
	 *
	 * @param string $code
	 *        the ISO 3166-1 code (country code), must not be empty
	 *
	 * @return void
	 */
	public function setIsoCountryCode($code) {
		$this->isoCountryCode = $code;
	}

	/**
	 * Gets the ISO 3166-2 code (country subdivision) of this federal state, e.g., "NW".
	 *
	 * @return string
	 *         the ISO 3166-2 code (country subdivision), will not be empty for proper models from the database
	 */
	public function getIsoZoneCode() {
		return $this->isoZoneCode;
	}

	/**
	 * Sets the ISO 3166-2 code (country subdivision) of this federal state, e.g., "NW".
	 *
	 * @param string $code
	 *        the 3166-2 ISO code (country subdivision), must not be empty
	 *
	 * @return void
	 */
	public function setIsoZoneCode($code) {
		$this->isoZoneCode = $code;
	}
}