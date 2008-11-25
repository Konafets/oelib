<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_template.php');

/**
 * Class 'tx_oelib_templateRegistry' for the 'oelib' extension.
 *
 * This class represents a registry for templates.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_templateRegistry {
	/**
	 * @var tx_oelib_templateRegistry the Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var array already created templates (by file name)
	 */
	private $templates = array();

	/**
	 * @var boolean whether this registry is in test mode
	 */
	private $testMode = false;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		$this->templates = array();
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return tx_oelib_templateRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_oelib_templateRegistry();
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
	 * Retrieves a template by template file name.
	 *
	 * @param string the file name of the template to retrieve, must not be empty
	 *               (except when in test mode)
	 *
	 * @return tx_oelib_template the template for the given template file name
	 *
	 * @see getByFile
	 */
	public static function get($templateFileName) {
		return self::getInstance()->getByFileName($templateFileName);
	}

	/**
	 * Retrieves a template by template file name.
	 *
	 * @param string the file name of the template to retrieve, must not be empty
	 *               (except when in test mode)
	 *
	 * @return tx_oelib_template the template for the given template file name
	 */
	public function getByFileName($templateFileName) {
		if ($templateFileName == '') {
			if ($this->testMode) {
				return $this->getTestTemplate();
			}

			throw new Exception(
				'The parameter $templateFileName must not be empty.'
			);
		}

		if (!isset($this->templates[$templateFileName])) {
			$template = t3lib_div::makeInstance('tx_oelib_template');
			$template->processTemplateFromFile($templateFileName);
			$this->templates[$templateFileName] = $template;
		}

		return $this->templates[$templateFileName];
	}

	/**
	 * Enables the test mode of this fixture.
	 */
	public function enableTestMode() {
		$this->testMode = true;
	}

	/**
	 * Retrieves a template for test purposes.
	 *
	 * @return tx_oelib_template the template for test purposes
	 */
	private function getTestTemplate() {
		if (!isset($this->templates['test'])) {
			$this->templates['test'] = t3lib_div::makeInstance('tx_oelib_template');
		}

		return $this->templates['test'];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_templateRegistry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_templateRegistry.php']);
}
?>