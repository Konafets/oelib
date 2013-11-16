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
 * This class stores all parameters which were meant to be sent as an e-mail and
 * provides various functions to get them for testing purposes.
 *
 * Regarding the Strategy pattern, sendEmail() represents one concrete behavior.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_oelib_emailCollector extends tx_oelib_abstractMailer {
	/**
	 * Two-dimensional array of e-mail data.
	 * Each e-mail is stored in one element. So the number of elements in the
	 * first dimension depends on how many e-mails are currently stored. One
	 * stored e-mail is always an associative array with four elements named
	 * 'recipient', 'subject', 'message' and 'headers'.
	 */
	private $emailData = array();

	/**
	 * @var boolean whether sendEmail() should always return TRUE
	 */
	private $fakeSuccess = TRUE;

	/**
	 * Stores the contents which were meant to be sent as an e-mail.
	 *
	 * @param string $emailAddress the recipient's e-mail address, will not be validated, must not be empty
	 * @param string $subject e-mail subject, must not be empty
	 * @param string $message message to send, must not be empty
	 * @param string $headers headers, separated by linefeed, may be empty
	 * @param string $encodingType encoding type: "quoted-printable" or "8bit"
	 * @param string $charset
	 *        charset to use for encoding headers (only if $encodingType is set to a valid value which produces such a header)
	 * @param boolean $doNotEncodeHeader if set, the header content will not be encoded
	 *
	 * @return boolean depending on whether success should be faked or not
	 */
	public function sendEmail(
		$emailAddress,
		$subject,
		$message,
		$headers = '',
		$encodingType = '',
		$charset = '',
		$doNotEncodeHeader = FALSE
	) {
		$this->emailData[] = array(
			'recipient' => $emailAddress,
			'subject' => t3lib_div::encodeHeader($subject, 'quoted-printable'),
			'message' => $this->formatEmailBody($message),
			'headers' => $headers
		);

		return $this->fakeSuccess;
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
	 * @return boolean TRUE if the e-mail was sent, FALSE otherwise
	 */
	public function mail(
		$emailAddress, $subject, $message, $headers = '',
		$additionalParameters = ''
	) {
		$this->checkParameters($emailAddress, $subject, $message);

		return $this->sendEmail($emailAddress, $subject, $message, $headers);
	}

	/**
	 * Sets the return value for sendEmail().
	 *
	 * @param boolean $isSuccessful TRUE if sendEmail() should return TRUE, FALSE otherwise
	 *
	 * @return void
	 */
	public function setFakedReturnValue($isSuccessful) {
		$this->fakeSuccess = $isSuccessful;
	}

	/**
	 * Returns the last e-mail or an empty array if there is none.
	 *
	 * @return array e-mail address, subject, message and headers of the
	 *               last e-mail in an array, will be empty if there is
	 *               no e-mail
	 */
	public function getLastEmail() {
		if (empty($this->emailData)) {
			return array();
		}

		return end($this->emailData);
	}

	/**
	 * Returns all e-mails sent with this instance or an empty array if there is
	 * none.
	 *
	 * @return array two-dimensional array with one element for each
	 *               e-mail, each inner array has four elements
	 *               'recipient', 'subject', 'message' and 'headers',
	 *               will be empty if there are no e-mails
	 *
	 * @see emailData
	 */
	public function getAllEmail() {
		return $this->emailData;
	}

	/**
	 * Returns the last e-mail's recipient.
	 *
	 * @return string recipient of the last sent e-mail or an empty string
	 *                if there is none
	 */
	public function getLastRecipient() {
		return $this->getElementFromLastEmail('recipient');
	}

	/**
	 * Returns the last e-mail's subject.
	 *
	 * @return string subject of the last sent e-mail or an empty string
	 *                if there is none
	 */
	public function getLastSubject() {
		return $this->getElementFromLastEmail('subject');
	}

	/**
	 * Returns the last e-mail's body.
	 *
	 * @return string body of the last sent e-mail or an empty string if
	 *                there is none
	 */
	public function getLastBody() {
		return $this->getElementFromLastEmail('message');
	}

	/**
	 * Returns the last e-mail's additional headers.
	 *
	 * @return string headers of the last sent e-mail or an empty string
	 *                if there are none
	 */
	public function getLastHeaders() {
		return $this->getElementFromLastEmail('headers');
	}

	/**
	 * Returns an element from the array with the last e-mail.
	 *
	 * @param string $key key of the element to return, must be "recipient", "subject", "message" or "headers"
	 *
	 * @return string value of the element, will be an empty string if
	 *                there was none
	 */
	private function getElementFromLastEmail($key) {
		if (!in_array($key, array('recipient', 'subject', 'message', 'headers'))) {
			throw new InvalidArgumentException(
				'The key "' . $key . '" is invalid. It must be "recipient", "subject", "message" or "headers".', 1331488710
			);
		}
		if (empty($this->emailData)) {
			return '';
		}

		$lastEmail = $this->getLastEmail();

		return $lastEmail[$key];
	}
}
?>