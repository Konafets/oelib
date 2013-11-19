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
 * Test case.
 *
 * This class represents a view helper for formatting a price.
 *
 * The value (setValue()) and the currency (setCurrencyFromIsoAlpha3Code())
 * should be set before calling render(). You can use the same instance of this
 * view helper to render different values in the same currency by changing the
 * value via setValue().
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_ViewHelper_Price {
	/**
	 * @var float the value of the price to render
	 */
	protected $value = 0.000;

	/**
	 * @var Tx_Oelib_Model_Currency the currency of the price to render
	 */
	protected $currency = NULL;

	/**
	 * Sets the value of the price to render.
	 *
	 * @param float $value
	 *        the value of the price to render, may be negative, positive or zero
	 *
	 * @return void
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Sets the currency of the price to render based on the currency's ISO
	 * alpha 3 code, e.g. "EUR" for Euro, "USD" for US dollars.
	 *
	 * @param string $isoAlpha3Code
	 *        the ISO alpha 3 code of the currency to set, must not be empty
	 *
	 * @return void
	 */
	public function setCurrencyFromIsoAlpha3Code($isoAlpha3Code) {
		try {
			$this->currency = Tx_Oelib_MapperRegistry::
				get('tx_oelib_Mapper_Currency')->findByIsoAlpha3Code($isoAlpha3Code);
		} catch (Exception $exception) {
			$this->currency = NULL;
		}
	}

	/**
	 * Renders the price based on $this->value and $this->currency.
	 *
	 * Please call setCurrencyFromIsoAlpha3Code() prior to calling render().
	 *
	 * If this function is called without setting a currency first, it will
	 * use some default rendering for the price.
	 *
	 * @return string the rendered price
	 */
	public function render() {
		if (!$this->currency) {
			return number_format($this->value, 2, '.', '');
		}

		$result = '';

		if ($this->currency->hasLeftSymbol()) {
			$result .= $this->currency->getLeftSymbol() . ' ';
		}

		$result .= number_format(
			$this->value,
			$this->currency->getDecimalDigits(),
			$this->currency->getDecimalSeparator(),
			$this->currency->getThousandsSeparator()
		);

		if ($this->currency->hasRightSymbol()) {
			$result .= ' ' . $this->currency->getRightSymbol();
		}

		return $result;
	}
}
?>