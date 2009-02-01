<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_DataMapper' for the 'oelib' extension.
 *
 * This class represents a mapper that maps database record to model instances.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_oelib_DataMapper {
	/**
	 * @var string the name of the database table for this mapper,
	 *             must not be empty in subclasses
	 */
	protected $tableName = '';

	/**
	 * @var string a comma-separated list of DB column names to retrieve
	 *             or "*" for all columns, must not be empty
	 */
	protected $columns = '*';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = '';

	/**
	 * @var tx_oelib_IdentityMap a map that holds the models that already
	 *                           have been retrieved
	 */
	protected $map = null;

	/**
	 * The constructor.
	 */
	public function __construct() {
		if ($this->tableName == '') {
			throw new Exception(
				get_class($this) . '::tableName must not be empty.'
			);
		}
		if ($this->columns == '') {
			throw new Exception(
				get_class($this) . '::columns must not be empty.'
			);
		}
		if ($this->modelClassName == '') {
			throw new Exception(
				get_class($this) . '::modelClassName must not be empty.'
			);
		}

		$this->map = t3lib_div::makeInstance('tx_oelib_IdentityMap');
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->map) {
			$this->map->__destruct();
			unset($this->map);
		}
	}

	/**
	 * Retrieves a model for the record with the UID $uid. If that particular
	 * model already is cached in memory, the cached instance is returned.
	 *
	 * The model may still be a ghost which will get fully initialized once its
	 * data is accessed.
	 *
	 * Note: This function does not check that a record with the UID $uid
	 * actually exists in the database.
	 *
	 * @param integer the UID of the record to retrieve, must be > 0
	 *
	 * @return tx_oelib_Model the model with the UID $uid
	 */
	public function find($uid) {
		try {
			$model = $this->map->get($uid);
		} catch (tx_oelib_Exception_NotFound $exception) {
			$model = $this->createGhost($uid);
			$this->map->add($model);
		}

		return $model;
	}

	/**
	 * Checks whether a model with a certain UID actually exists in the database
	 * and could be loaded.
	 *
	 * @param integer the UID of the record to retrieve, must be > 0
	 *
	 * @return boolean true if a model with the UID $uid exists in the database,
	 *                 false otherwise
	 */
	public function existsModel($uid) {
		$result = true;

		try {
			if (!$this->find($uid)->isLoaded()) {
				$this->load($this->find($uid));
			}
		} catch (tx_oelib_Exception_NotFound $exception) {
			$result = false;
		}

		return $result;
	}

	/**
	 * Loads a model's data from the database (retrieved by using the
	 * model's UID) and fills the model with it.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record in the DB
	 *                                     with the model's UID
	 *
	 * @param tx_oelib_Model the model to fill, must have a UID
	 */
	public function load(tx_oelib_Model $model) {
		if (!$model->hasUid()) {
			throw new Exception(
				'load must only be called with models that already have a UID.'
			);
		}

		$model->setData($this->retrieveRecord($model->getUid()));
	}

	/**
	 * Reads a record from the database by UID (from this mapper's table).
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record in the DB
	 *                                     with the UID $uid
	 *
	 * @param integer the UID of the record to retrieve, must be > 0
	 *
	 * @return array the record from the database, will not be empty
	 */
	private function retrieveRecord($uid) {
		try {
			$data = tx_oelib_db::selectSingle(
				$this->columns,
				$this->tableName,
				'uid = ' . $uid . tx_oelib_db::enableFields($this->tableName)
			);
		} catch (tx_oelib_Exception_EmptyQueryResult $exception) {
			throw new tx_oelib_Exception_NotFound(
				'The record with the UID ' . $uid . ' could not be retrieved ' .
					'from the table ' . $this->tableName . '.'
			);
		}

		return $data;
	}

	/**
	 * Creates a new ghost model with the UID $uid.
	 *
	 * @return tx_oelib_Model a ghost model with the UID $uid
	 */
	protected function createGhost($uid) {
		$model = t3lib_div::makeInstance($this->modelClassName);
		$model->setUid($uid);
		$model->setLoadCallback(array($this, 'load'));

		return $model;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_DataMapper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_DataMapper.php']);
}
?>