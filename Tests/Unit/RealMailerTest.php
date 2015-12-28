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
	 * @var \TYPO3\CMS\Core\Mail\MailMessage|PHPUnit_Framework_MockObject_MockObject
	 */
	private $message = NULL;

	protected function setUp() {
		$this->subject = new Tx_Oelib_RealMailer();

		$this->message = $this->getMock('TYPO3\CMS\Core\Mail\MailMessage', array('send', '__destruct'));
		\TYPO3\CMS\Core\Utility\GeneralUtility::addInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage', $this->message);
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