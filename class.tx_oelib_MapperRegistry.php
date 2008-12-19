<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(t3lib_extMgm::extPath('oelib') . 'exceptions/class.tx_oelib_notFoundException.php');

/**
 * Class 'tx_oelib_MapperRegistry' for the 'oelib' extension.
 *
 * This class represents a registry for mappers. The mappers must be located in
 * the directory Mapper/ in each extension. Extension can use mappers from
 * other extensions as well.
 *
 * Note: This does not work with user_ extensions yet.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_MapperRegistry {
	/**
	 * @var tx_oelib_MapperRegistry the Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var array already created mappers (by class name)
	 */
	private $mappers = array();

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		foreach ($this->mappers as $key => $mapper) {
			$mapper->__destruct();
			unset($this->mappers[$key]);
		}
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return tx_oelib_MapperRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_oelib_MapperRegistry();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 */
	public static function purgeInstance() {
		self::$instance = null;
	}

	/**
	 * Retrieves a dataMapper by class name.
	 *
	 * @throws tx_oelib_notFoundException if there is no such mapper
	 *
	 * @param string the name of an existing mapper class
	 *
	 * @see getByClassName
	 */
	public static function get($className) {
		return self::getInstance()->getByClassName($className);
	}

	/**
	 * Retrieves a dataMapper by class name.
	 *
	 * @throws tx_oelib_notFoundException if there is no such mapper class
	 *
	 * @param string the name of an existing mapper class
	 */
	private function getByClassName($className) {
		if ($className == '') {
			throw new Exception('$key must not be empty.');
		}
		if (!preg_match('/^tx_[a-z0-9]+_[a-zA-Z_]+/', $className)) {
			throw new Exception(
				'$className must be in the format ' .
					'tx_extensionname[_Folder]_ClassName, but was "' .
					$className . '".'
			);
		}

		if (!isset($this->mappers[$className])) {
			if (!class_exists($className)) {
				$path = $this->createPathFromClassName($className);

				if (!file_exists($path)) {
					throw new tx_oelib_notFoundException(
						'No mapper class "' . $className . '" could be found.'
					);
				}

				include_once($path);
			}

			$this->mappers[$className] = t3lib_div::makeInstance($className);
		}

		return $this->mappers[$className];
	}

	/**
	 * Creates the path to a mapper class.
	 *
	 * @param string the name of an existing mapper class in an extension
	 *
	 * @return string the path to that class, will not be empty
	 */
	private function createPathFromClassName($className) {
		$matches = array();
		preg_match('/^tx_([a-z]+)_/', $className, $matches);

		$extensionName = $matches[1];

		return t3lib_extMgm::extPath($extensionName) . 'Mapper/class.' .
			$className . '.php';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_MapperRegistry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_MapperRegistry.php']);
}
?>