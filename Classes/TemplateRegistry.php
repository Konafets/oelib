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
 * This class represents a registry for templates.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_TemplateRegistry {
	/**
	 * @var Tx_Oelib_TemplateRegistry the Singleton instance
	 */
	private static $instance = NULL;

	/**
	 * @var Tx_Oelib_Template[] already created templates (by file name)
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
	 * @return Tx_Oelib_TemplateRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new Tx_Oelib_TemplateRegistry();
		}

		return self::$instance;
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
	 * Creates a new template for a provided template file name with an already
	 * parsed the template file.
	 *
	 * If the template file name is empty, no template file will be used for
	 * that template.
	 *
	 * @param string $templateFileName
	 *        the file name of the template to retrieve, may not be empty to get a template that is not related to a template file.
	 *
	 * @return Tx_Oelib_Template the template for the given template file name
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
	 * @param string $fileName
	 *        the file name of the template to retrieve, may not be empty to get a template that is not related to a template file
	 *
	 * @return Tx_Oelib_Template the template for the given template file name
	 */
	public function getByFileName($fileName) {
		if (!isset($this->templates[$fileName])) {
			$template = t3lib_div::makeInstance('Tx_Oelib_Template');

			if ($fileName !== '') {
				$template->processTemplateFromFile($fileName);
			}
			$this->templates[$fileName] = $template;
		}

		return clone $this->templates[$fileName];
	}
}