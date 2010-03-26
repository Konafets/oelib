<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon <mail@niels-pardon.de>
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
 * Class 'tx_oelib_tests_fixtures_TestingChildModel' for the 'oelib' extension.
 *
 * This class represents a domain model for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_tests_fixtures_TestingChildModel extends tx_oelib_Model {
	/**
	 * Sets the "title" data item for this model.
	 *
	 * @param string the value to set, may be empty
	 */
	public function setTitle($value) {
		$this->setAsString('title', $value);
	}

	/**
	 * Gets the "title" data item.
	 *
	 * @return string the value of the "title" data item, may be empty
	 */
	public function getTitle() {
		return $this->getAsString('title');
	}

	/**
	 * Gets the "parent" data item.
	 *
	 * @return tx_oelib_tests_fixtures_TestingModel the "friend" data item,
	 *                                              will be null if this model
	 *                                              has no parent
	 */
	public function getParent() {
		return $this->getAsModel('parent');
	}

	/**
	 * Sets the "friend" data item.
	 *
	 * @param tx_oelib_tests_fixtures_TestingModel $friend
	 *        the "parent" data item to set
	 */
	public function setParent(tx_oelib_tests_fixtures_TestingModel $parent) {
		$this->set('parent', $parent);
	}

	/**
	 * Sets the dummy column to TRUE.
	 */
	public function markAsDummyModel() {
		$this->set('is_dummy_record', TRUE);
	}
}
?>