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
 * This class represents a registration that allows the storage and retrieval
 * of configuration objects.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ConfigurationRegistry {
	/**
	 * @var Tx_Oelib_ConfigurationRegistry the Singleton instance
	 */
	private static $instance = NULL;

	/**
	 * @var Tx_Oelib_ConfigurationRegistry[] already created configurations (by namespace)
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
	 * @param string $namespace
	 *       the namespace of the configuration to drop, must not be empty, must
	 *       have been set in this registry
	 *
	 * @return void
	 */
	private function dropConfiguration($namespace) {
		unset($this->configurations[$namespace]);
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return Tx_Oelib_ConfigurationRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new Tx_Oelib_ConfigurationRegistry();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 *
	 * @return void
	 */
	public static function purgeInstance() {
		self::$instance = NULL;
	}

	/**
	 * Retrieves a Configuration by namespace.
	 *
	 * @param string $namespace
	 *        the name of a configuration namespace, e.g., "plugin.tx_oelib",
	 *        must not be empty
	 *
	 * @return Tx_Oelib_Configuration the configuration for the given namespace
	 *
	 * @see getByNamespace
	 */
	public static function get($namespace) {
		return self::getInstance()->getByNamespace($namespace);
	}

	/**
	 * Retrieves a Configuration by namespace.
	 *
	 * @param string $namespace
	 *        the name of a configuration namespace, e.g., "plugin.tx_oelib",
	 *        must not be empty
	 *
	 * @return Tx_Oelib_Configuration the configuration for the given namespace
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
	 * @param string $namespace
	 *        the namespace of the configuration to set, must not be empty
	 * @param Tx_Oelib_Configuration $configuration
	 *        the configuration to set
	 *
	 * @return void
	 */
	public function set($namespace, Tx_Oelib_Configuration $configuration) {
		$this->checkForNonEmptyNamespace($namespace);

		if (isset($this->configurations[$namespace])) {
			$this->dropConfiguration($namespace);
		}

		$this->configurations[$namespace] = $configuration;
	}

	/**
	 * Checks that $namespace is non-empty.
	 *
	 * @throws InvalidArgumentException if $namespace is empty
	 *
	 * @param string $namespace
	 *        namespace name to check
	 *
	 * @return void
	 */
	private function checkForNonEmptyNamespace($namespace) {
		if ($namespace === '') {
			throw new InvalidArgumentException('$namespace must not be empty.', 1331318549);
		}
	}

	/**
	 * Retrieves the configuration from TS Setup of the current page for a given
	 * namespace.
	 *
	 * @param string $namespace
	 *        the namespace of the configuration to retrieve, must not be empty
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

			$data = $data[$namespacePart . '.'];
		}

		$configuration = t3lib_div::makeInstance('Tx_Oelib_Configuration');
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
	private function getCompleteTypoScriptSetup() {
		$pageUid = Tx_Oelib_PageFinder::getInstance()->getPageUid();
		if ($pageUid === 0) {
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
	 * Note: This function can return TRUE even in the BE if there is a front
	 * end.
	 *
	 * @return boolean TRUE if there is an initialized front end, FALSE
	 *                 otherwise
	 */
	private function existsFrontEnd() {
		return isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])
			&& is_object($GLOBALS['TSFE']->tmpl)
			&& $GLOBALS['TSFE']->tmpl->loaded;
	}
}