<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(t3lib_extMgm::extPath('oelib') . 'tx_oelib_commonConstants.php');

/**
 * This class implements the SPL autoloader.
 *
 * In addition, it loads the common constants.
 *
 * This class is deprecated. Please use the extbase or TYPO3 CMS autoloader instead.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Autoloader {
	/**
	 * Tries to load a class by class name.
	 *
	 * @param string $className
	 *        the name of the class to load, may be empty
	 *
	 * @return boolean TRUE if the class could be loaded, FALSE otherwise
	 */
	public static function load($className) {
		// This is necessary so the XCLASS statements at the bottom of the
		// included class files can access $TYPO3_CONF_VARS in the context
		// where they are included.
		global $TYPO3_CONF_VARS;

		if ($className === '') {
			return FALSE;
		}

		if (class_exists($className, FALSE) || interface_exists($className, FALSE)
		) {
			return TRUE;
		}

		$path = self::createPath($className);
		if (($path !== '') && (is_readable($path))) {
			include_once($path);
			$result = TRUE;
		} else {
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * Creates a path from a class name.
	 *
	 * @param string $className
	 *        class name in the format tx_myext_Dir1_Dir2_MyClass or Tx_MyExt_Dir1_Dir2_MyClass, must not be empty
	 *
	 * @return string the path to that class, will be empty if the path could not be created
	 */
	private static function createPath($className) {
		$matches = array();

		if (!preg_match('/[tT]x_([a-zA-Z0-9]+)_((?:[a-zA-Z0-9]+_)*)(?:[a-zA-Z0-9]+)/', $className, $matches)) {
			return '';
		}

		$extensionKey = strtolower($matches[1]);
		if (!t3lib_extMgm::isLoaded($extensionKey)) {
			return '';
		}

		$directories = str_replace('_', '/', $matches[2]);

		return t3lib_extMgm::extPath($extensionKey) . $directories . 'class.' . $className . '.php';
	}
}

spl_autoload_register(array('tx_oelib_Autoloader', 'load'));