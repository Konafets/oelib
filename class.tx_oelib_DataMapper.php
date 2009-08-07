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
	 * @var array UIDs of models that are memory-only models that must not be
	 *            saved, using the UIDs as keys and true as value
	 */
	protected $uidsOfMemoryOnlyDummyModels = array();

	/**
	 * @var array the (possible) relations of the created models in the format
	 *            DB column name => mapper name
	 */
	protected $relations = array();

	/**
	 * @var boolean whether database access is denied for this mapper
	 */
	 private $denyDatabaseAccess = false;

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

		$this->map = tx_oelib_ObjectFactory::make('tx_oelib_IdentityMap');
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
	 * Returns a model for the provided array. If the UID provided with the
	 * array is already mapped, this yet existing model will be returned
	 * irrespective of the other provided data, otherwise the model will be
	 * loaded with the provided data.
	 *
	 * @param array data for the model to return, must at least contain an
	 *              element with the key "uid"
	 *
	 * @return tx_oelib_Model model for the provided UID, filled with the data
	 *                        provided in case it did not have any data in
	 *                        memory before
	 */
	public function getModel(array $data) {
		if (!isset($data['uid'])) {
			throw new Exception('$data must contain an element "uid".');
		}

		$model = $this->find($data['uid']);

		if ($model->isGhost()) {
			$this->fillModel($model, $data);
		}

		return $model;
	}

	/**
	 * Returns a list of models for the provided two-dimensional array with
	 * model data.
	 *
	 * @param array two-dimensional array, each inner array must at least
	 *              contain the element "uid", may be empty
	 *
	 * @return tx_oelib_List A list of models with the UIDs provided. The models
	 *                       will be filled with the data provided in case they
	 *                       did not have any data before, otherwise the already
	 *                       loaded data will be used. If $dataOfModels was
	 *                       empty, an empty list will be returned.
	 *
	 * @see getModel()
	 */
	public function getListOfModels(array $dataOfModels) {
		$list = tx_oelib_ObjectFactory::make('tx_oelib_List');

		foreach ($dataOfModels as $modelRecord) {
			$list->add($this->getModel($modelRecord));
		}

		return $list;
	}

	/**
	 * Retrieves a model based on the WHERE clause given in the parameter
	 * $whereClauseParts. Hidden records will be retrieved as well.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record in the DB
	 *                                     which matches the WHERE clause
	 *
	 * @param array WHERE clause parts for the record to retrieve, each element
	 *              must consist of a column name as key and a value to search
	 *              for as value (will automatically get quoted), must not be
	 *              empty
	 *
	 * @return tx_oelib_Model the model
	 */
	protected function findSingleByWhereClause(array $whereClauseParts) {
		if (empty($whereClauseParts)) {
			throw new Exception(
				'The parameter $whereClauseParts must not be empty.'
			);
		}

		return $this->getModel($this->retrieveRecord($whereClauseParts));
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
			$this->load($model);
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
			$this->fillModel(
				$model, $this->retrieveRecordByUid($model->getUid())
			);
		} catch (tx_oelib_Exception_NotFound $exception) {
			$model->markAsDead();
		}
	}

	/**
	 * Fills a model with data, including the relations.
	 *
	 * @param tx_oelib_Model the model to fill, needs to have a UID
	 * @param array the model data to process as it comes from the DB, will be
	 *              modified
	 */
	private function fillModel(tx_oelib_Model $model, array &$data) {
		$this->createRelations($data);
		$model->setData($data);
	}

	/**
	 * Processes a model's data and creates any relations that are hidden within
	 * it using foreign key mapping.
	 *
	 * @param array the model data to process, might be modified
	 */
	protected function createRelations(array &$data) {
		foreach (array_keys($this->relations) as $key) {
			if ($this->isOneToManyRelationConfigured($key)) {
				$this->createOneToManyRelation($data, $key);
			} elseif ($this->isManyToOneRelationConfigured($key)) {
				$this->createManyToOneRelation($data, $key);
			} else {
				if ($this->isManyToManyRelationConfigured($key)) {
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
	 * Checks whether the relation is configured in the TCA to be an 1:n
	 * relation.
	 *
	 * @param string key of the relation, must not be empty
	 *
	 * @return boolean true if the relation is an 1:n relation, false
	 *                 otherwise
	 */
	private function isOneToManyRelationConfigured($key) {
		$relationConfiguration = $this->getRelationConfigurationFromTca($key);

		return isset($relationConfiguration['foreign_field'])
			&& isset($relationConfiguration['foreign_table']);
	}

	/**
	 * Checks whether the relation is configured in the TCA to be an n:1
	 * relation.
	 *
	 * @param string key of the relation, must not be empty
	 *
	 * @return boolean true if the relation is an n:1 relation, false
	 *                 otherwise
	 */
	private function isManyToOneRelationConfigured($key) {
		$relationConfiguration = $this->getRelationConfigurationFromTca($key);
		$cardinality = (isset($relationConfiguration['maxitems']))
			? intval($relationConfiguration['maxitems']) : 1;

		return ($cardinality == 1);
	}

	/**
	 * Checks whether there is a table for an m:n relation configured in the
	 * TCA.
	 *
	 * @param string key of the relation, must not be empty
	 *
	 * @return boolean true if the relation's configuration provides an m:n
	 *                 table, false otherwise
	 */
	private function isManyToManyRelationConfigured($key) {
		$relationConfiguration = $this->getRelationConfigurationFromTca($key);

		return isset($relationConfiguration['MM']);
	}

	/**
	 * Creates an 1:n relation using foreign field mapping.
	 *
	 * @param array the model data to process, will be modified
	 * @param string the key of the data item for which the relation should
	 *               be created, must not be empty
	 */
	private function createOneToManyRelation(array &$data, $key) {
		$relationUids = array();

		if ($data[$key] > 0) {
			$relationConfiguration = $this->getRelationConfigurationFromTca($key);
			$foreignTable = $relationConfiguration['foreign_table'];
			$foreignField = $relationConfiguration['foreign_field'];
			$foreignSortBy = $relationConfiguration['foreign_sortby'];
			$relationUids = tx_oelib_db::selectMultiple(
				'uid', $foreignTable, $foreignField . ' = ' . $data['uid'], '',
				$foreignSortBy
			);
		}

		$data[$key] = tx_oelib_MapperRegistry::get($this->relations[$key])
			->getListOfModels($relationUids);
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

			if (!isset($relationConfiguration['MM_opposite_field'])) {
				$relationUids = tx_oelib_db::selectMultiple(
					'uid_foreign AS uid', $mnTable, 'uid_local = ' . $data['uid'],
					'', 'sorting'
				);
			} else {
				$relationUids = tx_oelib_db::selectMultiple(
					'uid_local AS uid', $mnTable, 'uid_foreign = ' . $data['uid'],
					'', 'uid_local'
				);
			}

			foreach ($relationUids as $relationUid) {
				$list->add($mapper->find($relationUid['uid']));
			}
		}

		$data[$key] = $list;
	}

	/**
	 * Reads a record from the database (from this mapper's table) by the
	 * WHERE clause provided. Hidden records will be retrieved as well.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record in the DB
	 *                                     which matches the WHERE clause
	 * @throws tx_oelib_Exception_NotFound if database access is disabled
	 *
	 * @param array WHERE clause parts for the record to retrieve, each element
	 *              must consist of a column name as key and a value to search
	 *              for as value (will automatically get quoted), must not be
	 *              empty
	 *
	 * @return array the record from the database, will not be empty
	 */
	protected function retrieveRecord(array $whereClauseParts) {
		if (!$this->hasDatabaseAccess()) {
			throw new tx_oelib_Exception_NotFound(
				'No record can be retrieved from the database because database' .
					' access is disabled for this mapper instance.'
			);
		}

		$whereClauses = array();
		foreach ($whereClauseParts as $key => $value) {
			$columnDefinition = tx_oelib_db::getColumnDefinition(
				$this->tableName, $key
			);

			$whereClauses[] = $key . ' = ' . (
				(strpos($columnDefinition['Type'], 'int') !== false)
					? $value
					: $GLOBALS['TYPO3_DB']->fullQuoteStr(
						$value, $this->tableName
					)
			);
		}
		$whereClause = implode(' AND ', $whereClauses);

		try {
			$data = tx_oelib_db::selectSingle(
				$this->columns,
				$this->tableName,
				$whereClause . tx_oelib_db::enableFields($this->tableName, 1)
			);
		} catch (tx_oelib_Exception_EmptyQueryResult $exception) {
			throw new tx_oelib_Exception_NotFound(
				'The record where "' . $whereClause . '" could not be ' .
					'retrieved from the table ' . $this->tableName . '.'
			);
		}

		return $data;
	}

	/**
	 * Reads a record from the database by UID (from this mapper's table).
	 * Hidden records will be retrieved as well.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record in the DB
	 *                                     with the UID $uid
	 *
	 * @param integer the UID of the record to retrieve, must be > 0
	 *
	 * @return array the record from the database, will not be empty
	 */
	protected function retrieveRecordByUid($uid) {
		return $this->retrieveRecord(array('uid' => $uid));
	}

	/**
	 * Creates a new ghost model with the UID $uid and registers it.
	 *
	 * @return tx_oelib_Model a ghost model with the UID $uid
	 */
	protected function createGhost($uid) {
		$model = tx_oelib_ObjectFactory::make($this->modelClassName);
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
		$model = $this->createGhost($this->map->getNewUid());
		$this->registerModelAsMemoryOnlyDummy($model);

		return $model;
	}

	/**
	 * Creates a new registered model with a UID that has not been used in this
	 * data mapper yet and loads it with the data provided in $data.
	 *
	 * The data is considered to be in the same format as in the database.
	 * Relations will be created as needed.
	 *
	 * This function should only be used in unit tests for mappers (to avoid
	 * creating records in the DB when the DB access itself needs not be
	 * tested).
	 *
	 * To use this function for testing relations to the same mapper, the mapper
	 * needs to be accessed via the mapper registry so object identity is
	 * ensured.
	 *
	 * Note: The UID is not guaranteed to be unused in the database.
	 *
	 * @return tx_oelib_Model a new model loaded with $data
	 */
	public function getLoadedTestingModel(array $data) {
		$model = $this->getNewGhost();
		$this->fillModel($model, $data);

		return $model;
	}

	/**
	 * Disables all database querying, so model data can only be fetched from
	 * memory.
	 *
	 * This function is for testing purposes only. For testing, it should be
	 * used whenever possible.
	 */
	public function disableDatabaseAccess() {
		$this->denyDatabaseAccess = true;
	}

	/**
	 * Checks whether the database may be accessed.
	 *
	 * @return boolean true is database access is granted, false otherwise
	 */
	public function hasDatabaseAccess() {
		return !$this->denyDatabaseAccess;
	}

	/**
	 * Writes a model to the database. Does nothing if database access is
	 * denied, if the model is clean, if the model has status dead, virgin or
	 * ghost, if the model is read-only or if there is no data to set.
	 *
	 * @param tx_oelib_Model model to write to the database
	 */
	public function save(tx_oelib_Model $model) {
		if ($this->isModelAMemoryOnlyDummy($model)) {
			throw new Exception(
				'This model is a memory-only dummy that must not be saved.'
			);
		}

		if (!$this->hasDatabaseAccess()
			|| !$model->isDirty()
			|| !$model->isLoaded()
			|| $model->isReadOnly()
		) {
			return;
		}

		$data = $this->getPreparedModelData($model);

		if ($model->hasUid()) {
			tx_oelib_db::update(
				$this->tableName, 'uid = ' . $model->getUid(), $data
			);
		} else {
			$model->setUid(tx_oelib_db::insert($this->tableName, $data));
			$this->map->add($model);
		}

		$model->markAsClean();
		if ($model->isDeleted()) {
			$model->markAsDead();
		}
	}

	/**
	 * Prepares the model's data for the database. Changes the relations into a
	 * database-applicable format. Sets the timestamp and sets the "crdate" for
	 * new models.
	 *
	 * @param tx_oelib_Model model to write to the database
	 *
	 * @return array the model's data prepared for the database, will not be
	 *               empty
	 */
	private function getPreparedModelData(tx_oelib_Model $model) {
		if (!$model->hasUid()) {
			$model->setCreationDate();
		}
		$model->setTimestamp();

		$data = $model->getData();

		foreach (array_keys($this->relations) as $key) {
			if ($this->isOneToManyRelationConfigured($key)) {
				$functionName = 'count';
			} elseif ($this->isManyToOneRelationConfigured($key)) {
				$functionName = 'getUid';
			} else {
				if ($this->isManyToManyRelationConfigured($key)) {
					$functionName = 'count';
				} else {
					$functionName = 'getUids';
				}
			}

			$data[$key] = (isset($data[$key]) && is_object($data[$key]))
				? $data[$key]->$functionName() : 0;
		}

		return $data;
	}

	/**
	 * Registers a model as a memory-only dummy that must not be saved.
	 *
	 * @param tx_oelib_Model $model the model to register
	 */
	private function registerModelAsMemoryOnlyDummy(tx_oelib_Model $model) {
		if (!$model->hasUid()) {
			return;
		}

		$this->uidsOfMemoryOnlyDummyModels[$model->getUid()] = true;
	}

	/**
	 * Checks whether $model is a memory-only dummy that must not be saved
	 *
	 * @param tx_oelib_Model $model the model to check
	 *
	 * @return boolean true if $model is a memory-only dummy, false otherwise
	 */
	private function isModelAMemoryOnlyDummy(tx_oelib_Model $model) {
			if (!$model->hasUid()) {
			return;
		}

		return isset($this->uidsOfMemoryOnlyDummyModels[$model->getUid()]);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_DataMapper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_DataMapper.php']);
}
?>