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
 * This class represents an object that can be sorted.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface tx_oelib_Interface_Sortable {
	/**
	 * Returns the sorting value for this object.
	 *
	 * This is the sorting as used in the back end.
	 *
	 * @return integer the sorting value of this object, will be >= 0
	 */
	public function getSorting();

	/**
	 * Sets the sorting value for this object.
	 *
	 * This is the sorting as used in the back end.
	 *
	 * @param integer $sorting the sorting value of this object, must be >= 0
	 *
	 * @return void
	 */
	public function setSorting($sorting);
}