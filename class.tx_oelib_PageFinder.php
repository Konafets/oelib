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
	 * @var integer the sources the page can come from
	 */
	const SOURCE_AUTO = 0,
		SOURCE_FRONT_END = 1,
		SOURCE_BACK_END = 2;

	/**
	 * @var tx_oelib_PageFinder the Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var integer the manually set page UID
	 */
	private $storedPageUid = 0;

	/**
	 * @var integer the source the page is retrieved from
	 */
	private $pageUidSource = self::SOURCE_AUTO;

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
	 * If pageUidSource is set to SOURCE_FRONT_END or SOURCE_BACK_END, this
	 * function returns the UID set in this part. Otherwise starts with looking
	 * into the manually set page UID, then if a FE page UID is present
	 * and finally if a BE page UID is present.
	 *
	 * @return integer the ID of the current page, will be zero if no page is
	 *                 present
	 */
	public function getPageUid() {
		if ($this->isForceSourceSet()) {
			return $this->getForcedPageUid();
		}

		if ($this->storedPageUid > 0) {
			$result = $this->storedPageUid;
		} elseif (is_object($GLOBALS['TSFE']) && ($GLOBALS['TSFE']->id > 0)) {
			$result = $this->retrievePageUID(self::SOURCE_FRONT_END);
		} else {
			$result = $this->retrievePageUID(self::SOURCE_BACK_END);
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

	/**
	 * Forces the getPageUid function to get the page UID from a specific
	 * source, ignoring an empty value or the original precedence.
	 *
	 * @param integer SOURCE_BACK_END or SOURCE_FRONT_END
	 */
	public function forceSource($modeToForce) {
		$this->pageUidSource = $modeToForce;
	}

	/**
	 * Checks if the pageUidSource has been set.
	 *
	 * @return boolean true if pageUidSource is set to SOURCE_BACK_END or
	 *                 SOURCE_FRONT_END, false otherwise
	 */
	private function isForceSourceSet() {
		return ($this->pageUidSource != self::SOURCE_AUTO);
	}

	/**
	 * Returns the UID of the page in the source set in force mode.
	 * Must only be called after forceModeIsSet and the function returned true.
	 *
	 * @return integer the UID of the page fromn the forced source, may be zero
	 */
	private function getForcedPageUid() {
		return ($this->pageUidSource == self::SOURCE_FRONT_END)
			? $this->retrievePageUID(self::SOURCE_FRONT_END)
			: $this->retrievePageUID(self::SOURCE_BACK_END);
	}

	/**
	 * Returns the page UID from given source.
	 *
	 * @param integer the source to fetch the page UID from, must be
	 *                SOURCE_FRONT_END or SOURCE_BACK_END
	 *
	 * @return integer the page UID, will be empty if no page UID in given
	 *                 source was set
	 */
	private function retrievePageUID($pidSource) {
		if (!in_array(
			$pidSource, array(self::SOURCE_BACK_END, self::SOURCE_FRONT_END)
		)) {
			throw new Exception('The given PID source was "' . $pidSource . '".' .
				'Only the values "' . self::SOURCE_FRONT_END . '" and "' .
				self::SOURCE_BACK_END . '" are allowed.'
			);
		}

		if ($pidSource == self::SOURCE_BACK_END) {
			$result = intval(t3lib_div::_GP('id'));
		} else {
			$result = (is_object($GLOBALS['TSFE']))
				? $GLOBALS['TSFE']->id
				: 0;
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_PageFinder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_PageFinder.php']);
}
?>