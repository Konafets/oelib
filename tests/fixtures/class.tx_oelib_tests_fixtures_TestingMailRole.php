<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_oelib_tests_fixtures_TestingMailRole' for the 'oelib' extension.
 *
 * This class represents an e-mail role, e.g. a sender or a recipient.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_tests_fixtures_TestingMailRole extends tx_oelib_Object implements tx_oelib_Interface_MailRole {
	/**
	 * @var array the data of this object
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
	 */
	public function setEmailAddress($eMail) {
		$this->setAsString('email', $eMail);
	}
}
?>