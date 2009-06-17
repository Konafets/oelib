<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Saskia Metzler <saskia@merlin.owl.de>
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
 * Class 'tx_oelib_headerProxyFactory' for the 'oelib' extension.
 *
 * This class returns either an instance of the tx_oelib_realHeaderProxy which
 * adds HTTP headers or an instance of the tx_oelib_headerCollector. The
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
	private static $instance = null;

	/** whether the test mode is set */
	private $isTestMode = false;

	/** the header proxy object */
	private $headerProxy = null;

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
	 * @return tx_oelib_abstractHeaderProxy the singleton header proxy
	 */
	public function getHeaderProxy() {
		if ($this->isTestMode) {
			$className = 'tx_oelib_headerCollector';
		} else {
			$className = 'tx_oelib_realHeaderProxy';
		}

		if (!is_object($this->headerProxy)
			|| (get_class($this->headerProxy) != $className)
		) {
			$this->headerProxy = tx_oelib_ObjectFactory::make($className);
		}

		return $this->headerProxy;
	}

	/**
	 * Discards the current header proxy instance.
	 *
	 * @deprecated 2009-02-04 use purgeInstance instead
	 */
	public function discardInstance() {
		self::purgeInstance();
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
	 * Enables the test mode.
	 */
	public function enableTestMode() {
		$this->isTestMode = true;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_headerProxyFactory.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_headerProxyFactory.php']);
}
?>