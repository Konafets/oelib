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
 * This class provides a registry for translators.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Benjamin Schulte <benj@minschulte.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_TranslatorRegistry {
	/**
	 * @var Tx_Oelib_TranslatorRegistry the Singleton instance
	 */
	private static $instance = NULL;

	/**
	 * extension name => Translator entries
	 *
	 * @var Tx_Oelib_Translator[]
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
	private $charsetConversion = NULL;

	/**
	 * @var string the path to the locallang.xml file, relative to an
	 *             extension's root directory
	 */
	const LANGUAGE_FILE_PATH = 'Resources/Private/Language/locallang.xml';

	/**
	 * @var string the default render charset (both front end and back end)
	 */
	const DEFAULT_CHARSET = 'utf-8';

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
		unset($this->charsetConversion, $this->translators);
	}

	/**
	 * Initializes the TranslatorRegistry for the front end.
	 *
	 * @return void
	 */
	private function initializeFrontEnd() {
		$this->setLanguageKeyFromConfiguration(Tx_Oelib_ConfigurationRegistry::get('config'));
		$this->setLanguageKeyFromConfiguration(Tx_Oelib_ConfigurationRegistry::get('page.config'));

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
	 * @param Tx_Oelib_Configuration $configuration the configuration to read
	 *
	 * @return void
	 */
	private function setLanguageKeyFromConfiguration(Tx_Oelib_Configuration $configuration) {
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
	 *
	 * @return void
	 */
	private function initializeBackEnd() {
		$backEndUser = Tx_Oelib_BackEndLoginManager::getInstance()->
			getLoggedInUser('tx_oelib_Mapper_BackEndUser');
		$this->languageKey = $backEndUser->getLanguage();
		$this->renderCharset = $GLOBALS['LANG']->charset;
		$this->charsetConversion = $GLOBALS['LANG']->csConvObj;
	}

	/**
	 * Returns the charset for a given language code.
	 *
	 * @param string $languageCode the language code to get the charset for, must not be empty
	 *
	 * @return string the charset for the given language code, will not be empty
	 */
	private function getCharsetOfLanguage($languageCode) {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4007000) {
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
	 * @return Tx_Oelib_TranslatorRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new Tx_Oelib_TranslatorRegistry();
		}

		return self::$instance;
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
	 * Gets a Translator by its extension name.
	 *
	 * This is a wrapper for self::getInstance()->getByExtensionName().
	 *
	 * @param string $extensionName
	 *        the extension name to get the Translator for, must not be empty, the corresponding extension must be loaded
	 *
	 * @return Tx_Oelib_Translator the Translator for the specified extension
	 *
	 * @see getByExtensionName()
	 */
	public static function get($extensionName) {
		return self::getInstance()->getByExtensionName($extensionName);
	}

	/**
	 * Gets a Translator by its extension name.
	 *
	 * @param string $extensionName
	 *        the extension name to get the Translator for, must not be empty, the corresponding extension must be loaded
	 *
	 * @return Tx_Oelib_Translator the Translator for the specified extension
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

				$version = t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version);
				foreach ($labelsFromTyposcript as $labelKey => $labelFromTyposcript) {
					if ($version >= 4006000) {
						$localizedLabels[$this->languageKey][$labelKey][0]['target'] = $labelFromTyposcript;
					} else {
						$localizedLabels[$this->languageKey][$labelKey] = $labelFromTyposcript;
					}
				}
			}

			$this->translators[$extensionName] = t3lib_div::makeInstance(
				'Tx_Oelib_Translator',
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
	 * @param string $extensionName
	 *        the extension name to get the localized labels from file for,
	 *        must not be empty, the corresponding extension must be loaded
	 *
	 * @return string[] the localized labels from an extension's language file, will be empty if there are none
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
	 * @param string $extensionName
	 *        the extension name to get the localized labels from TypoScript setup for,
	 *        must not be empty, the corresponding extension must be loaded
	 *
	 * @return string[] the localized labels from the extension's TypoScript setup, will be empty if there are none
	 */
	private function getLocalizedLabelsFromTypoScript($extensionName) {
		if ($extensionName == '') {
			throw new InvalidArgumentException('The parameter $extensionName must not be empty.', 1331489630);
		}

		$result = array();
		$sourceCharset = $this->getCharsetOfLanguage($this->languageKey);
		$namespace = 'plugin.tx_' . $extensionName . '._LOCAL_LANG.' . $this->languageKey;

		$configuration = Tx_Oelib_ConfigurationRegistry::get($namespace);
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
	 *
	 * @return void
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