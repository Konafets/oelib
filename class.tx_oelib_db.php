<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(PATH_t3lib . 'class.t3lib_page.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Class 'tx_oelib_db' for the 'oelib' extension.
 *
 * This class provides some static database-related functions.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_db {
	/**
	 * @var t3lib_pageSelect page object which we will use to call
	 *                       enableFields on
	 */
	private static $pageForEnableFields = null;

	/**
	 * @var array cached results for the enableFields function
	 */
	private static $enableFieldsCache = array();

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
	 * @param string table name found in the $TCA array
	 * @param integer If $showHidden is set (0/1), any hidden-fields in
	 *                records are ignored. NOTICE: If you call this function,
	 *                consider what to do with the show_hidden parameter.
	 *                Maybe it should be set? See tslib_cObj->enableFields
	 *                where it's implemented correctly.
	 * @param array Array you can pass where keys can be "disabled",
	 *              "starttime", "endtime", "fe_group" (keys from
	 *              "enablefields" in TCA) and if set they will make sure
	 *              that part of the clause is not added. Thus disables
	 *              the specific part of the clause. For previewing etc.
	 * @param boolean If set, enableFields will be applied regardless of
	 *                any versioning preview settings which might otherwise
	 *                disable enableFields.
	 *
	 * @return string the WHERE clause starting like " AND ...=... AND ...=..."
	 */
	public static function enableFields(
		$table, $showHidden = -1, array $ignoreArray = array(),
		$noVersionPreview = false
	) {
		if (!in_array($showHidden, array(-1, 0, 1))) {
			throw new Exception(
				'$showHidden may only be -1, 0 or 1, but actually is ' .
					$showHidden
			);
		}

		// maps $showHidden (-1..1) to (0..2) which ensures valid array keys
		$showHiddenKey = $showHidden + 1;
		$ignoresKey = serialize($ignoreArray);
		$previewKey = intval($noVersionPreview);
		if (!isset(self::$enableFieldsCache[$table][$showHiddenKey][$ignoresKey]
			[$previewKey])
		) {
			self::retrievePageForEnableFields();
			self::$enableFieldsCache[$table][$showHiddenKey][$ignoresKey]
				[$previewKey]
				= self::$pageForEnableFields->enableFields(
					$table,
					$showHidden,
					$ignoreArray,
					$noVersionPreview
				);
		}

		return self::$enableFieldsCache[$table][$showHiddenKey][$ignoresKey]
			[$previewKey];
	}

	/**
	 * Makes sure that self::$pageForEnableFields is a page object.
	 */
	private static function retrievePageForEnableFields() {
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
	 * Recursively creates a comma-separated list of subpage UIDs from
	 * a list of pages. The result also includes the original pages.
	 * The maximum level of recursion can be limited:
	 * 0 = no recursion (the default value, will return $startPages),
	 * 1 = only direct child pages,
	 * ...,
	 * 250 = all descendants for all sane cases
	 *
	 * Note: The returned page list is _not_ sorted.
	 *
	 * @param string comma-separated list of page UIDs to start from,
	 *               must only contain numbers and commas, may be empty
	 * @param integer maximum depth of recursion, must be >= 0
	 *
	 * @return string comma-separated list of subpage UIDs including the
	 *                UIDs provided in $startPages, will be empty if
	 *                $startPages is empty
	 */
	public static function createRecursivePageList(
		$startPages, $recursionDepth = 0
	) {
		if ($recursionDepth < 0) {
			throw new Exception('$recursionDepth must be >= 0.');
		}
		if ($recursionDepth == 0) {
			return $startPages;
		}
		if ($startPages == '') {
			return '';
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid',
			'pages',
			'pid IN (' . $startPages . ')' . tx_oelib_db::enableFields('pages')
		);
		if (!$dbResult) {
			throw new Exception(DATABASE_QUERY_ERROR);
		}

		$subPages = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			$subPages[] = $row['uid'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		if (!empty($subPages)) {
			$result = $startPages . ',' . self::createRecursivePageList(
				implode(',', $subPages), $recursionDepth - 1
			);
		} else {
			$result = $startPages;
		}
		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_db.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_db.php']);
}
?>