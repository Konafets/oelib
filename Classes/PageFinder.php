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
 * This class provides an abstraction for selecting a page in the FE or BE.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Oelib_PageFinder {
	/**
	 * @var int the sources the page can come from
	 */
	const SOURCE_AUTO = 0,
		SOURCE_FRONT_END = 1,
		SOURCE_BACK_END = 2,
		SOURCE_MANUAL = 3,
		NO_SOURCE_FOUND = 4;

	/**
	 * @var Tx_Oelib_PageFinder the Singleton instance
	 */
	private static $instance = NULL;

	/**
	 * @var int the manually set page UID
	 */
	private $storedPageUid = 0;

	/**
	 * @var int the source the page is retrieved from
	 */
	private $manualPageUidSource = self::SOURCE_AUTO;

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
	 * @return Tx_Oelib_PageFinder the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new Tx_Oelib_PageFinder();
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
	 * Returns the UID of the current page.
	 *
	 * If manualPageUidSource is set to SOURCE_FRONT_END or SOURCE_BACK_END, this
	 * function returns the UID set in this part. Otherwise starts with looking
	 * into the manually set page UID, then if a FE page UID is present
	 * and finally if a BE page UID is present.
	 *
	 * @return int the ID of the current page, will be zero if no page is
	 *                 present or no page source could be found
	 */
	public function getPageUid() {
		switch ($this->getCurrentSource()) {
			case self::SOURCE_MANUAL:
				$result = $this->storedPageUid;
				break;
			case self::SOURCE_FRONT_END:
				$result = intval($GLOBALS['TSFE']->id);
				break;
			case self::SOURCE_BACK_END:
				$result = intval(t3lib_div::_GP('id'));
				break;
			default:
				$result = 0;
		}

		return $result;
	}

	/**
	 * Manually sets a page UID which always will be returned by getPageUid.
	 *
	 * @param int $uidToStore the page UID to store manually, must be > 0
	 *
	 * @return void
	 */
	public function setPageUid($uidToStore) {
		if ($uidToStore <= 0) {
			throw new InvalidArgumentException(
				'The given page UID was "' . $uidToStore . '". Only integer values greater than zero are allowed.',
				1331489010
			);
		}
		$this->storedPageUid = $uidToStore;
	}

	/**
	 * Forces the getPageUid function to get the page UID from a specific
	 * source, ignoring an empty value or the original precedence.
	 *
	 * @param int $modeToForce SOURCE_BACK_END or SOURCE_FRONT_END
	 *
	 * @return void
	 */
	public function forceSource($modeToForce) {
		$this->manualPageUidSource = $modeToForce;
	}

	/**
	 * Returns the current source for the page UID.
	 *
	 * @return int either SOURCE_BACK_END, SOURCE_FRONT_END or SOURCE_MANUAL,
	 *                 will be NO_SOURCE_FOUND if no source could be detected
	 */
	public function getCurrentSource() {
		if ($this->manualPageUidSource !== self::SOURCE_AUTO) {
			$result = $this->manualPageUidSource;
		} elseif ($this->hasManualPageUid()) {
			$result = self::SOURCE_MANUAL;
		} elseif ($this->hasFrontEnd()) {
			$result = self::SOURCE_FRONT_END;
		} elseif ($this->hasBackEnd()) {
			$result = self::SOURCE_BACK_END;
		} else {
			$result = self::NO_SOURCE_FOUND;
		}

		return $result;
	}

	/**
	 * Checks whether a front end (with a non-zero page UID) is present.
	 *
	 * @return bool TRUE if there is a front end with a non-zero page UID,
	 *                 FALSE otherwise
	 */
	private function hasFrontEnd() {
		return (is_object($GLOBALS['TSFE']) && ($GLOBALS['TSFE']->id > 0));
	}

	/**
	 * Checks whether a back-end page UID has been set.
	 *
	 * @return bool TRUE if a back-end page UID has been set, FALSE otherwise
	 */
	private function hasBackEnd() {
		return (intval(t3lib_div::_GP('id')) > 0);
	}

	/**
	 * Checks whether a manual page UID has been set.
	 *
	 * @return bool TRUE if a page UID has been set manually, FALSE otherwise
	 */
	private function hasManualPageUid() {
		return ($this->storedPageUid > 0);
	}
}