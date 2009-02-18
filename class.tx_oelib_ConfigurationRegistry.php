<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_ConfigurationRegistry' for the 'oelib' extension.
 *
 * This class represents a registration that allows the storage and retrieval
 * of configuration objects.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_ConfigurationRegistry {
	/**
	 * @var tx_oelib_ConfigurationRegistry the Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var array already created configurations (by namespace)
	 */
	private $configurations = array();

	/**
	 * The constructor. Use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		foreach (array_keys($this->configurations) as $namespace) {
			$this->dropConfiguration($namespace);
		}
	}

	/**
	 * Destructs a configuration for a given namespace and drops the reference to
	 * it.
	 *
	 * @param string the namespace of the configuration to drop, must not be empty,
	 *               must have been set in this registry
	 */
	private function dropConfiguration($namespace) {
		$this->configurations[$namespace]->__destruct();
		unset($this->configurations[$namespace]);
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return tx_oelib_ConfigurationRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_oelib_ConfigurationRegistry();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 */
	public static function purgeInstance() {
		if (self::$instance) {
			self::$instance->__destruct();
		}
		self::$instance = null;
	}

	/**
	 * Retrieves a dataMapper by class name.
	 *
	 * @param string the name of a configuration namespace, e.g. "plugin.tx_oelib",
	 *               must not be empty
	 *
	 * @return tx_oelib_Configuration the configuration for the given namespace
	 *
	 * @see getByNamespace
	 */
	public static function get($namespace) {
		return self::getInstance()->getByNamespace($namespace);
	}

	/**
	 * Retrieves a dataMapper by class name.
	 *
	 * @param string the name of a configuration namespace, e.g. "plugin.tx_oelib",
	 *               must not be empty
	 *
	 * @return tx_oelib_Configuration the configuration for the given namespace
	 */
	private function getByNamespace($namespace) {
		$this->checkForNonEmptyNamespace($namespace);

		if (!isset($this->configurations[$namespace])) {
			$this->configurations[$namespace]
				= t3lib_div::makeInstance('tx_oelib_Configuration');
		}

		return $this->configurations[$namespace];
	}

	/**
	 * Sets a configuration for a certain namespace.
	 *
	 * @param string the namespace of the configuration to set, must not be
	 *               empty
	 * @param tx_oelib_Configuration the configuration to set
	 */
	public function set($namespace, tx_oelib_Configuration $configuration) {
		$this->checkForNonEmptyNamespace($namespace);

		if (isset($this->configurations[$namespace])) {
			$this->dropConfiguration($namespace);
		}

		$this->configurations[$namespace] = $configuration;
	}

	/**
	 * Checks that $namespace is non-empty.
	 *
	 * @throws Exception if $namespace is empty
	 *
	 * @param string namespace name to check
	 */
	private function checkForNonEmptyNamespace($namespace) {
		if ($namespace == '') {
			throw new Exception('$namespace must not be empty.');
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_ConfigurationRegistry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_ConfigurationRegistry.php']);
}
?>