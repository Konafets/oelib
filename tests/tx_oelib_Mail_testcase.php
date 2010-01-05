<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
		$this->assertNull(
			$this->fixture->getSender()
		);
	}

	public function testGetSenderForNonEmptySenderReturnsSender() {
		$sender = new tx_oelib_tests_fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);

		$this->fixture->setSender($sender);

		$this->assertSame(
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

	/**
	 * @test
	 */
	public function hasMessageInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasMessage()
		);
	}

	/**
	 * @test
	 */
	public function hasMessageWithMessageReturnsTrue() {
		$this->fixture->setMessage('test');

		$this->assertTrue(
			$this->fixture->hasMessage()
		);
	}


	//////////////////////////////////////////////////////////
	// Tests regarding setting and getting the HTML message.
	//////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getHTMLMessageInitiallyReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getHTMLMessage()
		);
	}

	/**
	 * @test
	 */
	public function getHTMLMessageWithNonEmptyMessageReturnsMessage() {
		$this->fixture->setHTMLMessage('test message');

		$this->assertEquals(
			'test message',
			$this->fixture->getHTMLMessage()
		);
	}

	/**
	 * @test
	 */
	public function setHTMLMessageWithEmptyMessageThrowsException() {
		$this->setExpectedException(
			'Exception', '$message must not be empty.'
		);

		$this->fixture->setHTMLMessage('');
	}

	/**
	 * @test
	 */
	public function hasHTMLMessageInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasHTMLMessage()
		);
	}

	/**
	 * @test
	 */
	public function hasHTMLMessageWithHTMLMessageReturnsTrue() {
		$this->fixture->setHTMLMessage('<p>test</p>');

		$this->assertTrue(
			$this->fixture->hasHTMLMessage()
		);
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


	//////////////////////////////////////////////////////
	// Tests regarding setting and getting the CSS file.
	//////////////////////////////////////////////////////

	public function test_SetCssFile_ForNoCssFileGiven_DoesNotSetCssFile() {
		$this->fixture->setCssFile('');

		$this->assertFalse(
			$this->fixture->hasCssFile()
		);
	}

	public function test_SetCssFile_ForStringGivenWhichIsNoFile_DoesNotSetCssFile() {
		$this->fixture->setCssFile('foo');

		$this->assertFalse(
			$this->fixture->hasCssFile()
		);
	}

	public function test_SetCssFile_ForGivenCssFileWithAbsolutePath_SetsCssFile() {
		$this->fixture->setCssFile(
			t3lib_extMgm::extPath('oelib') . 'tests/fixtures/test.css'
		);

		$this->assertTrue(
			$this->fixture->hasCssFile()
		);
	}

	public function test_SetCssFile_ForGivenCssFileWithAbsoluteExtPath_SetsCssFile() {
		$this->fixture->setCssFile('EXT:oelib/tests/fixtures/test.css');

		$this->assertTrue(
			$this->fixture->hasCssFile()
		);
	}

	public function test_SetCssFile_ForGivenCssFile_StoresContentsOfCssFile() {
		$this->fixture->setCssFile('EXT:oelib/tests/fixtures/test.css');

		$this->assertContains(
			'h3',
			$this->fixture->getCssFile()
		);
	}

	public function test_SetCssFile_ForSetCssFileAndThenGivenEmtpyString_ClearesStoredCssFileData() {
		$this->fixture->setCssFile('EXT:oelib/tests/fixtures/test.css');
		$this->fixture->setCssFile('');

		$this->assertFalse(
			$this->fixture->hasCssFile()
		);
	}

	public function test_SetCssFile_ForSetCssFileAndThenGivenNewCssFile_RemovesOldCssDataFromStorage() {
		$this->fixture->setCssFile('EXT:oelib/tests/fixtures/test.css');
		$this->fixture->setCssFile('EXT:oelib/tests/fixtures/test_2.css');

		$this->assertNotContains(
			'h3',
			$this->fixture->getCssFile()
		);
	}

	public function test_SetCssFile_ForSetCssFileAndThenGivenNewCssFile_StoresNewCssData() {
		$this->fixture->setCssFile('EXT:oelib/tests/fixtures/test.css');
		$this->fixture->setCssFile('EXT:oelib/tests/fixtures/test_2.css');

		$this->assertContains(
			'h4',
			$this->fixture->getCssFile()
		);
	}


	/////////////////////////////////////////////////////////////////////////////
	// Tests concerning the mogrification of the HTML Messages and the CSS file
	/////////////////////////////////////////////////////////////////////////////

	public function test_SetHtmlMessage_WithNoCssFileStored_OnlyStoresTheHtmlMessage() {
		$htmlMessage =
			'<html>' .
				'<head><title>foo</title></head>' .
				'<body><h3>Bar</h3></body>' .
			'</html>';
		$this->fixture->setHTMLMessage($htmlMessage);

		$this->assertEquals(
			$htmlMessage,
			$this->fixture->getHTMLMessage()
		);
	}

	public function test_SetHtmlMessage_WithCssFileStored_StoresAttributesFromCssInHtmlMessage() {
		$this->fixture->setCssFile(
			t3lib_extMgm::extPath('oelib') . 'tests/fixtures/test.css'
		);
		$this->fixture->setHTMLMessage(
			'<html>' .
				'<head><title>foo</title></head>' .
				'<body><h3>Bar</h3></body>' .
			'</html>'
		);

		$this->assertEquals(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"' .
				' "http://www.w3.org/TR/REC-html40/loose.dtd">' . LF .
			'<html>' . LF .
				'<head><title>foo</title></head>' . LF .
				'<body><h3 style="font-weight: bold;">Bar</h3></body>' . LF .
			'</html>'
			,
			$this->fixture->getHTMLMessage()
		);
	}
}
?>