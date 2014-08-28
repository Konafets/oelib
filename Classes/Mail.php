<?php
/**
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
 * This class represents an e-mail.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Mail extends Tx_Oelib_Object {
	/**
	 * @var Tx_Oelib_Interface_MailRole the sender of the e-mail
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
	 * @var Tx_Oelib_Attachment[] attachments of the e-mail
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
	 * @return mixed the data for the key $key, will be an empty string if the key has not been set yet
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
	 * @param Tx_Oelib_Interface_MailRole $sender the sender of the e-mail
	 *
	 * @return void
	 */
	public function setSender(Tx_Oelib_Interface_MailRole $sender) {
		$this->sender = $sender;
	}

	/**
	 * Returns the sender of the e-mail.
	 *
	 * @return Tx_Oelib_Interface_MailRole the sender of the e-mail, will be NULL if the sender has not been set
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
	 * @param Tx_Oelib_Interface_MailRole $recipient a recipient for the e-mail, must not be empty
	 *
	 * @return void
	 */
	public function addRecipient(Tx_Oelib_Interface_MailRole $recipient) {
		$this->recipients[] = $recipient;
	}

	/**
	 * Returns the recipients of the e-mail.
	 *
	 * @return Tx_Oelib_Interface_MailRole[] the recipients of the e-mail, will be empty if no recipients have been set
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function setSubject($subject) {
		if ($subject === '') {
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
	 * @return string the subject of the e-mail, will be empty if the subject has not been set
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function setMessage($message) {
		if ($message === '') {
			throw new InvalidArgumentException('$message must not be empty.', 1331488834);
		}

		$this->setAsString('message', $message);
	}

	/**
	 * Returns the message of the e-mail.
	 *
	 * @return string the message of the e-mail, will be empty if the message has not been set
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function setHTMLMessage($message) {
		if ($message == '') {
			throw new InvalidArgumentException('$message must not be empty.', 1331488845);
		}

		if ($this->hasCssFile()) {
			$this->loadEmogrifierClass();
			$emogrifier = new \Pelago\Emogrifier($message, $this->getCssFile());
			$messageToStore = $emogrifier->emogrify();
		} else {
			$messageToStore = $message;
		}

		$this->setAsString('html_message', $messageToStore);
	}

	/**
	 * Makes the Emogrifier class loadable via the autoloader.
	 *
	 * @return void
	 */
	protected function loadEmogrifierClass() {
		if (!class_exists('Pelago\\Emogrifier', TRUE)) {
			require_once(t3lib_extMgm::extPath('oelib') . 'Packages/vendor/autoload.php');
		}
	}

	/**
	 * Returns the HTML message of the e-mail.
	 *
	 * @return string the HTML message of the e-mail, will be empty if the message has not been set
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
	 * @return Tx_Oelib_Attachment[] the attachments of the e-mail, might be empty
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
			if (($cssFile !== '') && is_readable($absoluteFileName)
			) {
				self::$cssFileCache[$cssFile] = file_get_contents($absoluteFileName);
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
	 * @return string the file contents of the CSS file, will be empty if no CSS file was stored
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
	 * If a return path has already been set, it will be overridden by the new value.
	 * If an empty string is given this function is a no-op.
	 *
	 * @param string $returnPath the e-mail address for the return path, may be empty
	 *
	 * @return void
	 */
	public function setReturnPath($returnPath) {
		$this->returnPath = $returnPath;
	}

	/**
	 * Returns the return path set via setReturnPath
	 *
	 * @return string the return path, will be an empty string if nothing has been stored
	 */
	public function getReturnPath() {
		return $this->returnPath;
	}
}