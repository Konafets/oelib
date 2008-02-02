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
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 */

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_mailerFactory.php');

class tx_oelib_mailerFactory_testcase extends tx_phpunit_testcase {
	private $fixture;

	private static $email = array(
		'recipient' => 'any-recipient@email-address.org',
		'subject' => 'any subject',
		'message' => 'any message'
	);
	private static $otherEmail = array(
		'recipient' => 'any-other-recipient@email-address.org',
		'subject' => 'any other subject',
		'message' => 'any other message'
	);

	protected function setUp() {
		// Only the instance with an enabled test mode can be tested as in the
		// non-test mode e-mails are sent.
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->fixture = tx_oelib_mailerFactory::getInstance()->getMailer();
	}

	protected function tearDown() {
		$this->fixture->cleanUpCollectedEmailData();
		unset($this->fixture);
	}

	public function testGetMailer() {
		$this->assertTrue(is_object($this->fixture));
	}

	public function testGetMailerReturnsTheSameObjectWhenCalledInTheSameClassInTheSameMode() {
		$this->assertSame(
			$this->fixture,
			tx_oelib_mailerFactory::getInstance()->getMailer()
		);
	}

	public function testGetMailerNotReturnsTheSameObjectWhenCalledInTheSameClassInAnotherMode() {
		tx_oelib_mailerFactory::getInstance()->disableTestMode();
		$this->assertNotSame(
			$this->fixture,
			tx_oelib_mailerFactory::getInstance()->getMailer()
		);
	}

	public function testStoreAnEmailAndGetIt() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
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
			self::$email['message']
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

	public function testStoreAnEmailAndCleanUpTheStoredEmails() {
		$this->fixture->sendEmail(
			self::$email['recipient'],
			self::$email['subject'],
			self::$email['message']
		);
		$this->fixture->cleanUpCollectedEmailData();

		$this->assertEquals(
			array(),
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
}
?>
