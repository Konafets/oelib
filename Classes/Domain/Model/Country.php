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
 * This class represents a country.
 *
 * @deprecated Do not use this class. It will be copied to the static_info_tables extension and then removed.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Domain_Model_Country extends Tx_Extbase_DomainObject_AbstractEntity {
	/**
	 * @var string
	 */
	protected $localShortName = '';

	/**
	 * @var string
	 */
	protected $localOfficialName = '';

	/**
	 * @var string
	 */
	protected $englishShortName = '';

	/**
	 * @var string
	 */
	protected $englishOfficialName = '';

	/**
	 * @var string
	 */
	protected $isoAlphaTwoCode = '';

	/**
	 * @var string
	 */
	protected $isoAlphaThreeCode = '';

	/**
	 * Gets the local short name of this country, e.g., "Deutschland".
	 *
	 * @return string
	 *         the local short name, will not be empty for proper models from
	 *         the database
	 */
	public function getLocalShortName() {
		return $this->localShortName;
	}

	/**
	 * Sets the local short name, e.g., "Deutschland".
	 *
	 * @param string $localShortName
	 *        the local short name, must not be empty
	 *
	 * @return void
	 */
	public function setLocalShortName($localShortName) {
		$this->localShortName = $localShortName;
	}

	/**
	 * Gets the local official name of this country, e.g.,
	 * "Bundesrepublik Deutschland".
	 *
	 * @return string
	 *         the local official name, will not be empty for proper models from
	 *         the database
	 */
	public function getLocalOfficialName() {
		return $this->localOfficialName;
	}

	/**
	 * Sets the local official name, e.g., "Bundesrepublik Deutschland".
	 *
	 * @param string $localOfficialName
	 *        the local official name, must not be empty
	 *
	 * @return void
	 */
	public function setLocalOfficialName($localOfficialName) {
		$this->localOfficialName = $localOfficialName;
	}

	/**
	 * Gets the english short name of this country, e.g., "Germany".
	 *
	 * @return string
	 *         the english short name, will not be empty for proper models from
	 *         the database
	 */
	public function getEnglishShortName() {
		return $this->englishShortName;
	}

	/**
	 * Sets the english short name, e.g., "Germany".
	 *
	 * @param string $englishShortName
	 *        the english short name, must not be empty
	 *
	 * @return void
	 */
	public function setEnglishShortName($englishShortName) {
		$this->englishShortName = $englishShortName;
	}

	/**
	 * Gets the english official name of this country, e.g.,
	 * "Federal Republic of Germany".
	 *
	 * @return string
	 *         the english official name, will not be empty for proper models from
	 *         the database
	 */
	public function getEnglishOfficialName() {
		return $this->englishOfficialName;
	}

	/**
	 * Sets the english official name, e.g.,
	 * "Federal Republic of Germany".
	 *
	 * @param string $englishOfficialName
	 *        the english official name, must not be empty
	 *
	 * @return void
	 */
	public function setEnglishOfficialName($englishOfficialName) {
		$this->englishOfficialName = $englishOfficialName;
	}

	/**
	 * Gets the ISO 3166 alpha-2 code of this country, e.g., "DE".
	 *
	 * @return string
	 *         the ISO 3166 alpha-2 code, will not be empty for proper models
	 *         from the database
	 */
	public function getIsoAlphaTwoCode() {
		return $this->isoAlphaTwoCode;
	}

	/**
	 * Sets the ISO 3166 alpha-2 code of this country, e.g., "DE".
	 *
	 * @param string $code
	 *        the 3166 ISO alpha-2 code, must not be empty
	 *
	 * @return void
	 */
	public function setIsoAlphaTwoCode($code) {
		$this->isoAlphaTwoCode = $code;
	}

	/**
	 * Gets the ISO 3166 alpha-3 code of this country, e.g., "DEU".
	 *
	 * @return string
	 *         the ISO 3166 alpha-3 code, will not be empty for proper models
	 *         from the database
	 */
	public function getIsoAlphaThreeCode() {
		return $this->isoAlphaThreeCode;
	}

	/**
	 * Sets the ISO 3166 alpha-3 code of this country, e.g., "DEU".
	 *
	 * @param string $code
	 *        the ISO 3166 alpha-3 code, must not be empty
	 *
	 * @return void
	 */
	public function setIsoAlphaThreeCode($code) {
		$this->isoAlphaThreeCode = $code;
	}
}