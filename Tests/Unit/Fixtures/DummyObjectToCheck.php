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
 * This is mere a class to test the configuration check class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
final class Tx_Oelib_DummyObjectToCheck extends Tx_Oelib_TemplateHelper implements Tx_Oelib_Interface_ConfigurationCheckable {
	/**
	 * The constructor.
	 *
	 * @param array $configuration
	 *        configuration for the dummy object, may be empty
	 */
	public function __construct(array $configuration) {
		$this->init($configuration);
	}

	/**
	 * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
	 *
	 * @return string the namespace prefix, will end with a dot
	 */
	public function getTypoScriptNamespace() {
		return 'plugin.tx_oelib_test.';
	}
}