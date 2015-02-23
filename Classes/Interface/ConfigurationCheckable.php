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
 * This interface represents an object that can have an automatic configuration check.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Oelib_Interface_ConfigurationCheckable {
	/**
	 * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
	 *
	 * @return string the namespace prefix, will end with a dot
	 */
	public function getTypoScriptNamespace();
}