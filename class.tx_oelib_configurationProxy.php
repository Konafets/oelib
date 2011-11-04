<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Saskia Metzler <saskia@merlin.owl.de>
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
 * Class 'tx_oelib_configurationProxy' for the 'oelib' extension.
 *
 * This singleton class provides access to an extension's global configuration
 * and allows to fake global configuration values for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_oelib_configurationProxy extends tx_oelib_PublicObject {
	/**
	 * @var array the singleton configuration proxy objects
	 */
	private static $instances = array();

	/**
	 * @var array stored configuration data for each extension which currently
	 *            uses the configuration proxy
	 */
	private $configuration = array();

	/**
	 * @var string key of the extension for which the EM configuration is stored
	 */
	private $extensionKey = '';

	/**
	 * @var boolean whether the configuration is already loaded
	 */
	private $isConfigurationLoaded = FALSE;

	/**
	 * Don't call this constructor; use getInstance instead.
	 *
	 * @param string extension key without the 'tx' prefix, used to
	 *               retrieve the EM configuration and as identifier for
	 *               an extension's instance of this class, must not be empty
	 */
	private function __construct($extensionKey) {
		$this->extensionKey = $extensionKey;
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
	}

	/**
	 * Retrieves the singleton configuration proxy instance for the extension
	 * named $extensionKey. This function usually should be called statically.
	 *
	 * @param string extension key without the 'tx' prefix, used to
	 *               retrieve the EM configuration and as identifier for
	 *               an extension's instance of this class, must not be empty
	 *
	 * @return tx_oelib_configurationProxy the singleton configuration
	 *                                     proxy object
	 */
	public static function getInstance($extensionKey) {
		if ($extensionKey == '') {
			throw new Exception('The extension key was not set.');
		}

		if (!is_object(self::$instances[$extensionKey])) {
			self::$instances[$extensionKey]
				= new tx_oelib_configurationProxy($extensionKey);
		}

		return self::$instances[$extensionKey];
	}

	/**
	 * Purges the current instances so that getInstance will create new
	 * instances.
	 */
	public static function purgeInstances() {
		self::$instances = array();
	}

	/**
	 * Loads the EM configuration for the extionsion key passed via
	 * getInstance() if the configuration is not yet loaded.
	 */
	private function loadConfigurationLazily() {
		if (!$this->isConfigurationLoaded) {
			$this->retrieveConfiguration();
		}
	}

	/**
	 * Retrieves the EM configuration for the extionsion key passed via
	 * getInstance().
	 *
	 * This function is accessible for testing purposes. As lazy implementation
	 * is used, this function might be useful to ensure static test conditions.
	 */
	public function retrieveConfiguration() {
		$this->configuration = unserialize(
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extensionKey]
		);
		$this->isConfigurationLoaded = TRUE;
	}

	/**
	 * Checks whether a certain key exists in an extension's configuration.
	 *
	 * @param string key to check, must not be empty
	 *
	 * @return boolean whether $key occurs in the configuration array of
	 *                 the extension named $this->extensionKey
	 */
	private function hasConfigurationValue($key) {
		$this->loadConfigurationLazily();

		return isset($this->configuration[$key]);
	}

	/**
	 * Returns a string configuration value.
	 *
	 * @param string key of the value to get, must not be empty
	 *
	 * @return string configuration value string, might be empty
	 */
	protected function get($key) {
		$this->loadConfigurationLazily();

		if ($this->hasConfigurationValue($key)) {
			$result = $this->configuration[$key];
		} else {
			$result = '';
		}

		return $result;
	}

	/**
	 * Returns a configuration value.
	 *
	 * @param string key of the value to get, must not be empty
	 *
	 * @return mixed configuration value, will be an empty string if the
	 *               value does not exist
	 *
	 * @deprecated 2009-06-12 Will be removed in oelib 0.8.0. Use get() instead
	 */
	private function getConfigurationValue($key) {
		t3lib_div::logDeprecatedFunction();

		return $this->get($key);
	}

	/**
	 * Sets a new configuration value.
	 *
	 * The configuration setters are intended to be used for testing purposes
	 * only.
	 *
	 * @param string key of the value to set, must not be empty
	 * @param mixed value to set
	 */
	protected function set($key, $value) {
		$this->loadConfigurationLazily();

		$this->configuration[$key] = $value;
	}

	/**
	 * Sets a new configuration value.
	 *
	 * The configuration setters are intended to be used for testing purposes
	 * only.
	 *
	 * @param string key of the value to set, must not be empty
	 * @param mixed value to set
	 *
	 * @deprecated 2009-06-12 Will be removed in oelib 0.8.0. Use set() instead
	 */
	private function setConfigurationValue($key, $value) {
		t3lib_div::logDeprecatedFunction();

		$this->set($key, $value);
	}

	/**
	 * Returns a string configuration value.
	 *
	 * @param string key of the value to get, must not be empty
	 *
	 * @return string configuration value string, might be empty
	 *
	 * @deprecated 2009-06-12 Will be removed in oelib 0.8.0. Use getAsString() instead
	 */
	public function getConfigurationValueString($key) {
		t3lib_div::logDeprecatedFunction();

		return $this->getAsString($key);
	}

	/**
	 * Sets a string configuration value.
	 *
	 * @param string key of the value to set, must not be empty
	 * @param string value to set, may be empty
	 *
	 * @deprecated 2009-06-12 Will be removed in oelib 0.8.0. Use setAsString() instead
	 */
	public function setConfigurationValueString($key, $value) {
		t3lib_div::logDeprecatedFunction();

		$this->setAsString($key, $value);
	}

	/**
	 * Returns a boolean configuration value.
	 *
	 * @param string key of the value to get, must not be empty
	 *
	 * @return boolean boolean configuration value
	 *
	 * @deprecated 2009-06-12 Will be removed in oelib 0.8.0. Use getAsBoolean() instead
	 */
	public function getConfigurationValueBoolean($key) {
		t3lib_div::logDeprecatedFunction();

		return $this->getAsBoolean($key);
	}

	/**
	 * Sets a boolean configuration value.
	 *
	 * @param string key of the value to set, must not be empty
	 * @param boolean value to set
	 *
	 * @deprecated 2009-06-12 Will be removed in oelib 0.8.0. Use setAsBoolean() instead
	 */
	public function setConfigurationValueBoolean($key, $value) {
		t3lib_div::logDeprecatedFunction();

		$this->setAsBoolean($key, $value);
	}

	/**
	 * Returns an integer configuration value.
	 *
	 * @param string key of the value to get, must not be empty
	 *
	 * @return integer configuration value
	 *
	 * @deprecated 2009-06-12 Will be removed in oelib 0.8.0. Use getAsInteger() instead
	 */
	public function getConfigurationValueInteger($key) {
		t3lib_div::logDeprecatedFunction();

		return $this->getAsInteger($key);
	}

	/**
	 * Sets an integer configuration value.
	 *
	 * @param string key of the value to set, must not be empty
	 * @param integer value to set
	 *
	 * @deprecated 2009-06-12 Will be removed in oelib 0.8.0. Use setAsInteger() instead
	 */
	public function setConfigurationValueInteger($key, $value) {
		t3lib_div::logDeprecatedFunction();

		$this->setAsInteger($key, $value);
	}

	/**
	 * Returns an extension's complete configuration.
	 *
	 * @return array an extension's configuration, empty if the
	 *               configuration was not retrieved before
	 */
	public function getCompleteConfiguration() {
		$this->loadConfigurationLazily();

		return $this->configuration;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_configurationProxy.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_configurationProxy.php']);
}
?>