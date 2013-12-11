<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Saskia Metzler <saskia@merlin.owl.de>
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
 * This class stores HTTP header which were meant to be sent instead of really
 * sending them and provides various functions to get them for testing purposes.
 *
 * Regarding the Strategy pattern, addHeader() represents one concrete behavior.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_HeaderCollector extends Tx_Oelib_AbstractHeaderProxy {
	/** headers which were meant to be sent */
	private $headers = array();

	/**
	 * Stores a HTTP header which was meant to be sent.
	 *
	 * @param string $header HTTP header to send, must not be empty
	 *
	 * @return void
	 */
	public function addHeader($header) {
		$this->headers[] = $header;
	}

	/**
	 * Returns the last header or an empty string if there are none.
	 *
	 * @return string last header, will be empty if there are none
	 */
	public function getLastAddedHeader() {
		if (empty($this->headers)) {
			return '';
		}

		return end($this->headers);
	}

	/**
	 * Returns all headers added with this instance or an empty array if there
	 * is none.
	 *
	 * @return array all added headers, will be empty if there is none
	 */
	public function getAllAddedHeaders() {
		return $this->headers;
	}
}