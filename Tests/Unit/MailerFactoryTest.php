<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Saskia Metzler <saskia@merlin.owl.de> All rights reserved
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

if (!class_exists('mail_mime', FALSE)) {
	require_once(t3lib_extMgm::extPath('oelib') . 'contrib/PEAR/Mail/mime.php');
}

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_MailerFactoryTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_emailCollector
	 */
	private $fixture;

	private static $email = array(
		'recipient' => 'any-recipient@email-address.org',
		'subject' => 'any subject',
		'message' => 'any message',
		'headers' => ''
	);
	private static $otherEmail = array(
		'recipient' => 'any-other-recipient@email-address.org',
		'subject' => 'any other subject',
		'message' => 'any other message',
		'headers' => ''
	);

	protected function setUp() {
		// Only the instance with an enabled test mode can be tested as in the
		// non-test mode e-mails are sent.
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->fixture = tx_oelib_mailerFactory::getInstance()->getMailer();

		$this->addHeadersToTestEmail();
	}

	protected function tearDown() {
		tx_oelib_mailerFactory::purgeInstance();
		unset($this->fixture);
	}


	/////////////////////
	// Utility functions
	/////////////////////

	/**
	 * Adds the headers to the static test e-mail as LF cannot be used when it
	 * is defined.
	 *
	 * @return void
	 */
	private function addHeadersToTestEmail() {
		self::$email['headers'] = 'From: any-sender@email-address.org' . LF .
			'CC: "another recipient" <another-recipient@email-address.org>' . LF .
			'Reply-To: any-sender@email-address.org';
	}

	/**
	 * Gets the current character set in TYPO3, e.g., "utf-8".
	 *
	 * @return string the current character set, will not be empty
	 */
	private function getCharacterSet() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4007000) {
			return 'utf-8';
		}

		return ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] != '') ?
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : 'utf-8';
	}


	/////////////////////////////////////////////
	// Tests concerning the basic functionality
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getMailerInTestMode() {
		$this->assertSame(
			'tx_oelib_emailCollector',
			get_class($this->fixture)
		);
	}

	/**
	 * @test
	 */
	public function getMailerInNonTestMode() {
		// initially, the test mode is disabled
		tx_oelib_mailerFactory::purgeInstance();

		$this->assertSame(
			'tx_oelib_realMailer',
			get_class(tx_oelib_mailerFactory::getInstance()->getMailer())
		);
	}

	/**
	 * @test
	 */
	public function getMailerReturnsTheSameObjectWhenTheInstanceWasNotDiscarded() {
		$this->assertSame(
			$this->fixture,
			tx_oelib_mailerFactory::getInstance()->getMailer()
		);
	}

	/**
	 * @test
	 */
	public function getMailerAfterPurgeInstanceReturnsNewObject() {
		tx_oelib_mailerFactory::purgeInstance();

		$this->assertNotSame(
			$this->fixture,
			tx_oelib_mailerFactory::getInstance()->getMailer()
		);
	}

	/**
	 * @test
	 */
	public function storeAnEmailAndGetIt() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);

		$this->assertSame(
			self::$email,
			$this->fixture->getLastEmail()
		);
	}

	/**
	 * @test
	 */
	public function storeTwoEmailsAndGetTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);
		$this->fixture->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertSame(
			self::$otherEmail,
			$this->fixture->getLastEmail()
		);
	}

	/**
	 * @test
	 */
	public function storeNoEmailAndTryToGetTheLastEmail() {
		$this->assertSame(
			array(),
			$this->fixture->getLastEmail()
		);
	}

	/**
	 * @test
	 */
	public function storeTwoEmailsAndGetBothEmails() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);
		$this->fixture->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertSame(
			array(
				self::$email,
				self::$otherEmail
			),
			$this->fixture->getAllEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailReturnsTrueIfTheReturnValueIsSetToTrue() {
		$this->fixture->setFakedReturnValue(TRUE);

		$this->assertTrue(
			$this->fixture->sendEmail('', '', '')
		);
	}

	/**
	 * @test
	 */
	public function sendEmailReturnsFalseIfTheReturnValueIsSetToFalse() {
		$this->fixture->setFakedReturnValue(FALSE);

		$this->assertFalse(
			$this->fixture->sendEmail('', '', '')
		);
	}

	/**
	 * @test
	 */
	public function getLastRecipientReturnsTheRecipientOfTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertSame(
			self::$email['recipient'],
			$this->fixture->getLastRecipient()
		);
	}

	/**
	 * @test
	 */
	public function getLastRecipientReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertSame(
			'',
			$this->fixture->getLastRecipient()
		);
	}

	/**
	 * @test
	 */
	public function getLastSubjectReturnsTheSubjectOfTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertSame(
			self::$email['subject'],
			$this->fixture->getLastSubject()
		);
	}

	/**
	 * @test
	 */
	public function getLastSubjectReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertSame(
			'',
			$this->fixture->getLastSubject()
		);
	}

	/**
	 * @test
	 */
	public function getLastBodyReturnsTheBodyOfTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertSame(
			self::$email['message'],
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function getLastBodyReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertSame(
			'',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function getLastHeadersIfTheEmailDoesNotHaveAny() {
		$this->fixture->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertSame(
			'',
			$this->fixture->getLastHeaders()
		);
	}

	/**
	 * @test
	 */
	public function getLastHeadersReturnsTheLastHeaders() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);

		$this->assertSame(
			self::$email['headers'],
			$this->fixture->getLastHeaders()
		);
	}

	/**
	 * @test
	 */
	public function sendWithAnEmailAndGetIt() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$this->fixture->send($eMail);

		$characterSet = $this->getCharacterSet();
		$buildParameter = array(
			'text_encoding' => 'quoted-printable',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		$mimeEmail = new Mail_mime(array('eol' => LF));
		$mimeEmail->setFrom($sender->getEmailAddress());
		$mimeEmail->setTXTBody(self::$email['message']);

		$this->assertSame(
			array(
				'recipient' => self::$email['recipient'],
				'subject' => self::$email['subject'],
				'message' => $mimeEmail->get($buildParameter),
				'headers' => $mimeEmail->txtHeaders(),
			),
			$this->fixture->getLastEmail()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function sendingPlainTextMailRemovesAnyCarriageReturnFromBody() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(
			'one long line ...........................................' . CRLF .
			'now a blank line:' . LF . LF .
			'another long line .........................................' . LF .
			'and a line with umlauts: Hörbär saß früh.'
		);

		$this->fixture->send($eMail);

		$this->assertNotContains(
			CR,
			$this->fixture->getLastBody()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function sendWithTwoEmailsAndGetTheLastEmail() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$otherRecipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$otherEmail['recipient']
		);

		$otherEmail = new tx_oelib_Mail();
		$otherEmail->setSender($sender);
		$otherEmail->addRecipient($otherRecipient);
		$otherEmail->setSubject(self::$otherEmail['subject']);
		$otherEmail->setMessage(self::$otherEmail['message']);

		$this->fixture->send($eMail);
		$this->fixture->send($otherEmail);

		$characterSet = $this->getCharacterSet();
		$buildParameter = array(
			'text_encoding' => 'quoted-printable',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		$mimeEmail = new Mail_mime(array('eol' => LF));
		$mimeEmail->setFrom($sender->getEmailAddress());
		$mimeEmail->setTXTBody(self::$otherEmail['message']);

		$this->assertSame(
			array(
				'recipient' => self::$otherEmail['recipient'],
				'subject' => self::$otherEmail['subject'],
				'message' => $mimeEmail->get($buildParameter),
				'headers' => $mimeEmail->txtHeaders(),
			),
			$this->fixture->getLastEmail()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
		$otherRecipient->__destruct();
		$otherEmail->__destruct();
	}

	/**
	 * @test
	 */
	public function sendWithTwoEmailsAndGetBothEmails() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$otherRecipient =new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$otherEmail['recipient']
		);

		$otherEmail = new tx_oelib_Mail();
		$otherEmail->setSender($sender);
		$otherEmail->addRecipient($otherRecipient);
		$otherEmail->setSubject(self::$otherEmail['subject']);
		$otherEmail->setMessage(self::$otherEmail['message']);

		$this->fixture->send($eMail);
		$this->fixture->send($otherEmail);

		$characterSet = $this->getCharacterSet();
		$buildParameter = array(
			'text_encoding' => 'quoted-printable',
			'head_charset' => $characterSet,
			'text_charset' => $characterSet,
			'html_charset' => $characterSet,
		);

		$mimeEmail = new Mail_mime(array('eol' => LF));
		$mimeEmail->setFrom($sender->getEmailAddress());
		$mimeEmail->setTXTBody(self::$email['message']);

		$otherMimeEmail = new Mail_mime(array('eol' => LF));
		$otherMimeEmail->setFrom($sender->getEmailAddress());
		$otherMimeEmail->setTXTBody(self::$otherEmail['message']);

		$this->assertSame(
			array(
				array(
					'recipient' => self::$email['recipient'],
					'subject' => self::$email['subject'],
					'message' => $mimeEmail->get($buildParameter),
					'headers' => $mimeEmail->txtHeaders(),
				),
				array(
					'recipient' => self::$otherEmail['recipient'],
					'subject' => self::$otherEmail['subject'],
					'message' => $otherMimeEmail->get($buildParameter),
					'headers' => $otherMimeEmail->txtHeaders(),
				),
			),
			$this->fixture->getAllEmail()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
		$otherRecipient->__destruct();
		$otherEmail->__destruct();
	}

	/**
	 * @test
	 */
	public function sendWithoutSenderThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$email must have a sender set.'
		);

		$eMail = new tx_oelib_Mail();

		$this->fixture->send($eMail);
	}

	/**
	 * @test
	 */
	public function sendWithHTMLMessage() {
		$htmlMessage = '<h1>Very cool HTML message</h1>' . LF .
			'<p>Great to have HTML e-mails in oelib.</p>';

		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setHTMLMessage($htmlMessage);

		$this->fixture->send($eMail);

		$characterSet = $this->getCharacterSet();
		$this->assertSame(
			array(
				'recipient' => self::$email['recipient'],
				'subject' => self::$email['subject'],
				'message' => $htmlMessage,
				'headers' => 'MIME-Version: 1.0' . LF .
					'Content-Type: text/html;' . LF . ' charset=' . $characterSet . LF .
					'Content-Transfer-Encoding: quoted-printable' . LF .
					'From: any-sender@email-address.org' . LF,
			),
			$this->fixture->getLastEmail()
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function mailWithEmptySenderThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$emailAddress must not be empty.'
		);

		$this->fixture->mail('', 'subject', 'message');
	}

	/**
	 * @test
	 */
	public function mailWithEmptySubjectThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$subject must not be empty.'
		);

		$this->fixture->mail('john@doe.com', '', 'message');
	}

	/**
	 * @test
	 */
	public function mailWithEmptyMessageThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$message must not be empty.'
		);

		$this->fixture->mail('john@doe.com', 'subject', '');
	}

	/**
	 * @test
	 */
	public function sendForSubjectWithAsciiCharactersOnlyDoesNotEncodeIt() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$this->fixture->send($eMail);

		$this->assertSame(
			self::$email['subject'],
			$this->fixture->getLastSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendForSubjectWithNonAsciiCharactersEncodesItWithUtf8CharsetInformation() {
		if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] == '') {
			$this->markTestSkipped(
				'This test applies to installations with forceCharset only.'
			);
		}

		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('föö');
		$eMail->setMessage(self::$email['message']);

		$this->fixture->send($eMail);

		$this->assertContains(
			'utf-8',
			$this->fixture->getLastSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendForSubjectWithNonAsciiCharactersEncodesItWithUtfEightCharsetInformation() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4007000) {
			$this->markTestSkipped('This test is only applicable in TYPO3 < 4.7.');
		}
		if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] != '') {
			$this->markTestSkipped('This test applies to installations without forceCharset only.');
		}

		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('föö');
		$eMail->setMessage(self::$email['message']);

		$this->fixture->send($eMail);

		$this->assertContains(
			'UTF-8',
			$this->fixture->getLastSubject()
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning formatting the e-mail body.
	/////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function oneLineFeedIsKeptIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . LF . 'bar');

		$this->assertSame(
			'foo' . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function oneCarriageReturnIsReplacedByLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . CR . 'bar');

		$this->assertSame(
			'foo' . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function twoLineFeedsAreKeptIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . LF . LF . 'bar');

		$this->assertSame(
			'foo' . LF . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function twoCarriageReturnsAreReplacedByTwoLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . CR . CR . 'bar');

		$this->assertSame(
			'foo' . LF . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function severalLineFeedsAreReplacedByTwoLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . LF . LF . LF . LF . LF . 'bar');

		$this->assertSame(
			'foo' . LF . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function severalCarriageReturnsAreReplacedByTwoLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . CR . CR . CR . CR . CR . 'bar');

		$this->assertSame(
			'foo' . LF . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function emailBodyIsNotChangesWhenFormattingIsDisabled() {
		$this->fixture->sendFormattedEmails(FALSE);
		$this->fixture->sendEmail('', '', 'foo' . CR . CR . CR . CR . CR . 'bar');

		$this->assertSame(
			'foo' . CR . CR . CR . CR . CR . 'bar',
			$this->fixture->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function oneCrLfPairIsReplacedByLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo' . CRLF . 'bar');

		$this->assertSame(
			'foo' . LF . 'bar',
			$this->fixture->getLastBody()
		);
	}


	/////////////////////////////////
	// Tests concerning the headers
	/////////////////////////////////

	/**
	 * @test
	 */
	public function fromWithoutUmlautsInTheirNameStaysUnencoded() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'John Doe', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->fixture->send($eMail);

		$rawMail = $this->fixture->getLastEmail();
		$this->assertContains(
			'From: "John Doe" <any-sender@email-address.org>',
			$rawMail['headers']
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function fromWithUmlautsInTheirNameGetsEncoded() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'Jöhn Döe', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->fixture->send($eMail);

		$characterSet = $this->getCharacterSet();
		$encodedName = t3lib_div::encodeHeader(
			'Jöhn Döe', 'quoted-printable', $characterSet
		);

		$rawMail = $this->fixture->getLastEmail();
		$this->assertContains(
			'From: "' . $encodedName . '" <any-sender@email-address.org>',
			$rawMail['headers']
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function longFromWithoutUmlautsInTheirNameStaysUnchanged() {
		$senderName = 'Pfefferminzia Lakritzia Kunigunde Canneloria ' .
			'Coffeinia Hydrogenoxidia Pizzeria Ambrosia Antagonia Antipasti';
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			$senderName, 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->fixture->send($eMail);

		$rawMail = $this->fixture->getLastEmail();
		$this->assertContains(
			'From: "' . $senderName . '" <any-sender@email-address.org>',
			$rawMail['headers']
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}

	/**
	 * @test
	 */
	public function longFromWithUmlautsInTheirNameIsEncodedButStaysOtherwiseUnchanged() {
		$senderName = 'Pfefferminzia Lakritzia Kunigunde Canneloria Bäria ' .
			'Coffeinia Hydrogenoxidia Pizzeria Ambrosia Antagonia Antipasti';
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			$senderName, 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->fixture->send($eMail);

		$characterSet = $this->getCharacterSet();
		$encodedName = t3lib_div::encodeHeader(
			$senderName, 'quoted-printable', $characterSet
		);
		$this->assertNotContains(
			LF,
			$encodedName
		);

		$rawMail = $this->fixture->getLastEmail();
		$this->assertContains(
			'From: "' . $encodedName . '" <any-sender@email-address.org>',
			$rawMail['headers']
		);

		$sender->__destruct();
		$recipient->__destruct();
		$eMail->__destruct();
	}


	///////////////////////////////////////////////////////////
	// Tests concerning the additional headers in the e-mails
	///////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function sendForEmailWithAdditionalHeaderAddsThisHeaderToSentMail() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', 'any-sender@email-address.org'
		);

		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$email['recipient']
		);

		$eMail = new tx_oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);
		$eMail->setReturnPath('mail@foobar.com');

		$this->fixture->send($eMail);

		$sentMail = $this->fixture->getLastEmail();

		$this->assertContains(
			'Return-Path: <mail@foobar.com>',
			$sentMail['headers']
		);
	}
}
?>