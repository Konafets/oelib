<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_oelib_Model_Currency' for the 'oelib' extension.
 *
 * This class represents a currency.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_Model_Currency extends tx_oelib_Model {
	/**
	 * @var boolean whether this model is read-only
	 */
	protected $readOnly = TRUE;

	/**
	 * Returns the ISO 4217 alpha-3 code for this currency.
	 *
	 * @return string the ISO 4217 alpha-3 code of this currency, will not be
	 *                empty
	 */
	public function getIsoAlpha3Code() {
		return $this->getAsString('cu_iso_3');
	}

	/**
	 * Returns whether this currency has a left symbol.
	 *
	 * @return boolean TRUE if this currency has a left symbol, FALSE otherwise
	 */
	public function hasLeftSymbol() {
		return $this->hasString('cu_symbol_left');
	}

	/**
	 * Returns the left currency symbol.
	 *
	 * @return string the left currency symbol, will be empty if this currency
	 *                has no left symbol
	 */
	public function getLeftSymbol() {
		return $this->getAsString('cu_symbol_left');
	}

	/**
	 * Returns whether this currency has a right symbol.
	 *
	 * @return boolean TRUE if this currency has a right symbol, FALSE otherwise
	 */
	public function hasRightSymbol() {
		return $this->hasString('cu_symbol_right');
	}

	/**
	 * Returns the right currency symbol.
	 *
	 * @return string the right currency symbol, will be empty if this currency
	 *                has no right symbol
	 */
	public function getRightSymbol() {
		return $this->getAsString('cu_symbol_right');
	}

	/**
	 * Returns the thousands separator.
	 *
	 * @return string the thousands separator, will not be empty
	 */
	public function getThousandsSeparator() {
		return $this->getAsString('cu_thousands_point');
	}

	/**
	 * Returns the decimal separator.
	 *
	 * @return string the decimal separator, will not be empty
	 */
	public function getDecimalSeparator() {
		return $this->getAsString('cu_decimal_point');
	}

	/**
	 * Returns the number of decimal digits.
	 *
	 * @return integer the number of decimal digits, will be >= 0
	 */
	public function getDecimalDigits() {
		return $this->getAsInteger('cu_decimal_digits');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_Currency.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_Currency.php']);
}
?>