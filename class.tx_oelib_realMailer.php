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
 * Class 'tx_oelib_realMailer' for the 'oelib' extension.
 * Regarding the Strategy pattern, sendEmail() represents one concrete behavior.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 */
require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_abstractMailer.php');

class tx_oelib_realMailer extends tx_oelib_abstractMailer {
	/**
	 * Sends an e-mail.
	 *
	 * @param	string		the recipient's e-mail address, will not be
	 * 						validated, must not be empty
	 * @param	string		e-mail subject, must not be empty
	 * @param	string		message to send, must not be empty
	 * @param	string		headers, separated by linefeed, may be empty
	 * @param	string		encoding type: "base64", "quoted-printable" or "8bit"
	 * @param	string		charset to use for encoding headers (only if
	 * 						$encodingType is set to a valid value which produces
	 * 						such a header)
	 * @param	boolean		if set, the header content will not be encoded
	 *
	 * @return	boolean		true if the e-mail was sent, false otherwise
	 */
	public function sendEmail(
		$emailAddress,
		$subject,
		$message,
		$headers = '',
		$encodingType = '',
		$charset = '',
		$doNotEncodeHeader = false
	) {
		return (boolean) t3lib_div::plainMailEncoded(
			$emailAddress,
			$subject,
			$message,
			$headers,
			$encodingType,
			$charset,
			$doNotEncodeHeader
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_realMailer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_realMailer.php']);
}
?>
