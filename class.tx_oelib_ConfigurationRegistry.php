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
	 * @var array already created configurations (by scope)
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
		foreach (array_keys($this->configurations) as $scope) {
			$this->dropConfiguration($scope);
		}
	}

	/**
	 * Destructs a configuration for a given scope and drops the reference to
	 * it.
	 *
	 * @param string the scope of the configuration to drop, must not be empty,
	 *               must have been set in this registry
	 */
	private function dropConfiguration($scope) {
		$this->configurations[$scope]->__destruct();
		unset($this->configurations[$scope]);
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
	 * @param string the name of a configuration scope, e.g. "plugin.tx_oelib",
	 *               must not be empty
	 *
	 * @return tx_oelib_Configuration the configuration for the given scope
	 *
	 * @see getByScope
	 */
	public static function get($scope) {
		return self::getInstance()->getByScope($scope);
	}

	/**
	 * Retrieves a dataMapper by class name.
	 *
	 * @param string the name of a configuration scope, e.g. "plugin.tx_oelib",
	 *               must not be empty
	 *
	 * @return tx_oelib_Configuration the configuration for the given scope
	 */
	private function getByScope($scope) {
		$this->checkForNonEmptyScope($scope);

		if (!isset($this->configurations[$scope])) {
			$this->configurations[$scope]
				= t3lib_div::makeInstance('tx_oelib_Configuration');
		}

		return $this->configurations[$scope];
	}

	/**
	 * Sets a configuration for a certain scope.
	 *
	 * @param string the scope of the configuration to set, must not be empty
	 * @param tx_oelib_Configuration the configuration to set
	 */
	public function set($scope, tx_oelib_Configuration $configuration) {
		$this->checkForNonEmptyScope($scope);

		if (isset($this->configurations[$scope])) {
			$this->dropConfiguration($scope);
		}

		$this->configurations[$scope] = $configuration;
	}

	/**
	 * Checks that $scope is non-empty
	 *
	 * @throws Exception if $scope is empty
	 *
	 * @param string scope name to check
	 */
	private function checkForNonEmptyScope($scope) {
		if ($scope == '') {
			throw new Exception('$scope must not be empty.');
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_ConfigurationRegistry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_ConfigurationRegistry.php']);
}
?>