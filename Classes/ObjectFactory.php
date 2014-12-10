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
 * This helper class can create class instances with and without parameters.
 *
 * @deprecated 2014-04-11 use t3lib_div::makeInstance instead
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ObjectFactory {
	/**
	 * Creates an instance of the class $className.
	 *
	 * You can use additional parameters that will be passed to the constructor
	 * of the instantiated class.
	 *
	 * @param string $className the name of the existing class to create
	 *
	 * @return mixed an instance of $className
	 *
	 * @deprecated 2014-04-11 use t3lib_div::makeInstance instead
	 */
	public static function make($className) {
		t3lib_div::logDeprecatedFunction();

		// Makes sure that the parent class is included so it can load any
		// XCLASS subclasses.
		Tx_Oelib_Autoloader::load($className);

		if (func_num_args() === 1) {
			return t3lib_div::makeInstance($className);
		}

		$parameters = func_get_args();
		return call_user_func_array(
			array('t3lib_div', 'makeInstance'), $parameters
		);
	}
}