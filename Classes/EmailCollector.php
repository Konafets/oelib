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
 * This class stores all parameters which were meant to be sent as an e-mail and
 * provides various functions to get them for testing purposes.
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