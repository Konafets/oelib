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
 * This class sends e-mails.
 *
 * Regarding the Strategy pattern, sendEmail() represents one concrete behavior.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_RealMailer extends Tx_Oelib_AbstractMailer {
	/**
	 * Sends a plain-text e-mail.
	 *
	 * Note: This function cannot handle multi-part e-mails.
	 *
	 * @deprecated 2014-08-28 use send instead
	 *
	 * @param string $emailAddress the recipient's e-mail address, will not be validated, must not be empty
	 * @param string $subject e-mail subject, must not be empty
	 * @param string $message message to send, must not be empty
	 * @param string $headers headers, separated by linefeed, may be empty
	 * @param string $encodingType encoding type: "quoted-printable" or "8bit"
	 * @param string $charset
	 *        charset to use for encoding headers (only if $encodingType is set to a valid value which produces such a header)
	 * @param bool $doNotEncodeHeader if set, the header content will not be encoded
	 *
	 * @return bool TRUE if the e-mail was sent, FALSE otherwise
	 */
	public function sendEmail(
		$emailAddress, $subject, $message, $headers = '', $encodingType = '', $charset = '', $doNotEncodeHeader = FALSE
	) {
		t3lib_div::logDeprecatedFunction();

		return t3lib_div::plainMailEncoded(
			$emailAddress, $subject, $this->formatEmailBody($message), $headers, $encodingType, $charset, $doNotEncodeHeader
		);
	}

	/**
	 * Sends an e-mail.
	 *
	 * This function can handle plain-text and multi-part e-mails.
	 *
	 * @deprecated 2014-08-28 use send instead
	 *
	 * @param string $emailAddress
	 *        the recipient's e-mail address, will not be validated, must not be empty
	 * @param string $subject e-mail subject, must not be empty
	 * @param string $message message to send, must not be empty
	 * @param string $headers headers, separated by linefeed, may be empty
	 * @param string $additionalParameters
	 *        additional parameters to pass to the mail program as command line arguments
	 *
	 * @return bool TRUE if the e-mail was sent, FALSE otherwise
	 */
	public function mail($emailAddress, $subject, $message, $headers = '', $additionalParameters = '') {
		t3lib_div::logDeprecatedFunction();

		$this->checkParameters($emailAddress, $subject, $message);

		if (!ini_get('safe_mode')) {
			return @mail($emailAddress, $subject, $message, $headers, $additionalParameters);
		} else {
			return @mail($emailAddress, $subject, $message, $headers);
		}
	}

	/**
	 * Sends a Swift e-mail.
	 *
	 * @param t3lib_mail_Message $email the e-mail to send.
	 *
	 * @return void
	 */
	protected function sendSwiftMail(t3lib_mail_Message $email) {
		$email->send();
	}
}