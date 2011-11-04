<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2011 Oliver Klee (typo3-coding@oliverklee.de)
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

// In the back end, PATH_tslib isn't defined yet.
if (!defined('PATH_tslib')) {
	require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');
}

/**
 * Class 'tx_oelib_salutationswitcher' for the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_oelib_salutationswitcher extends tslib_pibase {
	/**
	  * Pointer to alternative fall-back language to use. This is non-empty so
	  * we always have a valid fallback language even if it hasn't been
	  * explicitely set.
	  */
	public $altLLkey = 'default';

	/**
	 * A list of language keys for which the localizations have been loaded
	 * (or null if the list has not been compiled yet).
	 */
	private $availableLanguages = null;

	/**
	 * An ordered list of language label suffixes that should be tried to get
	 * localizations in the preferred order of formality (or null if the list
	 * has not been compiled yet).
	 */
	private $suffixesToTry = null;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset(
			$this->availableLanguages, $this->suffixesToTry, $this->conf,
			$this->pi_EPtemp_cObj, $this->cObj, $this->LOCAL_LANG
		);
	}

	/**
	 * Retrieves the localized string for the local language key $key.
	 *
	 * This function checks whether the FE or BE localization functions are
	 * available and then uses the appropriate method.
	 *
	 * In $this->conf['salutation'], a suffix to the key may be set (which may
	 * be either 'formal' or 'informal'). If a corresponding key exists, the
	 * formal/informal localized string is used instead.
	 * If the formal/informal key doesn't exist, this function just uses the
	 * regular string.
	 *
	 * Example: key = 'greeting', suffix = 'informal'. If the key
	 * 'greeting_informal' exists, that string is used.
	 * If it doesn't exist, this functions tries to use the string with the key
	 * 'greeting'.
	 *
	 * @param string the local language key for which to
	 *               return the value, must not be empty
	 * @param boolean whether the output should be passed through
	 *                htmlspecialchars()
	 *
	 * @return string the requested local language key, might be empty
	 */
	public function translate(
		$key, $useHtmlSpecialChars = FALSE
	) {
		if ($key == '') {
			throw new Exception('$key must not be empty.');
		}

		if (is_object($GLOBALS['TSFE']) && is_array($this->LOCAL_LANG)) {
			$result = $this->translateInFrontEnd($key);
		} elseif (is_object($GLOBALS['LANG'])) {
			$result = $this->translateInBackEnd($key);
		} else {
			$result = $key;
		}

		if ($useHtmlSpecialChars) {
			$result = htmlspecialchars($result);
		}

		return $result;
	}

	/**
	 * Retrieves the localized string for the local language key $key, using the
	 * BE localization methods.
	 *
	 * @param string the local language key for which to
	 *               return the value, must not be empty
	 *
	 * @return string the requested local language key, might be empty
	 */
	private function translateInBackEnd($key) {
		return $GLOBALS['LANG']->getLL($key);
	}

	/**
	 * Retrieves the localized string for the local language key $key, using the
	 * FE localization methods.
	 *
	 * In $this->conf['salutation'], a suffix to the key may be set (which may
	 * be either 'formal' or 'informal'). If a corresponding key exists, the
	 * formal/informal localized string is used instead.
	 * If the formal/informal key doesn't exist, this function just uses the
	 * regular string.
	 *
	 * Example: key = 'greeting', suffix = 'informal'. If the key
	 * 'greeting_informal' exists, that string is used.
	 * If it doesn't exist, this functions tries to use the string with the key
	 * 'greeting'.
	 *
	 * @param string the local language key for which to
	 *               return the value, must not be empty
	 *
	 * @return string the requested local language key, might be empty
	 */
	private function translateInFrontEnd($key) {
		$hasFoundATranslation = FALSE;

		$availableLanguages = $this->getAvailableLanguages();
		$suffixesToTry = $this->getSuffixesToTry();

		foreach ($availableLanguages as $language) {
			foreach ($suffixesToTry as $suffix) {
				$completeKey = $key.$suffix;
				if (isset($this->LOCAL_LANG[$language][$completeKey])) {
					$result = parent::pi_getLL($completeKey);
					$hasFoundATranslation = TRUE;
					break 2;
				}
			}
		}

		// If still nothing has been found, just return the key.
		if (!$hasFoundATranslation) {
			$result = $key;
		}

		return $result;
	}

	/**
	 * Compiles a list of language keys for which localizations have been loaded.
	 *
	 * @return array a list of language keys (may be empty)
	 */
	private function getAvailableLanguages() {
		if ($this->availableLanguages === null) {
			$this->availableLanguages = array();

			if (!empty($this->LLkey)) {
				$this->availableLanguages[] = $this->LLkey;
			}
			if (!empty($this->altLLkey)) {
				$this->availableLanguages[] = $this->altLLkey;
			}
			// The key for English is "default", not "en".
			$this->availableLanguages = preg_replace(
				'/en/', 'default', $this->availableLanguages
			);
			// Remove duplicates in case the default language is the same as the
			// fall-back language.
			$this->availableLanguages = array_unique($this->availableLanguages);

			// Now check that we only keep languages for which we have
			// translations.
			foreach ($this->availableLanguages as $index => $code) {
				if (!isset($this->LOCAL_LANG[$code])) {
					unset($this->availableLanguages[$index]);
				}
			}
		}

		return $this->availableLanguages;
	}

	/**
	 * Gets an ordered list of language label suffixes that should be tried to
	 * get localizations in the preferred order of formality.
	 *
	 * @return array ordered list of suffixes from "", "_formal" and "_informal", will not be empty
	 */
	private function getSuffixesToTry() {
		if ($this->suffixesToTry === null) {
			$this->suffixesToTry = array();

			if (isset($this->conf['salutation'])
				&& ($this->conf['salutation'] == 'informal')) {
				$this->suffixesToTry[] = '_informal';
			}
			$this->suffixesToTry[] = '_formal';
			$this->suffixesToTry[] = '';
		}

		return $this->suffixesToTry;
	}
}
?>