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
 * This class represents a language.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Model_Language extends Tx_Oelib_Model {
	/**
	 * @var bool whether this model is read-only
	 */
	protected $readOnly = TRUE;

	/**
	 * Returns the language's local name.
	 *
	 * @return string the language's local name, will not be empty
	 */
	public function getLocalName() {
		return $this->getAsString('lg_name_local');
	}

	/**
	 * Returns the ISO 639-1 alpha-2 code for this language.
	 *
	 * @return string the ISO 639-1 alpha-2 code of this language, will not be empty
	 */
	public function getIsoAlpha2Code() {
		return $this->getAsString('lg_iso_2');
	}
}