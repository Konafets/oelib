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
 * Class 'tx_oelib_emailCollector' for the 'oelib' extension.
 *
 * This class stores all parameters which were meant to be sent as an e-mail and
 * provides various functions to get them for testing purposes.
 * Regarding the Strategy pattern, sendEmail() represents one concrete behavior.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 */
require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_abstractMailer.php');

class tx_oelib_emailCollector extends tx_oelib_abstractMailer {
	/**
	 * Two-dimensional array of e-mail data.
	 * Each e-mail is stored in one element. So the number of elements in the
	 * first dimension depends on how many e-mails are currently stored. One
	 * stored e-mail is always an associative array with four elements named
	 * 'recipient', 'subject', 'message' and 'headers'.
	 */
	private $emailData = array();

	/** whether sendEmail() should return true or false */
	private $fakeSuccess = true;

	/**
	 * Stores the contents which were meant to be sent as an e-mail.
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
	 * @param	boolean		depending on whether success should be faked or not
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
		$this->emailData[] = array(
			'recipient' => $emailAddress,
			'subject' => $subject,
			'message' => $message,
			'headers' => $headers
		);

		return $this->fakeSuccess;
	}

	/**
	 * Sets the return value for sendEmail().
	 *
	 * @param	boolean		true if sendEmail() should return true, false
	 * 						otherwise
	 */
	public function setFakedReturnValue($isSuccessful) {
		$this->fakeSuccess = $isSuccessful;
	}

	/**
	 * Deletes all collected e-mail data.
	 */
	public function cleanUpCollectedEmailData() {
		$this->emailData = array();
	}

	/**
	 * Returns the last e-mail or an empty array if there is none.
	 *
	 * @return	array		e-mail address, subject, message and headers of the
	 * 						last e-mail in an array, will be empty if there is
	 * 						no e-mail
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
	 * @return	array		two-dimensional array with one element for each
	 * 						e-mail, each inner array has four elements
	 * 						'recipient', 'subject', 'message' and 'headers',
	 * 						will be empty if there are no e-mails
	 *
	 * @see		emailData
	 */
	public function getAllEmail() {
		return $this->emailData;
	}

	/**
	 * Returns the last e-mail's recipient.
	 *
	 * @return	string		recipient of the last sent e-mail or an empty string
	 * 						if there is none
	 */
	public function getLastRecipient() {
		return $this->getElementFromLastEmail('recipient');
	}

	/**
	 * Returns the last e-mail's subject.
	 *
	 * @return	string		subject of the last sent e-mail or an empty string
	 * 						if there is none
	 */
	public function getLastSubject() {
		return $this->getElementFromLastEmail('subject');
	}

	/**
	 * Returns the last e-mail's body.
	 *
	 * @return	string		body of the last sent e-mail or an empty string if
	 * 						there is none
	 */
	public function getLastBody() {
		return $this->getElementFromLastEmail('message');
	}

	/**
	 * Returns the last e-mail's additional headers.
	 *
	 * @return	string		headers of the last sent e-mail or an empty string
	 * 						if there are none
	 */
	public function getLastHeaders() {
		return $this->getElementFromLastEmail('headers');
	}

	/**
	 * Returns an element from the array with the last e-mail.
	 *
	 * @param	string		key of the element to return, must be 'recipient',
	 * 						'subject', 'message' or 'headers'
	 *
	 * @return	string		value of the element, will be an empty string if
	 * 						there was none
	 */
	private function getElementFromLastEmail($key) {
		if (!in_array($key, array('recipient', 'subject', 'message', 'headers'))) {
			throw new Exception(
				'The key "'.$key.'" is invalid. '
					.'It must be "recipient", "subject", "message" or "headers".'
			);
		}
		if (empty($this->emailData)) {
			return '';
		}

		$lastEmail = $this->getLastEmail();

		return $lastEmail[$key];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_emailCollector.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_emailCollector.php']);
}
?>
