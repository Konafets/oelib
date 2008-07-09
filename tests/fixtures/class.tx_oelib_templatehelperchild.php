<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_oelib_templatehelperchild' for the 'oelib' extension.
 *
 * This is mere a class used for unit tests of the 'oelib' extension. Don't
 * use it for any other purpose.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(PATH_tslib.'class.tslib_content.php');
require_once(PATH_t3lib.'class.t3lib_timetrack.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_templatehelper.php');

final class tx_oelib_templatehelperchild extends tx_oelib_templatehelper {
	public $prefixId = 'tx_oelib_templatehelperchild';
	public $scriptRelPath
		= 'tests/fixtures/class.tx_oelib_templatehelperchild.php';
	public $extKey = 'oelib';

	/**
	 * The constructor.
	 *
	 * @param	array	TS setup configuration array, may be empty
	 */
	public function __construct(array $configuration) {
		parent::init($configuration);
	}

	/**
	 * Bolsters up the fake front end.
	 */
	public function fakeFrontend() {
		return parent::fakeFrontend();
	}

	/**
	 * Returns the current configuration check object (or null if there is no
	 * such object).
	 *
	 * @return	object		the current configuration check object
	 */
	public function getConfigurationCheck() {
		return $this->configurationCheck;
	}

	/**
	 * Sets the salutation mode.
	 *
	 * @param	string		the salutation mode to use ("formal" or "informal")
	 */
	public function setSalutationMode($salutation) {
		$this->setConfigurationValue('salutation', $salutation);
	}

	/**
	 * Returns the localized label of the LOCAL_LANG key $key, simulating an FE
	 * environment.
	 *
	 * @param	string		the key from the LOCAL_LANG array for which to
	 * 						return the value
	 * @param	string		alternative string to return if no value is found
	 * 						for the key, neither for the local language nor the
	 * 						default.
	 * @param	boolean		If true, the output label is passed through
	 * 						htmlspecialchars().
	 *
	 * @return	string		the value from LOCAL_LANG
	 */
	public function translate($key, $alternativeString = '',
		$useHtmlSpecialChars = false
	) {
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
	 * @param	integer		page ID of the page for which the configuration
	 * 						should be retrieved, must be > 0
	 *
	 * @return	array		configuration array of the requested page for the
	 * 						current extension key
	 */
	public function &retrievePageConfig($pageId) {
		return parent::retrievePageConfig($pageId);
	}

	/**
	 * Switches the cached page in self::$pageForEnableFields to use the
	 * versioning preview.
	 *
	 * This function is intended for testing purposes only.
	 */
	public function enableVersioningPreviewForCachedPage() {
		$this->retrievePageForEnableFields();
		self::$pageForEnableFields->versioningPreview = true;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/tests/fixtures/class.tx_oelib_templatehelperchild.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/tests/fixtures/class.tx_oelib_templatehelperchild.php']);
}
?>