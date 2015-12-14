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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This utility class provides some commonly-used functions for handling
 * templates (in addition to all functionality provided by the base classes).
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_TemplateHelper extends Tx_Oelib_SalutationSwitcher {
	/**
	 * @var string the prefix used for CSS classes
	 */
	public $prefixId = '';

	/**
	 * @var string the path of this file relative to the extension directory
	 */
	public $scriptRelPath = '';

	/**
	 * @var string the extension key
	 */
	public $extKey = '';

	/**
	 * @var bool whether init() already has been called (in order to
	 *              avoid double calls)
	 */
	protected $isInitialized = FALSE;

	/**
	 * @var Tx_Oelib_ConfigCheck
	 */
	protected $configurationCheck = NULL;

	/**
	 * @var string the file name of the template set via TypoScript or FlexForms
	 */
	private $templateFileName = '';

	/**
	 * @var Tx_Oelib_Template this object's (only) template
	 */
	private $template = NULL;

	/**
	 * TS Setup for plugin.tx_extensionkey, using the current page UID as key
	 *
	 * @var array[]
	 */
	static private $cachedConfigurations = array();

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->template, $this->configurationCheck);

		parent::__destruct();
	}

	/**
	 * Initializes the FE plugin stuff and reads the configuration.
	 *
	 * It is harmless if this function gets called multiple times as it
	 * recognizes this and ignores all calls but the first one.
	 *
	 * This is merely a convenience function.
	 *
	 * If the parameter is omitted, the configuration for plugin.tx_[extkey] is
	 * used instead, e.g. plugin.tx_seminars.
	 *
	 * @param array|NULL $configuration TypoScript configuration for the plugin, set to NULL to load the configuration from a BE page
	 *
	 * @return void
	 */
	public function init(array $configuration = NULL) {
		if ($this->isInitialized) {
			return;
		}

		$frontEnd = $this->getFrontEndController();
		if ($frontEnd !== NULL && !isset($frontEnd->config['config'])) {
			$frontEnd->config['config'] = array();
		}

		// Calls the base class's constructor manually as this isn't done automatically.
		parent::__construct();

		if ($configuration !== NULL) {
			$this->conf = $configuration;
		} else {
			$pageId = $this->getCurrentBePageId();
			if (isset(self::$cachedConfigurations[$pageId])) {
				$this->conf = self::$cachedConfigurations[$pageId];
			} else {
				// We need to create our own template setup if we are in the
				// BE and we aren't currently creating a DirectMail page.
				if ((TYPO3_MODE === 'BE') && ($frontEnd === NULL)) {
					$this->conf = $this->retrievePageConfig($pageId);
				} else {
					// On the front end, we can use the provided template setup.
					$this->conf = $frontEnd->tmpl->setup['plugin.']['tx_' . $this->extKey . '.'];
				}

				self::$cachedConfigurations[$pageId] = $this->conf;
			}
		}

		$this->ensureContentObject();
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		if (($this->extKey !== '') && Tx_Oelib_ConfigurationProxy::getInstance($this->extKey)->getAsBoolean('enableConfigCheck')) {
			$configurationCheckClassName = 'tx_' . $this->extKey . '_configcheck';
			if (class_exists($configurationCheckClassName, TRUE)) {
				$this->configurationCheck = GeneralUtility::makeInstance($configurationCheckClassName, $this);
			}
		}

		$this->isInitialized = TRUE;
	}

	/**
	 * Ensures that $this->cObj points to a valid content object.
	 *
	 * If this object already has a valid cObj, this function does nothing.
	 *
	 * If there is a front end and this object does not have a cObj yet, the cObj from the front end is used.
	 *
	 * If this object has no cObj and there is no front end, this function will do nothing.
	 *
	 * @return void
	 */
	protected function ensureContentObject() {
		if ($this->cObj !== NULL) {
			return;
		}

		$frontEnd = $this->getFrontEndController();
		// TSFE->cObj will be an empty string if not initialized, not NULL.
		// In addition, the specific class name will depend on whether we are on TYPO3 CMS < 6.0 or >= 6.0.
		// We want to avoid using \TYPO3\CMS\Core\Utility\VersionNumberUtility here, though.
		if (is_object($frontEnd->cObj)) {
			$this->cObj = $frontEnd->cObj;
		}
	}

	/**
	 * Checks that this object is properly initialized.
	 *
	 * @return bool TRUE if this object is properly initialized, FALSE otherwise
	 */
	public function isInitialized() {
		return $this->isInitialized;
	}

	/**
	 * Retrieves the configuration (TS setup) of the page with the PID provided
	 * as the parameter $pageId.
	 *
	 * Only the configuration for the current extension key will be retrieved.
	 * For example, if the extension key is "foo", the TS setup for plugin.
	 * tx_foo will be retrieved.
	 *
	 * @param int $pageId UID of the page for which the configuration should be retrieved, must be > 0
	 *
	 * @return array configuration array of the requested page for the
	 *               current extension key
	 */
	protected function retrievePageConfig($pageId) {
		/** @var \TYPO3\CMS\Core\TypoScript\TemplateService $template */
		$template = GeneralUtility::makeInstance('TYPO3\CMS\Core\TypoScript\TemplateService');
		// Disables the logging of time-performance information.
		$template->tt_track = 0;
		$template->init();

		/** @var \TYPO3\CMS\Frontend\Page\PageRepository $page */
		$page = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Page\PageRepository');

		// Gets the root line.
		// Finds the selected page in the BE exactly as in t3lib_SCbase::init().
		$rootLine = $page->getRootLine($pageId);

		// Generates the constants/config and hierarchy info for the template.
		$template->runThroughTemplates($rootLine, 0);
		$template->generateConfig();

		if (isset($template->setup['plugin.']['tx_'.$this->extKey.'.'])) {
			$result = $template->setup['plugin.']['tx_' . $this->extKey . '.'];
		} else {
			$result = array();
		}

		return $result;
	}

	/**
	 * Gets a value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS setup,
	 * an empty string is returned.
	 *
	 * @param string $fieldName field name to extract
	 * @param string $sheet sheet pointer, eg. "sDEF"
	 * @param bool $isFileName whether this is a filename, which has to be combined with a path
	 * @param bool $ignoreFlexform
	 *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
	 *
	 * @return string the value of the corresponding flexforms or TS setup
	 *                entry (may be empty)
	 */
	private function getConfValue($fieldName, $sheet = 'sDEF', $isFileName = FALSE,
		$ignoreFlexform = FALSE
	) {
		$flexformsValue = '';
		if (!$ignoreFlexform) {
			$flexformsValue = $this->pi_getFFvalue(
				$this->cObj->data['pi_flexform'],
				$fieldName,
				$sheet
			);
		}

		if ($isFileName && !empty($flexformsValue)) {
			$flexformsValue = $this->addPathToFileName($flexformsValue);
		}
		$confValue = isset($this->conf[$fieldName])
			? $this->conf[$fieldName] : '';

		return ($flexformsValue) ? $flexformsValue : $confValue;
	}

	/**
	 * Adds a path in front of the file name.
	 * This is used for files that are selected in the Flexform of the front end
	 * plugin.
	 *
	 * If no path is provided, the default (uploads/[extension_name]/) is used
	 * as path.
	 *
	 * An example (default, with no path provided):
	 * If the file is named 'template.tmpl', the output will be
	 * 'uploads/[extension_name]/template.tmpl'.
	 * The '[extension_name]' will be replaced by the name of the calling
	 * extension.
	 *
	 * @param string $fileName the file name
	 * @param string $path
	 *        the path to the file (without filename), must contain a slash at the end,
	 *        may contain a slash at the beginning (if not relative)
	 *
	 * @return string the complete path including file name
	 */
	private function addPathToFileName($fileName, $path = '') {
		if (empty($path)) {
			$path = 'uploads/tx_'.$this->extKey.'/';
		}

		return $path.$fileName;
	}

	/**
	 * Gets a trimmed string value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS
	 * setup, an empty string is returned.
	 *
	 * @param string $fieldName field name to extract
	 * @param string $sheet sheet pointer, eg. "sDEF"
	 * @param bool $isFileName whether this is a filename, which has to be combined with a path
	 * @param bool $ignoreFlexform
	 *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
	 *
	 * @return string the trimmed value of the corresponding flexforms or
	 *                TS setup entry (may be empty)
	 */
	public function getConfValueString(
		$fieldName, $sheet = 'sDEF', $isFileName = FALSE, $ignoreFlexform = FALSE
	) {
		return trim($this->getConfValue(
			$fieldName,
			$sheet,
			$isFileName,
			$ignoreFlexform)
		);
	}

	/**
	 * Checks whether a string value from flexforms or TS setup is set.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is checked. If there is no field with that name in TS
	 * setup, FALSE is returned.
	 *
	 * @param string $fieldName field name to extract
	 * @param string $sheet sheet pointer, eg. "sDEF"
	 * @param bool $ignoreFlexform
	 *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
	 *
	 * @return bool whether there is a non-empty value in the
	 *                 corresponding flexforms or TS setup entry
	 */
	public function hasConfValueString(
		$fieldName, $sheet = 'sDEF', $ignoreFlexform = FALSE
	) {
		return ($this->getConfValueString(
			$fieldName,
			$sheet,
			FALSE,
			$ignoreFlexform) !== ''
		);
	}

	/**
	 * Gets an integer value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS
	 * setup, zero is returned.
	 *
	 * @param string $fieldName field name to extract
	 * @param string $sheet sheet pointer, eg. "sDEF"
	 *
	 * @return int the int value of the corresponding flexforms or
	 *                 TS setup entry
	 */
	public function getConfValueInteger($fieldName, $sheet = 'sDEF') {
		return (int)$this->getConfValue($fieldName, $sheet);
	}

	/**
	 * Checks whether an integer value from flexforms or TS setup is set and
	 * non-zero. The priority lies on flexforms; if nothing is found there, the
	 * value from TS setup is checked. If there is no field with that name in
	 * TS setup, FALSE is returned.
	 *
	 * @param string $fieldName field name to extract
	 * @param string $sheet sheet pointer, eg. "sDEF"
	 *
	 * @return bool whether there is a non-zero value in the
	 *                 corresponding flexforms or TS setup entry
	 */
	public function hasConfValueInteger($fieldName, $sheet = 'sDEF') {
		return (bool)$this->getConfValueInteger($fieldName, $sheet);
	}

	/**
	 * Gets a boolean value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS
	 * setup, FALSE is returned.
	 *
	 * @param string $fieldName field name to extract
	 * @param string $sheet sheet pointer, eg. "sDEF"
	 *
	 * @return bool the boolean value of the corresponding flexforms or
	 *                 TS setup entry
	 */
	public function getConfValueBoolean($fieldName, $sheet = 'sDEF') {
		return (bool)$this->getConfValue($fieldName, $sheet);
	}

	/**
	 * Sets a configuration value.
	 *
	 * This function is intended to be used for testing purposes only.
	 *
	 * @param string $key key of the configuration property to set, must not be empty
	 * @param mixed $value value of the configuration property, may be empty or zero
	 *
	 * @return void
	 */
	public function setConfigurationValue($key, $value) {
		if ($key === '') {
			throw new InvalidArgumentException('$key must not be empty', 1331489491);
		}

		$this->ensureConfigurationArray();
		$this->conf[$key] = $value;
	}

	/**
	 * Sets a cached configuration value that will be used when a new instance
	 * is created.
	 *
	 * This function is intended to be used for testing purposes only.
	 *
	 * @param string $key
	 *        key of the configuration property to set, must not be empty
	 * @param mixed $value
	 *        value of the configuration property, may be empty or zero
	 *
	 * @return void
	 */
	static public function setCachedConfigurationValue($key, $value) {
		$pageUid = Tx_Oelib_PageFinder::getInstance()->getPageUid();

		if (!isset(self::$cachedConfigurations[$pageUid])) {
			self::$cachedConfigurations[$pageUid] = array();
		}

		self::$cachedConfigurations[$pageUid][$key] = $value;
	}

	/**
	 * Purges all cached configuration values.
	 *
	 * This function is intended to be used for testing purposes only.
	 *
	 * @return void
	 */
	static public function purgeCachedConfigurations() {
		self::$cachedConfigurations = array();
	}

	/**
	 * Gets the configuration.
	 *
	 * @return array configuration array, might be empty
	 */
	public function getConfiguration() {
		$this->ensureConfigurationArray();
		return $this->conf;
	}

	/**
	 * Ensures that $this->conf is set and that it is an array.
	 *
	 * @return void
	 */
	private function ensureConfigurationArray() {
		if (!is_array($this->conf)) {
			$this->conf = array();
		}
	}

	/**
	 * Retrieves the plugin template file set in $this->conf['templateFile'] (or
	 * also via flexforms if TYPO3 mode is FE) and writes it to
	 * $this->templateCode. The subparts will be written to $this->templateCache.
	 *
	 * @param bool $ignoreFlexform whether the settings in the Flexform should be ignored
	 *
	 * @return void
	 */
	public function getTemplateCode($ignoreFlexform = FALSE) {
		// Trying to fetch the template code via $this->cObj in BE mode leads to
		// a non-catchable error in the tslib_content class because the cObj
		// configuration array is not initialized properly.
		// As flexforms can be used in FE mode only, $ignoreFlexform is set TRUE
		// if we are in the BE mode. By this, $this->cObj->fileResource can be
		// sheltered from being called.
		if (TYPO3_MODE === 'BE') {
			$ignoreFlexform = TRUE;
		}

		$templateFileName = $this->getConfValueString(
			'templateFile',
			's_template_special',
			TRUE,
			$ignoreFlexform
		);

		if (!$ignoreFlexform) {
			$templateFileName = $this->getFrontEndController()->tmpl->getFileName(
				$templateFileName
			);
		}

		$this->templateFileName = $templateFileName;
	}

	/**
	 * Returns the template object from the template registry for the file name
	 * in $this->templateFileName.
	 *
	 * @return Tx_Oelib_Template the template object for the template file name
	 *                           in $this->templateFileName
	 */
	protected function getTemplate() {
		if ($this->template === NULL) {
			$this->template = Tx_Oelib_TemplateRegistry::get(
				$this->templateFileName
			);
		}

		return $this->template;
	}

	/**
	 * Stores the given HTML template and retrieves all subparts, writing them
	 * to $this->templateCache.
	 *
	 * The subpart names are automatically retrieved from $templateCode and
	 * are used as array keys. For this, the ### are removed, but the names stay
	 * uppercase.
	 *
	 * Example: The subpart ###MY_SUBPART### will be stored with the array key
	 * 'MY_SUBPART'.
	 *
	 * @param string $templateCode the content of the HTML template
	 *
	 * @return void
	 */
	public function processTemplate($templateCode) {
		$this->getTemplate()->processTemplate($templateCode);
	}

	/**
	 * Gets a list of markers with a given prefix.
	 * Example: If the prefix is "WRAPPER" (or "wrapper", case is not relevant),
	 * the following array might be returned: ("WRAPPER_FOO", "WRAPPER_BAR")
	 *
	 * If there are no matches, an empty array is returned.
	 *
	 * @deprecated 2015-03-01
	 *
	 * @param string $prefix case-insensitive prefix for the marker names to look for
	 *
	 * @return string[] array of matching marker names, might be empty
	 */
	public function getPrefixedMarkers($prefix) {
		GeneralUtility::logDeprecatedFunction();

		try {
			return $this->getTemplate()->getPrefixedMarkers($prefix);
		} catch (Exception $exception) {
			return array();
		}
	}

	/**
	 * Sets a marker's content.
	 *
	 * Example: If the prefix is "field" and the marker name is "one", the
	 * marker "###FIELD_ONE###" will be written.
	 *
	 * If the prefix is empty and the marker name is "one", the marker
	 * "###ONE###" will be written.
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param string $content the marker's content, may be empty
	 * @param string $prefix prefix to the marker name (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function setMarker($markerName, $content, $prefix = '') {
		$this->getTemplate()->setMarker($markerName, $content, $prefix);
	}

	/**
	 * Gets a marker's content.
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 *
	 * @return string the marker's content or an empty string if the
	 *                marker has not been set before
	 */
	public function getMarker($markerName) {
		return $this->getTemplate()->getMarker($markerName);
	}

	/**
	 * Sets a subpart's content.
	 *
	 * Example: If the prefix is "field" and the subpart name is "one", the
	 * subpart "###FIELD_ONE###" will be written.
	 *
	 * If the prefix is empty and the subpart name is "one", the subpart
	 * "###ONE###" will be written.
	 *
	 * @param string $subpartName
	 *        the subpart's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param string $content the subpart's content, may be empty
	 * @param string $prefix prefix to the subpart name (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function setSubpart($subpartName, $content, $prefix = '') {
		try {
			$this->getTemplate()->setSubpart($subpartName, $content, $prefix);
		} catch (Exception $exception) {
			$this->setErrorMessage('The subpart <strong>' . $subpartName .
				'</strong> is missing in the HTML template file <strong>' .
				$this->getConfValueString(
					'templateFile', 's_template_special', TRUE
				) .
				'</strong>. If you are using a modified HTML template, please ' .
				'fix it. If you are using the original HTML template file, ' .
				'please file a bug report in the ' .
				'<a href="https://bugs.oliverklee.com/">bug tracker</a>.'
			);
		}
	}

	/**
	 * Sets a marker based on whether the int content is non-zero.
	 *
	 * If (int)$content is non-zero, this function sets the marker's content, working
	 * exactly like setMarker($markerName, $content, $markerPrefix).
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param mixed $content content with which the marker will be filled, may be empty
	 * @param string $markerPrefix  to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE otherwise
	 *
	 * @see setMarkerIfNotEmpty
	 */
	public function setMarkerIfNotZero($markerName, $content, $markerPrefix = '') {
		return $this->getTemplate()->setMarkerIfNotZero(
			$markerName, $content, $markerPrefix
		);
	}

	/**
	 * Sets a marker based on whether the (string) content is non-empty.
	 * If $content is non-empty, this function sets the marker's content,
	 * working exactly like setMarker($markerName, $content, $markerPrefix).
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param string $content content with which the marker will be filled, may be empty
	 * @param string $markerPrefix prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE otherwise
	 *
	 * @see setMarkerIfNotZero
	 */
	public function setMarkerIfNotEmpty($markerName, $content, $markerPrefix = '') {
		return $this->getTemplate()->setMarkerIfNotEmpty(
			$markerName, $content, $markerPrefix
		);
	}

	/**
	 * Checks whether a subpart is visible.
	 *
	 * Note: If the subpart to check does not exist, this function will return
	 * FALSE.
	 *
	 * @param string $subpartName name of the subpart to check (without the ###), must not be empty
	 *
	 * @return bool TRUE if the subpart is visible, FALSE otherwise
	 */
	public function isSubpartVisible($subpartName) {
		return $this->getTemplate()->isSubpartVisible($subpartName);
	}

	/**
	 * Takes a comma-separated list of subpart names and sets them to hidden. In
	 * the process, the names are changed from 'aname' to '###BLA_ANAME###' and
	 * used as keys.
	 *
	 * Example: If the prefix is "field" and the list is "one,two", the subparts
	 * "###FIELD_ONE###" and "###FIELD_TWO###" will be hidden.
	 *
	 * If the prefix is empty and the list is "one,two", the subparts
	 * "###ONE###" and "###TWO###" will be hidden.
	 *
	 * @param string $subparts comma-separated list of at least 1 subpart name to hide (case-insensitive, will get uppercased)
	 * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function hideSubparts($subparts, $prefix = '') {
		$this->getTemplate()->hideSubparts($subparts, $prefix);
	}

	/**
	 * Takes an array of subpart names and sets them to hidden. In the process,
	 * the names are changed from 'aname' to '###BLA_ANAME###' and used as keys.
	 *
	 * Example: If the prefix is "field" and the array has two elements "one"
	 * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
	 * hidden.
	 *
	 * If the prefix is empty and the array has two elements "one" and "two",
	 * the subparts "###ONE###" and "###TWO###" will be hidden.
	 *
	 * @param string[] $subparts subpart names to hide (may be empty, case-insensitive, will get uppercased)
	 * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function hideSubpartsArray(array $subparts, $prefix = '') {
		$this->getTemplate()->hideSubpartsArray($subparts, $prefix);
	}

	/**
	 * Takes a comma-separated list of subpart names and unhides them if they
	 * have been hidden beforehand.
	 *
	 * Note: All subpartNames that are provided with the second parameter will
	 * not be unhidden. This is to avoid unhiding subparts that are hidden by
	 * the configuration.
	 *
	 * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
	 *
	 * Example: If the prefix is "field" and the list is "one,two", the subparts
	 * "###FIELD_ONE###" and "###FIELD_TWO###" will be unhidden.
	 *
	 * If the prefix is empty and the list is "one,two", the subparts
	 * "###ONE###" and "###TWO###" will be unhidden.
	 *
	 * @param string $subparts
	 *        comma-separated list of at least 1 subpart name to unhide (case-insensitive, will get uppercased), must not be empty
	 * @param string $permanentlyHiddenSubparts
	 *        comma-separated list of subpart names that shouldn't get unhidden
	 * @param string $prefix
	 *        prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function unhideSubparts(
		$subparts, $permanentlyHiddenSubparts = '', $prefix = ''
	) {
		$this->getTemplate()->unhideSubparts(
			$subparts, $permanentlyHiddenSubparts, $prefix
		);
	}

	/**
	 * Takes an array of subpart names and unhides them if they have been hidden
	 * beforehand.
	 *
	 * Note: All subpartNames that are provided with the second parameter will
	 * not be unhidden. This is to avoid unhiding subparts that are hidden by
	 * the configuration.
	 *
	 * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
	 *
	 * Example: If the prefix is "field" and the array has two elements "one"
	 * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
	 * unhidden.
	 *
	 * If the prefix is empty and the array has two elements "one" and "two",
	 * the subparts "###ONE###" and "###TWO###" will be unhidden.
	 *
	 * @param string[] $subparts subpart names to unhide (may be empty, case-insensitive, will get uppercased)
	 * @param string[] $permanentlyHiddenSubparts subpart names that shouldn't get unhidden
	 * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function unhideSubpartsArray(
		array $subparts, array $permanentlyHiddenSubparts = array(), $prefix = ''
	) {
		$this->getTemplate()->unhideSubpartsArray(
			$subparts, $permanentlyHiddenSubparts, $prefix
		);
	}

	/**
	 * Sets or hides a marker based on $condition.
	 * If $condition is TRUE, this function sets the marker's content, working
	 * exactly like setMarker($markerName, $content, $markerPrefix).
	 * If $condition is FALSE, this function removes the wrapping subpart,
	 * working exactly like hideSubparts($markerName, $wrapperPrefix).
	 *
	 * @param string $markerName
	 *        the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param bool $condition
	 *        if this is TRUE, the marker will be filled, otherwise the wrapped marker will be hidden
	 * @param string $content
	 *        content with which the marker will be filled, may be empty
	 * @param string $markerPrefix
	 *        prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 * @param string $wrapperPrefix
	 *        prefix to the subpart name for hiding (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE if
	 *                 the subpart has been hidden
	 *
	 * @see setMarkerContent
	 * @see hideSubparts
	 */
	public function setOrDeleteMarker($markerName, $condition, $content,
		$markerPrefix = '', $wrapperPrefix = ''
	) {
		return $this->getTemplate()->setOrDeleteMarker(
			$markerName, $condition, $content, $markerPrefix, $wrapperPrefix
		);
	}

	/**
	 * Sets or hides a marker based on whether the int content is non-zero.
	 *
	 * If (int)$content is non-zero, this function sets the marker's content,
	 * working exactly like setMarker($markerName, $content,
	 * $markerPrefix).
	 * If (int)$condition is zero, this function removes the wrapping
	 * subpart, working exactly like hideSubparts($markerName, $wrapperPrefix).
	 *
	 * @param string $markerName
	 *        the marker's name without the ### signs, case-insensitive, will get uppercased, must not be* empty
	 * @param mixed $content
	 *        content with which the marker will be filled, may be empty
	 * @param string $markerPrefix
	 *        prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 * @param string $wrapperPrefix
	 *        prefix to the subpart name for hiding (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE if
	 *                 the subpart has been hidden
	 *
	 * @see setOrDeleteMarker
	 * @see setOrDeleteMarkerIfNotEmpty
	 * @see setMarkerContent
	 * @see hideSubparts
	 */
	public function setOrDeleteMarkerIfNotZero($markerName, $content,
		$markerPrefix = '', $wrapperPrefix = ''
	) {
		return $this->getTemplate()->setOrDeleteMarkerIfNotZero(
			$markerName, $content, $markerPrefix, $wrapperPrefix
		);
	}

	/**
	 * Sets or hides a marker based on whether the (string) content is
	 * non-empty.
	 * If $content is non-empty, this function sets the marker's content,
	 * working exactly like setMarker($markerName, $content,
	 * $markerPrefix).
	 * If $condition is empty, this function removes the wrapping subpart,
	 * working exactly like hideSubparts($markerName, $wrapperPrefix).
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param string $content content with which the marker will be filled, may be empty
	 * @param string $markerPrefix prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 * @param string $wrapperPrefix prefix to the subpart name for hiding (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE if the subpart has been hidden
	 *
	 * @see setOrDeleteMarker
	 * @see setOrDeleteMarkerIfNotZero
	 * @see setMarkerContent
	 * @see hideSubparts
	 */
	public function setOrDeleteMarkerIfNotEmpty($markerName, $content,
		$markerPrefix = '', $wrapperPrefix = ''
	) {
		return $this->getTemplate()->setOrDeleteMarkerIfNotEmpty(
			$markerName, $content, $markerPrefix, $wrapperPrefix
		);
	}

	/**
	 * Retrieves a named subpart, recursively filling in its inner subparts
	 * and markers. Inner subparts that are marked to be hidden will be
	 * substituted with empty strings.
	 *
	 * This function either works on the subpart with the name $key or the
	 * complete HTML template if $key is an empty string.
	 *
	 * @param string $key
	 *        key of an existing subpart, for example 'LIST_ITEM' (without the ###),
	 *        or an empty string to use the complete HTML template
	 *
	 * @return string the subpart content or an empty string if the
	 *                subpart is hidden or the subpart name is missing
	 */
	public function getSubpart($key = '') {
		try {
			return $this->getTemplate()->getSubpart($key);
		} catch (Exception $exception) {
			$this->setErrorMessage('The subpart <strong>' . $key .
				'</strong> is missing in the HTML template file <strong>' .
				$this->getConfValueString(
					'templateFile', 's_template_special', TRUE
				) .
				'</strong>. If you are using a modified HTML template, please ' .
				'fix it. If you are using the original HTML template file, ' .
				'please file a bug report in the ' .
				'<a href="https://bugs.oliverklee.com/">bug tracker</a>.'
			);

			return '';
		}
	}

	/**
	 * Retrieves a named subpart, recursively filling in its inner subparts
	 * and markers. Inner subparts that are marked to be hidden will be
	 * substituted with empty strings.
	 *
	 * This function either works on the subpart with the name $key or the
	 * complete HTML template if $key is an empty string.
	 *
	 * All label markers in the rendered subpart are automatically replaced with their corresponding localized labels,
	 * removing the need use the very expensive setLabels method.
	 *
	 * @param string $subpartKey
	 *        key of an existing subpart, for example 'LIST_ITEM' (without the ###),
	 *        or an empty string to use the complete HTML template
	 *
	 * @return string the subpart content or an empty string if the subpart is hidden or the subpart name is missing
	 */
	public function getSubpartWithLabels($subpartKey = '') {
		$renderedSubpart = $this->getSubpart($subpartKey);

		$translator = $this;
		return preg_replace_callback(
			Tx_Oelib_Template::LABEL_PATTERN,
			function(array $matches) use ($translator) {
				return $translator->translate(strtolower($matches[1]));
			},
			$renderedSubpart
		);
	}


	/**
	 * Writes all localized labels for the current template into their
	 * corresponding template markers.
	 *
	 * For this, the label markers in the template must be prefixed with
	 * "LABEL_" (e.g. "###LABEL_FOO###"), and the corresponding localization
	 * entry must have the same key, but lowercased and without the ###
	 * (e.g. "label_foo").
	 *
	 * @return void
	 */
	public function setLabels() {
		$template = $this->getTemplate();
		try {
			$labels = $template->getLabelMarkerNames();
		} catch (Exception $exception) {
			$labels = array();
		}

		foreach ($labels as $label) {
			$template->setMarker($label, $this->translate($label));
		}
	}

	/**
	 * Sets the all CSS classes from TS for the template in $this->markers.
	 * The list of needed CSS classes will be extracted from the template file.
	 *
	 * Classes are set only if they are set via TS, else the marker will be an
	 * empty string.
	 *
	 * @deprecated 2015-02-28
	 *
	 * @return void
	 */
	public function setCss() {
		GeneralUtility::logDeprecatedFunction();

		try {
			$cssEntries = $this->getTemplate()->getPrefixedMarkers('class');
		} catch (Exception $exception) {
			$cssEntries = array();
		}

		foreach ($cssEntries as $currentCssEntry) {
			$this->getTemplate()->setMarker(
				$currentCssEntry,
				$this->createClassAttribute(
					$this->getConfValueString(strtolower($currentCssEntry))
				)
			);
		}
	}

	/**
	 * Creates an CSS class attribute. The parameter is the class name.
	 *
	 * Example: If the parameter is 'foo', our extension is named 'bar' and we
	 * are in p1, then the return value is 'class="tx-bar-pi1-foo"'.
	 *
	 * If the parameter is an emty string, the return value is an empty string
	 * as well (not an attribute with an empty value).
	 *
	 * @param string $className a CSS class name (may be empty)
	 *
	 * @return string a CSS class attribute (may be empty)
	 */
	private function createClassAttribute($className) {
		return !empty($className) ? $this->pi_classParam($className) : '';
	}

	/**
	 * Includes a link to the JavaScript file configured as "jsFile" and adds it
	 * to the automatic page header with $this->prefixId.'_js' as the array key.
	 *
	 * If no file is specified, no link is created.
	 *
	 * This function may only be called if $this->$prefixId has been set.
	 *
	 * @return void
	 */
	public function addJavaScriptToPageHeader() {
		if ($this->hasConfValueString('jsFile', 's_template_special')) {
			$this->getFrontEndController()->additionalHeaderData[$this->prefixId.'_js']
				= '<script type="text/javascript" src="'
				.$this->getConfValueString(
					'jsFile',
					's_template_special',
					TRUE
				).'"></script>';
		}
	}

	/**
	 * Resets the list of subparts to hide.
	 *
	 * @return void
	 */
	public function resetSubpartsHiding() {
		$this->getTemplate()->resetSubpartsHiding();
	}

	/**
	 * Intvals all piVars that are supposed to be integers. These are the keys
	 * showUid, pointer and mode and the keys provided in $additionalPiVars.
	 *
	 * If some piVars are not set or no piVars array is defined yet, this
	 * function will set the not yet existing piVars to zero.
	 *
	 * @param string[] $additionalPiVars
	 *        keys for $this->piVars that will be ensured to exist as ints in $this->piVars as well, may be empty
	 *
	 * @return void
	 */
	protected function ensureIntegerPiVars(array $additionalPiVars = array()) {
		if (!is_array($this->piVars)) {
			$this->piVars = array();
		}

		foreach (array_merge(
			array('showUid', 'pointer', 'mode'), $additionalPiVars
		) as $key) {
			if (isset($this->piVars[$key])) {
				$this->piVars[$key] = (int)$this->piVars[$key];
			} else {
				$this->piVars[$key] = 0;
			}
		}
	}

	/**
	 * Ensures that all values in the given array are cast to ints and removes empty
	 * or invalid values.
	 *
	 * @param string[] $keys the keys of the piVars to check, may be empty
	 *
	 * @return void
	 */
	protected function ensureIntegerArrayValues(array $keys) {
		if (empty($keys)) {
			return;
		}

		foreach ($keys as $key) {
			if (!isset($this->piVars[$key])
				|| !is_array($this->piVars[$key])
			) {
				continue;
			}

			foreach ($this->piVars[$key] as $innerKey => $value) {
				$integerValue = (int)$value;

				if ($integerValue === 0) {
					unset($this->piVars[$key][$innerKey]);
				} else {
					$this->piVars[$key][$innerKey] = $integerValue;
				}
			}
		}
	}

	/**
	 * Creates an IMG for a resized image version of $path.
	 * If the image cannot be created, the ALT text is returned instead.
	 *
	 * Note: When this function is unit-tested in the BE, the image tag's src
	 * attribute will always contain an empty string because the handling of
	 * relative paths by cObject::IMAGE is broken.
	 *
	 * In the FE, the src attribute is correctly filled with the URI of the
	 * resized image.
	 *
	 * @deprecated 2014-09-02 use the TYPO3 image functions instead
	 *
	 * @throws InvalidArgumentException if $path is empty
	 *
	 * @param string $path path to of the original image, must be relative to the TYPO3 root or start with EXT:, must not be empty
	 * @param string $altText alt text, may be empty
	 * @param int $maxWidth max width in pixels, set to zero to set no limit
	 * @param int $maxHeight max height in pixels, set to zero to set no limit
	 * @param int $maxArea (unused, must be zero)
	 * @param string $titleText title text, may be empty
	 * @param string $id HTML ID for the image, may be empty
	 *
	 * @return string IMG tag (or alt text), will not be empty
	 */
	public function createRestrictedImage(
		$path, $altText = '', $maxWidth = 0, $maxHeight = 0, $maxArea = 0, $titleText = '', $id = ''
	) {
		GeneralUtility::logDeprecatedFunction();

		if ($path === '') {
			throw new InvalidArgumentException('$path must not be empty.', 1331489502);
		}
		if ($maxArea !== 0) {
			throw new InvalidArgumentException('$maxArea is not used anymore and must be zero.', 1331489515);
		}

		$imageConfiguration = array(
			'file' => $path,
			'file.' => array(),
			'altText' => $altText,
			'titleText' => $titleText,
		);

		if ($maxWidth > 0) {
			$imageConfiguration['file.']['maxW'] = $maxWidth;
		}
		if ($maxHeight > 0) {
			$imageConfiguration['file.']['maxH'] = $maxHeight;
		}
		if ($id !== '') {
			$imageConfiguration['params'] = 'id="' . $id . '"';
		}

		if (class_exists('TYPO3\\CMS\\Core\\Resource\\Exception\\FileDoesNotExistException', TRUE)) {
			try {
				$result = $this->cObj->IMAGE($imageConfiguration);
			} catch (\TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException $exception) {
				$result = NULL;
			}
		} else {
			$result = $this->cObj->IMAGE($imageConfiguration);
		}

		if (($result === NULL) || ($result === '') || (strpos($result, 'src=""') !== FALSE)) {
			$result = htmlspecialchars($altText);
		}

		return $result;
	}

	/**
	 * Extracts a value within listView.
	 *
	 * @param string $fieldName TS setup field name to extract (within listView.), must not be empty
	 *
	 * @return string the contents of that field within listView., may be empty
	 */
	private function getListViewConfigurationValue($fieldName) {
		if (empty($fieldName)) {
			throw new InvalidArgumentException('$fieldName must not be empty.', 1331489528);
		}

		if (!isset($this->conf['listView.'])
			|| !isset($this->conf['listView.'][$fieldName])
		) {
			return '';
		}

		return $this->conf['listView.'][$fieldName];
	}

	/**
	 * Returns a string value within listView.
	 *
	 * @param string $fieldName TS setup field name to extract (within listView.), must not be empty
	 *
	 * @return string the trimmed contents of that field within listView.
	 *                or an empty string if the value was not set
	 */
	public function getListViewConfValueString($fieldName) {
		return trim($this->getListViewConfigurationValue($fieldName));
	}

	/**
	 * Returns an integer value within listView.
	 *
	 * @param string $fieldName TS setup field name to extract (within listView.), must not be empty
	 *
	 * @return int the integer value of that field within listView. or
	 *                 zero if the value was not set
	 */
	public function getListViewConfValueInteger($fieldName) {
		return (int)$this->getListViewConfigurationValue($fieldName);
	}

	/**
	 * Returns a boolean value within listView.
	 *
	 * @param string $fieldName TS setup field name to extract (within listView.), must not be empty
	 *
	 * @return bool the boolean value of that field within listView.,
	 *                 FALSE if no value was set
	 */
	public function getListViewConfValueBoolean($fieldName) {
		return (bool)$this->getListViewConfigurationValue($fieldName);
	}

	/**
	 * Returns the UID of the currently logged-in FE user
	 * or 0 if no FE user is logged in.
	 *
	 * @deprecated 2013-02-09 use Tx_Oelib_FrontEndLoginManager::getLoggedInUser instead
	 *
	 * @return int the UID of the logged-in FE user or 0 if no FE user is logged in
	 */
	public function getFeUserUid() {
		GeneralUtility::logDeprecatedFunction();

		$loginManager = Tx_Oelib_FrontEndLoginManager::getInstance();
		if (!$loginManager->isLoggedIn()) {
			return 0;
		}

		return $loginManager->getLoggedInUser()->getUid();
	}

	/**
	 * Sets the "flavor" of the object to check.
	 *
	 * @param string $flavor a short string identifying the "flavor" of the object to check (may be empty)
	 *
	 * @return void
	 */
	public function setFlavor($flavor) {
		if ($this->configurationCheck) {
			$this->configurationCheck->setFlavor($flavor);
		}
	}

	/**
	 * Returns the current flavor of the object to check.
	 *
	 * @return string the current flavor of the object to check (or an empty
	 *                string if no flavor is set)
	 */
	public function getFlavor() {
		$result = '';

		if ($this->configurationCheck) {
			$result = $this->configurationCheck->getFlavor();
		}

		return $result;
	}

	/**
	 * Sets the error text of $this->configurationCheck.
	 *
	 * If this->configurationCheck is NULL, this function is a no-op.
	 *
	 * @param string $message error text to set (may be empty)
	 *
	 * @return void
	 */
	protected function setErrorMessage($message) {
		if ($this->configurationCheck) {
			$this->configurationCheck->setErrorMessage($message);
		}
	}

	/**
	 * Checks this object's configuration and returns a formatted error message
	 * (if any). If there are several objects of this class, still only one
	 * error message is created (in order to prevent duplicate messages).
	 *
	 * @param bool $useRawMessage whether to use the raw message instead of the wrapped message
	 * @param string $temporaryFlavor flavor to use temporarily for this call (leave empty to not change the flavor)
	 *
	 * @return string a formatted error message (if there are errors) or an
	 *                empty string
	 */
	public function checkConfiguration($useRawMessage = FALSE, $temporaryFlavor = '') {
		static $hasDisplayedMessage = FALSE;
		$result = '';

		if ($this->configurationCheck) {
			if (!empty($temporaryFlavor)) {
				$oldFlavor = $this->getFlavor();
				$this->setFlavor($temporaryFlavor);
			} else {
				$oldFlavor = '';
			}

			$message = ($useRawMessage) ?
				$this->configurationCheck->checkIt() :
				$this->configurationCheck->checkItAndWrapIt();

			if (!empty($temporaryFlavor)) {
				$this->setFlavor($oldFlavor);
			}

			// If we have a message, only returns it if it is the first message
			// for objects of this class.
			if (!empty($message) && !$hasDisplayedMessage) {
				$result = $message;
				$hasDisplayedMessage = TRUE;
			}
		}

		return $result;
	}

	/**
	 * Returns an empty string if there are no configuration errors.
	 * Otherwise, returns the wrapped error text.
	 *
	 * Use this method if you want to display this message pretty
	 * directly and it doesn't need to get handled to other configcheck
	 * objects.
	 *
	 * @return string the wrapped error text (or an empty string if there are no
	 *                errors)
	 */
	public function getWrappedConfigCheckMessage() {
		$result = '';

		if ($this->configurationCheck) {
			$result = $this->configurationCheck->getWrappedMessage();
		}

		return $result;
	}

	/**
	 * Gets the ID of the currently selected back-end page.
	 *
	 * @return int the current back-end page ID (or 0 if there is an
	 *                 error)
	 */
	public function getCurrentBePageId() {
		return Tx_Oelib_PageFinder::getInstance()->getPageUid();
	}

	/**
	 * Returns the general record storage PID for the current page.
	 *
	 * This function must only be called in the front end or when a front end is present.
	 *
	 * @return int the general record storage PID for the current page, will be 0 if the page has no storage page set
	 */
	public function getStoragePid() {
		$pageData = $this->getFrontEndController()->getStorageSiterootPids();

		return $pageData['_STORAGE_PID'];
	}

	/**
	 * Checks whether the current page has a general record storage PID set.
	 *
	 * @return bool TRUE if the current page has a general record storage PID set, FALSE otherwise
	 */
	public function hasStoragePid() {
		return $this->getStoragePid() > 0;
	}

	/**
	 * Returns the current front-end instance.
	 *
	 * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController|NULL
	 */
	protected function getFrontEndController() {
		return isset($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : NULL;
	}
}