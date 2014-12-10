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
 * This class represents a validator for a float number with three decimal
 * digits. It is called by TCEmain.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Double3Validator {
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
		$isNegative = $cleanValue{0} === '-';
		$veryCleanValue = strtr($cleanValue, array(',' => '.', '-' => ''));
		if (strpos($veryCleanValue, '.') === FALSE) {
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