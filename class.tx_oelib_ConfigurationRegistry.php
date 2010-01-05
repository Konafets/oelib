<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(PATH_t3lib . 'class.t3lib_page.php');
require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');

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
				= $this->retrieveConfigurationFromTypoScriptSetup($namespace);
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

	/**
	 * Retrieves the configuration from TS Setup of the current page for a given
	 * namespace.
	 *
	 * @param string the namespace of the configuration to retrieve, must
	 *               not be empty
	 *
	 * @return array the TypoScript configuration for that namespace, might be
	 *               empty
	 */
	private function retrieveConfigurationFromTypoScriptSetup($namespace) {
		$data = $this->getCompleteTypoScriptSetup();

		$namespaceParts = explode('.', $namespace);
		foreach ($namespaceParts as $namespacePart) {
			if (!array_key_exists($namespacePart . '.', $data)) {
				$data = array();
				break;
			}

			$data =& $data[$namespacePart . '.'];
		}

		$configuration = tx_oelib_ObjectFactory::make('tx_oelib_Configuration');
		$configuration->setData($data);
		return $configuration;
	}

	/**
	 * Retrieves the complete TypoScript setup for the current page as a nested
	 * array.
	 *
	 * @return array the TypoScriptSetup for the current page, will be empty if
	 *               no page is selected or if the TS setup of the page is empty
	 */
	private function &getCompleteTypoScriptSetup() {
		$pageUid = tx_oelib_PageFinder::getInstance()->getPageUid();
		if ($pageUid == 0) {
			return array();
		}

		if ($this->existsFrontEnd()) {
			return $GLOBALS['TSFE']->tmpl->setup;
		}

		$template = t3lib_div::makeInstance('t3lib_TStemplate');
		$template->tt_track = 0;
		$template->init();

		$page = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootline = $page->getRootLine($pageUid);
		$template->runThroughTemplates($rootline, 0);
		$template->generateConfig();

		return $template->setup;
	}

	/**
	 * Checks whether there is an initialized front end with a loaded TS template.
	 *
	 * Note: This function can return true even in the BE if there is a front
	 * end.
	 *
	 * @return boolean true if there is an initialized front end, false
	 *                 otherwise
	 */
	private function existsFrontEnd() {
		return isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])
			&& is_object($GLOBALS['TSFE']->tmpl)
			&& $GLOBALS['TSFE']->tmpl->loaded;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_ConfigurationRegistry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_ConfigurationRegistry.php']);
}
?>