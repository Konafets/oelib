<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Saskia Metzler <saskia@merlin.owl.de>
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
 * Class 'tx_oelib_realMailer' for the 'oelib' extension.
 * Regarding the Strategy pattern, sendEmail() represents one concrete behavior.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_oelib_realMailer extends tx_oelib_abstractMailer {
	/**
	 * Sends a plain-text e-mail.
	 *
	 * Note: This function cannot handle multi-part e-mails.
	 *
	 * Note: This function always will return true. After this extension
	 * requires TYPO3 4.2, it can be changed to return the success status of the
	 * e-mail (bug 1636).
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1636
	 *
	 * @param string the recipient's e-mail address, will not be
	 *               validated, must not be empty
	 * @param string e-mail subject, must not be empty
	 * @param string message to send, must not be empty
	 * @param string headers, separated by linefeed, may be empty
	 * @param string encoding type: "quoted-printable" or "8bit"
	 * @param string charset to use for encoding headers (only if
	 *               $encodingType is set to a valid value which produces
	 *               such a header)
	 * @param boolean if set, the header content will not be encoded
	 *
	 * @return boolean always true
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
		t3lib_div::plainMailEncoded(
			$emailAddress,
			$subject,
			$this->formatEmailBody($message),
			$headers,
			$encodingType,
			$charset,
			$doNotEncodeHeader
		);

		return true;
	}

	/**
	 * Sends an e-mail.
	 *
	 * This function can handle plain-text and multi-part e-mails.
	 *
	 * @param string $emailAddress
	 *        the recipient's e-mail address, will not be validated, must not be
	 *        empty
	 * @param string $subject e-mail subject, must not be empty
	 * @param string $message message to send, must not be empty
	 * @param string $headers headers, separated by linefeed, may be empty
	 * @param string $additionalParameters
	 *        additional parameters to pass to the mail program as command line
	 *        arguments
	 *
	 * @return boolean true if the e-mail was sent, false otherwise
	 */
	public function mail(
		$emailAddress, $subject, $message, $headers = '',
		$additionalParameters = ''
	) {
		$this->checkParameters($emailAddress, $subject, $message);

		if (!ini_get('safe_mode')) {
			return @mail(
				$emailAddress, $subject, $message, $headers,
				$additionalParameters
			);
		} else {
			return @mail($emailAddress, $subject, $message, $headers);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_realMailer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_realMailer.php']);
}
?>