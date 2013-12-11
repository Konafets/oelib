<?php
/***************************************************************
* Copyright notice
*
* (c) 2012-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Base class for repositories that do not allow any modification of the models, i.e., for static data.
 *
 * @deprecated Do not use this class. It will be copied to the static_info_tables extension and then removed.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Oelib_Domain_Repository_AbstractReadOnlyRepository extends Tx_Extbase_Persistence_Repository {
	/**
	 * Adds an object to this repository.
	 *
	 * @param object $object The object to add
	 *
	 * @return void
	 */
	public function add($object) {
		throw new BadMethodCallException(
			'This is a read-only repository in which the add method must not be called.',
			1325613912
		);
	}

	/**
	 * Removes an object from this repository.
	 *
	 * @param object $object The object to remove
	 *
	 * @return void
	 */
	public function remove($object) {
		throw new BadMethodCallException(
			'This is a read-only repository in which the remove method must not be called.',
			1325613913
		);
	}

	/**
	 * Replaces an object by another.
	 *
	 * @param object $existingObject The existing object
	 * @param object $newObject The new object
	 *
	 * @return void
	 */
	public function replace($existingObject, $newObject) {
		throw new BadMethodCallException(
			'This is a read-only repository in which the replace method must not be called.',
			1325613914
		);
	}

	/**
	 * Replaces an existing object with the same identifier by the given object.
	 *
	 * @param object $modifiedObject The modified object
	 *
	 * @return void
	 */
	public function update($modifiedObject) {
		throw new BadMethodCallException(
			'This is a read-only repository in which the update method must not be called.',
			1325613915
		);
	}

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 */
	public function removeAll() {
		throw new BadMethodCallException(
			'This is a read-only repository in which the removeAll method must not be called.',
			1325613916
		);
	}
}