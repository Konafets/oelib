<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Saskia Metzler <saskia@merlin.owl.de>
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
 * Abstract class 'tx_oelib_abstractHeaderProxy' for the 'oelib' extension.
 * This class declares the function addHeader() for its inheritants. So they
 * need to implement the concrete behavior.
 * Regarding the Strategy pattern, addHeader() represents the abstract strategy.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 */

abstract class tx_oelib_abstractHeaderProxy {
	/**
	 * This function usually should add a HTTP header.
	 *
	 * @param	string		HTTP header to send, e.g. 'Status: 404 Not Found',
	 * 						must not be empty
	 */
	public abstract function addHeader($header);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_abstractHeaderProxy.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_abstractHeaderProxy.php']);
}
?>
