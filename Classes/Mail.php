<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2014 Niels Pardon (mail@niels-pardon.de)
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

if (!class_exists('emogrifier', FALSE)) {
	require_once(t3lib_extMgm::extPath('oelib') . 'contrib/emogrifier/emogrifier.php');
}

/**
 * This class represents an e-mail.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Mail extends Tx_Oelib_Object {
	/**
	 * @var tx_oelib_Interface_MailRole the sender of the e-mail
	 */
	private $sender = NULL;

	/**
	 * @var array the recipients of the e-mail
	 */
	private $recipients = array();

	/**
	 * @var array the data of this object
	 */
	private $data = array();

	/**
	 * @var array attachments of the e-mail
	 */
	private $attachments = array();

	/**
	 * @var array contains the CSS files which already have been read
	 */
	private static $cssFileCache = array();

	/**
	 * @var array additional headers which should be added to the e-mail headers
	 */
	private $additionalHeaders = array();

	/**
	 * @var string the return path for the e-mails
	 */
	private $returnPath = '';

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->data, $this->sender, $this->recipients, $this->attachments);
	}

	/**
	 * Sets the value of the data item for the key $key.
	 *
	 * @param string $key the key of the data item to set, must not be empty
	 * @param mixed $value the data for the key $key
	 *
	 * @return void
	 */
	protected function set($key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param string $key the key of the data item to get, must not be empty
	 *
	 * @return mixed the data for the key $key, will be an empty string
	 *               if the key has not been set yet
	 */
	protected function get($key) {
		if (!isset($this->data[$key])) {
			return '';
		}

		return $this->data[$key];
	}

	/**
	 * Sets the sender of the e-mail.
	 *
	 * @param tx_oelib_Interface_MailRole $sender the sender of the e-mail
	 *
	 * @return void
	 */
	public function setSender(tx_oelib_Interface_MailRole $sender) {
		$this->sender = $sender;
	}

	/**
	 * Returns the sender of the e-mail.
	 *
	 * @return tx_oelib_Interface_MailRole the sender of the e-mail, will be
	 *                                     NULL if the sender has not been set
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * Returns whether the e-mail has a sender.
	 *
	 * @return boolean TRUE if the e-mail has a sender, FALSE otherwise
	 */
	public function hasSender() {
		return is_object($this->sender);
	}

	/**
	 * Adds a recipient for the e-mail.
	 *
	 * @param tx_oelib_Interface_MailRole $recipient a recipient for the e-mail, must not be empty
	 *
	 * @return void
	 */
	public function addRecipient(tx_oelib_Interface_MailRole $recipient) {
		$this->recipients[] = $recipient;
	}

	/**
	 * Returns the recipients of the e-mail.
	 *
	 * @return array the recipients of the e-mail, will be empty if no
	 *               recipients have been set
	 */
	public function getRecipients() {
		return $this->recipients;
	}

	/**
	 * Sets the subject of the e-mail.
	 *
	 * @param string $subject the subject of the e-mail, must not be empty
	 *
	 * @return void
	 */
	public function setSubject($subject) {
		if ($subject == '') {
			throw new InvalidArgumentException('$subject must not be empty.', 1331488802);
		}

		if ((strpos($subject, CR) !== FALSE) || (strpos($subject, LF) !== FALSE)) {
			throw new InvalidArgumentException('$subject must not contain any line breaks or carriage returns.', 1331488817);
		}

		$this->setAsString('subject', $subject);
	}

	/**
	 * Returns the subject of the e-mail.
	 *
	 * @return string the subject of the e-mail, will be empty if the subject has
	 *                not been set
	 */
	public function getSubject() {
		return $this->getAsString('subject');
	}

	/**
	 * Sets the message of the e-mail.
	 *
	 * @param string $message the message of the e-mail, must not be empty
	 *
	 * @return void
	 */
	public function setMessage($message) {
		if ($message == '') {
			throw new InvalidArgumentException('$message must not be empty.', 1331488834);
		}

		$this->setAsString('message', $message);
	}

	/**
	 * Returns the message of the e-mail.
	 *
	 * @return string the message of the e-mail, will be empty if the message has
	 *                not been set
	 */
	public function getMessage() {
		return $this->getAsString('message');
	}

	/**
	 * Returns whether the e-mail has a message.
	 *
	 * @return boolean TRUE if the e-mail has a message, FALSE otherwise
	 */
	public function hasMessage() {
		return $this->hasString('message');
	}

	/**
	 * Sets the HTML message of the e-mail.
	 *
	 * @param string $message the HTML message of the e-mail, must not be empty
	 *
	 * @return void
	 */
	public function setHTMLMessage($message) {
		if ($message == '') {
			throw new InvalidArgumentException('$message must not be empty.', 1331488845);
		}

		if ($this->hasCssFile()) {
			$emogrifier = new Emogrifier($message, $this->getCssFile());
			$messageToStore = $emogrifier->emogrify();
		} else {
			$messageToStore = $message;
		}

		$this->setAsString('html_message', $messageToStore);
	}

	/**
	 * Returns the HTML message of the e-mail.
	 *
	 * @return string the HTML message of the e-mail, will be empty if the
	 *                message has not been set
	 */
	public function getHTMLMessage() {
		return $this->getAsString('html_message');
	}

	/**
	 * Returns whether the e-mail has an HTML message.
	 *
	 * @return string TRUE if the e-mail has an HTML message, FALSE otherwise
	 */
	public function hasHTMLMessage() {
		return $this->hasString('html_message');
	}

	/**
	 * Adds an attachment to the e-mail.
	 *
	 * @param Tx_Oelib_Attachment $attachment the attachment to add
	 *
	 * @return void
	 */
	public function addAttachment(Tx_Oelib_Attachment $attachment) {
		$this->attachments[] = $attachment;
	}

	/**
	 * Returns the attachments of the e-mail.
	 *
	 * @return array the attachments of the e-mail, might be empty
	 */
	public function getAttachments() {
		return $this->attachments;
	}

	/**
	 * Sets the CSS file for sending an e-mail.
	 *
	 * @param string $cssFile the complete path to a valid CSS file, may be empty
	 *
	 * @return void
	 */
	public function setCssFile($cssFile) {
		if (!$this->cssFileIsCached($cssFile)) {
			$absoluteFileName = t3lib_div::getFileAbsFileName($cssFile);
			if (($cssFile != '') && is_readable($absoluteFileName)
			) {
				self::$cssFileCache[$cssFile]
					= file_get_contents($absoluteFileName);
			} else {
				self::$cssFileCache[$cssFile] = '';
			}
		}

		$this->setAsString('cssFile', self::$cssFileCache[$cssFile]);
	}

	/**
	 * Returns whether e-mail has a CSS file.
	 *
	 * @return boolean TRUE if a CSS file has been set, FALSE otherwise
	 */
	public function hasCssFile() {
		return $this->hasString('cssFile');
	}

	/**
	 * Returns the stored content of the CSS file.
	 *
	 * @return string the file contents of the CSS file, will be empty if no CSS
	 *                file was stored
	 */
	public function getCssFile() {
		return $this->getAsString('cssFile');
	}

	/**
	 * Checks whether the given CSS file has already been read.
	 *
	 * @param string $cssFile the absolute path to the CSS file, must not be empty
	 *
	 * @return boolean TRUE when the CSS file was read earlier, FALSE otherwise
	 */
	private function cssFileIsCached($cssFile) {
		return isset(self::$cssFileCache[$cssFile]);
	}

	/**
	 * Sets the return path (and errors-to) of the e-mail.
	 *
	 * The return path is stored in a way that the MIME mail class can read it.
	 * If a return path has already been set, it will be overridden by the new
	 * value.
	 * If an empty string is given this function is a no-op.
	 *
	 * @param string $returnPath the e-mail address for the return path, may be empty
	 *
	 * @return void
	 */
	public function setReturnPath($returnPath) {
		if ($returnPath == '') {
			return;
		}

		$this->returnPath = $returnPath;
		$this->additionalHeaders['Return-Path'] = '<' . $returnPath . '>';
		$this->additionalHeaders['Errors-To'] = $returnPath;
	}

	/**
	 * Returns the return path set via setReturnPath
	 *
	 * @return string the return path, will be an empty string if nothing has
	 *                been stored
	 */
	public function getReturnPath() {
		return $this->returnPath;
	}

	/**
	 * Returns the additional headers for this e-mail.
	 *
	 * @return array the additional headers for this e-mail, will be empty if no
	 *               additional headers have been set
	 */
	public function getAdditionalHeaders() {
		return $this->additionalHeaders;
	}

	/**
	 * Checks whether this e-mail has any additional headers.
	 *
	 * @return boolean TRUE if this e-mail has any additional headers, FALSE
	 *                 otherwise
	 */
	public function hasAdditionalHeaders() {
		return !empty($this->additionalHeaders);
	}
}