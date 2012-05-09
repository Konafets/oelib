<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_oelib_TranslatorRegistry' for the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Benjamin Schulte <benj@minschulte.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_TranslatorRegistry {
	/**
	 * @var tx_oelib_TranslatorRegistry the Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var array holds the extension name => Translator entries
	 */
	private $translators = array();

	/**
	 * @var string the key of the language to load the translations for
	 */
	private $languageKey = 'default';

	/**
	 * @var string the key of the alternative language to load the translations
	 *             for
	 */
	private $alternativeLanguageKey = '';

	/**
	 * @var string the charset the localized labels should be rendered in
	 */
	private $renderCharset = 'utf-8';

	/**
	 * @var t3lib_cs helper for charset conversion
	 */
	private $charsetConversion = null;

	/**
	 * @var string the path to the locallang.xml file, relative to an
	 *             extension's root directory
	 */
	const LANGUAGE_FILE_PATH = 'Resources/Private/Language/locallang.xml';

	/**
	 * @var string the default render charset (both front end and back end)
	 */
	const DEFAULT_CHARSET = 'iso-8859-1';

	/**
	 * The constructor.
	 */
	private function __construct() {
		if (isset($GLOBALS['TSFE'])) {
			$this->initializeFrontEnd();
		} elseif (isset($GLOBALS['LANG'])) {
			$this->initializeBackEnd();
		} else {
			throw new BadMethodCallException('There was neither a front end nor a back end detected.', 1331489564);
		}
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		foreach ($this->translators as $key => $translator) {
			$translator->__destruct();
			unset($this->translators[$key]);
		}

		unset($this->charsetConversion);
	}

	/**
	 * Initializes the TranslatorRegistry for the front end.
	 */
	private function initializeFrontEnd() {
		$this->setLanguageKeyFromConfiguration(tx_oelib_ConfigurationRegistry::get('config'));
		$this->setLanguageKeyFromConfiguration(tx_oelib_ConfigurationRegistry::get('page.config'));

		$this->renderCharset = $GLOBALS['TSFE']->renderCharset;
		$this->charsetConversion = $GLOBALS['TSFE']->csConvObj;
	}

	/**
	 * Reads the language key from a configuration and sets it as current language.
	 * Also sets the alternate language if one is configured.
	 *
	 * The language key is read from the "language" key and the alternate language is read
	 * from the language_alt key.
	 *
	 * @param tx_oelib_Configuration $configuration the configuration to read
	 *
	 * @return void
	 */
	private function setLanguageKeyFromConfiguration(tx_oelib_Configuration $configuration) {
		if (!$configuration->hasString('language')) {
			return;
		}

		$this->languageKey = $configuration->getAsString('language');
		if ($configuration->hasString('language_alt')) {
			$this->alternativeLanguageKey = $configuration->getAsString('language_alt');
		}
	}

	/**
	 * Initializes the TranslatorRegistry for the back end.
	 */
	private function initializeBackEnd() {
		$backEndUser = tx_oelib_BackEndLoginManager::getInstance()->
			getLoggedInUser('tx_oelib_Mapper_BackEndUser');
		$this->languageKey = $backEndUser->getLanguage();
		$this->renderCharset = $GLOBALS['LANG']->charset;
		$this->charsetConversion = $GLOBALS['LANG']->csConvObj;
	}

	/**
	 * Returns the charset for a given language code.
	 *
	 * @param string the language code to get the charset for, must not be empty
	 *
	 * @return string the charset for the given language code, will not be empty
	 */
	private function getCharsetOfLanguage($languageCode) {
		$version = class_exists('t3lib_utility_VersionNumber')
			? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version)
			: t3lib_div::int_from_ver(TYPO3_version);
		if ($version >= 4007000) {
			return 'utf-8';
		}

		if (isset($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'])) {
			return $this->charsetConversion->parse_charset(
				$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']
			);
		}

		$charset = $this->charsetConversion->charSetArray[$languageCode];

		return ($charset != '') ? $charset : self::DEFAULT_CHARSET;
	}

	/**
	 * Returns the instance of this class.
	 *
	 * @return tx_oelib_TranslatorRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new tx_oelib_TranslatorRegistry();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 */
	public static function purgeInstance() {
		if (self::$instance) {
			self::$instance->__destruct();
		}
		self::$instance = null;
	}

	/**
	 * Gets a Translator by its extension name.
	 *
	 * This is a wrapper for self::getInstance()->getByExtensionName().
	 *
	 * @param string the extension name to get the Translator for, must not be
	 *               empty, the corresponding extension must be loaded
	 *
	 * @return tx_oelib_Translator the Translator for the specified extension
	 *
	 * @see getByExtensionName()
	 */
	public static function get($extensionName) {
		return self::getInstance()->getByExtensionName($extensionName);
	}

	/**
	 * Gets a Translator by its extension name.
	 *
	 * @param string the extension name to get the Translator for, must not be
	 *               empty, the corresponding extension must be loaded
	 *
	 * @return tx_oelib_Translator the Translator for the specified extension
	 *                             name
	 */
	private function getByExtensionName($extensionName) {
		if ($extensionName == '') {
			throw new InvalidArgumentException('The parameter $extensionName must not be empty.', 1331489578);
		}

		if (!t3lib_extmgm::isLoaded($extensionName)) {
			throw new BadMethodCallException('The extension with the name "' . $extensionName . '" is not loaded.', 1331489598);
		}

		if (!isset($this->translators[$extensionName])) {
			$localizedLabels = $this->getLocalizedLabelsFromFile($extensionName);
			// Overrides the localized labels with labels from TypoScript only
			// in the front end.

			if (isset($GLOBALS['TSFE'])
				&& isset($localizedLabels[$this->languageKey]) && is_array($localizedLabels[$this->languageKey])
			) {
				$labelsFromTyposcript = $this->getLocalizedLabelsFromTypoScript($extensionName);

				$version = class_exists('t3lib_utility_VersionNumber')
					? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version)
					: t3lib_div::int_from_ver(TYPO3_version);
				foreach ($labelsFromTyposcript as $labelKey => $labelFromTyposcript) {
					if ($version >= 4006000) {
						$localizedLabels[$this->languageKey][$labelKey][0]['target'] = $labelFromTyposcript;
					} else {
						$localizedLabels[$this->languageKey][$labelKey] = $labelFromTyposcript;
					}
				}
			}

			$this->translators[$extensionName] = tx_oelib_ObjectFactory::make(
				'tx_oelib_Translator',
				$this->languageKey,
				$this->alternativeLanguageKey,
				$localizedLabels
			);
		}

		return $this->translators[$extensionName];
	}

	/**
	 * Returns the localized labels from an extension's language file.
	 *
	 * @param string the extension name to get the localized labels from file
	 *               for, must not be empty, the corresponding extension must be
	 *               loaded
	 *
	 * @return array the localized labels from an extension's language file,
	 *               will be empty if there are none
	 */
	private function getLocalizedLabelsFromFile($extensionName) {
		if ($extensionName == '') {
			throw new InvalidArgumentException('The parameter $extensionName must not be empty.', 1331489618);
		}

		$languageFile = t3lib_extmgm::extPath($extensionName) . self::LANGUAGE_FILE_PATH;
		$localizedLabels = t3lib_div::readLLfile(
			$languageFile,
			$this->languageKey,
			$this->renderCharset
		);

		if ($this->alternativeLanguageKey !== '') {
			$alternativeLocalizedLabels = t3lib_div::readLLfile(
				$languageFile,
				$this->alternativeLanguageKey,
				$this->renderCharset
			);
			$localizedLabels = array_merge(
				$alternativeLocalizedLabels,
				(is_array($localizedLabels) ? $localizedLabels : array())
			);
		}

		return $localizedLabels;
	}

	/**
	 * Returns the localized labels from an extension's TypoScript setup.
	 *
	 * Returns only the labels set for the language stored in $this->languageKey
	 *
	 * @param string the extension name to get the localized labels from
	 *               TypoScript setup for, must not be empty, the corresponding
	 *               extension must be loaded
	 *
	 * @return array the localized labels from the extension's TypoScript setup,
	 *               will be empty if there are none
	 */
	private function getLocalizedLabelsFromTypoScript($extensionName) {
		if ($extensionName == '') {
			throw new InvalidArgumentException('The parameter $extensionName must not be empty.', 1331489630);
		}

		$result = array();
		$sourceCharset = $this->getCharsetOfLanguage($this->languageKey);
		$namespace = 'plugin.tx_' . $extensionName . '._LOCAL_LANG.' . $this->languageKey;

		$configuration = tx_oelib_ConfigurationRegistry::get($namespace);
		foreach ($configuration->getArrayKeys() as $key) {
			// Converts the label from the source charset to the render
			// charset.
			$result[$key] =	$this->charsetConversion->conv(
				$configuration->getAsString($key),
				$sourceCharset,
				$this->renderCharset,
				TRUE
			);
		}

		return $result;
	}

	/**
	 * Sets the language for the translator.
	 *
	 * @param string $languageKey the language key to set for the translator,
	 *        must not be empty
	 */
	public function setLanguageKey($languageKey) {
		if ($languageKey == '') {
			throw new InvalidArgumentException('The given language key must not be empty.', 1331489643);
		}

		$this->languageKey = $languageKey;
	}

	/**
	 * Returns the language key set for the translator.
	 *
	 * @return string the language key of the translator, will not be
	 *         empty
	 */
	public function getLanguageKey() {
		return $this->languageKey;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_TranslatorRegistry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_TranslatorRegistry.php']);
}
?>