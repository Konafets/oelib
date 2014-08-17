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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_MailTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Mail
	 */
	private $subject;

	public function setUp() {
		$this->subject = new Tx_Oelib_Mail();
	}

	public function tearDown() {
		unset($this->subject);
	}


	////////////////////////////////////////////////////
	// Tests regarding setting and getting the sender.
	////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSenderInitiallyReturnsNull() {
		$this->assertNull(
			$this->subject->getSender()
		);
	}

	/**
	 * @test
	 */
	public function getSenderForNonEmptySenderReturnsSender() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);

		$this->subject->setSender($sender);

		$this->assertSame(
			$sender,
			$this->subject->getSender()
		);
	}

	/**
	 * @test
	 */
	public function hasSenderInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->subject->hasSender()
		);
	}

	/**
	 * @test
	 */
	public function hasSenderWithSenderReturnsTrue() {
		$sender = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);

		$this->subject->setSender($sender);

		$this->assertTrue(
			$this->subject->hasSender()
		);
	}


	////////////////////////////////////////////////////////
	// Tests regarding adding and getting the recipients.
	////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRecipientsInitiallyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getRecipients()
		);
	}

	/**
	 * @test
	 */
	public function getRecipientsWithOneRecipientReturnsOneRecipient() {
		$recipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);
		$this->subject->addRecipient($recipient);

		$this->assertSame(
			array($recipient),
			$this->subject->getRecipients()
		);
	}

	/**
	 * @test
	 */
	public function getRecipientsWithTwoRecipientsReturnsTwoRecipients() {
		$recipient1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);
		$recipient2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole(
			'John Doe', 'foo@bar.com'
		);
		$this->subject->addRecipient($recipient1);
		$this->subject->addRecipient($recipient2);

		$this->assertSame(
			array($recipient1, $recipient2),
			$this->subject->getRecipients()
		);
	}


	/////////////////////////////////////////////////////
	// Tests regarding setting and getting the subject.
	/////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSubjectInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function getSubjectWithNonEmptySubjectReturnsSubject() {
		$this->subject->setSubject('test subject');

		$this->assertSame(
			'test subject',
			$this->subject->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function setSubjectWithEmptySubjectThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$subject must not be empty.'
		);

		$this->subject->setSubject('');
	}

	/**
	 * @test
	 */
	public function setSubjectWithSubjectContainingCarriageReturnThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$subject must not contain any line breaks or carriage returns.'
		);

		$this->subject->setSubject('test ' . CR . ' subject');
	}

	/**
	 * @test
	 */
	public function setSubjectWithSubjectContainingLinefeedThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$subject must not contain any line breaks or carriage returns.'
		);

		$this->subject->setSubject('test ' . LF . ' subject');
	}

	/**
	 * @test
	 */
	public function setSubjectWithSubjectContainingCarriageReturnLinefeedThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$subject must not contain any line breaks or carriage returns.'
		);

		$this->subject->setSubject('test ' . CRLF . ' subject');
	}


	/////////////////////////////////////////////////////
	// Tests regarding setting and getting the message.
	/////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getMessageInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getMessage()
		);
	}

	/**
	 * @test
	 */
	public function getMessageWithNonEmptyMessageReturnsMessage() {
		$this->subject->setMessage('test message');

		$this->assertSame(
			'test message',
			$this->subject->getMessage()
		);
	}

	/**
	 * @test
	 */
	public function setMessageWithEmptyMessageThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$message must not be empty.'
		);

		$this->subject->setMessage('');
	}

	/**
	 * @test
	 */
	public function hasMessageInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->subject->hasMessage()
		);
	}

	/**
	 * @test
	 */
	public function hasMessageWithMessageReturnsTrue() {
		$this->subject->setMessage('test');

		$this->assertTrue(
			$this->subject->hasMessage()
		);
	}


	//////////////////////////////////////////////////////////
	// Tests regarding setting and getting the HTML message.
	//////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getHTMLMessageInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getHTMLMessage()
		);
	}

	/**
	 * @test
	 */
	public function getHTMLMessageWithNonEmptyMessageReturnsMessage() {
		$this->subject->setHTMLMessage('test message');

		$this->assertSame(
			'test message',
			$this->subject->getHTMLMessage()
		);
	}

	/**
	 * @test
	 */
	public function setHTMLMessageWithEmptyMessageThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$message must not be empty.'
		);

		$this->subject->setHTMLMessage('');
	}

	/**
	 * @test
	 */
	public function hasHTMLMessageInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->subject->hasHTMLMessage()
		);
	}

	/**
	 * @test
	 */
	public function hasHTMLMessageWithHTMLMessageReturnsTrue() {
		$this->subject->setHTMLMessage('<p>test</p>');

		$this->assertTrue(
			$this->subject->hasHTMLMessage()
		);
	}

	////////////////////////////////////////////////////
	// Tests regarding adding and getting attachments.
	////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAttachmentsInitiallyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getAttachments()
		);
	}

	/**
	 * @test
	 */
	public function getAttachmentsWithOneAttachmentReturnsOneAttachment() {
		$attachment = new Tx_Oelib_Attachment();
		$attachment->setFileName('test.txt');
		$attachment->setContentType('text/plain');
		$attachment->setContent('Test');
		$this->subject->addAttachment($attachment);

		$this->assertSame(
			array($attachment),
			$this->subject->getAttachments()
		);
	}

	/**
	 * @test
	 */
	public function getAttachmentsWithTwoAttachmentsReturnsTwoAttachments() {
		$attachment = new Tx_Oelib_Attachment();
		$attachment->setFileName('test.txt');
		$attachment->setContentType('text/plain');
		$attachment->setContent('Test');
		$this->subject->addAttachment($attachment);

		$otherAttachment = new Tx_Oelib_Attachment();
		$otherAttachment->setFileName('second_test.txt');
		$otherAttachment->setContentType('text/plain');
		$otherAttachment->setContent('Second Test');
		$this->subject->addAttachment($otherAttachment);

		$this->assertSame(
			array($attachment, $otherAttachment),
			$this->subject->getAttachments()
		);
	}


	//////////////////////////////////////////////////////
	// Tests regarding setting and getting the CSS file.
	//////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setCssFileForNoCssFileGivenDoesNotSetCssFile() {
		$this->subject->setCssFile('');

		$this->assertFalse(
			$this->subject->hasCssFile()
		);
	}

	/**
	 * @test
	 */
	public function setCssFileForStringGivenWhichIsNoFileDoesNotSetCssFile() {
		$this->subject->setCssFile('foo');

		$this->assertFalse(
			$this->subject->hasCssFile()
		);
	}

	/**
	 * @test
	 */
	public function setCssFileForGivenCssFileWithAbsolutePathSetsCssFile() {
		$this->subject->setCssFile(t3lib_extMgm::extPath('oelib') . 'Tests/Unit/Fixtures/test.css');

		$this->assertTrue(
			$this->subject->hasCssFile()
		);
	}

	/**
	 * @test
	 */
	public function setCssFileForGivenCssFileWithAbsoluteExtPathSetsCssFile() {
		$this->subject->setCssFile('EXT:oelib/Tests/Unit/Fixtures/test.css');

		$this->assertTrue(
			$this->subject->hasCssFile()
		);
	}

	/**
	 * @test
	 */
	public function setCssFileForGivenCssFileStoresContentsOfCssFile() {
		$this->subject->setCssFile('EXT:oelib/Tests/Unit/Fixtures/test.css');

		$this->assertContains(
			'h3',
			$this->subject->getCssFile()
		);
	}

	/**
	 * @test
	 */
	public function setCssFileForSetCssFileAndThenGivenEmptyStringClearsStoredCssFileData() {
		$this->subject->setCssFile('EXT:oelib/Tests/Unit/Fixtures/test.css');
		$this->subject->setCssFile('');

		$this->assertFalse(
			$this->subject->hasCssFile()
		);
	}

	/**
	 * @test
	 */
	public function setCssFileForSetCssFileAndThenGivenNewCssFileRemovesOldCssDataFromStorage() {
		$this->subject->setCssFile('EXT:oelib/Tests/Unit/Fixtures/test.css');
		$this->subject->setCssFile('EXT:oelib/Tests/Unit/Fixtures/test_2.css');

		$this->assertNotContains(
			'h3',
			$this->subject->getCssFile()
		);
	}

	/**
	 * @test
	 */
	public function setCssFileForSetCssFileAndThenGivenNewCssFileStoresNewCssData() {
		$this->subject->setCssFile('EXT:oelib/Tests/Unit/Fixtures/test.css');
		$this->subject->setCssFile('EXT:oelib/Tests/Unit/Fixtures/test_2.css');

		$this->assertContains(
			'h4',
			$this->subject->getCssFile()
		);
	}


	/////////////////////////////////////////////////////////////////////////////
	// Tests concerning the mogrification of the HTML Messages and the CSS file
	/////////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setHtmlMessageWithNoCssFileStoredOnlyStoresTheHtmlMessage() {
		$htmlMessage =
			'<html>' .
				'<head><title>foo</title></head>' .
				'<body><h3>Bar</h3></body>' .
			'</html>';
		$this->subject->setHTMLMessage($htmlMessage);

		$this->assertSame(
			$htmlMessage,
			$this->subject->getHTMLMessage()
		);
	}

	/**
	 * @test
	 */
	public function setHtmlMessageWithCssFileStoredStoresAttributesFromCssInHtmlMessage() {
		$this->subject->setCssFile(t3lib_extMgm::extPath('oelib') . 'Tests/Unit/Fixtures/test.css');
		$this->subject->setHTMLMessage(
			'<html>' .
				'<head><title>foo</title></head>' .
				'<body><h3>Bar</h3></body>' .
			'</html>'
		);

		$this->assertSame(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"' .
				' "http://www.w3.org/TR/REC-html40/loose.dtd">' . LF .
			'<html>' . LF .
				'<head><title>foo</title></head>' . LF .
				'<body><h3 style="font-weight: bold;">Bar</h3></body>' . LF .
			'</html>'
			,
			$this->subject->getHTMLMessage()
		);
	}


	////////////////////////////////////////////
	// Tests concerning the additional headers
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAdditionalHeadersForNoAdditionalHeadersReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getAdditionalHeaders()
		);
	}

	/**
	 * @test
	 */
	public function setReturnPathForNoReturnPathSetSetsGivenReturnPath() {
		$this->subject->setReturnPath('foo@bar.com');

		$this->assertSame(
			array(
				'Return-Path' => '<foo@bar.com>',
				'Errors-To' => 'foo@bar.com',
			),
			$this->subject->getAdditionalHeaders()
		);
	}

	/**
	 * @test
	 */
	public function setReturnPathForAlreadySetReturnPathOverridesOldReturnPath() {
		$this->subject->setReturnPath('old@mail.com');
		$this->subject->setReturnPath('foo@bar.com');

		$this->assertSame(
			array(
				'Return-Path' => '<foo@bar.com>',
				'Errors-To' => 'foo@bar.com',
			),
			$this->subject->getAdditionalHeaders()
		);
	}

	/**
	 * @test
	 */
	public function setReturnPathForNoSetReturnPathAndEmptyStringGivenDoesNotSetAnyReturnPath() {
		$this->subject->setReturnPath('');

		$this->assertSame(
			array(),
			$this->subject->getAdditionalHeaders()
		);
	}

	/**
	 * @test
	 */
	public function setReturnPathForSetReturnPathAndEmptyStringGivenDoesNotUnsetReturnPath() {
		$this->subject->setReturnPath('foo@bar.com');
		$this->subject->setReturnPath('');

		$this->assertSame(
			array(
				'Return-Path' => '<foo@bar.com>',
				'Errors-To' => 'foo@bar.com',
			),
			$this->subject->getAdditionalHeaders()
		);
	}

	/**
	 * @test
	 */
	public function setReturnPathSetsMemberVariableReturnPath() {
		$this->subject->setReturnPath('foo@bar.com');

		$this->assertSame(
			'foo@bar.com',
			$this->subject->getReturnPath()
		);
	}

	/**
	 * @test
	 */
	public function getReturnPathInitiallyReturnsAnEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getReturnPath()
		);
	}

	/**
	 * @test
	 */
	public function setReturnPathForAlreadySetReturnPathAndNoStringGivenDoesNotOverrideTheReturnPath() {
		$this->subject->setReturnPath('foo@bar.com');
		$this->subject->setReturnPath('');

		$this->assertSame(
			'foo@bar.com',
			$this->subject->getReturnPath()
		);
	}

	/**
	 * @test
	 */
	public function getReturnPathForStringSetInReturnPathReturnsThisString() {
		$this->subject->setReturnPath('foo@bar.com');

		$this->assertSame(
			'foo@bar.com',
			$this->subject->getReturnPath()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalHeadersForNoAdditionalHeadersSetReturnsFalse() {
		$this->assertFalse(
			$this->subject->hasAdditionalHeaders()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalHeadersForAdditionalHeadersSetReturnsTrue() {
		$this->subject->setReturnPath('foo@bar.com');

		$this->assertTrue(
			$this->subject->hasAdditionalHeaders()
		);
	}
}