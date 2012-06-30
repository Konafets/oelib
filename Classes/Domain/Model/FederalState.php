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
	protected $isoAlphaTwoCode = '';

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
	 * Gets the ISO 3166 alpha-2 code of this federal state, e.g., "NW".
	 *
	 * @return string
	 *         the ISO 3166 alpha-2 code, will not be empty for proper models from the database
	 */
	public function getIsoAlphaTwoCode() {
		return $this->isoAlphaTwoCode;
	}

	/**
	 * Sets the ISO 3166 alpha-2 code of this federal state, e.g., "NW".
	 *
	 * @param string $code
	 *        the 3166 ISO alpha-2 code, must not be empty
	 *
	 * @return void
	 */
	public function setIsoAlphaTwoCode($code) {
		$this->isoAlphaTwoCode = $code;
	}
}
?>