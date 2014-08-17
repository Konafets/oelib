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
 * This class returns either an instance of the Tx_Oelib_RealMailer which sends
 * e-mails or an instance of the tx_oelib_emailCollector. The collector stores
 * the data provided to sendEmail() and does not send it. This mode is for
 * testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_MailerFactory {
	/**
	 * @var Tx_Oelib_MailerFactory the singleton factory
	 */
	private static $instance = NULL;

	/**
	 * @var boolean whether the test mode is set
	 */
	private $isTestMode = FALSE;

	/**
	 * @var Tx_Oelib_AbstractMailer the mailer
	 */
	private $mailer = NULL;

	/**
	 * Don't call this constructor; use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->mailer);
	}

	/**
	 * Retrieves the singleton instance of the factory.
	 *
	 * @return Tx_Oelib_MailerFactory the singleton factory
	 */
	public static function getInstance() {
		if (!is_object(self::$instance)) {
			self::$instance = new Tx_Oelib_MailerFactory();
		}

		return self::$instance;
	}

	/**
	 * Retrieves the singleton mailer instance. Depending on the mode, this
	 * instance is either an e-mail collector or a real mailer.
	 *
	 * @return Tx_Oelib_AbstractMailer|Tx_Oelib_RealMailer|tx_oelib_emailCollector the singleton mailer object
	 */
	public function getMailer() {
		if ($this->isTestMode) {
			$className = 'tx_oelib_emailCollector';
		} else {
			$className = 'Tx_Oelib_RealMailer';
		}

		if (!is_object($this->mailer)
			|| (get_class($this->mailer) != $className)
		) {
			$this->mailer = t3lib_div::makeInstance($className);
		}

		return $this->mailer;
	}

	/**
	 * Purges the current instance so that getInstance will create a new instance.
	 *
	 * @return void
	 */
	public static function purgeInstance() {
		self::$instance = NULL;
	}

	/**
	 * Enables the test mode.
	 *
	 * @return void
	 */
	public function enableTestMode() {
		$this->isTestMode = TRUE;
	}
}