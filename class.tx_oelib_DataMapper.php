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
	 * @var array the (possible) relations of the created models in the format
	 *            DB column name => mapper name
	 */
	protected $relations = array();

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
		}

		return $model;
	}

	/**
	 * Retrieves a model based on the WHERE clause given in the parameter
	 * $whereClause.
	 *
	 * @param string the WHERE clause used to retrieve the model, must not be
	 *               empty, must be a valid WHERE clause
	 *
	 * @return tx_oelib_Model the model, null if there was no model found
	 */
	protected function findSingleByWhereClause($whereClause) {
		if ($whereClause == '') {
			throw new Exception('The parameter $whereClause must not be empty.');
		}

		try {
			$uid = tx_oelib_db::selectSingle(
				'uid', $this->tableName, $whereClause .
				tx_oelib_db::enableFields($this->tableName)
			);
			$model = $this->find($uid['uid']);
		} catch (tx_oelib_Exception_EmptyQueryResult $exception) {
			$model = null;
		}

		return $model;
	}

	/**
	 * Checks whether a model with a certain UID actually exists in the database
	 * and could be loaded.
	 *
	 * @param integer the UID of the record to retrieve, must be > 0
	 * @param boolean whether hidden records should be allowed to be retrieved
	 *
	 * @return boolean true if a model with the UID $uid exists in the database,
	 *                 false otherwise
	 */
	public function existsModel($uid, $allowHidden = false) {
		$model = $this->find($uid);

		if ($model->isGhost()) {
			$this->load($model, $allowHidden);
		}

		return $model->isLoaded() && (!$model->isHidden() || $allowHidden);
	}

	/**
	 * Loads a model's data from the database (retrieved by using the
	 * model's UID) and fills the model with it.
	 *
	 * If a model's data cannot be retrieved from the DB, the model will be set
	 * to the "dead" state.
	 *
	 * @param tx_oelib_Model the model to fill, must have a UID
	 */
	public function load(tx_oelib_Model $model) {
		if (!$model->hasUid()) {
			throw new Exception(
				'load must only be called with models that already have a UID.'
			);
		}

		try {
			$data = $this->retrieveRecord($model->getUid());
			$this->createRelations($data);
			$model->setData($data);
		} catch (tx_oelib_Exception_NotFound $exception) {
			$model->markAsDead();
		}
	}

	/**
	 * Processes a model's data and creates any relations that are hidden within
	 * it using foreign key mapping.
	 *
	 * @param array the model data to process, might be modified
	 */
	protected function createRelations(array &$data) {
		foreach (array_keys($this->relations) as $key) {
			$relationConfiguration
				= $this->getRelationConfigurationFromTca($key);
			$cardinality = (isset($relationConfiguration['maxitems']))
				? intval($relationConfiguration['maxitems']) : 1;

			if ($cardinality == 1) {
				$this->createManyToOneRelation($data, $key);
			} else {
				if (isset($relationConfiguration['MM'])) {
					$this->createMToNRelation($data, $key);
				} else {
					$this->createCommaSeparatedRelation($data, $key);
				}
			}
		}
	}

	/**
	 * Retrieves the configuration of a relation from the TCA.
	 *
	 * @param string the key of the relation to retrieve, must not be empty
	 *
	 * @return array configuration for that relation, will not be empty if the
	 *               TCA is valid
	 */
	private function getRelationConfigurationFromTca($key) {
		$tca = tx_oelib_db::getTcaForTable($this->tableName);

		if (!isset($tca['columns'][$key])) {
			throw new Exception(
				'In the table ' . $this->tableName . ', the column ' .
					$key . ' does not have a TCA entry.'
			);
		}

		return $tca['columns'][$key]['config'];
	}

	/**
	 * Creates an n:1 relation using foreign key mapping.
	 *
	 * @param array the model data to process, will be modified
	 * @param string the key of the data item for which the relation should
	 *               be created, must not be empty
	 */
	private function createManyToOneRelation(array &$data, $key) {
		$uid = isset($data[$key]) ? intval($data[$key]) : 0;

		$data[$key] = ($uid > 0)
			? tx_oelib_MapperRegistry::get($this->relations[$key])->find($uid)
			: null;
	}

	/**
	 * Creates an n:1 relation using a comma-separated list of UIDs.
	 *
	 * @param array the model data to process, will be modified
	 * @param string the key of the data item for which the relation should
	 *               be created, must not be empty
	 */
	private function createCommaSeparatedRelation(array &$data, $key) {
		$list = t3lib_div::makeInstance('tx_oelib_List');

		$uidList = isset($data[$key]) ? trim($data[$key]) : '';
		if ($uidList != '') {
			$mapper = tx_oelib_MapperRegistry::get($this->relations[$key]);
			$uids = t3lib_div::intExplode(',', $uidList);

			foreach ($uids as $uid) {
				$list->add(
					$mapper->find($uid)
				);
			}
		}

		$data[$key] = $list;
	}

	/**
	 * Creates an m:n relation using an m:n table.
	 *
	 * Note: This doesn't work for the reverse direction of bidirectional
	 * relations yet.
	 *
	 * @param array the model data to process, will be modified
	 * @param string the key of the data item for which the relation should
	 *               be created, must not be empty
	 */
	private function createMToNRelation(array &$data, $key) {
		$list = t3lib_div::makeInstance('tx_oelib_List');

		if ($data[$key] > 0) {
			$mapper = tx_oelib_MapperRegistry::get($this->relations[$key]);
			$relationConfiguration
				= $this->getRelationConfigurationFromTca($key);
			$mnTable = $relationConfiguration['MM'];
			$relationUids = tx_oelib_db::selectMultiple(
				'uid_foreign', $mnTable, 'uid_local = ' . $data['uid'], '',
				'sorting'
			);

			foreach ($relationUids as $relationUid) {
				$list->add($mapper->find($relationUid['uid_foreign']));
			}
		}

		$data[$key] = $list;
	}

	/**
	 * Reads a record from the database by UID (from this mapper's table). Also
	 * hidden records will be retrieved.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record in the DB
	 *                                     with the UID $uid
	 *
	 * @param integer the UID of the record to retrieve, must be > 0
	 *
	 * @return array the record from the database, will not be empty
	 */
	protected function retrieveRecord($uid) {
		try {
			$data = tx_oelib_db::selectSingle(
				$this->columns,
				$this->tableName,
				'uid = ' . $uid . tx_oelib_db::enableFields($this->tableName, 1)
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
	 * Creates a new ghost model with the UID $uid and registers it.
	 *
	 * @return tx_oelib_Model a ghost model with the UID $uid
	 */
	protected function createGhost($uid) {
		$model = t3lib_div::makeInstance($this->modelClassName);
		$model->setUid($uid);
		$model->setLoadCallback(array($this, 'load'));
		$this->map->add($model);

		return $model;
	}

	/**
	 * Creates a new registered ghost with a UID that has not been used in this
	 * data mapper yet.
	 *
	 * Note: The UID is not guaranteed to be unused in the database.
	 *
	 * @return tx_oelib_Model a new ghost
	 */
	public function getNewGhost() {
		return $this->createGhost($this->map->getNewUid());
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_DataMapper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_DataMapper.php']);
}
?>