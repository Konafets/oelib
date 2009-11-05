<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_oelib_Double3Validator' for the 'oelib' extension.
 *
 * This class represents a validator for a float number with three decimal
 * digits. It is called by TCEmain.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_Double3Validator {
	/**
	 * Returns the JavaScript for the validation.
	 *
	 * @return string the JavaScript for the validation
	 */
	public function returnFieldJS() {
		$result = 'var theVal = "" + value;';
		$result .= 'theVal = theVal.replace(/[^0-9,\.-]/g, "");';
		$result .= 'var negative = theVal.substring(0, 1) === \'-\';';
		$result .= 'theVal = theVal.replace(/-/g, "");';
		$result .= 'theVal = theVal.replace(/,/g, ".");';
		$result .= 'if (theVal.indexOf(".") == -1) {';
		$result .= '	theVal += ".0";';
		$result .= '}';
		$result .= 'var parts = theVal.split(".");';
		$result .= 'var dec = parts.pop();';
		$result .= 'theVal = Number(parts.join("") + "." + dec);';
		$result .= 'if (negative) {';
		$result .= '	theVal *= -1;';
		$result .= '}';
		$result .= 'theVal = theVal.toFixed(3);';
		$result .= 'return theVal;';

		return $result;
	}

	/**
	 * Cleans an incoming value so it is a float with 3 decimal digits.
	 *
	 * @param string $value the incoming value to validate, may be empty
	 *
	 * @return float the cleaned float value with 3 decimal digits
	 */
	public function evaluateFieldValue($value) {
		$cleanValue = preg_replace('/[^0-9,\.-]/', '', $value);
		$isNegative = (substr($cleanValue, 0, 1) == '-');
		$veryCleanValue = strtr($cleanValue, array(',' => '.', '-' => ''));
		if (strpos($veryCleanValue, '.') === false) {
			$veryCleanValue .= '.0';
		}
		$valueParts = explode('.', $veryCleanValue);
		$decimalDigits = array_pop($valueParts);
		$result = implode('', $valueParts) . '.' . $decimalDigits;
		if ($isNegative) {
			$result *= -1;
		}
		return number_format($result, 3, '.', '');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Double3Validator.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Double3Validator.php']);
}
?>