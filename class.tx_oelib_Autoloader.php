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

// In the back end, PATH_tslib isn't defined yet.
if (!defined('PATH_tslib')) {
	define('PATH_tslib', t3lib_extMgm::extPath('cms') . 'tslib/');
}

require_once(t3lib_extMgm::extPath('oelib') . 'tx_oelib_commonConstants.php');

/**
 * Class 'tx_oelib_Autoloader' for the 'oelib' extension.
 *
 * This class implements the SPL autoloader.
 *
 * In addition, it loads the common constants.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Autoloader {
	/**
	 * Tries to load a class by class name.
	 *
	 * @param string the name of the class to load, may be empty
	 *
	 * @return boolean true if the class could be loaded, false otherwise
	 */
	public static function load($className) {
		// This is necessary so the XCLASS statements at the bottom of the
		// included class files can access $TYPO3_CONF_VARS in the context
		// where they are included.
		global $TYPO3_CONF_VARS;

		if ($className == '') {
			return false;
		}

		if (class_exists($className, false)
			|| interface_exists($className, false)
		) {
			return true;
		}

		$path = self::createPath($className);
		if (($path != '') && (is_readable($path))) {
			include_once($path);
			$result = true;
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Creates a path from a class name.
	 *
	 * @param string class name in the format tx_myext_Dir1_Dir2_MyClass,
	 *               must not be empty
	 *
	 * @return string the path to that class, will be empty if the path could
	 *                not be created
	 */
	private static function createPath($className) {
		$matches = array();

		if (!preg_match(
			'/tx_([a-z0-9]+)_((?:[a-zA-Z0-9]+_)*)(?:[a-zA-Z0-9]+)/', $className, $matches
		)) {
			return '';
		}

		$extensionKey = $matches[1];
		if (!t3lib_extMgm::isLoaded($extensionKey)) {
			return '';
		}

		$directories = str_replace('_', '/', $matches[2]);

		return t3lib_extMgm::extPath($extensionKey) . $directories . 'class.' .
			$className . '.php';
	}
}

spl_autoload_register(array('tx_oelib_Autoloader', 'load'));

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Autoloader.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Autoloader.php']);
}
?>