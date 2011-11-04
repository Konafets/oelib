<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Saskia Metzler <saskia@merlin.owl.de>
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
 * This class provides file functions.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_oelib_FileFunctions {
	/**
	 * Wrapper function for rmdir, allowing recursive deletion of folders and
	 * files.
	 *
	 * @param string Absolute path to folder, see PHP rmdir() function.
	 *               Removes trailing slash internally.
	 * @param boolean whether to allow deletion of non-empty directories
	 *
	 * @return boolean TRUE if @rmdir went well, FALSE otherwise
	 *
	 * @see t3lib_div::rmdir()
	 *
	 * @deprecated 2010-07-22 use t3lib_div::rmdir instead
	 */
	public static function rmdir($path, $removeNonEmpty = FALSE) {
		return t3lib_div::rmdir($path, $removeNonEmpty);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_FileFunctions.php']) {
	include_once ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_FileFunctions.php']);
}
?>