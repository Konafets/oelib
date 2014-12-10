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
 * This class represents a front-end user group.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Oelib_Model_FrontEndUserGroup extends Tx_Oelib_Model {
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