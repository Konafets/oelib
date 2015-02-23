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
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class Tx_Oelib_Tests_Unit_Fixtures_TestingTemplateHelper extends Tx_Oelib_TemplateHelper {
	/**
	 * @var string the path of this file relative to the extension directory
	 */
	public $scriptRelPath = 'Tests/Unit/Fixtures/TestingTemplateHelper.php';

	/**
	 * @var string the extension key
	 */
	public $extKey = 'oelib';

	/**
	 * The constructor.
	 *
	 * @param array $configuration
	 *        TS setup configuration, may be empty
	 */
	public function __construct(array $configuration = array()) {
		parent::init($configuration);
	}

	/**
	 * Returns the current configuration check object (or NULL if there is no
	 * such object).
	 *
	 * @return Tx_Oelib_ConfigCheck the current configuration check object
	 */
	public function getConfigurationCheck() {
		return $this->configurationCheck;
	}

	/**
	 * Sets the salutation mode.
	 *
	 * @param string $salutation
	 *        the salutation mode to use ("formal" or "informal")
	 *
	 * @return void
	 */
	public function setSalutationMode($salutation) {
		$this->setConfigurationValue('salutation', $salutation);
	}

	/**
	 * Returns the localized label of the LOCAL_LANG key $key, simulating an FE
	 * environment.
	 *
	 * @param string $key
	 *        the key from the LOCAL_LANG array for which to return the value
	 * @param string $alternativeString
	 *        alternative string to return if no value is found for the key,
	 *        neither for the local language nor the default.
	 * @param bool $useHtmlSpecialChars
	 *        If TRUE, the output label is passed through htmlspecialchars().
	 *
	 * @return string the value from LOCAL_LANG
	 */
	public function translate($key, $alternativeString = '', $useHtmlSpecialChars = FALSE) {
		return parent::translate(
			$key,
			$alternativeString,
			$useHtmlSpecialChars
		);
	}

	/**
	 * Retrieves the configuration (TS setup) of the page with the PID provided
	 * as the parameter $pageId.
	 *
	 * Only the configuration for the current extension key will be retrieved.
	 * For example, if the extension key is "foo", the TS setup for plugin.
	 * tx_foo will be retrieved.
	 *
	 * @param int $pageId
	 *        page ID of the page for which the configuration should be retrieved, must be > 0
	 *
	 * @return array configuration array of the requested page for the current
	 *               extension key
	 */
	public function retrievePageConfig($pageId) {
		return parent::retrievePageConfig($pageId);
	}

	/**
	 * Intvals all piVars that are supposed to be integers:
	 * showUid, pointer, mode
	 *
	 * If some piVars are not set or no piVars array is defined yet, this
	 * function will set the not yet existing piVars to zero.
	 *
	 * @param string[] $additionalPiVars
	 *        keys for $this->piVars that will be ensured to exist as ints in
	 *        $this->piVars as well
	 *
	 * @return void
	 */
	public function ensureIntegerPiVars(array $additionalPiVars = array()) {
		parent::ensureIntegerPiVars($additionalPiVars);
	}

	/**
	 * Ensures that all values in the given array are cast to int and removes empty
	 * or invalid values.
	 *
	 * @param string[] $keys the keys of the piVars to check, may be empty
	 *
	 * @return void
	 */
	public function ensureIntegerArrayValues(array $keys) {
		parent::ensureIntegerArrayValues($keys);
	}

	/**
	 * Ensures that $this->cObj points to a valid content object.
	 *
	 * If this object alread has a valid cObj, this function does nothing.
	 *
	 * If there is a front end and this object does not have a cObj yet, the
	 * cObj from the front end is used.
	 *
	 * If this object has no cObj and there is no front end, this function will
	 * do nothing.
	 *
	 * @return void
	 */
	public function ensureContentObject() {
		parent::ensureContentObject();
	}
}