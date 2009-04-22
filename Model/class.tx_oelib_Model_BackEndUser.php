<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Saskia Metzler <saskia@merlin.owl.de>
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
 * Class 'tx_oelib_Model_BackEndUser' for the 'oelib' extension.
 *
 * This class represents a back-end user.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_oelib_Model_BackEndUser extends tx_oelib_Model implements tx_oelib_Interface_MailRole {
	/**
	 * Gets this user's user name.
	 *
	 * @return string this user's user name, will not be empty for valid users
	 */
	public function getUserName() {
		return $this->getAsString('username');
	}

	/**
	 * Gets this user's real name.
	 *
	 * @return string the user's real name, will not be empty for valid records
	 */
	public function getName() {
		return $this->getAsString('realName');
	}

	/**
	 * Gets the user's e-mail address
	 */
	public function getEMailAddress() {
		return $this->getAsString('email');
	}

	/**
	 * Gets this user's language. Will be a two-letter "lg_typo3" key of the
	 * "static_languages" table or "default" for the default language.
	 *
	 * @return string this user's language key, will not be empty
	 */
	public function getLanguage() {
		return ($this->hasLanguage() ? $this->getAsString('lang') : 'default');
	}

	/**
	 * Sets this user's language.
	 *
	 * @param string this user's language key, must be a two-letter "lg_typo3"
	 *               key of the "static_languages" table or "default" for the
	 *               default language
	 */
	public function setLanguage($language) {
		if ($language == '') {
			throw new Exception('$language must not be empty.');
		}

		$this->setAsString(
			'lang',
			($language != 'default') ? $language : ''
		);
	}

	/**
	 * Checks whether this user has a non-default language set.
	 *
	 * @return boolean true if this user has a non-default language set, false
	 *                 otherwise
	 */
	public function hasLanguage() {
		return $this->hasString('lang');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_BackEndUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_BackEndUser.php']);
}
?>