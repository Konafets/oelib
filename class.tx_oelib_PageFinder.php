<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Bernd Schönbach <bernd@oliverklee.de>
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
 * Class 'tx_oelib_PageFinder' for the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_oelib_PageFinder {
	/**
	 * @var tx_oelib_PageFinder the Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var integer the manually set page UID
	 */
	private $storedPageUid = 0;

	/**
	 * Don't call this constructor; use getInstance instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return tx_oelib_MapperRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_oelib_PageFinder();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 */
	public static function purgeInstance() {
		if (is_object(self::$instance)) {
			self::$instance->__destruct();
		}

		self::$instance = null;
	}

	/**
	 * Returns the UID of the current page.
	 *
	 * Will start with looking into the manually set page UID, then if a FE
	 * page UID is present and finally if a BE page UID is present.
	 *
	 * @return integer the ID of the current page, will be zero if no page is
	 *                 present
	 */
	public function getPageUid() {
		if ($this->storedPageUid > 0) {
			$result =  $this->storedPageUid;
		} elseif (is_object($GLOBALS['TSFE']) && ($GLOBALS['TSFE']->id > 0)) {
			$result = $GLOBALS['TSFE']->id;
		} elseif (intval(t3lib_div::_GP('id')) > 0) {
			$result = intval(t3lib_div::_GP('id'));
		} else {
			$result = 0;
		}

		return $result;
	}

	/**
	 * Manually sets a page UID which always will be returned by getPageUid.
	 *
	 * @param integer the page UID to store manually, must be > 0
	 */
	public function setPageUid($uidToStore) {
		if ($uidToStore <= 0) {
			throw new Exception('The given page UID was "' . $uidToStore . '". ' .
				'Only integer values greater than zero are allowed.'
			);
		}
		$this->storedPageUid = $uidToStore;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_PageFinder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_PageFinder.php']);
}
?>