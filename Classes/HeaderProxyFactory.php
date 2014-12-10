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
 * This class returns either an instance of the Tx_Oelib_RealHeaderProxy which
 * adds HTTP headers or an instance of the Tx_Oelib_HeaderCollector. The
 * collector stores the headers that were added and does not send them. This
 * mode is for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_oelib_headerProxyFactory {
	/** the singleton factory */
	private static $instance = NULL;

	/** whether the test mode is set */
	private $isTestMode = FALSE;

	/** the header proxy object */
	private $headerProxy = NULL;

	/**
	 * Don't call this constructor; use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->headerProxy);
	}

	/**
	 * Retrieves the singleton instance of the factory.
	 *
	 * @return tx_oelib_headerProxyFactory the singleton factory
	 */
	public static function getInstance() {
		if (!is_object(self::$instance)) {
			self::$instance = new tx_oelib_headerProxyFactory();
		}

		return self::$instance;
	}

	/**
	 * Retrieves the singleton header proxy instance. Depending on the mode,
	 * this instance is either a header collector or a real header proxy.
	 *
	 * @return Tx_Oelib_AbstractHeaderProxy|Tx_Oelib_HeaderCollector|Tx_Oelib_RealHeaderProxy the singleton header proxy
	 */
	public function getHeaderProxy() {
		if ($this->isTestMode) {
			$className = 'Tx_Oelib_HeaderCollector';
		} else {
			$className = 'Tx_Oelib_RealHeaderProxy';
		}

		if (!is_object($this->headerProxy)
			|| (get_class($this->headerProxy) !== $className)
		) {
			$this->headerProxy = t3lib_div::makeInstance($className);
		}

		return $this->headerProxy;
	}

	/**
	 * Purges the current instance so that getInstance will create a new instance.
	 *
	 * @return void
	 */
	public static function purgeInstance() {
		self::$instance = NULL;
	}

	/**
	 * Enables the test mode.
	 *
	 * @return void
	 */
	public function enableTestMode() {
		$this->isTestMode = TRUE;
	}
}