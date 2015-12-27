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
 * This class returns localized labels in the given languages.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Translator {
	/**
	 * @var string the key of the language to load the translations for
	 */
	private $languageKey = '';

	/**
	 * @var string the key of the alternative language to load the translations for
	 */
	private $alternativeLanguageKey = '';

	/**
	 * @var array
	 *      the localized labels in a nested associative array:
	 *      'languageKey' => array('labelkey' => array(0 => array('source' => 'label', 'target' => 'label')
	 */
	private $localizedLabels = array();

	/**
	 * The constructor.
	 *
	 * @param string $languageKey the key of the language to load the translations for, may be empty
	 * @param string $alternativeLanguageKey the key of the alternative language to load the translations for, may be empty
	 * @param array[] $localizedLabels the localized labels in a nested associative array:
	 *        'languageKey' => array('labelkey' => 'label'),
	 *        may be empty
	 */
	public function __construct($languageKey, $alternativeLanguageKey, array $localizedLabels) {
		$this->languageKey = $languageKey;
		$this->alternativeLanguageKey = $alternativeLanguageKey;
		$this->localizedLabels = $localizedLabels;
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->localizedLabels);
	}

	/**
	 * Returns the localized label for the key $key.
	 *
	 * @param string $key
	 *        the key of the label to get the localization for, must not be empty
	 * @param bool $useHtmlSpecialChars
	 *        whether the localized label should be processes with htmlspecialchars prior to returning it
	 *
	 * @return string the localized label, might be empty
	 */
	public function translate($key, $useHtmlSpecialChars = FALSE) {
		if ($key === '') {
			throw new InvalidArgumentException('The parameter $key must not be empty.', 1331489544);
		}

		$translation = $this->translateForNewTypo3($key);

		return ($useHtmlSpecialChars ? htmlspecialchars($translation) : $translation);
	}

	/**
	 * Returns the localized label for the key $key.
	 *
	 * @param string $key the key of the label to get the localization for, must not be empty
	 *
	 * @return string the localized label, might be empty
	 */
	protected function translateForNewTypo3($key) {
		if (isset($this->localizedLabels[$this->languageKey][$key][0]['target'])) {
			$translation = $this->localizedLabels[$this->languageKey][$key][0]['target'];
		} elseif (
			($this->alternativeLanguageKey !== '')
				&& isset($this->localizedLabels[$this->alternativeLanguageKey][$key][0]['target'])
		) {
			$translation = $this->localizedLabels[$this->alternativeLanguageKey][$key][0]['target'];
		} elseif (isset($this->localizedLabels['default'][$key][0]['target'])) {
			$translation = $this->localizedLabels['default'][$key][0]['target'];
		} else {
			$translation = $key;
		}

		return $translation;
	}

	/**
	 * Returns the language key in $this->languageKey.
	 *
	 * Note: This method is meant for testing purposes.
	 *
	 * @return string the language key in $this->languageKey, may be empty
	 */
	public function getLanguageKey() {
		return $this->languageKey;
	}

	/**
	 * Returns the alternative language key in $this->alternativeLanguageKey.
	 *
	 * Note: This method is meant for testing purposes.
	 *
	 * @return string the alternative language key in
	 *                $this->alternativeLanguageKey, may be empty
	 */
	public function getAlternativeLanguageKey() {
		return $this->alternativeLanguageKey;
	}
}