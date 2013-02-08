<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Bernd Schönbach <bernd@oliverklee.de>
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
 * This class represents a front-end user group.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_oelib_Model_FrontEndUserGroup extends tx_oelib_Model {
	/**
	 * Gets this group's title.
	 *
	 * @return string the title of this group, will be empty if the group has
	 *                none
	 */
	public function getTitle() {
		return $this->getAsString('title');
	}

	/**
	 * Gets this group's description.
	 *
	 * @return string the description of this group, will be empty if the group
	 *                has none
	 */
	public function getDescription() {
		return $this->getAsString('description');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_FrontEndUserGroup.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Model/class.tx_oelib_Model_FrontEndUserGroup.php']);
}
?>