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
 * This view helper converts strings to uppercase.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ViewHelpers_UppercaseViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
	/**
	 * Converts the rendered children to uppercase.
	 *
	 * @return string the uppercased rendered children, might be empty
	 */
	public function render() {
		$renderedChildren = $this->renderChildren();
		$encoding = mb_detect_encoding($renderedChildren);

		return mb_strtoupper($renderedChildren, $encoding);
	}
}