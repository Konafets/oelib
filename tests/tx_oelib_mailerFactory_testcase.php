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
 * Testcase for the mailer factory class and the e-mail collector class in the
 * 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 */

require_once(t3lib_extMgm::extPath('oelib') . 'tx_oelib_commonConstants.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_mailerFactory.php');

class tx_oelib_mailerFactory_testcase extends tx_phpunit_testcase {
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
		tx_oelib_mailerFactory::getInstance()->discardInstance();
		unset($this->fixture);
	}

	public function testGetMailerInTestMode() {
		$this->assertEquals(
			'tx_oelib_emailCollector',
			get_class($this->fixture)
		);
	}

	public function testGetMailerInNonTestMode() {
		// initially, the test mode is disabled
		tx_oelib_mailerFactory::getInstance()->discardInstance();

		$this->assertEquals(
			'tx_oelib_realMailer',
			get_class(tx_oelib_mailerFactory::getInstance()->getMailer())
		);
	}

	public function testGetMailerReturnsTheSameObjectWhenTheInstanceWasNotDiscarded() {
		$this->assertSame(
			$this->fixture,
			tx_oelib_mailerFactory::getInstance()->getMailer()
		);
	}

	public function testGetMailerNotReturnsTheSameObjectWhenTheInstanceWasDiscarded() {
		tx_oelib_mailerFactory::getInstance()->discardInstance();
		$this->assertNotSame(
			$this->fixture,
			tx_oelib_mailerFactory::getInstance()->getMailer()
		);
	}

	public function testStoreAnEmailAndGetIt() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);

		$this->assertEquals(
			self::$email,
			$this->fixture->getLastEmail()
		);
	}

	public function testStoreTwoEmailsAndGetTheLastEmail() {
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

		$this->assertEquals(
			self::$otherEmail,
			$this->fixture->getLastEmail()
		);
	}

	public function testStoreNoEmailAndTryToGetTheLastEmail() {
		$this->assertEquals(
			array(),
			$this->fixture->getLastEmail()
		);
	}

	public function testStoreTwoEmailsAndGetBothEmails() {
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

		$this->assertEquals(
			array(
				self::$email,
				self::$otherEmail
			),
			$this->fixture->getAllEmail()
		);
	}

	public function testSendEmailReturnsTrueIfTheReturnValueIsSetToTrue() {
		$this->fixture->setFakedReturnValue(true);

		$this->assertTrue(
			$this->fixture->sendEmail('', '', '')
		);
	}

	public function testSendEmailReturnsFalseIfTheReturnValueIsSetToFalse() {
		$this->fixture->setFakedReturnValue(false);

		$this->assertFalse(
			$this->fixture->sendEmail('', '', '')
		);
	}

	public function testGetLastRecipientReturnsTheRecipientOfTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertEquals(
			self::$email['recipient'],
			$this->fixture->getLastRecipient()
		);
	}

	public function testGetLastRecipientReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertEquals(
			'',
			$this->fixture->getLastRecipient()
		);
	}

	public function testGetLastSubjectReturnsTheSubjectOfTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertEquals(
			self::$email['subject'],
			$this->fixture->getLastSubject()
		);
	}

	public function testGetLastSubjectReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertEquals(
			'',
			$this->fixture->getLastSubject()
		);
	}

	public function testGetLastBodyReturnsTheBodyOfTheLastEmail() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);

		$this->assertEquals(
			self::$email['message'],
			$this->fixture->getLastBody()
		);
	}

	public function testGetLastBodyReturnsAnEmptyStringIfThereWasNoEmail() {
		$this->assertEquals(
			'',
			$this->fixture->getLastBody()
		);
	}

	public function testGetLastHeadersIfTheEmailDoesNotHaveAny() {
		$this->fixture->sendEmail(
			self::$otherEmail['recipient'],
			self::$otherEmail['subject'],
			self::$otherEmail['message']
		);

		$this->assertEquals(
			'',
			$this->fixture->getLastHeaders()
		);
	}

	public function testGetLastHeadersReturnsTheLastHeaders() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message'],
			self::$email['headers']
		);

		$this->assertEquals(
			self::$email['headers'],
			$this->fixture->getLastHeaders()
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning formatting the e-mail body.
	/////////////////////////////////////////////////

	public function testOneLineFeedIsReplacedByCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.LF.'bar');

		$this->assertEquals(
			'foo'.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testOneCarriageReturnIsReplacedByCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.CR.'bar');

		$this->assertEquals(
			'foo'.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testTwoLineFeedsAreReplacedByTwoCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.LF.LF.'bar');

		$this->assertEquals(
			'foo'.CRLF.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testTwoCarriageReturnsAreReplacedByTwoCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.CR.CR.'bar');

		$this->assertEquals(
			'foo'.CRLF.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testSeveralLineFeedsAreReplacedByTwoCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.LF.LF.LF.LF.LF.'bar');

		$this->assertEquals(
			'foo'.CRLF.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testSeveralCarriageReturnsAreReplacedByTwoCrLfIfFormatingIsEnabled() {
		$this->fixture->sendEmail('', '', 'foo'.CR.CR.CR.CR.CR.'bar');

		$this->assertEquals(
			'foo'.CRLF.CRLF.'bar',
			$this->fixture->getLastBody()
		);
	}

	public function testEmailBodyIsNotFormattedWhenFormattingIsDisabled() {
		$this->fixture->sendFormattedEmails(false);
		$this->fixture->sendEmail('', '', 'foo'.CR.CR.CR.CR.CR.'bar');

		$this->assertEquals(
			'foo'.CR.CR.CR.CR.CR.'bar',
			$this->fixture->getLastBody()
		);
	}


	/////////////////////
	// Utility functions
	/////////////////////

	/**
	 * Adds the headers to the static test e-mail as LF cannot be used when it
	 * is defined.
	 */
	private function addHeadersToTestEmail() {
		self::$email['headers'] = 'From: any-sender@email-address.org'.LF.
			'CC: "another recipient" <another-recipient@email-address.org>'.LF.
			'Reply-To: any-sender@email-address.org';
	}
}
?>