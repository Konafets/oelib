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
 * This class represents an e-mail role, e.g. a sender or a recipient.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_Unit_Fixtures_TestingMailRole extends Tx_Oelib_Object implements Tx_Oelib_Interface_MailRole {
	/**
	 * @var string[] the data of this object
	 */
	private $data = array();

	/**
	 * The constructor. Sets the name and the e-mail address of the e-mail role.
	 *
	 * @param string $name
	 *        the name of the e-mail role, may be empty
	 * @param string $eMail
	 *        the e-mail address of the e-mail role, may be empty
	 */
	public function __construct($name, $eMail) {
		$this->setName($name);
		$this->setEmailAddress($eMail);
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->data);
	}

	/**
	 * Sets the value of the data item for the key $key.
	 *
	 * @param string $key
	 *        the key of the data item to set, must not be empty
	 * @param mixed $value
	 *        the data for the key $key
	 *
	 * @return void
	 */
	protected function set($key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param string $key
	 *        the key of the data item to get, must not be empty
	 *
	 * @return mixed the data for the key $key, will be an empty string
	 *               if the key has not been set yet
	 */
	protected function get($key) {
		if (!isset($this->data[$key])) {
			return '';
		}

		return $this->data[$key];
	}

	/**
	 * Returns the real name of the e-mail role.
	 *
	 * @return string the real name of the e-mail role, might be empty
	 */
	public function getName() {
		return $this->getAsString('name');
	}

	/**
	 * Sets the real name of the e-mail role.
	 *
	 * @param string $name
	 *        the real name of the e-mail role, may be empty
	 *
	 * @return void
	 */
	public function setName($name) {
		$this->setAsString('name', $name);
	}

	/**
	 * Returns the e-mail address of the e-mail role.
	 *
	 * @return string the e-mail address of the e-mail role, might be empty
	 */
	public function getEmailAddress() {
		return $this->getAsString('email');
	}

	/**
	 * Sets the e-mail address of the e-mail role.
	 *
	 * @param string $eMail
	 *        the e-mail address of the e-mail role, may be empty
	 *
	 * @return void
	 */
	public function setEmailAddress($eMail) {
		$this->setAsString('email', $eMail);
	}
}