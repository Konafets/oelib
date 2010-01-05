<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_Model_FrontEndUser' for the 'oelib' extension.
 *
 * This class represents a front-end user.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Model_FrontEndUser extends tx_oelib_Model implements
	tx_oelib_Interface_MailRole, tx_oelib_Interface_Address
{
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
	 * First, the "name" field is checked. If that is empty, the fields
	 * "first_name" and "last_name" are checked. If those are empty as well,
	 * the user name is returned as a fallback value.
	 *
	 * @return string the user's real name, will not be empty for valid records
	 */
	public function getName() {
		if ($this->hasString('name')) {
			$result = $this->getAsString('name');
		} elseif (
			($this->hasString('first_name')) || ($this->hasString('last_name'))
		) {
			$result = trim(
				$this->getAsString('first_name') . ' ' .
					$this->getAsString('last_name')
			);
		} else {
			$result = $this->getUserName();
		}

		return $result;
	}

	/**
	 * Checks whether this user has a non-empty name.
	 *
	 * @return boolean true if this user has a non-empty name, false otherwise
	 */
	public function hasName() {
		return ($this->hasString('name') || $this->hasString('first_name')
			|| $this->hasString('last_name'));
	}

	/**
	 * Gets this user's company.
	 *
	 * @return string this user's company, may be empty
	 */
	public function getCompany() {
		return $this->getAsString('company');
	}

	/**
	 * Checks whether this user has a non-empty company set.
	 *
	 * @return boolean true if this user has a company set, false otherwise
	 */
	public function hasCompany() {
		return $this->hasString('company');
	}

	/**
	 * Gets this user's street.
	 *
	 * @return string this user's street, may be multi-line, may be empty
	 */
	public function getStreet() {
		return $this->getAsString('address');
	}

	/**
	 * Checks whether this user has a non-empty street set.
	 *
	 * @return boolean true if this user has a street set, false otherwise
	 */
	public function hasStreet() {
		return $this->hasString('address');
	}

	/**
	 * Gets this user's ZIP code.
	 *
	 * @return string this user's ZIP code, may be empty
	 */
	public function getZip() {
		return $this->getAsString('zip');
	}

	/**
	 * Checks whether this user has a non-empty ZIP code set.
	 *
	 * @return boolean true if this user has a ZIP code set, false otherwise
	 */
	public function hasZip() {
		return $this->hasString('zip');
	}

	/**
	 * Gets this user's city.
	 *
	 * @return string this user's city, may be empty
	 */
	public function getCity() {
		return $this->getAsString('city');
	}

	/**
	 * Checks whether this user has a non-empty city set.
	 *
	 * @return boolean true if this user has a city set, false otherwise
	 */
	public function hasCity() {
		return $this->hasString('city');
	}

	/**
	 * Gets this user's ZIP code and city, separated by a space.
	 *
	 * @return string this user's ZIP code city, will be empty if the user has
	 *                no city set
	 */
	public function getZipAndCity() {
		if (!$this->hasCity()) {
			return '';
		}

		return trim($this->getZip() . ' ' . $this->getCity());
	}

	/**
	 * Gets this user's phone number.
	 *
	 * @return string this user's phone number, may be empty
	 */
	public function getPhoneNumber() {
		return $this->getAsString('telephone');
	}

	/**
	 * Checks whether this user has a non-empty phone number set.
	 *
	 * @return boolean true if this user has a phone number set, false otherwise
	 */
	public function hasPhoneNumber() {
		return $this->hasString('telephone');
	}

	/**
	 * Gets this user's e-mail address.
	 *
	 * @return string this user's e-mail address, may be empty
	 */
	public function getEMailAddress() {
		return $this->getAsString('email');
	}

	/**
	 * Checks whether this user has a non-empty e-mail address set.
	 *
	 * @return boolean true if this user has an e-mail address set, false
	 *                 otherwise
	 */
	public function hasEMailAddress() {
		return $this->hasString('email');
	}

	/**
	 * Gets this user's homepage URL (not linked yet).
	 *
	 * @return string this user's homepage URL, may be empty
	 */
	public function getHomepage() {
		return $this->getAsString('www');
	}

	/**
	 * Checks whether this user has a non-empty homepage set.
	 *
	 * @return boolean true if this user has a homepage set, false otherwise
	 */
	public function hasHomepage() {
		return $this->hasString('www');
	}

	/**
	 * Gets this user's image path (relative to the global upload directory).
	 *
	 * @return string this user's image path, may be empty
	 */
	public function getImage() {
		return $this->getAsString('image');
	}

	/**
	 * Checks whether this user has an image set.
	 *
	 * @return boolean true if this user has an image set, false otherwise
	 */
	public function hasImage() {
		return $this->hasString('image');
	}

	/**
	 * Checks whether this user has agreed to receive HTML e-mails.
	 *
	 * @return boolean true if the user agreed to receive HTML e-mails, false
	 *                 otherwise
	 */
	public function wantsHtmlEMail() {
		return $this->getAsBoolean('module_sys_dmail_html');
	}

	/**
	 * Gets this user's user groups.
	 *
	 * @return tx_oelib_List this user's FE user groups, will not be empty if
	 *                       the user data is valid
	 */
	public function getUserGroups() {
		return $this->getAsList('usergroup');
	}

	/**
	 * Checks whether this user is a member of at least one of the user groups
	 * provided as comma-separated UID list.
	 *
	 * @param string comma-separated list of user group UIDs, can also consist
	 *               of only one UID but must not be empty
	 *
	 * @return boolean true if the user is member of at least one of the user
	 *                 groups provided, false otherwise
	 */
	public function hasGroupMembership($uidList) {
		if ($uidList == '') {
			throw new Exception('$uidList must not be empty.');
		}

		$isMember = false;

		foreach (t3lib_div::trimExplode(',', $uidList, true) as $uid) {
			if ($this->getUserGroups()->hasUid($uid)) {
				$isMember = true;
				break;
			}
		}

		return $isMember;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_FrontEndUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_FrontEndUser.php']);
}
?>