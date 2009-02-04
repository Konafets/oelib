<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the mail class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_Mail_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Mail
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Mail();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	////////////////////////////////////////////////////
	// Tests regarding setting and getting the sender.
	////////////////////////////////////////////////////

	public function testGetSenderInitiallyReturnsNull() {
		$this->assertEquals(
			null,
			$this->fixture->getSender()
		);
	}

	public function testGetSenderForNonEmptySenderReturnsSender() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);

		$this->fixture->setSender($sender);

		$this->assertEquals(
			$sender,
			$this->fixture->getSender()
		);

		$sender->__destruct();
	}

	/**
	 * @test
	 */
	public function hasSenderInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasSender()
		);
	}

	/**
	 * @test
	 */
	public function hasSenderWithSenderReturnsTrue() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);

		$this->fixture->setSender($sender);

		$this->assertTrue(
			$this->fixture->hasSender()
		);

		$sender->__destruct();
	}


	////////////////////////////////////////////////////////
	// Tests regarding adding and getting the recipients.
	////////////////////////////////////////////////////////

	public function testGetRecipientsInitiallyReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getRecipients()
		);
	}

	public function testGetRecipientsWithOneRecipientReturnsOneRecipient() {
		$recipient = new tx_oelib_tests_fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);
		$this->fixture->addRecipient($recipient);

		$this->assertEquals(
			array($recipient),
			$this->fixture->getRecipients()
		);

		$recipient->__destruct();
	}

	public function testGetRecipientsWithTwoRecipientsReturnsTwoRecipients() {
		$recipient1 = new tx_oelib_tests_fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);
		$recipient2 = new tx_oelib_tests_fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);
		$this->fixture->addRecipient($recipient1);
		$this->fixture->addRecipient($recipient2);

		$this->assertEquals(
			array($recipient1, $recipient2),
			$this->fixture->getRecipients()
		);

		$recipient1->__destruct();
		$recipient2->__destruct();
	}


	/////////////////////////////////////////////////////
	// Tests regarding setting and getting the subject.
	/////////////////////////////////////////////////////

	public function testGetSubjectInitiallyReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getSubject()
		);
	}

	public function testGetSubjectWithNonEmptySubjectReturnsSubject() {
		$this->fixture->setSubject('test subject');

		$this->assertEquals(
			'test subject',
			$this->fixture->getSubject()
		);
	}

	public function testSetSubjectWithEmptySubjectThrowsException() {
		$this->setExpectedException(
			'Exception', '$subject must not be empty.'
		);

		$this->fixture->setSubject('');
	}

	public function testSetSubjectWithSubjectContainingCarriageReturnThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$subject must not contain any line breaks or carriage returns.'
		);

		$this->fixture->setSubject('test ' . CR . ' subject');
	}

	public function testSetSubjectWithSubjectContainingLinefeedThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$subject must not contain any line breaks or carriage returns.'
		);

		$this->fixture->setSubject('test ' . LF . ' subject');
	}

	public function testSetSubjectWithSubjectContainingCarriageReturnLinefeedThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$subject must not contain any line breaks or carriage returns.'
		);

		$this->fixture->setSubject('test ' . CRLF . ' subject');
	}


	/////////////////////////////////////////////////////
	// Tests regarding setting and getting the message.
	/////////////////////////////////////////////////////

	public function testGetMessageInitiallyReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getMessage()
		);
	}

	public function testGetMessageWithNonEmptyMessageReturnsMessage() {
		$this->fixture->setMessage('test message');

		$this->assertEquals(
			'test message',
			$this->fixture->getMessage()
		);
	}

	public function testSetMessageWithEmptyMessageThrowsException() {
		$this->setExpectedException(
			'Exception', '$message must not be empty.'
		);

		$this->fixture->setMessage('');
	}


	////////////////////////////////////////////////////
	// Tests regarding adding and getting attachments.
	////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAttachmentsInitiallyReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getAttachments()
		);
	}

	/**
	 * @test
	 */
	public function getAttachmentsWithOneAttachmentReturnsOneAttachment() {
		$attachment = new tx_oelib_Attachment();
		$attachment->setFileName('test.txt');
		$attachment->setContentType('text/plain');
		$attachment->setContent('Test');
		$this->fixture->addAttachment($attachment);

		$this->assertEquals(
			array($attachment),
			$this->fixture->getAttachments()
		);
	}

	/**
	 * @test
	 */
	public function getAttachmentsWithTwoAttachmentsReturnsTwoAttachments() {
		$attachment = new tx_oelib_Attachment();
		$attachment->setFileName('test.txt');
		$attachment->setContentType('text/plain');
		$attachment->setContent('Test');
		$this->fixture->addAttachment($attachment);

		$otherAttachment = new tx_oelib_Attachment();
		$otherAttachment->setFileName('second_test.txt');
		$otherAttachment->setContentType('text/plain');
		$otherAttachment->setContent('Second Test');
		$this->fixture->addAttachment($otherAttachment);

		$this->assertEquals(
			array($attachment, $otherAttachment),
			$this->fixture->getAttachments()
		);
	}
}
?>