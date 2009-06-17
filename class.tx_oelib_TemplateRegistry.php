<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_oelib_TemplateRegistry' for the 'oelib' extension.
 *
 * This class represents a registry for templates.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_TemplateRegistry {
	/**
	 * @var tx_oelib_TemplateRegistry the Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var array already created templates (by file name)
	 */
	private $templates = array();

	/**
	 * The constructor. Use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		$this->templates = array();
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return tx_oelib_TemplateRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_oelib_TemplateRegistry();
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
	 * Creates a new template for a provided template file name with an already
	 * parsed the template file.
	 *
	 * If the template file name is empty, no template file will be used for
	 * that template.
	 *
	 * @param string the file name of the template to retrieve, may not be empty
	 *               to get a template that is not related to a template file.
	 *
	 * @return tx_oelib_Template the template for the given template file name
	 *
	 * @see getByFileName
	 */
	public static function get($templateFileName) {
		return self::getInstance()->getByFileName($templateFileName);
	}

	/**
	 * Creates a new template for a provided template file name with an already
	 * parsed the template file.
	 *
	 * If the template file name is empty, no template file will be used for
	 * that template.
	 *
	 * @param string the file name of the template to retrieve, may not be empty
	 *               to get a template that is not related to a template file.
	 *
	 * @return tx_oelib_Template the template for the given template file name
	 */
	public function getByFileName($fileName) {
		if (!isset($this->templates[$fileName])) {
			$template = tx_oelib_ObjectFactory::make('tx_oelib_Template');

			if ($fileName != '') {
				$template->processTemplateFromFile($fileName);
			}
			$this->templates[$fileName] = $template;
		}

		return clone $this->templates[$fileName];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_TemplateRegistry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_TemplateRegistry.php']);
}
?>