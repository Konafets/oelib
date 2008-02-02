<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Saskia Metzler <saskia@merlin.owl.de> All rights reserved
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
 * Abstract class 'tx_oelib_abstractMailer' for the 'oelib' extension.
 * This class declares the function sendEmail() for its inheritants. So they
 * need to implement the concrete behavior.
 * Regarding the Strategy pattern, sendEmail() represents the abstract strategy.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 */
abstract class tx_oelib_abstractMailer {
	/**
	 * This function usually should send e-mails.
	 *
	 * @param	string		the recipient's e-mail address, will not be
	 * 						validated, must not be empty
	 * @param	string		e-mail subject, must not be empty
	 * @param	string		message to send, must not be empty
	 *
	 * @return	boolean		true if the e-mail was sent, false otherwise
	 */
	public abstract function sendEmail($emailAddress, $subject, $message);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_abstractMailer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_abstractMailer.php']);
}
?>
