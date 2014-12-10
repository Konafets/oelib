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
 * This view helper returns the contents of the src attribute of the first image
 * tag within it.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ViewHelpers_ImageSourceViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
	/**
	 * Returns the src from the first image tag.
	 *
	 * @return string the src contents, will be empty if there is no image tag inside
	 */
	public function render() {
		$matches = array();
		preg_match('/<img[^>]*src *= *("|\')([^"\']*)("|\')/', $this->renderChildren(), $matches);

		$imageSource = isset($matches[2]) ? $matches[2] : '';

		return $imageSource;
	}
}