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
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class Tx_Oelib_EmailCollector extends Tx_Oelib_AbstractMailer {
	/**
	 * Two-dimensional array of e-mail data.
	 * Each e-mail is stored in one element. So the number of elements in the
	 * first dimension depends on how many e-mails are currently stored. One
	 * stored e-mail is always an associative array with four elements named
	 * 'recipient', 'subject', 'message' and 'headers'.
	 *
	 * @var array[]
	 */
	private $emailData = array();

	/**
	 * @var bool whether sendEmail() should always return TRUE
	 */
	private $fakeSuccess = TRUE;

	/**
	 * @var t3lib_mail_Message[]
	 */
	protected $sentEmails = array();

	/**
	 * The destructor.
	 */
	public function __destruct() {
		$this->cleanUp();
	}

	/**
	 * Cleans up (if necessary).
	 *
	 * @return void
	 */
	public function cleanUp() {
		$this->sentEmails = array();
	}

	/**
	 * Stores the contents which were meant to be sent as an e-mail.
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
	 * @return bool depending on whether success should be faked or not
	 */
	public function sendEmail(
		$emailAddress, $subject, $message, $headers = '', $encodingType = '', $charset = '', $doNotEncodeHeader = FALSE
	) {
		GeneralUtility::logDeprecatedFunction();

		$this->emailData[] = array(
			'recipient' => $emailAddress,
			'subject' => GeneralUtility::encodeHeader($subject, 'quoted-printable'),
			'message' => $this->formatEmailBody($message),
			'headers' => $headers,
		);

		return $this->fakeSuccess;
	}

	/**
	 * Sends an e-mail.
	 *
	 * This function can handle plain-text and multi-part e-mails.
	 *
	 * @deprecated 2014-08-28 use send instead
	 *
	 * @param string $emailAddress the recipient's e-mail address, will not be validated, must not be empty
	 * @param string $subject e-mail subject, must not be empty
	 * @param string $message message to send, must not be empty
	 * @param string $headers headers, separated by linefeed, may be empty
	 * @param string $additionalParameters
	 *        additional parameters to pass to the mail program as command line arguments
	 *
	 * @return bool TRUE if the e-mail was sent, FALSE otherwise
	 */
	public function mail($emailAddress, $subject, $message, $headers = '', $additionalParameters = '') {
		GeneralUtility::logDeprecatedFunction();

		$this->checkParameters($emailAddress, $subject, $message);

		return $this->sendEmail($emailAddress, $subject, $message, $headers);
	}

	/**
	 * Sends a Swift e-mail.
	 *
	 * @param t3lib_mail_Message $email the e-mail to send.
	 *
	 * @return void
	 */
	protected function sendSwiftMail(t3lib_mail_Message $email) {
		$this->sentEmails[] = $email;
	}

	/**
	 * Sets the return value for sendEmail().
	 *
	 * @param bool $isSuccessful TRUE if sendEmail() should return TRUE, FALSE otherwise
	 *
	 * @return void
	 */
	public function setFakedReturnValue($isSuccessful) {
		$this->fakeSuccess = $isSuccessful;
	}

	/**
	 * Returns the last e-mail or an empty array if there is none.
	 *
	 * @deprecated 2014-08-28 use getSentEmails instead
	 *
	 * @return array e-mail address, subject, message and headers of the last e-mail in an array, will be empty if there is
	 *               no e-mail
	 */
	public function getLastEmail() {
		if (empty($this->emailData)) {
			return array();
		}

		return end($this->emailData);
	}

	/**
	 * Returns all e-mails sent with this instance or an empty array if there is none.
	 *
	 * @deprecated 2014-08-28 use getSentEmails instead
	 *
	 * @return array[] two-dimensional array with one element for each e-mail, each inner array has four elements
	 *               'recipient', 'subject', 'message' and 'headers', will be empty if there are no e-mails
	 *
	 * @see emailData
	 */
	public function getAllEmail() {
		return $this->emailData;
	}

	/**
	 * Returns the last e-mail's recipient.
	 *
	 * @deprecated 2014-08-28 use getSentEmails instead
	 *
	 * @return string recipient of the last sent e-mail or an empty string if there is none
	 */
	public function getLastRecipient() {
		return $this->getElementFromLastEmail('recipient');
	}

	/**
	 * Returns the last e-mail's subject.
	 *
	 * @deprecated 2014-08-28 use getSentEmails instead
	 *
	 * @return string subject of the last sent e-mail or an empty string if there is none
	 */
	public function getLastSubject() {
		return $this->getElementFromLastEmail('subject');
	}

	/**
	 * Returns the last e-mail's body.
	 *
	 * @deprecated 2014-08-28 use getSentEmails instead
	 *
	 * @return string body of the last sent e-mail or an empty string if there is none
	 */
	public function getLastBody() {
		return $this->getElementFromLastEmail('message');
	}

	/**
	 * Returns the last e-mail's additional headers.
	 *
	 * @deprecated 2014-08-28 use getSentEmails instead
	 *
	 * @return string headers of the last sent e-mail or an empty string if there are none
	 */
	public function getLastHeaders() {
		return $this->getElementFromLastEmail('headers');
	}

	/**
	 * Returns an element from the array with the last e-mail.
	 *
	 * @deprecated 2014-08-28 use getSentEmails instead
	 *
	 * @param string $key key of the element to return, must be "recipient", "subject", "message" or "headers"
	 *
	 * @return string value of the element, will be an empty string if there was none
	 *
	 * @throws InvalidArgumentException
	 */
	private function getElementFromLastEmail($key) {
		if (!in_array($key, array('recipient', 'subject', 'message', 'headers'), TRUE)) {
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

	/**
	 * Returns the e-mails that would have been sent via the send method.
	 *
	 * @return t3lib_mail_Message[]
	 */
	public function getSentEmails() {
		return $this->sentEmails;
	}

	/**
	 * Returns the number of e-mails that would have been sent via the send method.
	 *
	 * @return int the number of send e-mails, will be >= 0
	 */
	public function getNumberOfSentEmails() {
		return count($this->getSentEmails());
	}

	/**
	 * Returns the first sent-email or NULL if none has been sent.
	 *
	 * @return t3lib_mail_Message|NULL
	 */
	public function getFirstSentEmail() {
		if ($this->getNumberOfSentEmails() === 0) {
			return NULL;
		}

		$sendEmails = $this->getSentEmails();

		return $sendEmails[0];
	}
}