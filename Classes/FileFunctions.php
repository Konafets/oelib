<?php
/**
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
 * This class provides file functions.
 *
 * @deprecated 2010-07-22 use t3lib_div::rmdir instead
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_FileFunctions {
	/**
	 * Wrapper function for rmdir, allowing recursive deletion of folders and
	 * files.
	 *
	 * @param string $path Absolute path to folder, see PHP rmdir() function. Removes trailing slash internally.
	 * @param boolean $removeNonEmpty whether to allow deletion of non-empty directories
	 *
	 * @return boolean TRUE if @rmdir went well, FALSE otherwise
	 *
	 * @see t3lib_div::rmdir()
	 *
	 * @deprecated 2010-07-22 use t3lib_div::rmdir instead
	 */
	public static function rmdir($path, $removeNonEmpty = FALSE) {
		t3lib_div::logDeprecatedFunction();

		return t3lib_div::rmdir($path, $removeNonEmpty);
	}
}