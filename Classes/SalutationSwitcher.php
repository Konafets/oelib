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
 * This class provides functions for localization.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Oelib_SalutationSwitcher extends tslib_pibase {
	/**
	 * A list of language keys for which the localizations have been loaded
	 * (or NULL if the list has not been compiled yet).
	 *
	 * @var string[]|NULL
	 */
	private $availableLanguages = NULL;

	/**
	 * An ordered list of language label suffixes that should be tried to get
	 * localizations in the preferred order of formality (or NULL if the list
	 * has not been compiled yet).
	 *
	 * @var string[]|NULL
	 */
	private $suffixesToTry = NULL;

	/**
	 * @var string[]
	 */
	protected $translationCache = array();

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		$this->translationCache = array();

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
	 * @param string $key the local language key for which to return the value, must not be empty
	 * @param bool $useHtmlSpecialChars whether the output should be passed through htmlspecialchars()
	 *
	 * @return string the requested local language key, might be empty
	 */
	public function translate($key, $useHtmlSpecialChars = FALSE) {
		if ($key === '') {
			throw new InvalidArgumentException('$key must not be empty.', 1331489025);
		}

		if (isset($this->translationCache[$key])) {
			$result = $this->translationCache[$key];
		} else {
			if (($this->getFrontEndController() !== NULL) && is_array($this->LOCAL_LANG)) {
				$result = $this->translateInFrontEnd($key);
			} elseif ($this->getLanguageService() !== NULL) {
				$result = $this->translateInBackEnd($key);
			} else {
				$result = $key;
			}

			$this->translationCache[$key] = $result;
		}

		return $useHtmlSpecialChars ? htmlspecialchars($result) : $result;
	}

	/**
	 * Retrieves the localized string for the local language key $key, using the
	 * BE localization methods.
	 *
	 * @param string $key the local language key for which to return the value, must not be empty
	 *
	 * @return string the requested local language key, might be empty
	 */
	private function translateInBackEnd($key) {
		return $this->getLanguageService()->getLL($key);
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
	 * @param string $key the local language key for which to return the value, must not be empty
	 *
	 * @return string the requested local language key, might be empty
	 */
	private function translateInFrontEnd($key) {
		$hasFoundATranslation = FALSE;
		$result = '';

		$availableLanguages = $this->getAvailableLanguages();
		foreach ($this->getSuffixesToTry() as $suffix) {
			foreach ($availableLanguages as $language) {
				$completeKey = $key . $suffix;
				if (isset($this->LOCAL_LANG[$language][$completeKey])) {
					$result = parent::pi_getLL($completeKey);
					$hasFoundATranslation = TRUE;
					break 2;
				}
			}
		}

		if (!$hasFoundATranslation) {
			$result = $key;
		}

		return $result;
	}

	/**
	 * Compiles a list of language keys for which localizations have been loaded.
	 *
	 * @return string[] a list of language keys (may be empty)
	 */
	private function getAvailableLanguages() {
		if ($this->availableLanguages === NULL) {
			$this->availableLanguages = array();

			if (!empty($this->LLkey)) {
				$this->availableLanguages[] = $this->LLkey;
			}
			// The key for English is "default", not "en".
			$this->availableLanguages = str_replace(
				'en', 'default', $this->availableLanguages
			);
			// Remove duplicates in case the default language is the same as the fall-back language.
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
	 * @return string[] ordered list of suffixes from "", "_formal" and "_informal", will not be empty
	 */
	private function getSuffixesToTry() {
		if ($this->suffixesToTry === NULL) {
			$this->suffixesToTry = array();

			if (isset($this->conf['salutation'])) {
				if ($this->conf['salutation'] === 'informal') {
					$this->suffixesToTry[] = '_informal';
				}
				$this->suffixesToTry[] = '_formal';
			}
			$this->suffixesToTry[] = '';
		}

		return $this->suffixesToTry;
	}

	/**
	 * Returns the current front-end instance.
	 *
	 * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController|NULL
	 */
	protected function getFrontEndController() {
		return isset($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : NULL;
	}

	/**
	 * Returns $GLOBALS['LANG'].
	 *
	 * @return language|NULL
	 */
	protected function getLanguageService() {
		return isset($GLOBALS['LANG']) ? $GLOBALS['LANG'] : NULL;
	}
}