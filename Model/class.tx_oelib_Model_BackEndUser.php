<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Saskia Metzler <saskia@merlin.owl.de>
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
 * This class represents a back-end user.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Model_BackEndUser extends Tx_Oelib_Model implements tx_oelib_Interface_MailRole {
	/**
	 * @var array the user's configuration unserialized
	 */
	private $configuration = array();

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
	 * Gets the user's e-mail address.
	 *
	 * @return string the e-mail address, might be empty
	 */
	public function getEmailAddress() {
		return $this->getAsString('email');
	}

	/**
	 * Gets this user's language. Will be a two-letter "lg_typo3" key of the
	 * "static_languages" table or "default" for the default language.
	 *
	 * @return string this user's language key, will not be empty
	 */
	public function getLanguage() {
		$configuration = $this->getConfiguration();
		if (isset($configuration['lang']) &&
			($configuration['lang'] != '')
		) {
			$result = $configuration['lang'];
		} else {
			$result = $this->getDefaultLanguage();
		}

		return ($result != '') ? $result : 'default';
	}

	/**
	 * Sets this user's default language.
	 *
	 * @param string $language
	 *        this user's language key, must be a two-letter "lg_typo3" key of
	 *        the "static_languages" table or "default" for the default language
	 *
	 * @return void
	 */
	public function setDefaultLanguage($language) {
		if ($language == '') {
			throw new InvalidArgumentException('$language must not be empty.', 1331488621);
		}

		$this->setAsString(
			'lang',
			($language != 'default') ? $language : ''
		);
	}

	/**
	 * Checks whether this user has a non-default language set.
	 *
	 * @return boolean TRUE if this user has a non-default language set, FALSE
	 *                 otherwise
	 */
	public function hasLanguage() {
		return ($this->getLanguage() != 'default');
	}

	/**
	 * Returns the direct user groups of this user.
	 *
	 * @return Tx_Oelib_List the user's direct groups, will be empty if this
	 *                       user has no groups
	 */
	public function getGroups() {
		return $this->getAsList('usergroup');
	}

	/**
	 * Recursively gets all groups and subgroups of this user.
	 *
	 * @return Tx_Oelib_List all groups and subgroups of this user, will be
	 *                       empty if this user has no groups
	 */
	public function getAllGroups() {
		$result = Tx_Oelib_ObjectFactory::make('Tx_Oelib_List');
		$groupsToProcess = $this->getGroups();

		do {
			$groupsForNextStep = Tx_Oelib_ObjectFactory::make('Tx_Oelib_List');
			$result->append($groupsToProcess);
			foreach ($groupsToProcess as $group) {
				$subgroups = $group->getSubgroups();
				foreach ($subgroups as $subgroup)
				if (!$result->hasUid($subgroup->getUid())) {
					$groupsForNextStep->add($subgroup);
				}
			}
			$groupsToProcess = $groupsForNextStep;
		} while (!$groupsToProcess->isEmpty());

		return $result;
	}

	/**
	 * Retrieves the user's configuration, and unserializes it.
	 *
	 * @return array the user's configuration, will be empty if the user has no
	 *               configuration set
	 */
	private function getConfiguration() {
		if (empty($this->configuration)) {
			$this->configuration = unserialize($this->getAsString('uc'));
		}

		return $this->configuration;
	}

	/**
	 * Returns the user's default language.
	 *
	 * @return string the user's default language, will be empty if no default
	 *                language has been set
	 */
	private function getDefaultLanguage() {
		return $this->getAsString('lang');
	}
}
?>