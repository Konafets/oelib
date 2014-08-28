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
	private $subject = NULL;

	private static $email = array(
		'recipient' => 'any-recipient@email-address.org',
		'subject' => 'any subject',
		'message' => 'any message',
		'headers' => '',
	);
	private static $otherEmail = array(
		'recipient' => 'any-other-recipient@email-address.org',
		'subject' => 'any other subject',
		'message' => 'any other message',
		'headers' => '',
	);

	protected function setUp() {
		// Only the instance with an enabled test mode can be tested as in the
		// non-test mode e-mails are sent.
		Tx_Oelib_MailerFactory::getInstance()->enableTestMode();
		$this->subject = Tx_Oelib_MailerFactory::getInstance()->getMailer();

		$this->addHeadersToTestEmail();
	}

	protected function tearDown() {
		Tx_Oelib_MailerFactory::purgeInstance();
		unset($this->subject);
	}


	/*
	 * Utility functions
	 */

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


	/*
	 * Tests concerning the basic functionality
	 */

	/**
	 * @test
	 */
	public function getMailerInTestMode() {
		$this->assertSame(
			'Tx_Oelib_EmailCollector',
			get_class($this->subject)
		);
	}

	/**
	 * @test
	 */
	public function getMailerInNonTestMode() {
		// initially, the test mode is disabled
		Tx_Oelib_MailerFactory::purgeInstance();

		$this->assertSame(
			'Tx_Oelib_RealMailer',
			get_class(Tx_Oelib_MailerFactory::getInstance()->getMailer())
		);
	}

	/**
	 * @test
	 */
	public function getMailerReturnsTheSameObjectWhenTheInstanceWasNotDiscarded() {
		$this->assertSame(
			$this->subject,
			Tx_Oelib_MailerFactory::getInstance()->getMailer()
		);
	}

	/**
	 * @test
	 */
	public function getMailerAfterPurgeInstanceReturnsNewObject() {
		Tx_Oelib_MailerFactory::purgeInstance();

		$this->assertNotSame(
			$this->subject,
			Tx_Oelib_MailerFactory::getInstance()->getMailer()
		);
	}

	/**
	 * @test
	 */
	public function storeAnEmailAndGetIt() {
		$this->subject->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);

		$this->assertSame(
			self::$email,
			$this->subject->getLastEmail()
		);
	}

	/**
	 * @test
	 */
	public function storeTwoEmailsAndGetTheLastEmail() {
		$this->subject->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);
		$this->subject->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertSame(
			self::$otherEmail,
			$this->subject->getLastEmail()
		);
	}

	/**
	 * @test
	 */
	public function storeNoEmailAndTryToGetTheLastEmail() {
		$this->assertSame(
			array(),
			$this->subject->getLastEmail()
		);
	}

	/**
	 * @test
	 */
	public function storeTwoEmailsAndGetBothEmails() {
		$this->subject->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);
		$this->subject->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertSame(
			array(
				self::$email,
				self::$otherEmail
			),
			$this->subject->getAllEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailReturnsTrueIfTheReturnValueIsSetToTrue() {
		$this->subject->setFakedReturnValue(TRUE);

		$this->assertTrue(
			$this->subject->sendEmail('', '', '')
		);
	}

	/**
	 * @test
	 */
	public function sendEmailReturnsFalseIfTheReturnValueIsSetToFalse() {
		$this->subject->setFakedReturnValue(FALSE);

		$this->assertFalse(
			$this->subject->sendEmail('', '', '')
		);
	}

	/**
	 * @test
	 */
	public function getLastRecipientReturnsTheRecipientOfTheLastEmail() {
		$this->subject->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertSame(
			self::$email['recipient'],
			$this->subject->getLastRecipient()
		);
	}

	/**
	 * @test
	 */
	public function getLastRecipientReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertSame(
			'',
			$this->subject->getLastRecipient()
		);
	}

	/**
	 * @test
	 */
	public function getLastSubjectReturnsTheSubjectOfTheLastEmail() {
		$this->subject->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertSame(
			self::$email['subject'],
			$this->subject->getLastSubject()
		);
	}

	/**
	 * @test
	 */
	public function getLastSubjectReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertSame(
			'',
			$this->subject->getLastSubject()
		);
	}

	/**
	 * @test
	 */
	public function getLastBodyReturnsTheBodyOfTheLastEmail() {
		$this->subject->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertSame(
			self::$email['message'],
			$this->subject->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function getLastBodyReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertSame(
			'',
			$this->subject->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function getLastHeadersIfTheEmailDoesNotHaveAny() {
		$this->subject->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertSame(
			'',
			$this->subject->getLastHeaders()
		);
	}

	/**
	 * @test
	 */
	public function getLastHeadersReturnsTheLastHeaders() {
		$this->subject->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);

		$this->assertSame(
			self::$email['headers'],
			$this->subject->getLastHeaders()
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$this->subject->send($eMail);

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
			$this->subject->getLastEmail()
		);
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(
			'one long line ...........................................' . CRLF .
			'now a blank line:' . LF . LF .
			'another long line .........................................' . LF .
			'and a line with umlauts: Hörbär saß früh.'
		);

		$this->subject->send($eMail);

		$this->assertNotContains(
			CR,
			$this->subject->getLastBody()
		);
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$otherRecipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$otherEmail['recipient']
		);

		$otherEmail = new Tx_Oelib_Mail();
		$otherEmail->setSender($sender);
		$otherEmail->addRecipient($otherRecipient);
		$otherEmail->setSubject(self::$otherEmail['subject']);
		$otherEmail->setMessage(self::$otherEmail['message']);

		$this->subject->send($eMail);
		$this->subject->send($otherEmail);

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
			$this->subject->getLastEmail()
		);
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$otherRecipient =new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'', self::$otherEmail['recipient']
		);

		$otherEmail = new Tx_Oelib_Mail();
		$otherEmail->setSender($sender);
		$otherEmail->addRecipient($otherRecipient);
		$otherEmail->setSubject(self::$otherEmail['subject']);
		$otherEmail->setMessage(self::$otherEmail['message']);

		$this->subject->send($eMail);
		$this->subject->send($otherEmail);

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
			$this->subject->getAllEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendWithoutSenderThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$email must have a sender set.'
		);

		$eMail = new Tx_Oelib_Mail();

		$this->subject->send($eMail);
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setHTMLMessage($htmlMessage);

		$this->subject->send($eMail);

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
			$this->subject->getLastEmail()
		);
	}

	/**
	 * @test
	 */
	public function mailWithEmptySenderThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$emailAddress must not be empty.'
		);

		$this->subject->mail('', 'subject', 'message');
	}

	/**
	 * @test
	 */
	public function mailWithEmptySubjectThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$subject must not be empty.'
		);

		$this->subject->mail('john@doe.com', '', 'message');
	}

	/**
	 * @test
	 */
	public function mailWithEmptyMessageThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$message must not be empty.'
		);

		$this->subject->mail('john@doe.com', 'subject', '');
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);

		$this->subject->send($eMail);

		$this->assertSame(
			self::$email['subject'],
			$this->subject->getLastSubject()
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('föö');
		$eMail->setMessage(self::$email['message']);

		$this->subject->send($eMail);

		$this->assertContains(
			'utf-8',
			$this->subject->getLastSubject()
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('föö');
		$eMail->setMessage(self::$email['message']);

		$this->subject->send($eMail);

		$this->assertContains(
			'UTF-8',
			$this->subject->getLastSubject()
		);
	}


	/*
	 * Tests concerning formatting the e-mail body.
	 */

	/**
	 * @test
	 */
	public function oneLineFeedIsKeptIfFormattingIsEnabled() {
		$this->subject->sendEmail('', '', 'foo' . LF . 'bar');

		$this->assertSame(
			'foo' . LF . 'bar',
			$this->subject->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function oneCarriageReturnIsReplacedByLfIfFormattingIsEnabled() {
		$this->subject->sendEmail('', '', 'foo' . CR . 'bar');

		$this->assertSame(
			'foo' . LF . 'bar',
			$this->subject->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function twoLineFeedsAreKeptIfFormattingIsEnabled() {
		$this->subject->sendEmail('', '', 'foo' . LF . LF . 'bar');

		$this->assertSame(
			'foo' . LF . LF . 'bar',
			$this->subject->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function twoCarriageReturnsAreReplacedByTwoLfIfFormattingIsEnabled() {
		$this->subject->sendEmail('', '', 'foo' . CR . CR . 'bar');

		$this->assertSame(
			'foo' . LF . LF . 'bar',
			$this->subject->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function severalLineFeedsAreReplacedByTwoLfIfFormattingIsEnabled() {
		$this->subject->sendEmail('', '', 'foo' . LF . LF . LF . LF . LF . 'bar');

		$this->assertSame(
			'foo' . LF . LF . 'bar',
			$this->subject->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function severalCarriageReturnsAreReplacedByTwoLfIfFormattingIsEnabled() {
		$this->subject->sendEmail('', '', 'foo' . CR . CR . CR . CR . CR . 'bar');

		$this->assertSame(
			'foo' . LF . LF . 'bar',
			$this->subject->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function emailBodyIsNotChangesWhenFormattingIsDisabled() {
		$this->subject->sendFormattedEmails(FALSE);
		$this->subject->sendEmail('', '', 'foo' . CR . CR . CR . CR . CR . 'bar');

		$this->assertSame(
			'foo' . CR . CR . CR . CR . CR . 'bar',
			$this->subject->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function oneCrLfPairIsReplacedByLfIfFormattingIsEnabled() {
		$this->subject->sendEmail('', '', 'foo' . CRLF . 'bar');

		$this->assertSame(
			'foo' . LF . 'bar',
			$this->subject->getLastBody()
		);
	}


	/*
	 * Tests concerning the headers
	 */

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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->subject->send($eMail);

		$rawMail = $this->subject->getLastEmail();
		$this->assertContains(
			'From: "John Doe" <any-sender@email-address.org>',
			$rawMail['headers']
		);
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->subject->send($eMail);

		$characterSet = $this->getCharacterSet();
		$encodedName = t3lib_div::encodeHeader(
			'Jöhn Döe', 'quoted-printable', $characterSet
		);

		$rawMail = $this->subject->getLastEmail();
		$this->assertContains(
			'From: "' . $encodedName . '" <any-sender@email-address.org>',
			$rawMail['headers']
		);
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->subject->send($eMail);

		$rawMail = $this->subject->getLastEmail();
		$this->assertContains(
			'From: "' . $senderName . '" <any-sender@email-address.org>',
			$rawMail['headers']
		);
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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject('Hello world');
		$eMail->setMessage('Summertime...');

		$this->subject->send($eMail);

		$characterSet = $this->getCharacterSet();
		$encodedName = t3lib_div::encodeHeader(
			$senderName, 'quoted-printable', $characterSet
		);
		$this->assertNotContains(
			LF,
			$encodedName
		);

		$rawMail = $this->subject->getLastEmail();
		$this->assertContains(
			'From: "' . $encodedName . '" <any-sender@email-address.org>',
			$rawMail['headers']
		);
	}


	/*
	 * Tests concerning the additional headers in the e-mails
	 */

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

		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($sender);
		$eMail->addRecipient($recipient);
		$eMail->setSubject(self::$email['subject']);
		$eMail->setMessage(self::$email['message']);
		$eMail->setReturnPath('mail@foobar.com');

		$this->subject->send($eMail);

		$sentMail = $this->subject->getLastEmail();

		$this->assertContains(
			'Return-Path: <mail@foobar.com>',
			$sentMail['headers']
		);
	}
}