<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2011 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Interface tx_oelib_Interface_Sortable for the "oelib" extension.
 *
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
	 */
	public function setSorting($sorting);
}
?>