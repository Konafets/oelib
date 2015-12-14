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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_RealMailerTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_RealMailer
	 */
	private $subject = NULL;

	/**
	 * @var t3lib_mail_Message|PHPUnit_Framework_MockObject_MockObject
	 */
	private $message = NULL;

	protected function setUp() {
		$this->subject = new Tx_Oelib_RealMailer();

		$this->message = $this->getMock('t3lib_mail_Message', array('send', '__destruct'));
		$finalMailMessageClassName = t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 6000000
			? 'TYPO3\\CMS\\Core\\Mail\\MailMessage' : 't3lib_mail_Message';
		\TYPO3\CMS\Core\Utility\GeneralUtility::addInstance($finalMailMessageClassName, $this->message);
	}

	/**
	 * @test
	 */
	public function sendSendsEmail() {
		$senderAndRecipient = new Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole('John Doe', 'john@example.com');
		$eMail = new Tx_Oelib_Mail();
		$eMail->setSender($senderAndRecipient);
		$eMail->addRecipient($senderAndRecipient);
		$eMail->setSubject('Hello world!');
		$eMail->setMessage('Welcome!');

		$this->message->expects(self::once())->method('send');

		$this->subject->send($eMail);
	}
}