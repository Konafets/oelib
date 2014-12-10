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
 * Abstract class for sending e-mails (or faking it) using the Straegy pattern.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Oelib_AbstractMailer {
	/**
	 * @var bool whether an e-mail should be formatted before it is sent
	 */
	protected $enableFormatting = TRUE;

	/**
	 * Cleans up (if necessary).
	 *
	 * @return void
	 */
	public function cleanUp() {
	}

	/**
	 * Sends a plain-text e-mail.
	 *
	 * Note: This function cannot handle multi-part e-mails.
	 *
	 * @deprecated 2014-08-28 use send instead
	 *
	 * @param string $emailAddress
	 *        the recipient's e-mail address, will not be validated, must not be empty
	 * @param string $subject
	 *        e-mail subject, must not be empty
	 * @param string $message
	 *        message to send, must not be empty
	 * @param string $headers
	 *        headers, separated by linefeed, may be empty
	 * @param string $encodingType
	 *        encoding type: "quoted-printable" or "8bit"
	 * @param string $charset
	 *        charset to use for encoding headers (only if $encodingType is set
	 *        to a valid value which produces such a header)
	 * @param bool $doNotEncodeHeader
	 *        if set, the header content will not be encoded
	 *
	 * @return bool TRUE if the e-mail was sent, FALSE otherwise
	 */
	public abstract function sendEmail(
		$emailAddress, $subject, $message, $headers = '', $encodingType = '', $charset = '', $doNotEncodeHeader = FALSE
	);

	/**
	 * Sends an e-mail.
	 *
	 * This function can handle plain-text and multi-part e-mails.
	 *
	 * @deprecated 2014-08-28 use send instead
	 *
	 * @param string $emailAddress
	 *        the recipient's e-mail address, will not be validated, must not be empty
	 * @param string $subject
	 *        e-mail subject, must not be empty
	 * @param string $message
	 *        message to send, must not be empty
	 * @param string $headers
	 *        headers, separated by linefeed, may be empty
	 * @param string $additionalParameters
	 *        additional parameters to pass to the mail program as command line arguments
	 *
	 * @return bool TRUE if the e-mail was sent, FALSE otherwise
	 */
	public abstract function mail($emailAddress, $subject, $message, $headers = '', $additionalParameters = '');

	/**
	 * Sends an Tx_Oelib_Mail object (one separate message per recipient).
	 *
	 * @param Tx_Oelib_Mail $email the Tx_Oelib_Mail object to send
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function send(Tx_Oelib_Mail $email) {
		if (!$email->hasSender()) {
			throw new InvalidArgumentException('$email must have a sender set.', 1331318718);
		}
		$this->validateEmailAddress($email->getSender()->getEmailAddress(), 'From:');
		if ($email->getSubject() === '') {
			throw new InvalidArgumentException('The e-mail subject must not be empty.', 1409410879);
		}
		if (!$email->hasMessage()) {
			throw new InvalidArgumentException('The e-mail message must not be empty.', 1409410886);
		}
		$recipients = $email->getRecipients();
		if (empty($recipients)) {
			throw new InvalidArgumentException('The e-mail must have at least one recipient.', 1409410886);
		}
		foreach ($recipients as $recipient) {
			$this->validateEmailAddress($recipient->getEmailAddress(), 'To:');
		}

		/** @var t3lib_mail_Message $swiftMail */
		$swiftMail = t3lib_div::makeInstance('t3lib_mail_Message');
		$swiftMail->setSubject($email->getSubject());

		$sender = $email->getSender();
		$swiftMail->setFrom(array($sender->getEmailAddress() => $sender->getName()));
		$swiftMail->setCharset($this->getCharacterSet());

		$returnPath = $email->getReturnPath();
		if ($returnPath !== '') {
			$swiftMail->setReturnPath($returnPath);
		}

		if ($email->hasMessage()) {
			$swiftMail->setBody($this->formatEmailBody($email->getMessage()));
		}
		if ($email->hasHTMLMessage()) {
			$swiftMail->addPart($email->getHTMLMessage(), 'text/html');
		}

		/** @var Tx_Oelib_Attachment $attachment */
		foreach ($email->getAttachments() as $attachment) {
			if (($attachment->getFileName() !== '') && ($attachment->getContent() === '')) {
				$swiftAttachment = Swift_Attachment::fromPath($attachment->getFileName(), $attachment->getContentType());
			} else {
				$fileName = $attachment->getFileName() !== '' ? $attachment->getFileName() : NULL;
				$swiftAttachment = Swift_Attachment::newInstance(
					$attachment->getContent(), $fileName, $attachment->getContentType()
				);
			}

			$swiftMail->attach($swiftAttachment);
		}

		foreach ($email->getRecipients() as $recipient) {
			$swiftMail->setTo(array($recipient->getEmailAddress() => $recipient->getName()));
			$this->sendSwiftMail($swiftMail);
		}
	}

	/**
	 * Validates that $emailAddress is non-empty and valid. If it is not, this method throws an exception.
	 *
	 * @param string $emailAddress the supposed e-mail address to check
	 * @param string $roleDescription e.g., "To:" or "From:", must not be empty
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	protected function validateEmailAddress($emailAddress, $roleDescription) {
		if ($emailAddress === '') {
			throw new InvalidArgumentException(
				'The ' . $roleDescription . ' e-mail address "' . $emailAddress . '" was empty.', 1409601561
			);
		}
		if (!$this->isLocalhostAddress($emailAddress) && !t3lib_div::validEmail($emailAddress)) {
			throw new InvalidArgumentException(
				'The ' . $roleDescription . ' e-mail address "' . $emailAddress . '" was not valid.', 1409601561
			);
		}
	}

	/**
	 * Checks $emailAddress is a simple localhost address.
	 *
	 * @param string $emailAddress
	 *
	 * @return bool
	 */
	protected function isLocalhostAddress($emailAddress) {
		return (bool)preg_match('/[\-_\.a-zA-Z0-9]+@localhost/', $emailAddress);
	}

	/**
	 * Sends a Swift e-mail.
	 *
	 * @param t3lib_mail_Message $email the e-mail to send.
	 *
	 * @return void
	 */
	protected abstract function sendSwiftMail(t3lib_mail_Message $email);

	/**
	 * Sets whether the e-mail body should be formatted before sending the e-mail.
	 *
	 * Formatting will replace CRLF and CR by LF and strip multiple blank lines.
	 *
	 * @param bool $enableFormatting TRUE to enable formatting, FALSE to disable
	 *
	 * @return void
	 */
	public function sendFormattedEmails($enableFormatting) {
		$this->enableFormatting = $enableFormatting;
	}

	/**
	 * Formats the e-mail body if this is enabled.
	 *
	 * Replaces single carriage returns or carriage return plus linefeed
	 * with line feeds and strips surplus blank lines, so there are no more than
	 * two line breaks behind one another.
	 *
	 * @param string $rawEmailBody string raw e-mail body, must not be empty
	 *
	 * @return string e-mail body, formatted if formatting is enabled, will not be empty
	 */
	protected function formatEmailBody($rawEmailBody) {
		if (!$this->enableFormatting) {
			return $rawEmailBody;
		}

		$body = str_replace(CRLF, LF, $rawEmailBody);
		$body = str_replace(CR, LF, $body);
		$body = preg_replace('/\n{2,}/', LF . LF, $body);

		return trim($body);
	}

	/**
	 * Checks that none of the parameters is empty and throws an exception if one of them is empty.
	 *
	 * @deprecated 2014-08-28
	 *
	 * @param string $emailAddress
	 *        the recipient's e-mail address, will not be validated, must not be empty
	 * @param string $subject
	 *        e-mail subject, must not be empty
	 * @param string $message
	 *        message to send, must not be empty
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	protected function checkParameters($emailAddress, $subject, $message) {
		t3lib_div::logDeprecatedFunction();

		if ($emailAddress === '') {
			throw new InvalidArgumentException('$emailAddress must not be empty.', 1331318731);
		}

		if ($subject === '') {
			throw new InvalidArgumentException('$subject must not be empty.', 1331318747);
		}

		if ($message === '') {
			throw new InvalidArgumentException('$message must not be empty.', 1331318756);
		}
	}

	/**
	 * Retrieves the current character set used by TYPO3.
	 *
	 * @return string the current character set, e.g. utf-8
	 */
	private function getCharacterSet() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4007000) {
			return 'utf-8';
		}

		$charset = (string) $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
		return ($charset !== '') ? $charset : 'utf-8';
	}
}