<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Saskia Metzler <saskia@merlin.owl.de>
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

require_once(t3lib_extMgm::extPath('oelib') . 'contrib/PEAR/Mail/mime.php');

/**
 * Abstract class 'tx_oelib_abstractMailer' for the 'oelib' extension.
 * This class declares the function sendEmail() for its inheritants. So they
 * need to implement the concrete behavior.
 * Regarding the Strategy pattern, sendEmail() represents the abstract strategy.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
abstract class tx_oelib_abstractMailer {
	/**
	 * @var boolean whether an e-mail should be formatted before it is sent
	 */
	protected $enableFormatting = true;

	/**
	 * This function usually should send e-mails.
	 *
	 * @param string the recipient's e-mail address, will not be
	 *               validated, must not be empty
	 * @param string e-mail subject, must not be empty
	 * @param string message to send, must not be empty
	 * @param string headers, separated by linefeed, may be empty
	 * @param string encoding type: "base64", "quoted-printable" or "8bit"
	 * @param string charset to use for encoding headers (only if
	 *               $encodingType is set to a valid value which produces
	 *               such a header)
	 * @param boolean if set, the header content will not be encoded
	 *
	 * @return boolean true if the e-mail was sent, false otherwise
	 */
	public abstract function sendEmail(
		$emailAddress,
		$subject,
		$message,
		$headers = '',
		$encodingType = '',
		$charset = '',
		$doNotEncodeHeader = false
	);

	/**
	 * This function sends an e-mail.
	 *
	 * @param string the recipient's e-mail address, will not be
	 *               validated, must not be empty
	 * @param string e-mail subject, must not be empty
	 * @param string message to send, must not be empty
	 * @param string headers, separated by linefeed, may be empty
	 *
	 * @return boolean true if the e-mail was sent, false otherwise
	 */
	public abstract function mail($emailAddress, $subject, $message, $headers = '');

	/**
	 * Sends an tx_oelib_Mail object.
	 *
	 * @param tx_oelib_Mail the tx_oelib_Mail object to send
	 */
	public function send(tx_oelib_Mail $email) {
		if (!$email->hasSender()) {
			throw new Exception('$email must have a sender set.');
		}

		$mimeEMail = new Mail_mime();
		$mimeEMail->setFrom(
			$mimeEMail->encodeRecipients(
				$this->formatMailRole($email->getSender())
			)
		);
		$mimeEMail->setTXTBody($this->formatEmailBody($email->getMessage()));

		foreach ($email->getAttachments() as $attachment) {
			$mimeEMail->addAttachment(
				$attachment->getContent(),
				$attachment->getContentType(),
				$attachment->getFileName(),
				false,
				'base64'
			);
		}

		$characterSet = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ?
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'ISO-8859-1';

		$buildParameter = array(
			'text_encoding' => '8bit',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		foreach ($email->getRecipients() as $recipient) {
			$this->mail(
				$recipient->getEMailAddress(),
				$email->getSubject(),
				$mimeEMail->get($buildParameter),
				$mimeEMail->txtHeaders()
			);
		}
	}

	/**
	 * Sets whether the e-mail body should be formatted before sending the e-mail.
	 * Formatting will replace single linefeeds or carriage returns by carriage
	 * return plus linefeed and strip multiple blank lines.
	 *
	 * @param boolean true to enable formatting, false to disable
	 */
	public function sendFormattedEmails($enableFormatting) {
		$this->enableFormatting = $enableFormatting;
	}

	/**
	 * Formats the e-mail body if this is enabled.
	 *
	 * Replaces single linefeeds with carriage return plus linefeed and strips
	 * surplus blank lines, so there are no more than two linefeeds behind one
	 * another.
	 *
	 * @param string raw e-mail body, must not be empty
	 *
	 * @return string e-mail body, formatted if formatting is enabled,
	 *                will not be empty
	 */
	protected function formatEmailBody($rawEmailBody) {
		if (!$this->enableFormatting) {
			return $rawEmailBody;
		}

		$body = trim(preg_replace('/\n|\r/', CRLF, $rawEmailBody));

		return preg_replace('/(\r\n){2,}/', CRLF.CRLF, $body);
	}

	/**
	 * Formats a mail role for the e-mail sending process.
	 *
	 * @param tx_oelib_Interface_MailRole the mail role to format
	 *
	 * @return string the mail role formatted as string, e.g.
	 *                '"John Doe" <john@doe.com>' or just 'john@doe.com' if the
	 *                name is empty
	 */
	protected function formatMailRole(tx_oelib_Interface_MailRole $mailRole) {
		if ($mailRole->getName() == '') {
			return $mailRole->getEMailAddress();
		}

		return '"'. $mailRole->getName() . '"' .
			' <' . $mailRole->getEMailAddress() . '>';
	}

	/**
	 * Checks that none of the parameters is empty and throws an exception if
	 * one of them is empty.
	 *
	 * @param string the recipient's e-mail address, will not be
	 *               validated, must not be empty
	 * @param string e-mail subject, must not be empty
	 * @param string message to send, must not be empty
	 */
	protected function checkParameters($emailAddress, $subject, $message) {
		if ($emailAddress == '') {
			throw new Exception('$emailAddress must not be empty.');
		}

		if ($subject == '') {
			throw new Exception('$subject must not be empty.');
		}

		if ($message == '') {
			throw new Exception('$message must not be empty.');
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_abstractMailer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_abstractMailer.php']);
}
?>