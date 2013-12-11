<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This helper class can create class instances with and without parameters.
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
	 */
	public static function make($className) {
		// Makes sure that the parent class is included so it can load any
		// XCLASS subclasses.
		Tx_Oelib_Autoloader::load($className);

		if (func_num_args() == 1) {
			return t3lib_div::makeInstance($className);
		}

		$parameters = func_get_args();
		return call_user_func_array(
			array('t3lib_div', 'makeInstance'), $parameters
		);
	}
}