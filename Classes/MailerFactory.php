<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2014 Saskia Metzler <saskia@merlin.owl.de>
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