<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_List' for the 'oelib' extension.
 *
 * This class represents a list of models.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_List extends SplObjectStorage {
	/**
	 * @var the UIDs in the list using the UIDs as both the keys and values
	 */
	private $uids = array();

	/**
	 * The model this List belongs to.
	 *
	 * This is used for modeling relations and will remain null in any other
	 * context.
	 *
	 * @var tx_oelib_Model
	 */
	private $parentModel = null;

	/**
	 * whether there is at least one item without a UID
	 *
	 * @var boolean
	 */
	private $hasItemWithoutUid = FALSE;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 *
	 * Note: The models in this list are not destructed by this function (this
	 * should happen in the data mappers).
	 */
	public function __destruct() {
		$this->rewind();

		foreach ($this as $model) {
			// Models without UIDs are not registered at a mapper and thus will
			// not be destructed by the mapper.
			if (!$model->hasUid()) {
				$model->__destruct();
			}
			$this->detach($model);
		}

		$this->uids = array();
		unset($this->parentModel);
	}

	/**
	 * Adds a model to this list (as last element) if it is not already in the
	 * list.
	 *
	 * The model to add need not necessarily have a UID.
	 *
	 * @param tx_oelib_Model the model to add, need not have a UID
	 */
	public function add(tx_oelib_Model $model) {
		$this->attach($model);

		if ($model->hasUid()) {
			$uid = $model->getUid();
			$this->uids[$uid] = $uid;
		} else {
			$this->hasItemWithoutUid = TRUE;
		}

		// Initializes the Iterator.
		if ($this->count() == 1) {
			$this->rewind();
		}

		$this->markAsDirty();
	}

	/**
	 * Checks whether this list is empty.
	 *
	 * @return boolean TRUE if this list is empty, FALSE otherwise
	 */
	public function isEmpty() {
		return ($this->count() == 0);
	}

	/**
	 * Returns the first item.
	 *
	 * Note: This method rewinds the iterator.
	 *
	 * @return tx_oelib_Model the first item, will be null if this list is
	 *                        empty
	 */
	public function first() {
		$this->rewind();

		return $this->current();
	}

	/**
	 * Returns a comma-separted list of unique UIDs of the current items,
	 * ordered by first insertion.
	 *
	 * @return string comma-separated list of UIDs, will be empty if the list is
	 *                empty or no item has a UID
	 */
	public function getUids() {
		$this->checkUidCache();
		return implode(',', $this->uids);
	}

	/**
	 * Checks whether a model with a certain UID exists in this list
	 *
	 * @param integer UID to test, must be > 0
	 *
	 * @return TRUE if a model with the UID $uid exists in this list, FALSE
	 *              otherwise
	 */
	public function hasUid($uid) {
		$this->checkUidCache();
		return isset($this->uids[$uid]);
	}

	/**
	 * Checks whether the UID list cache needs to be rebuild and does so if
	 * necessary.
	 */
	private function checkUidCache() {
		if ($this->hasItemWithoutUid) {
			$this->rebuildUidCache();
		}
	}

	/**
	 * Rebuilds the UID cache.
	 */
	private function rebuildUidCache() {
		$this->hasItemWithoutUid = FALSE;

		foreach ($this as $item) {
			if ($item->hasUid()) {
				$uid = $item->getUid();
				$this->uids[$uid] = $uid;
			} else {
				$this->hasItemWithoutUid = TRUE;
			}
		}
	}

	/**
	 * Sorts this list by using the given callback function.
	 *
	 * The callback function, must take 2 parameters and return -1, 0 or 1.
	 * The return value -1 means that the first parameter is sorted before the
	 * second one, 1 means that the second parameter is sorted before the first
	 * one and 0 means the parameters stay in order.
	 *
	 * @param function a callback function to use with the models stored
	 *                 in the list, not empty
	 */
	public function sort($callbackFunction) {
		$items = iterator_to_array($this, false);
		usort($items, $callbackFunction);

		foreach ($items as $item) {
			$this->detach($item);
			$this->attach($item);
		}
	}

	/**
	 * Appends the contents of $list to this list.
	 *
	 * Note: Since tx_oelib_List extends SplObjectStorage this method is in most
	 * cases an synonym to appendUnique() as SplObjectStorage makes sure that
	 * no object is added more than once to it.
	 *
	 * @param tx_oelib_List list to append, may be empty
	 */
	public function append(tx_oelib_List $list) {
		foreach ($list as $item) {
			$this->add($item);
		}
	}

	/**
	 * Appends the contents of $list to this list. If an item with specific UID
	 * already exists in the list, the new item to append will be igored.
	 *
	 * @param tx_oelib_List list to append, may be empty
	 *
	 * @deprecated 2010-05-27 use append() instead
	 */
	public function appendUnique(tx_oelib_List $list) {
		foreach ($list as $item) {
			if (!$this->hasUid($item->getUid())) {
				$this->add($item);
			}
		}
	}

	/**
	 * Drops the current element from the list and sets the pointer to the
	 * next element.
	 *
	 * If the pointer does not point to a valid element, this function is a
	 * no-op.
	 */
	public function purgeCurrent() {
		if (!$this->valid()) {
			return;
		}

		if ($this->current()->hasUid()) {
			$uid = $this->current()->getUid();
			if (isset($this->uids[$uid])) {
				unset($this->uids[$uid]);
			}
		}

		$this->detach($this->current());

		$this->markAsDirty();
	}

	/**
	 * Sets the model this list belongs to.
	 *
	 * @param tx_oelib_Model $model the model this list belongs to
	 */
	public function setParentModel(tx_oelib_Model $model) {
		$this->parentModel = $model;
	}

	/**
	 * Marks the parent model as dirty.
	 */
	private function markAsDirty() {
		if ($this->parentModel instanceof tx_oelib_Model) {
			$this->parentModel->markAsDirty();
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_List.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_List.php']);
}
?>