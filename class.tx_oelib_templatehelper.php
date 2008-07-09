<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_oelib_templatehelper' for the 'oelib' extension
 * (taken from the 'seminars' extension).
 *
 * This utitity class provides some commonly-used functions for handling templates
 * (in addition to all functionality provided by the base classes).
 *
 * This is an abstract class; don't instantiate it.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_t3lib.'class.t3lib_timetrack.php');
require_once(PATH_t3lib.'class.t3lib_tstemplate.php');

require_once(PATH_typo3.'sysext/cms/tslib/class.tslib_content.php');
require_once(PATH_typo3.'sysext/lang/lang.php');

require_once(t3lib_extMgm::extPath('oelib').'tx_oelib_commonConstants.php');
require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_salutationswitcher.php');
require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_configurationProxy.php');

class tx_oelib_templatehelper extends tx_oelib_salutationswitcher {
	/** whether init() already has been called (in order to avoid double calls) */
	var $isInitialized = false;

	/** the complete HTML template */
	var $templateCode = '';

	/**
	 * Associative array of all HTML template subparts, using the marker names
	 * without ### as keys, for example 'MY_MARKER'.
	 */
	var $templateCache = array();

	/** list of the names of all markers (and subparts) of a template */
	var $markerNames = '';

	/**
	 * Associative array of populated markers and their contents (with the keys
	 * being the marker names including the wrapping hash signs ###).
	 */
	var $markers = array();

	/**
	 * List of subpart names that shouldn't be displayed. Set a subpart key like
	 * "FIELD_DATE" (the value does not matter) to remove that subpart.
	 */
	var $subpartsToHide = array();

	/** The configuration check object that will check this object. */
	var $configurationCheck = null;

	/** page object which we will use to call enableFields on */
	protected static $pageForEnableFields = null;

	/** cached results for the enableFields function */
	private static $enableFieldsCache = array();

	/**
	 * Initializes the FE plugin stuff and reads the configuration.
	 *
	 * It is harmless if this function gets called multiple times as it recognizes
	 * this and ignores all calls but the first one.
	 *
	 * This is merely a convenience function.
	 *
	 * If the parameter is omitted, the configuration for plugin.tx_[extkey] is
	 * used instead, e.g. plugin.tx_seminars.
	 *
 	 * @param	array		TypoScript configuration for the plugin
	 *
	 * @access	protected
	 */
	function init(array $conf = array()) {
		global $BE_USER;

		static $cachedConfigs = array();

		if (!$this->isInitialized) {
			if (TYPO3_MODE != 'FE') {
				$this->fakeFrontend();
			}

			if ($GLOBALS['TSFE'] && !isset($GLOBALS['TSFE']->config['config'])) {
				$GLOBALS['TSFE']->config['config'] = array();
			}

			// Calls the base class's constructor manually as this isn't done
			// automatically.
			parent::tslib_pibase();

			if (is_array($conf)) {
				$this->conf = $conf;
			} else {
				// We need to create our own template setup if we are in the BE
				// and we aren't currently creating a DirectMail page.
				if ((TYPO3_MODE == 'BE') && !is_object($GLOBALS['TSFE'])) {
					$pageId = $this->getCurrentBePageId();

					if (isset($cachedConfigs[$pageId])) {
						$this->conf =& $cachedConfigs[$pageId];
					} else {
						$this->conf =& $this->retrievePageConfig($pageId);
						$cachedConfigs[$pageId] =& $this->conf;
					}
				} else {
					// On the front end, we can use the provided template setup.
					$this->conf =& $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_'.$this->extKey.'.'];
				}
			}

			$this->pi_setPiVarDefaults();
			$this->pi_loadLL();

			if ((isset($this->extKey) && ($this->extKey != ''))
				&& tx_oelib_configurationProxy::getInstance($this->extKey)->
					getConfigurationValueBoolean('enableConfigCheck')
			) {
				$configurationCheckClassname = t3lib_div::makeInstanceClassName(
					'tx_'.$this->extKey.'_configcheck'
				);
				$configurationCheckFile = t3lib_extMgm::extPath($this->extKey)
					.'class.'.$configurationCheckClassname.'.php';
				if (is_file($configurationCheckFile)) {
					require_once($configurationCheckFile);
					$this->configurationCheck =& new $configurationCheckClassname(
						$this
					);
				}
			} else {
				$this->configurationCheck = null;
			}

			$this->isInitialized = true;
		}
	}

	/**
	 * Retrieves the configuration (TS setup) of the page with the PID provided
	 * as the parameter $pageId.
	 *
	 * Only the configuration for the current extension key will be retrieved.
	 * For example, if the extension key is "foo", the TS setup for plugin.
	 * tx_foo will be retrieved.
	 *
	 * @param	integer		page ID of the page for which the configuration
	 * 						should be retrieved, must be > 0
	 *
	 * @return	array		configuration array of the requested page for the
	 * 						current extension key
	 *
	 * @access	protected
	 */
	function &retrievePageConfig($pageId) {
		$template = t3lib_div::makeInstance('t3lib_TStemplate');
		// Disables the logging of time-performance information.
		$template->tt_track = 0;
		$template->init();

		// Gets the root line.
		$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		// Finds the selected page in the BE exactly as in t3lib_SCbase::init().
		$rootline = $sys_page->getRootLine($pageId);

		// Generates the constants/config and hierarchy info for the template.
		$template->runThroughTemplates($rootline, 0);
		$template->generateConfig();

		if (isset($template->setup['plugin.']['tx_'.$this->extKey.'.'])) {
			$result = $template->setup['plugin.']['tx_'.$this->extKey.'.'];
		} else {
			$result = array();
		}

		return $result;
	}

	/**
	 * Initializes '$GLOBALS['TSFE']->sys_page', '$GLOBALS['TT']' and
	 * '$this->cObj' as these objects are needed but only initialized
	 * automatically if TYPO3_MODE is 'FE'.
	 * This will allow the FE templating functions to be used even without the
	 * FE.
	 */
	 protected function fakeFrontend() {
	 	if (!is_object($GLOBALS['TT'])) {
	 		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_timeTrack');
	 	}

	 	if (!is_object($GLOBALS['TSFE']->sys_page)) {
	 		$GLOBALS['TSFE']->sys_page
	 			= t3lib_div::makeInstance('t3lib_pageSelect');
	 	}

		if (!is_object($this->cObj)) {
			$this->cObj = t3lib_div::makeInstance('tslib_cObj');
			$this->cObj->start('');
		}
	 }

	/**
	 * Gets a value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS setup,
	 * an empty string is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 * @param	boolean		whether this is a filename, which has to be combined
	 * 						with a path
	 * @param	boolean		whether to ignore the flexform values and just get
	 * 						the settings from TypoScript, may be empty
	 *
	 * @return	string		the value of the corresponding flexforms or TS setup
	 * 						entry (may be empty)
	 */
	private function getConfValue($fieldName, $sheet = 'sDEF', $isFileName = false,
		$ignoreFlexform = false
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
	 * @param	string		the file name
	 * @param	string		the path to the file (without filename), must
	 * 						contain a slash at the end, may contain a slash at
	 * 						the beginning (if not relative)
	 *
	 * @return	string		the complete path including file name
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
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 * @param	boolean		whether this is a filename, which has to be combined
	 * 						with a path
	 * @param	boolean		whether to ignore the flexform values and just get
	 * 						the settings from TypoScript, may be empty
	 *
	 * @return	string		the trimmed value of the corresponding flexforms or
	 * 						TS setup entry (may be empty)
	 *
	 * @access	public
	 */
	function getConfValueString(
		$fieldName, $sheet = 'sDEF', $isFileName = false, $ignoreFlexform = false
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
	 * setup, false is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 * @param	boolean		whether to ignore the flexform values and just get
	 * 						the settings from TypoScript, may be empty
	 *
	 * @return	boolean		whether there is a non-empty value in the
	 * 						corresponding flexforms or TS setup entry
	 *
	 * @access	public
	 */
	function hasConfValueString(
		$fieldName, $sheet = 'sDEF', $ignoreFlexform = false
	) {
		return ($this->getConfValueString(
			$fieldName,
			$sheet,
			false,
			$ignoreFlexform) != ''
		);
	}

	/**
	 * Gets an integer value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS
	 * setup, zero is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 *
	 * @return	integer		the inval'ed value of the corresponding flexforms or
	 * 						TS setup entry
	 *
	 * @access	public
	 */
	function getConfValueInteger($fieldName, $sheet = 'sDEF') {
		return intval($this->getConfValue($fieldName, $sheet));
	}

	/**
	 * Checks whether an integer value from flexforms or TS setup is set and
	 * non-zero. The priority lies on flexforms; if nothing is found there, the
	 * value from TS setup is checked. If there is no field with that name in
	 * TS setup, false is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 *
	 * @return	boolean		whether there is a non-zero value in the
	 * 						corresponding flexforms or TS setup entry
	 *
	 * @access	public
	 */
	function hasConfValueInteger($fieldName, $sheet = 'sDEF') {
		return (boolean) $this->getConfValueInteger($fieldName, $sheet);
	}

	/**
	 * Gets a boolean value from flexforms or TS setup.
	 * The priority lies on flexforms; if nothing is found there, the value
	 * from TS setup is returned. If there is no field with that name in TS
	 * setup, false is returned.
	 *
	 * @param	string		field name to extract
	 * @param	string		sheet pointer, eg. "sDEF"
	 *
	 * @return	boolean		the boolean value of the corresponding flexforms or
	 * 						TS setup entry
	 *
	 * @access	public
	 */
	function getConfValueBoolean($fieldName, $sheet = 'sDEF') {
		return (boolean) $this->getConfValue($fieldName, $sheet);
	}

	/**
	 * Sets a configuration value.
	 *
	 * This function is intended to be used for testing purposes only.
	 *
	 * @param	string		key of the configuration property to set, must not
	 * 						be empty
	 * @param	mixed		value of the configuration property, may be empty or
	 * 						zero
	 */
	public function setConfigurationValue($key, $value) {
		if ($key == '') {
			throw new Exception('$key must not be empty');
		}

		$this->ensureConfigurationArray();
		$this->conf[$key] = $value;
	}

	/**
	 * Gets the configuration.
	 *
	 * @return	array		configuration array, might be empty
	 */
	public function getConfiguration() {
		$this->ensureConfigurationArray();
		return $this->conf;
	}

	/**
	 * Ensures that $this->conf is set and that it is an array.
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
	 * @param	boolean		whether the settings in the Flexform should be
	 * 						ignored
	 *
	 * @access	protected
	 */
	function getTemplateCode($ignoreFlexform = false) {
		// Trying to fetch the template code via $this->cObj in BE mode leads to
		// a non-catchable error in the tslib_content class because the cObj
		// configuration array is not initialized properly.
		// As flexforms can be used in FE mode only, $ignoreFlexform is set true
		// if we are in the BE mode. By this, $this->cObj->fileResource can be
		// sheltered from being called.
		if (TYPO3_MODE == 'BE') {
			$ignoreFlexform = true;
		}

		$templateFileName = $this->getConfValueString(
			'templateFile',
			's_template_special',
			true,
			$ignoreFlexform
		);

		if (!$ignoreFlexform) {
			$templateRawCode = $this->cObj->fileResource($templateFileName);
		} else {
			// If there is no need to care about flexforms, the template file is
			// fetched directly from the local configuration array.
			$templateRawCode = file_get_contents(
				t3lib_div::getFileAbsFileName($templateFileName)
			);
		}

		$this->processTemplate($templateRawCode);
	}

	/**
	 * Stores the given HTML template and retrieves all subparts, writing them
	 * to $this->templateCache.
	 *
	 * The subpart names are automatically retrieved from $templateRawCode and
	 * are used as array keys. For this, the ### are removed, but the names stay
	 * uppercase.
	 *
	 * Example: The subpart ###MY_SUBPART### will be stored with the array key
	 * 'MY_SUBPART'.
	 *
	 * @param	string		the content of the HTML template
	 *
	 * @access	protected
	 */
	function processTemplate($templateRawCode) {
		$this->templateCode = $templateRawCode;
		$this->markerNames = $this->findMarkers();

		$subpartNames = $this->findSubparts();

		foreach ($subpartNames as $subpartName) {
			$matches = array();
			preg_match(
				'/<!-- *###'.$subpartName.'### *-->(.*)'
					.'<!-- *###'.$subpartName.'### *-->/msSU',
				$templateRawCode,
				$matches
			);
			if (isset($matches[1])) {
				$this->templateCache[$subpartName] = $matches[1];
			}
		}
	}

	/**
	 * Finds all subparts within the current HTML template.
	 * The subparts must be within HTML comments.
	 *
	 * @return	array		a list of the subpart names (uppercase, without ###,
	 *						for example 'MY_SUBPART')
	 *
	 * @access	protected
	 */
	function findSubparts() {
		$matches = array();
		preg_match_all(
			'/<!-- *(###)([A-Z]([A-Z0-9_]*[A-Z0-9])?)(###)/',
			$this->templateCode,
			$matches
		);

		return array_unique($matches[2]);
	}

	/**
	 * Finds all markers within the current HTML template.
	 * Note: This also finds subpart names.
	 *
	 * The result is one long string that is easy to process using regular
	 * expressions.
	 *
	 * Example: If the markers ###FOO### and ###BAR### are found, the string
	 * "#FOO#BAR#" would be returned.
	 *
	 * @return	string		a list of markes as one long string, separated,
	 *						prefixed and postfixed by '#'
	 */
	private function findMarkers() {
		$matches = array();
		preg_match_all(
			'/(###)(([A-Z0-9_]*[A-Z0-9])?)(###)/', $this->templateCode, $matches
		);

		$markerNames = array_unique($matches[2]);

		return '#'.implode('#', $markerNames).'#';
	}

	/**
	 * Gets a list of markers with a given prefix.
	 * Example: If the prefix is "WRAPPER" (or "wrapper", case is not relevant),
	 * the following array might be returned: ("WRAPPER_FOO", "WRAPPER_BAR")
	 *
	 * If there are no matches, an empty array is returned.
	 *
	 * The function <code>findMarkers</code> must be called before this function
	 * may be called.
	 *
	 * @param	string	case-insensitive prefix for the marker names to look for
	 *
	 * @return	array	array of matching marker names, might be empty
	 */
	public function getPrefixedMarkers($prefix) {
		$matches = array();
		preg_match_all(
			'/(#)('.strtoupper($prefix).'_[^#]+)/',
			$this->markerNames, $matches
		);

		$result = array_unique($matches[2]);

		return $result;
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
	 * @param	string		the marker's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 * @param	string		the marker's content, may be empty
	 * @param	string		prefix to the marker name (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 *
	 * @deprecated	2007-12-09	Use setMarker instead.
	 */
	function setMarkerContent($markerName, $content, $prefix = '') {
		$this->setMarker($markerName, $content, $prefix);
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
	 * @param	string		the marker's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 * @param	string		the marker's content, may be empty
	 * @param	string		prefix to the marker name (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 */
	function setMarker($markerName, $content, $prefix = '') {
		$unifiedMarkerName = $this->createMarkerName($markerName, $prefix);

		if ($this->isMarkerNameValidWithHashes($unifiedMarkerName)) {
			$this->markers[$unifiedMarkerName] = $content;
		}
	}

	/**
	 * Gets a marker's content.
	 *
	 * @param	string		the marker's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 *
	 * @return	string		the marker's content or an empty string if the
	 * 						marker has not been set before
	 *
	 * @access	protected
	 */
	function getMarker($markerName) {
		$unifiedMarkerName = $this->createMarkerName($markerName);
		if (!isset($this->markers[$unifiedMarkerName])) {
			return '';
		}

		return $this->markers[$unifiedMarkerName];
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
	 * @param	string		the subpart's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 * @param	string		the subpart's content, may be empty
	 * @param	string		prefix to the subpart name (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 *
	 * @deprecated	2007-12-09	Use setSubpart instead.
	 */
	function setSubpartContent($subpartName, $content, $prefix = '') {
		$this->setSubpart($subpartName, $content, $prefix);
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
	 * @param	string		the subpart's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 * @param	string		the subpart's content, may be empty
	 * @param	string		prefix to the subpart name (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 */
	function setSubpart($subpartName, $content, $prefix = '') {
		$subpartName = $this->createMarkerNameWithoutHashes(
			$subpartName, $prefix
		);

		if ($this->isMarkerNameValidWithoutHashes($subpartName)) {
			$this->templateCache[$subpartName] = $content;
		}
	}

	/**
	 * Sets a marker based on whether the (integer) content is non-zero.
	 * If intval($content) is non-zero, this function sets the marker's content, working
	 * exactly like setMarker($markerName, $content, $markerPrefix).
	 *
	 * @param	string		the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param	integer		content with which the marker will be filled, may be empty
	 * @param	string		prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return	boolean		true if the marker content has been set, false otherwise
	 *
	 * @access	protected
	 *
	 * @see	setMarkerIfNotEmpty
	 */
	function setMarkerIfNotZero($markerName, $content, $markerPrefix = '') {
		$condition = (intval($content) != 0);
		if ($condition) {
			$this->setMarker($markerName, ((string) $content), $markerPrefix);
		}
		return $condition;
	}

	/**
	 * Sets a marker based on whether the (string) content is non-empty.
	 * If $content is non-empty, this function sets the marker's content, working
	 * exactly like setMarker($markerName, $content, $markerPrefix).
	 *
	 * @param	string		the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param	string		content with which the marker will be filled, may be empty
	 * @param	string		prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return	boolean		true if the marker content has been set, false otherwise
	 *
	 * @access	protected
	 *
	 * @see	setMarkerIfNotZero
	 */
	function setMarkerIfNotEmpty($markerName, $content, $markerPrefix = '') {
		$condition = !empty($content);
		if ($condition) {
			$this->setMarker($markerName, $content, $markerPrefix);
		}
		return $condition;
	}

	/**
	 * Checks whether a subpart is visible.
	 *
	 * Note: If the subpart to check does not exist, this function will return
	 * false.
	 *
	 * @param	string		name of the subpart to check (without the ###), must
	 * 						not be empty
	 *
	 * @return	boolean		true if the subpart is visible, false otherwise
	 *
	 * @access	pulic
	 */
	function isSubpartVisible($subpartName) {
		if ($subpartName == '') {
			return false;
		}

		return (isset($this->templateCache[$subpartName])
			&& !isset($this->subpartsToHide[$subpartName]));
	}

	/**
	 * Takes a comma-separated list of subpart names and writes them to
	 * $this->subpartsToHide. In the process, the names are changed from 'aname'
	 * to '###BLA_ANAME###' and used as keys. The corresponding values in the
	 * array are empty strings.
	 *
	 * Example: If the prefix is "field" and the list is "one,two", the array keys
	 * "###FIELD_ONE###" and "###FIELD_TWO###" will be written.
	 *
	 * If the prefix is empty and the list is "one,two", the array keys
	 * "###ONE###" and "###TWO###" will be written.
	 *
	 * @param	string		comma-separated list of at least 1 subpart name to
	 *						hide (case-insensitive, will get uppercased)
	 * @param	string		prefix to the subpart names (may be empty,
	 *						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 *
	 * @deprecated	2007-08-22	Use hideSubparts instead.
	 */
	function readSubpartsToHide($subparts, $prefix = '') {
		$this->hideSubparts($subparts, $prefix);
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
	 * @param	string		comma-separated list of at least 1 subpart name to
	 * 						hide (case-insensitive, will get uppercased)
	 * @param	string		prefix to the subpart names (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 */
	function hideSubparts($subparts, $prefix = '') {
		$subpartNames = explode(',', $subparts);

		foreach ($subpartNames as $currentSubpartName) {
			$fullSubpartName = $this->createMarkerNameWithoutHashes(
				$currentSubpartName,
				$prefix
			);

			$this->subpartsToHide[$fullSubpartName] = true;
		}
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
	 * @param	string		comma-separated list of at least 1 subpart name to
	 * 						unhide (case-insensitive, will get uppercased)
	 * @param	string		comma-separated list of subpart names that
	 * 						shouldn't get unhidden
	 * @param	string		prefix to the subpart names (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 *
	 * @deprecated	2007-08-22	Use unhideSubparts instead.
	 */
	function readSubpartsToUnhide(
		$subparts, $permanentlyHiddenSubparts = '', $prefix = ''
	) {
		$this->unhideSubparts($subparts, $permanentlyHiddenSubparts, $prefix);
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
	 * @param	string		comma-separated list of at least 1 subpart name to
	 * 						unhide (case-insensitive, will get uppercased),
	 * 						must not be empty
	 * @param	string		comma-separated list of subpart names that
	 * 						shouldn't get unhidden
	 * @param	string		prefix to the subpart names (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @access	protected
	 */
	function unhideSubparts(
		$subparts, $permanentlyHiddenSubparts = '', $prefix = ''
	) {
		$subpartNames = explode(',', $subparts);
		if ($permanentlyHiddenSubparts != '') {
			$hiddenSubpartNames = explode(',', $permanentlyHiddenSubparts);
		} else {
			$hiddenSubpartNames = array();
		}

		foreach ($subpartNames as $currentSubpartName) {
			// Only unhide the current subpart if it is not on the list of
			// permanently hidden subparts (e.g. by configuration).
			if (!in_array($currentSubpartName, $hiddenSubpartNames)) {
				$currentMarkerName = $this->createMarkerNameWithoutHashes(
					$currentSubpartName, $prefix
				);
				unset($this->subpartsToHide[$currentMarkerName]);
			}
		}
	}

	/**
	 * Sets or hides a marker based on $condition.
	 * If $condition is true, this function sets the marker's content, working
	 * exactly like setMarker($markerName, $content, $markerPrefix).
	 * If $condition is false, this function removes the wrapping subpart,
	 * working exactly like hideSubparts($markerName, $wrapperPrefix).
	 *
	 * @param	string		the marker's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 * @param	boolean		if this is true, the marker will be filled,
	 * 						otherwise the wrapped marker will be hidden
	 * @param	string		content with which the marker will be filled, may be
	 * 						empty
	 * @param	string		prefix to the marker name for setting (may be empty,
	 * 						case-insensitive, will get uppercased)
	 * @param	string		prefix to the subpart name for hiding (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @return	boolean		true if the marker content has been set, false if
	 * 						the subpart has been hidden
	 *
	 * @access	protected
	 *
	 * @see	setMarkerContent
	 * @see	hideSubparts
	 */
	function setOrDeleteMarker($markerName, $condition, $content,
		$markerPrefix = '', $wrapperPrefix = ''
	) {
		if ($condition) {
			$this->setMarker($markerName, $content, $markerPrefix);
		} else {
			$this->hideSubparts($markerName, $wrapperPrefix);
		}

		return $condition;
	}

	/**
	 * Sets or hides a marker based on whether the (integer) content is
	 * non-zero.
	 * If intval($content) is non-zero, this function sets the marker's content,
	 * working exactly like setMarker($markerName, $content,
	 * $markerPrefix).
	 * If intval($condition) is zero, this function removes the wrapping
	 * subpart, working exactly like hideSubparts($markerName, $wrapperPrefix).
	 *
	 * @param	string		the marker's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 * @param	integer		content with which the marker will be filled, may be
	 * 						empty
	 * @param	string		prefix to the marker name for setting (may be empty,
	 * 						case-insensitive, will get uppercased)
	 * @param	string		prefix to the subpart name for hiding (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @return	boolean		true if the marker content has been set, false if
	 * 						the subpart has been hidden
	 *
	 * @access	protected
	 *
	 * @see	setOrDeleteMarker
	 * @see	setOrDeleteMarkerIfNotEmpty
	 * @see	setMarkerContent
	 * @see	hideSubparts
	 */
	function setOrDeleteMarkerIfNotZero($markerName, $content,
		$markerPrefix = '', $wrapperPrefix = ''
	) {
		return $this->setOrDeleteMarker(
			$markerName,
			(intval($content) != 0),
			((string) $content),
			$markerPrefix,
			$wrapperPrefix
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
	 * @param	string		the marker's name without the ### signs,
	 * 						case-insensitive, will get uppercased, must not be
	 * 						empty
	 * @param	string		content with which the marker will be filled, may be
	 * 						empty
	 * @param	string		prefix to the marker name for setting (may be empty,
	 * 						case-insensitive, will get uppercased)
	 * @param	string		prefix to the subpart name for hiding (may be empty,
	 * 						case-insensitive, will get uppercased)
	 *
	 * @return	boolean		true if the marker content has been set, false if
	 * 						the subpart has been hidden
	 *
	 * @access	protected
	 *
	 * @see	setOrDeleteMarker
	 * @see	setOrDeleteMarkerIfNotZero
	 * @see	setMarkerContent
	 * @see	hideSubparts
	 */
	function setOrDeleteMarkerIfNotEmpty($markerName, $content,
		$markerPrefix = '', $wrapperPrefix = ''
	) {
		return $this->setOrDeleteMarker(
			$markerName,
			(!empty($content)),
			$content,
			$markerPrefix,
			$wrapperPrefix
		);
	}

	/**
	 * Creates an uppercase marker (or subpart) name from a given name and an
	 * optional prefix, wrapping the result in three hash signs (###).
	 *
	 * Example: If the prefix is "field" and the marker name is "one", the
	 * result will be "###FIELD_ONE###".
	 *
	 * If the prefix is empty and the marker name is "one", the result will be
	 * "###ONE###".
	 */
	private function createMarkerName($markerName, $prefix = '') {
		return '###'
			.$this->createMarkerNameWithoutHashes($markerName, $prefix).'###';
	}

	/**
	 * Creates an uppercase marker (or subpart) name from a given name and an
	 * optional prefix, but without wrapping it in hash signs.
	 *
	 * Example: If the prefix is "field" and the marker name is "one", the
	 * result will be "FIELD_ONE".
	 *
	 * If the prefix is empty and the marker name is "one", the result will be
	 * "ONE".
	 */
	private function createMarkerNameWithoutHashes($markerName, $prefix = '') {
		// If a prefix is provided, uppercases it and separates it with an
		// underscore.
		if (!empty($prefix)) {
			$prefix .= '_';
		}

		return strtoupper($prefix.trim($markerName));
	}

	/**
	 * Retrieves a named subpart, recursively filling in its inner subparts
	 * and markers. Inner subparts that are marked to be hidden will be
	 * substituted with empty strings.
	 *
	 * This function either works on the subpart with the name $key or the
	 * complete HTML template if $key is an empty string.
	 *
	 * @param	string		key of an existing subpart, for example 'LIST_ITEM'
	 * 						(without the ###), or an empty string to use the
	 * 						complete HTML template
	 *
	 * @return	string		the subpart content or an empty string if the
	 * 						subpart is hidden or the subpart name is missing
	 *
	 * @access	protected
	 *
	 * @deprecated	2007-08-22	Use getSubpart instead.
	 */
	function substituteMarkerArrayCached($key = '') {
		return $this->getSubpart($key);
	}

	/**
	 * Retrieves a named subpart, recursively filling in its inner subparts
	 * and markers. Inner subparts that are marked to be hidden will be
	 * substituted with empty strings.
	 *
	 * This function either works on the subpart with the name $key or the
	 * complete HTML template if $key is an empty string.
	 *
	 * @param	string		key of an existing subpart, for example 'LIST_ITEM'
	 * 						(without the ###), or an empty string to use the
	 * 						complete HTML template
	 *
	 * @return	string		the subpart content or an empty string if the
	 * 						subpart is hidden or the subpart name is missing
	 *
	 * @access	protected
	 */
	function getSubpart($key = '') {
		if (($key != '') && !isset($this->templateCache[$key])) {
			$this->setErrorMessage('The subpart <strong>'.$key.'</strong> is '
				.'missing in the HTML template file <strong>'
				.$this->getConfValueString(
					'templateFile',
					's_template_special',
					true)
				.'</strong>. If you are using a modified HTML template, please '
				.'fix it. If you are using the original HTML template file, '
				.'please file a bug report in the '
				.'<a href="https://bugs.oliverklee.com/">bug tracker</a>.'
			);

			return '';
		}

		if (($key != '') && !$this->isSubpartVisible($key)) {
			return '';
		}

		$templateCode = ($key != '')
			? $this->templateCache[$key] : $this->templateCode;

		// recursively replaces subparts with their contents
		$noSubpartMarkers = preg_replace_callback(
			'/<!-- *###([^#]*)### *-->(.*)'
				.'<!-- *###\1### *-->/msSU',
			array(
				$this,
				'getSubpartForCallback'
			),
			$templateCode
		);

		// replaces markers with their contents
		return str_replace(
			array_keys($this->markers), $this->markers, $noSubpartMarkers
		);
	}

	/**
	 * Retrieves a subpart.
	 *
	 * @param	array		numeric array with matches from
	 * 						preg_replace_callback; the element #1 needs to
	 * 						contain the name of the subpart to retrieve (in
	 * 						uppercase without the surrounding ###)
	 *
	 * @return	string		the contents of the corresponding subpart or an
	 * 						empty string in case the subpart does not exist
	 */
	private function getSubpartForCallback(array $matches) {
		return $this->getSubpart($matches[1]);
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
	 * @access	protected
	 */
	function setLabels() {
		$labels = $this->getPrefixedMarkers('label');

		foreach ($labels as $currentLabel) {
			$this->setMarker(
				$currentLabel, $this->translate(strtolower($currentLabel))
			);
		}
	}

	/**
	 * Sets the all CSS classes from TS for the template in $this->markers.
	 * The list of needed CSS classes will be extracted from the template file.
	 *
	 * Classes are set only if they are set via TS, else the marker will be an
	 * empty string.
	 *
	 * @access	protected
	 */
	function setCss() {
		$cssEntries = $this->getPrefixedMarkers('class');

		foreach ($cssEntries as $currentCssEntry) {
			$this->setMarker(
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
	 * Example: If the parameter is 'foo', our extension is named 'bar' and we are in p1,
	 * then the return value is 'class="tx-bar-pi1-foo"'.
	 *
	 * If the parameter is an emty string, the return value is an empty string as well
	 * (not an attribute with an empty value).
	 *
	 * @param	string		a CSS class name (may be empty)
	 *
	 * @return	string		a CSS class attribute (may be empty)
	 *
	 * @access	protected
	 */
	function createClassAttribute($className) {
		return !empty($className) ? $this->pi_classParam($className) : '';
	}

	/**
	 * Includes a link to the CSS file configured as "cssFile" and adds it to the
	 * automatic page header with $this->prefixId.'_css' as the array key.
	 *
	 * If no file is specified, no link is created.
	 *
	 * This function may only be called if $this->$prefixId has been set.
	 *
	 * This function is deprecated as the CSS file should be added via TypoScript
	 * to the page. Copy the variable cssFile from your TS setup to your TS
	 * constants and change the value of the TS setup variable so it points to
	 * the TS constant variable. Additionally you should add the TS constant
	 * cssFile to your TS setup page.includeCSS. See the example for details:
	 *
	 * TS constants:
	 * plugin.tx_yourextension_pi1.cssFile = /path/to/yourextension.css
	 *
	 * TS setup:
	 * plugin.tx_yourextension_pi1.cssFile = {$plugin.tx_yourextension_pi1.cssFile}
	 * page.includeCSS.yourextension = {$plugin.tx_yourextension_pi1.cssFile}
	 *
	 * @access protected
	 *
	 * @deprecated	0.4.0 - 2007-12-14
	 */
	function addCssToPageHeader() {
		if ($this->hasConfValueString('cssFile', 's_template_special')) {
			// We use an explicit array key so the CSS file gets included only
			// once even if there are two instances of the front end plugin
			// on the same page.
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.'_css']
				 = '<style type="text/css">@import "'
				 .$this->getConfValueString(
					'cssFile',
					's_template_special',
					true
				).'";</style>';
		}
	}

	/**
	 * Includes a link to the JavaScript file configured as "jsFile" and adds it
	 * to the automatic page header with $this->prefixId.'_js' as the array key.
	 *
	 * If no file is specified, no link is created.
	 *
	 * This function may only be called if $this->$prefixId has been set.
	 *
	 * @access	protected
	 */
	function addJavaScriptToPageHeader() {
		if ($this->hasConfValueString('jsFile', 's_template_special')) {
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.'_js']
				= '<script type="text/javascript" src="'
				.$this->getConfValueString(
					'jsFile',
					's_template_special',
					true
				).'"></script>';
		}
	}

	/**
	 * Recursively creates a comma-separated list of subpage UIDs from
	 * a list of pages. The result also includes the original pages.
	 * The maximum level of recursion can be limited:
	 * 0 = no recursion (the default value, will return $startPages),
	 * 1 = only direct child pages,
	 * ...,
	 * 250 = all descendants for all sane cases
	 *
	 * @param	string		comma-separated list of page UIDs to start from,
	 * 						must only contain numbers and commas, may be empty
	 * @param	integer		maximum depth of recursion, must be >= 0
	 *
	 * @return	string		comma-separated list of subpage UIDs including the
	 * 						UIDs provided in $startPages, will be empty if
	 * 						$startPages is empty
	 */
	public function createRecursivePageList(
		$startPages, $recursionDepth = 0
	) {
		if ($recursionDepth < 0) {
			throw new Exception('$recursionDepth must be >= 0.');
		}
		if ($startPages == '') {
			return '';
		}

		$collectivePageList = $startPages;
		$currentPageList = $collectivePageList;
		$currentRecursionLevel = 0;

		while (
			!empty($currentPageList) && ($currentRecursionLevel < $recursionDepth)
		) {
		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid',
				'pages',
				'pid!=0' .
					' AND pid IN ('.$currentPageList.')' .
					$this->enableFields('pages')
			);
			if (!$dbResult) {
				throw new Exception(DATABASE_QUERY_ERROR);
			}

			$currentPageList = '';
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				if (!empty($currentPageList)) {
					$currentPageList .= ',';
				}
				$currentPageList .= intval($row['uid']);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
			if (!empty($currentPageList)) {
				// It is ensured that $collectivePageList is non-empty at this
				// point so the comma won't be the first character.
				$collectivePageList .= ','.$currentPageList;
			}

			$currentRecursionLevel++;
		}

		return $collectivePageList;
	}

	/**
	 * Intvals all piVars that are supposed to be integers:
	 * showUid, pointer, mode
	 *
	 * If some of these piVars are not set, this function will not set them either.
	 *
	 * If $this->piVars is empty, this function is a no-op.
	 *
	 * @param	array		array of array keys for $this->piVars that will be intvaled as well
	 *
	 * @access	protected
	 */
	function securePiVars(array $additionalPiVars = array()) {
		if ($this->piVars) {
			$defaultIntPiVars = array('showUid', 'pointer', 'mode');
			foreach (array_merge($defaultIntPiVars, $additionalPiVars) as $key) {
				if (isset($this->piVars[$key])) {
					$this->piVars[$key] = intval($this->piVars[$key]);
				}
			}
		}
	}

	/**
	 * Resets the marker contents.
	 *
	 * @access	protected
	 */
	function resetMarkers() {
		$this->markers = array();
	}

	/**
	 * Resets the list of subparts to hide.
	 *
	 * @access	protected
	 */
	function resetSubpartsHiding() {
		$this->subpartsToHide = array();
	}

	/**
	 * Creates an IMG for a resized image version of $fullPath.
	 * If the image cannot be created, the ALT text is returned instead.
	 *
	 * @param	string		full path to of the original image (may not be empty)
	 * @param	string		alt text (may be empty)
	 * @param	integer		max width in pixels (set to zero to set no limit)
	 * @param	integer		max height in pixels (set to zero to set no limit)
	 * @param	integer		max area in square pixels (set to zero to set no limit)
	 * @param	string		title text (may be empty)
	 *
	 * @return	string		IMG tag
	 *
	 * @access	protected
	 */
	function createRestrictedImage($fullPath, $altText = '', $maxWidth = 0, $maxHeight = 0, $maxArea = 0, $titleText = '') {
		$isOkay = false;

		$imageConf = array();
		$imageConf['file'] = $fullPath;
		$imageConf['altText'] = $altText;
		$imageConf['titleText'] = $titleText;

		$changeSize = false;

		$actualSize = null;
		if (!empty($fullPath) && is_file($fullPath)) {
			$actualSize = GetImageSize($fullPath);
		}
		if ($actualSize) {
			$width = $actualSize[0];
			$height = $actualSize[1];
		} else {
			$width = 0;
			$height = 0;
		}

		// Only images with a width and height > 0 make sense to use.
		if (($width > 0) && ($height > 0)) {
			$edgeQuotient = $width / $height;

			if ($maxArea) {
				$currentArea = $width * $height;
				if ($currentArea > $maxArea) {
					$width = round(sqrt($maxArea * $edgeQuotient));
					$height = round(sqrt($maxArea / $edgeQuotient));
					$changeSize = true;
				}
			}
			if ($maxWidth && ($width > $maxWidth)) {
				$width = $maxWidth;
				$height = round($width / $edgeQuotient);
				$changeSize = true;
			}
			if ($maxHeight && ($height > $maxHeight)) {
				$height = $maxHeight;
				$width = round($edgeQuotient * $height);
				$changeSize = true;
			}
			if ($changeSize) {
				$imageConf['file.']['width'] = $width;
				$imageConf['file.']['height'] = $height;
			}

			$result = $this->cObj->IMAGE($imageConf);
			if (!empty($result)) {
				$isOkay = true;
			}
		}

		if (!$isOkay) {
			$result = $altText;
		}

		return $result;
	}

	/**
	 * Extracts a value within listView.
	 *
	 * @param	string		TS setup field name to extract (within listView.),
	 * 						must not be empty
	 *
	 * @return	string		the contents of that field within listView., may be
	 * 						empty
	 */
	private function getListViewConfigurationValue($fieldName) {
		if (empty($fieldName)) {
			throw new Exception('$fieldName must not be empty.');
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
	 * @param	string		TS setup field name to extract (within listView.),
	 * 						must not be empty
	 *
	 * @return	string		the trimmed contents of that field within listView.
	 * 						or an empty string if the value was not set
	 */
	public function getListViewConfValueString($fieldName) {
		return trim($this->getListViewConfigurationValue($fieldName));
	}

	/**
	 * Returns an integer value within listView.
	 *
	 * @param	string		TS setup field name to extract (within listView.),
	 * 						must not be empty
	 *
	 * @return	integer		the integer value of that field within listView. or
	 * 						zero if the value was not set
	 */
	public function getListViewConfValueInteger($fieldName) {
		return intval($this->getListViewConfigurationValue($fieldName));
	}

	/**
	 * Returns a boolean value within listView.
	 *
	 * @param	string		TS setup field name to extract (within listView.),
	 * 						must not be empty
	 *
	 * @return	boolean		the boolean value of that field within listView.,
	 * 						false if no value was set
	 */
	public function getListViewConfValueBoolean($fieldName) {
		return (boolean) $this->getListViewConfigurationValue($fieldName);
	}

	/**
	 * Checks whether a front end user is logged in.
	 *
	 * @return	boolean		true if a user is logged in, false otherwise
	 *
	 * @access	public
	 */
	function isLoggedIn() {
		return ((boolean) $GLOBALS['TSFE']) && ((boolean) $GLOBALS['TSFE']->loginUser);
	}

	/**
	 * If a user is logged in, retrieves that user's data as stored in the
	 * table "feusers" and stores it in $this->feuser.
	 *
	 * If no user is logged in, $this->feuser will be null.
	 */
	private function retrieveFeUser() {
		$this->feuser = $this->isLoggedIn()
			? $GLOBALS['TSFE']->fe_user->user : null;
	}

	/**
	 * Returns the UID of the currently logged-in FE user
	 * or 0 if no FE user is logged in.
	 *
	 * @return	integer		the UID of the logged-in FE user or 0 if no FE user is logged in
	 *
	 * @access	public
	 */
	function getFeUserUid() {
		// If we don't have the FE user's UID (yet), try to retrieve it.
		if (!$this->feuser) {
			$this->retrieveFeUser();
		}

		return ($this->isLoggedIn() ? intval($this->feuser['uid']) : 0);
	}

	/**
	 * Sets the "flavor" of the object to check.
	 *
	 * @param	string		a short string identifying the "flavor" of the object to check (may be empty)
	 *
	 * @access	public
	 */
	function setFlavor($flavor) {
		if ($this->configurationCheck) {
			$this->configurationCheck->setFlavor($flavor);
		}
	}

	/**
	 * Returns the current flavor of the object to check.
	 *
	 * @return	string		the current flavor of the object to check (or an empty string if no flavor is set)
	 *
	 * @access	public
	 */
	function getFlavor() {
		$result = '';

		if ($this->configurationCheck) {
			$result = $this->configurationCheck->getFlavor();
		}

		return $result;
	}

	/**
	 * Sets the error text of $this->configurationCheck.
	 *
	 * If this->configurationCheck is null, this function is a no-op.
	 *
	 * @param	string		error text to set (may be empty)
	 *
	 * @access	protected
	 */
	function setErrorMessage($message) {
		if ($this->configurationCheck) {
			$this->configurationCheck->setErrorMessage($message);
		}
	}

	/**
	 * Checks this object's configuration and returns a formatted error message
	 * (if any). If there are several objects of this class, still only one
	 * error message is created (in order to prevent duplicate messages).
	 *
	 * @param	boolean		whether to use the raw message instead of the wrapped message
	 * @param	string		flavor to use temporarily for this call (leave empty to not change the flavor)
	 *
	 * @return	string		a formatted error message (if there are errors) or an empty string
	 *
	 * @access	public
	 */
	function checkConfiguration($useRawMessage = false, $temporaryFlavor = '') {
		static $hasDisplayedMessage = false;
		$result = '';

		if ($this->configurationCheck) {
			if (!empty($temporaryFlavor)) {
				$oldFlavor = $this->getFlavor();
				$this->setFlavor($temporaryFlavor);
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
				$hasDisplayedMessage = true;
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
	 * @return	string		the wrapped error text (or an empty string if there
	 * 						are no errors)
	 *
	 * @access	protected
	 */
	function getWrappedConfigCheckMessage() {
		$result = '';

		if ($this->configurationCheck) {
			$result = $this->configurationCheck->getWrappedMessage();
		}

		return $result;
	}

	/**
	 * Gets the ID of the currently selected back-end page.
	 *
	 * @return	integer		the current back-end page ID (or 0 if there is an
	 * 						error)
	 *
	 * @access	public
	 */
	function getCurrentBePageId() {
		return intval(t3lib_div::_GP('id'));
	}

	/**
	 * Wrapper function for t3lib_pageSelect::enableFields() since it is no
	 * longer accessible statically.
	 *
	 * Returns a part of a WHERE clause which will filter out records with
	 * start/end times or deleted/hidden/fe_groups fields set to values that
	 * should de-select them according to the current time, preview settings or
	 * user login.
	 * Is using the $TCA arrays "ctrl" part where the key "enablefields"
	 * determines for each table which of these features applies to that table.
	 *
	 * @param	string		table name found in the $TCA array
	 * @param	integer		If $showHidden is set (0/1), any hidden-fields in
	 * 						records are ignored. NOTICE: If you call this function,
	 * 						consider what to do with the show_hidden parameter.
	 * 						Maybe it should be set? See tslib_cObj->enableFields
	 * 						where it's implemented correctly.
	 * @param	array		Array you can pass where keys can be "disabled",
	 * 						"starttime", "endtime", "fe_group" (keys from
	 * 						"enablefields" in TCA) and if set they will make sure
	 * 						that part of the clause is not added. Thus disables
	 * 						the specific part of the clause. For previewing etc.
	 * @param	boolean		If set, enableFields will be applied regardless of
	 * 						any versioning preview settings which might otherwise
	 * 						disable enableFields.
	 *
	 * @return	string		the clause starting like " AND ...=... AND ...=..."
	 */
	public function enableFields(
		$table, $showHidden = -1, array $ignoreArray = array(),
		$noVersionPreview = false
	) {
		if (!in_array($showHidden, array(-1, 0, 1))) {
			throw new Exception(
				'$showHidden may only be -1, 0 or 1, but actually is '.$showHidden
			);
		}

		// maps $showHidden (-1..1) to (0..2) which to ensure valid array keys
		$showHiddenKey = $showHidden + 1;
		$ignoresKey = serialize($ignoreArray);
		$previewKey = intval($noVersionPreview);
		if (!isset(self::$enableFieldsCache[$table][$showHidden][$ignoresKey]
			[$previewKey])
		) {
			$this->retrievePageForEnableFields();
			self::$enableFieldsCache[$table][$showHidden][$ignoresKey]
				[$previewKey]
				= self::$pageForEnableFields->enableFields(
					$table,
					$showHidden,
					$ignoreArray,
					$noVersionPreview
				);
		}

		return self::$enableFieldsCache[$table][$showHidden][$ignoresKey]
			[$previewKey];
	}

	/**
	 * Makes sure that self::$pageForEnableFields is a page object.
	 */
	protected function retrievePageForEnableFields() {
		if (!is_object(self::$pageForEnableFields)) {
			if (isset($GLOBALS['TSFE'])
				&& is_object($GLOBALS['TSFE']->sys_page)
			) {
				self::$pageForEnableFields = $GLOBALS['TSFE']->sys_page;
			} else {
				self::$pageForEnableFields
					= t3lib_div::makeInstance('t3lib_pageSelect');
			}
		}
	}

	/**
	 * Checks whether a marker name (or subpart name) is valid (including the
	 * leading and trailing hashes ###).
	 *
	 * A valid marker name must be a non-empty string, consisting of uppercase
	 * and lowercase letters ranging A to Z, digits and underscores. It must
	 * start with a lowercase or uppercase letter ranging from A to Z. It must
	 * not end with an underscore. In addition, it must be prefixed and suffixed
	 * with ###.
	 *
	 * @param	string		marker name to check (with the hashes), may be
	 * 						empty
	 *
	 * @return	boolean		true if the marker name is valid, false otherwise
	 */
	private function isMarkerNameValidWithHashes($markerName) {
		return (boolean) preg_match(
			'/^###[a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?###$/', $markerName
		);
	}

	/**
	 * Checks whether a marker name (or subpart name) is valid (excluding the
	 * leading and trailing hashes ###).
	 *
	 * A valid marker name must be a non-empty string, consisting of uppercase
	 * and lowercase letters ranging A to Z, digits and underscores. It must
	 * start with a lowercase or uppercase letter ranging from A to Z. It must
	 * not end with an underscore.
	 *
	 * @param	string		marker name to check (without the hashes), may be
	 * 						empty
	 *
	 * @return	boolean		true if the marker name is valid, false otherwise
	 */
	private function isMarkerNameValidWithoutHashes($markerName) {
		return $this->isMarkerNameValidWithHashes('###'.$markerName.'###');
	}

	/**
	 * Sets the PHP locale (as set in config.locale_all).
	 */
	protected function setLocaleConvention() {
		setlocale(LC_ALL, $GLOBALS['TSFE']->config['config']['locale_all']);
	}

	/**
	 * Clears all caches of this object (not the FE cache, though).
	 */
	public function clearCaches() {
		self::$pageForEnableFields = null;
		self::$enableFieldsCache = array();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_templatehelper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_templatehelper.php']);
}
?>