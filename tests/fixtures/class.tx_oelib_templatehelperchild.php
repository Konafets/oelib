<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(PATH_tslib.'class.tslib_content.php');
require_once(PATH_t3lib.'class.t3lib_timetrack.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_templatehelper.php');

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
		// Bolster up the fake front end.
		$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');

		$GLOBALS['TSFE']->tmpl = t3lib_div::makeInstance('t3lib_tsparser_ext');
		$GLOBALS['TSFE']->tmpl->flattenSetup(array(), '', false);
		$GLOBALS['TSFE']->tmpl->init();
		$GLOBALS['TSFE']->tmpl->getCurrentPageData();

		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_timeTrack');

		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->cObj->start('');

		parent::init($configuration);
	}

	/**
	 * Sets a configuration value.
	 *
	 * @param	string		key of the configuration property to set, must not be empty
	 * @param	mixed		value of the configuration property, may be empty or zero
	 */
	public function setConfigurationValue($key, $value) {
		$this->conf[$key] = $value;
		return;
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/tests/fixtures/class.tx_oelib_templatehelperchild.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/tests/fixtures/class.tx_oelib_templatehelperchild.php']);
}

?>
