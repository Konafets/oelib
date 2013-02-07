<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2012 Oliver Klee <typo3-coding@oliverklee.de>
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
 * @author Niels Pardon <mail@niels-pardon.de>
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
	protected $map = NULL;

	/**
	 * @var array UIDs of models that are memory-only models that must not be
	 *            saved, using the UIDs as keys and TRUE as value
	 */
	protected $uidsOfMemoryOnlyDummyModels = array();

	/**
	 * @var array the (possible) relations of the created models in the format
	 *            DB column name => mapper name
	 */
	protected $relations = array();

	/**
	 * @var array the column names of additional string keys
	 */
	protected $additionalKeys = array();

	/**
	 * @var array two-dimensional cache for the objects by key:
	 *            [key name][key value] => model
	 */
	private $cacheByKey = array();

	/**
	 * @var boolean whether database access is denied for this mapper
	 */
	private $denyDatabaseAccess = FALSE;

	/**
	 * The constructor.
	 */
	public function __construct() {
		if ($this->tableName == '') {
			throw new InvalidArgumentException(get_class($this) . '::tableName must not be empty.', 1331319361);
		}
		if ($this->columns == '') {
			throw new InvalidArgumentException(get_class($this) . '::columns must not be empty.', 1331319374);
		}
		if ($this->modelClassName == '') {
			throw new InvalidArgumentException(get_class($this) . '::modelClassName must not be empty.', 1331319378);
		}

		$this->map = tx_oelib_ObjectFactory::make('tx_oelib_IdentityMap');

		foreach ($this->additionalKeys as $key) {
			$this->cacheByKey[$key] = array();
		}
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		$this->cacheByKey = array();
		if ($this->map) {
			$this->map->__destruct();
			unset($this->map);
		}
		$this->uidsOfMemoryOnlyDummyModels = array();
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
	 * @param integer $uid
	 *        the UID of the record to retrieve, must be > 0
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
	 * @param array $data
	 *        data for the model to return, must at least contain an element
	 *        with the key "uid"
	 *
	 * @return tx_oelib_Model model for the provided UID, filled with the data
	 *                        provided in case it did not have any data in
	 *                        memory before
	 */
	public function getModel(array $data) {
		if (!isset($data['uid'])) {
			throw new InvalidArgumentException('$data must contain an element "uid".', 1331319491);
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
	 * @param array<array> $dataOfModels
	 *        two-dimensional array, each inner array must at least contain the
	 *        element "uid", may be empty
	 *
	 * @return tx_oelib_List<tx_oelib_Model>
	 *         Models with the UIDs provided. The models will be filled with the
	 *         data provided in case they did not have any data before,
	 *         otherwise the already loaded data will be used. If $dataOfModels
	 *         was empty, an empty list will be returned.
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
	 * @param array $whereClauseParts
	 *        WHERE clause parts for the record to retrieve, each element must
	 *        consist of a column name as key and a value to search for as value
	 *        (will automatically get quoted), must not be empty
	 *
	 * @return tx_oelib_Model the model
	 */
	protected function findSingleByWhereClause(array $whereClauseParts) {
		if (empty($whereClauseParts)) {
			throw new InvalidArgumentException('The parameter $whereClauseParts must not be empty.', 1331319506);
		}

		return $this->getModel($this->retrieveRecord($whereClauseParts));
	}

	/**
	 * Checks whether a model with a certain UID actually exists in the database
	 * and could be loaded.
	 *
	 * @param integer $uid
	 *        the UID of the record to retrieve, must be > 0
	 * @param boolean $allowHidden
	 *        whether hidden records should be allowed to be retrieved
	 *
	 * @return boolean TRUE if a model with the UID $uid exists in the database,
	 *                 FALSE otherwise
	 */
	public function existsModel($uid, $allowHidden = FALSE) {
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
	 * @param tx_oelib_Model $model
	 *        the model to fill, must already have a UID
	 *
	 * @return void
	 */
	public function load(tx_oelib_Model $model) {
		if ($this->isModelAMemoryOnlyDummy($model)) {
			throw new InvalidArgumentException('This ghost was created via getNewGhost and must not be loaded.', 1331319529);
		}
		if (!$model->hasUid()) {
			throw new InvalidArgumentException('load must only be called with models that already have a UID.', 1331319554);
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
	 * This function also updates the cache-by-key.
	 *
	 * @param tx_oelib_Model $model
	 *        the model to fill, needs to have a UID
	 * @param array &$data
	 *        the model data to process as it comes from the DB, will be modified
	 *
	 * @return void
	 */
	private function fillModel(tx_oelib_Model $model, array &$data) {
		$this->cacheModelByKeys($model, $data);
		$this->createRelations($data, $model);
		$model->setData($data);
	}

	/**
	 * Processes a model's data and creates any relations that are hidden within
	 * it using foreign key mapping.
	 *
	 * @param array &$data
	 *        the model data to process, might be modified
	 * @param tx_oelib_Model $model
	 *        the model to create the relations for
	 *
	 * @return void
	 */
	protected function createRelations(array &$data, tx_oelib_Model $model) {
		foreach (array_keys($this->relations) as $key) {
			if ($this->isOneToManyRelationConfigured($key)) {
				$this->createOneToManyRelation($data, $key, $model);
			} elseif ($this->isManyToOneRelationConfigured($key)) {
				$this->createManyToOneRelation($data, $key);
			} else {
				if ($this->isManyToManyRelationConfigured($key)) {
					$this->createMToNRelation($data, $key, $model);
				} else {
					$this->createCommaSeparatedRelation($data, $key, $model);
				}
			}
		}
	}

	/**
	 * Retrieves the configuration of a relation from the TCA.
	 *
	 * @param string $key
	 *        the key of the relation to retrieve, must not be empty
	 *
	 * @return array configuration for that relation, will not be empty if the
	 *               TCA is valid
	 */
	private function getRelationConfigurationFromTca($key) {
		$tca = tx_oelib_db::getTcaForTable($this->tableName);

		if (!isset($tca['columns'][$key])) {
			throw new BadMethodCallException(
				'In the table ' . $this->tableName . ', the column ' . $key . ' does not have a TCA entry.', 1331319627
			);
		}

		return $tca['columns'][$key]['config'];
	}

	/**
	 * Checks whether the relation is configured in the TCA to be an 1:n
	 * relation.
	 *
	 * @param string $key
	 *        key of the relation, must not be empty
	 *
	 * @return boolean
	 *         TRUE if the relation is an 1:n relation, FALSE otherwise
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
	 * @param string $key
	 *        key of the relation, must not be empty
	 *
	 * @return boolean
	 *         TRUE if the relation is an n:1 relation, FALSE otherwise
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
	 * @param string $key
	 *        key of the relation, must not be empty
	 *
	 * @return boolean
	 *         TRUE if the relation's configuration provides an m:n table,
	 *         FALSE otherwise
	 */
	private function isManyToManyRelationConfigured($key) {
		$relationConfiguration = $this->getRelationConfigurationFromTca($key);

		return isset($relationConfiguration['MM']);
	}

	/**
	 * Creates an 1:n relation using foreign field mapping.
	 *
	 * @param array &$data
	 *        the model data to process, will be modified
	 * @param string $key
	 *        the key of the data item for which the relation should be created,
	 *        must not be empty
	 * @param tx_oelib_Model $model
	 *        the model to create the relation for
	 *
	 * @return void
	 */
	private function createOneToManyRelation(array &$data, $key, tx_oelib_Model $model) {
		$relationUids = array();

		if ($data[$key] > 0) {
			if ($this->isModelAMemoryOnlyDummy($model)) {
				throw new InvalidArgumentException(
					'This is a memory-only dummy which must not load any one-to-many relations from the database.', 1331319658
				);
			}

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
		$data[$key]->setParentModel($model);
	}

	/**
	 * Creates an n:1 relation using foreign key mapping.
	 *
	 * @param array &$data
	 *        the model data to process, will be modified
	 * @param string $key
	 *        the key of the data item for which the relation should be created, must not be empty
	 *
	 * @return void
	 */
	private function createManyToOneRelation(array &$data, $key) {
		$uid = isset($data[$key]) ? intval($data[$key]) : 0;

		$data[$key] = ($uid > 0)
			? tx_oelib_MapperRegistry::get($this->relations[$key])->find($uid)
			: NULL;
	}

	/**
	 * Creates an n:1 relation using a comma-separated list of UIDs.
	 *
	 * @param array &$data
	 *        the model data to process, will be modified
	 * @param string $key
	 *        the key of the data item for which the relation should be created, must not be empty
	 * @param tx_oelib_Model $model the model to create the relation for
	 *
	 * @return void
	 */
	private function createCommaSeparatedRelation(
		array &$data, $key, tx_oelib_Model $model
	) {
		$list = tx_oelib_ObjectFactory::make('tx_oelib_List');
		$list->setParentModel($model);

		$uidList = isset($data[$key]) ? trim($data[$key]) : '';
		if ($uidList != '') {
			$mapper = tx_oelib_MapperRegistry::get($this->relations[$key]);
			$uids = t3lib_div::intExplode(',', $uidList);

			foreach ($uids as $uid) {
				// Some relations might have a junk 0 in it. We ignore it to
				// avoid crashing.
				if ($uid == 0) {
					continue;
				}

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
	 * @param array &$data
	 *        the model data to process, will be modified
	 * @param string $key
	 *        the key of the data item for which the relation should be created, must not be empty
	 * @param tx_oelib_Model $model the model to create the relation for
	 *
	 * @return void
	 */
	private function createMToNRelation(
		array &$data, $key, tx_oelib_Model $model
	) {
		$list = tx_oelib_ObjectFactory::make('tx_oelib_List');
		$list->setParentModel($model);

		if ($data[$key] > 0) {
			$mapper = tx_oelib_MapperRegistry::get($this->relations[$key]);
			$relationConfiguration
				= $this->getRelationConfigurationFromTca($key);
			$mnTable = $relationConfiguration['MM'];

			if (!isset($relationConfiguration['MM_opposite_field'])) {
				$relationUids = tx_oelib_db::selectColumnForMultiple(
					'uid_foreign', $mnTable, 'uid_local = ' . $data['uid'], '',
					'sorting'
				);
			} else {
				$relationUids = tx_oelib_db::selectColumnForMultiple(
					'uid_local', $mnTable, 'uid_foreign = ' . $data['uid'], '',
					'uid_local'
				);
			}

			foreach ($relationUids as $relationUid) {
				$list->add($mapper->find($relationUid));
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
	 * @param array $whereClauseParts
	 *        WHERE clause parts for the record to retrieve, each element must consist of a column name as key and a value to
	 *        search for as value (will automatically get quoted), must not be empty
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

		$whereClauses = array($this->getUniversalWhereClause(TRUE));
		foreach ($whereClauseParts as $key => $value) {
			$columnDefinition = tx_oelib_db::getColumnDefinition(
				$this->tableName, $key
			);

			$whereClauses[] = $key . ' = ' . (
				(strpos($columnDefinition['Type'], 'int') !== FALSE)
					? $value
					: $GLOBALS['TYPO3_DB']->fullQuoteStr(
						$value, $this->tableName
					)
			);
		}
		$whereClause = implode(' AND ', $whereClauses);

		try {
			$data = tx_oelib_db::selectSingle(
				$this->columns, $this->tableName, $whereClause
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
	 * @param integer $uid the UID of the record to retrieve, must be > 0
	 *
	 * @return array the record from the database, will not be empty
	 */
	protected function retrieveRecordByUid($uid) {
		return $this->retrieveRecord(array('uid' => $uid));
	}

	/**
	 * Creates a new ghost model with the UID $uid and registers it.
	 *
	 * @param integer $uid the UID of the to-create ghost
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
	 * Important: As this ghost's UID has nothing to do with the real UIDs in
	 * the database, this ghost must not be loaded or saved.
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
	 * The data is considered to be in the same format as in the database,
	 * eg. m:1 relations are provided as the foreign UID, not as the constituded
	 * model.
	 *
	 * (tx_oelib_Model::setData works differently: There you need to provide the
	 * data with the relations already being the model/list objects.)
	 *
	 * This function should only be used in unit tests for mappers (to avoid
	 * creating records in the DB when the DB access itself needs not be
	 * tested).
	 *
	 * To use this function for testing relations to the same mapper, the mapper
	 * needs to be accessed via the mapper registry so object identity is
	 * ensured.
	 *
	 * Important: As this model's UID has nothing to do with the real UIDs in
	 * the database, this model must not be saved.
	 *
	 * @param array $data
	 *        the data as it would come from the database, may be empty
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
	 *
	 * @return void
	 */
	public function disableDatabaseAccess() {
		$this->denyDatabaseAccess = TRUE;
	}

	/**
	 * Checks whether the database may be accessed.
	 *
	 * @return boolean TRUE is database access is granted, FALSE otherwise
	 */
	public function hasDatabaseAccess() {
		return !$this->denyDatabaseAccess;
	}

	/**
	 * Writes a model to the database. Does nothing if database access is
	 * denied, if the model is clean, if the model has status dead, virgin or
	 * ghost, if the model is read-only or if there is no data to set.
	 *
	 * @param tx_oelib_Model $model the model to write to the database
	 *
	 * @return void
	 */
	public function save(tx_oelib_Model $model) {
		if ($this->isModelAMemoryOnlyDummy($model)) {
			throw new InvalidArgumentException('This model is a memory-only dummy that must not be saved.', 1331319682);
		}

		if (!$this->hasDatabaseAccess()
			|| !$model->isDirty()
			|| !$model->isLoaded()
			|| $model->isReadOnly()
		) {
			return;
		}

		$data = $this->getPreparedModelData($model);
		$this->cacheModelByKeys($model, $data);

		if ($model->hasUid()) {
			tx_oelib_db::update(
				$this->tableName, 'uid = ' . $model->getUid(), $data
			);
			$this->deleteManyToManyRelationIntermediateRecords($model);
		} else {
			$this->prepareDataForNewRecord($data);
			$model->setUid(tx_oelib_db::insert($this->tableName, $data));
			$this->map->add($model);
		}

		if ($model->isDeleted()) {
			$model->markAsDead();
		} else {
			$model->markAsClean();
			// We save the 1:n relations after marking this model as clean
			// in order to avoid infinite loops when the foreign model tries
			// to save this parent.
			$this->saveOneToManyRelationRecords($model);
			$this->createManyToManyRelationIntermediateRecords($model);
		}
	}

	/**
	 * Prepares the model's data for the database. Changes the relations into a
	 * database-applicable format. Sets the timestamp and sets the "crdate" for
	 * new models.
	 *
	 * @param tx_oelib_Model $model the model to write to the database
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

		foreach ($this->relations as $key => $relation) {
			if ($this->isOneToManyRelationConfigured($key)) {
				$functionName = 'count';
			} elseif ($this->isManyToOneRelationConfigured($key)) {
				$functionName = 'getUid';

				if ($data[$key] instanceof tx_oelib_Model) {
					$this->saveManyToOneRelatedModels(
						$data[$key], tx_oelib_MapperRegistry::get($relation)
					);
				}
			} else {
				if ($this->isManyToManyRelationConfigured($key)) {
					$functionName = 'count';
				} else {
					$functionName = 'getUids';
				}

				if ($data[$key] instanceof tx_oelib_List) {
					$this->saveManyToManyAndCommaSeparatedRelatedModels(
						$data[$key], tx_oelib_MapperRegistry::get($relation)
					);
				}
			}

			$data[$key] = (isset($data[$key]) && is_object($data[$key]))
				? $data[$key]->$functionName() : 0;
		}

		return $data;
	}

	/**
	 * Prepares the data for models that get newly inserted into the DB.
	 *
	 * @param array $data the data of the record, will be modified
	 *
	 * @return void
	 */
	protected function prepareDataForNewRecord(array &$data) {
	}

	/**
	 * Saves the related model of an n:1-relation.
	 *
	 * @param tx_oelib_Model $model the model to save
	 * @param tx_oelib_DataMapper $mapper the mapper to use for saving
	 *
	 * @return void
	 */
	private function saveManyToOneRelatedModels(
		tx_oelib_Model $model, tx_oelib_DataMapper $mapper
	) {
		$mapper->save($model);
	}

	/**
	 * Saves the related models of a comma-separated and a regular m:n relation.
	 *
	 * @param tx_oelib_List $list the list of models to save
	 * @param tx_oelib_DataMapper $mapper the mapper to use for saving
	 *
	 * @return void
	 */
	private function saveManyToManyAndCommaSeparatedRelatedModels(
		tx_oelib_List $list, tx_oelib_DataMapper $mapper
	) {
		foreach ($list as $model) {
			$mapper->save($model);
		}
	}

	/**
	 * Deletes the records in the intermediate table of m:n relations for a
	 * given model.
	 *
	 * @param tx_oelib_Model $model the model to delete the records in the
	 *                              intermediate table of m:n relations for
	 *
	 * @return void
	 */
	private function deleteManyToManyRelationIntermediateRecords(
		tx_oelib_Model $model
	) {
		foreach (array_keys($this->relations) as $key) {
			if ($this->isManyToManyRelationConfigured($key)) {
				$relationConfiguration =
					$this->getRelationConfigurationFromTca($key);
				$mnTable = $relationConfiguration['MM'];

				if (isset($relationConfiguration['MM_opposite_field'])) {
					$where = 'uid_foreign=' . $model->getUid();
				} else {
					$where = 'uid_local=' . $model->getUid();
				}
				tx_oelib_db::delete($mnTable, $where);
			}
		}
	}

	/**
	 * Creates records in the intermediate table of m:n relations for a given
	 * model.
	 *
	 * @param tx_oelib_Model $model the model to create the records in the
	 *                              intermediate table of m:n relations for
	 *
	 * @return void
	 */
	private function createManyToManyRelationIntermediateRecords(
		tx_oelib_Model $model
	) {
		$data = $model->getData();

		foreach (array_keys($this->relations) as $key) {
			if (
				$this->isManyToManyRelationConfigured($key)
				&& ($data[$key] instanceof tx_oelib_List)
			) {
				$sorting = 0;
				$relationConfiguration =
					$this->getRelationConfigurationFromTca($key);
				$mnTable = $relationConfiguration['MM'];

				foreach ($data[$key] as $relatedModel) {
					if (isset($relationConfiguration['MM_opposite_field'])) {
						$uidLocal = $relatedModel->getUid();
						$uidForeign = $model->getUid();
					} else {
						$uidLocal = $model->getUid();
						$uidForeign = $relatedModel->getUid();
					}
					tx_oelib_db::insert(
						$mnTable,
						$this->getManyToManyRelationIntermediateRecordData(
							$mnTable, $uidLocal, $uidForeign, $sorting
						)
					);
					$sorting++;
				}
			}
		}
	}

	/**
	 * Saves records that this model relates to as 1:n.
	 *
	 * @param tx_oelib_Model $model the model to save the related records for
	 *
	 * @return void
	 */
	private function saveOneToManyRelationRecords(tx_oelib_Model $model) {
		$data = $model->getData();

		foreach ($this->relations as $key => $relation) {
			if (!$this->isOneToManyRelationConfigured($key)) {
				continue;
			}
			$relatedModels = $data[$key];
			if (!($relatedModels instanceof tx_oelib_List)) {
				continue;
			}

			$relationConfiguration =
				$this->getRelationConfigurationFromTca($key);
			if (!isset($relationConfiguration['foreign_field'])) {
				throw new BadMethodCallException(
					'The relation ' . $this->tableName . ':' . $key . ' is missing the "foreign_field" setting.', 1331319719
				);
			}

			$relatedMapper = tx_oelib_MapperRegistry::get($relation);
			$foreignField = $relationConfiguration['foreign_field'];
			if (strpos($foreignField, 'tx_') === 0) {
				$foreignKey = ucfirst(
					preg_replace('/tx_[a-z]+_/', '', $foreignField)
				);
			} else {
				$foreignKey = ucfirst($foreignField);
			}
			$getter = 'get' . $foreignKey;
			$setter = 'set' . $foreignKey;

			foreach ($relatedModels as $relatedModel) {
				if (!method_exists($relatedModel, $getter)) {
					throw new BadMethodCallException(
						'The class ' . get_class($relatedModel) . ' is missing the function ' . $getter .
							' which is needed for saving a 1:n relation.',
						1331319751
					);
				}
				if (!method_exists($relatedModel, $setter)) {
					throw new BadMethodCallException(
						'The class ' . get_class($relatedModel) . ' is missing the function ' . $setter .
							' which is needed for saving a 1:n relation.',
						1331319803
					);
				}
				if ($relatedModel->$getter() !== $model) {
					 // Only sets the model if this would change anything. This
					 // avoids marking unchanged models as dirty.
					$relatedModel->$setter($model);
				}
				$relatedMapper->save($relatedModel);

				$unconnectedModels = $relatedMapper->findAllByRelation(
					$model, $foreignField, $relatedModels
				);
				foreach ($unconnectedModels as $unconnectedModel) {
					$relatedMapper->delete($unconnectedModel);
				}
			}
		}
	}

	/**
	 * Returns the record data for an intermediate m:n-relation record.
	 *
	 * Note: The $mnTable parameter is used for testing mappers in the mapper registry and must not be removed.
	 *
	 * @param string $mnTable the name of the intermediate m:n-relation table
	 * @param integer $uidLocal the UID of the local record
	 * @param integer $uidForeign the UID of the foreign record
	 * @param integer $sorting the sorting of the intermediate m:n-relation record
	 *
	 * @return array the record data for an intermediate m:n-relation record
	 */
	protected function getManyToManyRelationIntermediateRecordData($mnTable, $uidLocal, $uidForeign, $sorting) {
		return array(
			'uid_local' => $uidLocal,
			'uid_foreign' => $uidForeign,
			'sorting' => $sorting,
		);
	}

	/**
	 * Marks $model as deleted and saves it to the DB (if it has a UID).
	 *
	 * @param tx_oelib_Model $model
	 *        the model to delete, must not be a memory-only dummy, must not be
	 *        read-only
	 *
	 * @return void
	 */
	public function delete(tx_oelib_Model $model) {
		if ($this->isModelAMemoryOnlyDummy($model)) {
			throw new InvalidArgumentException('This model is a memory-only dummy that must not be deleted.', 1331319817);
		}
		if ($model->isReadOnly()) {
			throw new InvalidArgumentException('This model is read-only and must not be deleted.', 1331319836);
		}
		if ($model->isDead()) {
			return;
		}

		if ($model->hasUid()) {
			if (!$model->isLoaded()) {
				$this->load($model);
			}
			$model->setToDeleted();
			$this->save($model);
			$this->deleteOneToManyRelations($model);
		}
		$model->markAsDead();
	}

	/**
	 * Deletes all one-to-many related models of this model.
	 *
	 * @param tx_oelib_Model $model
	 *        the model for which to delete the related models
	 *
	 * @return void
	 */
	private function deleteOneToManyRelations(tx_oelib_Model $model) {
		$data = $model->getData();

		foreach ($this->relations as $key => $mapperName) {
			if ($this->isOneToManyRelationConfigured($key)) {
				$relatedModels = $data[$key];
				if (!is_object($relatedModels)) {
					continue;
				}

				$mapper = tx_oelib_MapperRegistry::get($mapperName);
				foreach ($relatedModels as $relatedModel) {
					$mapper->delete($relatedModel);
				}
			}
		}
	}

	/**
	 * Retrieves all non-deleted, non-hidden models from the DB.
	 *
	 * If no sorting is provided, the records are sorted like in the BE.
	 *
	 * @param string $sorting
	 *        the sorting for the found records, must be a valid DB field
	 *        optionally followed by "ASC" or "DESC" or may
	 *        be empty
	 *
	 * @return tx_oelib_List all models from the DB, already loaded
	 */
	public function findAll($sorting = '') {
		return $this->findByWhereClause('', $sorting);
	}

	/**
	 * Returns the WHERE clause that selects all visible records from the DB.
	 *
	 * @param boolean $allowHiddenRecords whether hidden records should be found
	 *
	 * @return string the WHERE clause that selects all visible records in the,
	 *                DB, will not be empty
	 */
	protected function getUniversalWhereClause($allowHiddenRecords = FALSE) {
		return '1 = 1' . tx_oelib_db::enableFields(
			$this->tableName, ($allowHiddenRecords ? 1 : -1)
		);
	}

	/**
	 * Registers a model as a memory-only dummy that must not be saved.
	 *
	 * @param tx_oelib_Model $model the model to register
	 *
	 * @return void
	 */
	private function registerModelAsMemoryOnlyDummy(tx_oelib_Model $model) {
		if (!$model->hasUid()) {
			return;
		}

		$this->uidsOfMemoryOnlyDummyModels[$model->getUid()] = TRUE;
	}

	/**
	 * Checks whether $model is a memory-only dummy that must not be saved
	 *
	 * @param tx_oelib_Model $model the model to check
	 *
	 * @return boolean TRUE if $model is a memory-only dummy, FALSE otherwise
	 */
	private function isModelAMemoryOnlyDummy(tx_oelib_Model $model) {
		if (!$model->hasUid()) {
			return FALSE;
		}

		return isset($this->uidsOfMemoryOnlyDummyModels[$model->getUid()]);
	}

	/**
	 * Retrieves all non-deleted, non-hidden models from the DB which match the
	 * given where clause.
	 *
	 * @param string $whereClause
	 *        WHERE clause for the record to retrieve must be quoted and SQL
	 *        safe, may be empty
	 * @param string $sorting
	 *        the sorting for the found records, must be a valid DB field
	 *        optionally followed by "ASC" or "DESC", may be empty
	 * @param string $limit the LIMIT value ([begin,]max), may be empty
	 *
	 * @return tx_oelib_List all models found in DB for the given where clause,
	 *                       will be an empty list if no models were found
	 */
	protected function findByWhereClause($whereClause = '', $sorting = '', $limit = '') {
		$orderBy = '';

		$tca = tx_oelib_db::getTcaForTable($this->tableName);
		if ($sorting != '') {
			$orderBy = $sorting;
		} elseif (isset($tca['ctrl']['default_sortby'])) {
			$matches = array();
			if (preg_match(
				'/^ORDER BY (.+)$/', $tca['ctrl']['default_sortby'],
				$matches
			)) {
				$orderBy = $matches[1];
			}
		}

		$completeWhereClause = ($whereClause == '')
			? ''
			: $whereClause . ' AND ';

		$rows = tx_oelib_db::selectMultiple(
			'*',
			$this->tableName,
			$completeWhereClause . $this->getUniversalWhereClause(),
			'',
			$orderBy,
			$limit
		);

		return $this->getListOfModels($rows);
	}

	/**
	 * Finds all records which are located on the given pages.
	 *
	 * @param string $pageUids
	 *        comma-separated UIDs of the pages on which the records should be
	 *        found, may be empty
	 * @param string $sorting
	 *        the sorting for the found records, must be a valid DB field
	 *        optionally followed by "ASC" or "DESC", may be empty
	 * @param string $limit the LIMIT value ([begin,]max), may be empty
	 *
	 * @return tx_oelib_List all records with the matching page UIDs, will be
	 *                       empty if no records have been found
	 */
	public function findByPageUid($pageUids, $sorting = '', $limit = '') {
		if (($pageUids == '') || ($pageUids == '0')) {
			return $this->findByWhereClause('', $sorting, $limit);
		}

		return $this->findByWhereClause(
			$this->tableName . '.pid IN (' . $pageUids . ')',
			$sorting,
			$limit
		);
	}

	/**
	 * Looks up a model in the cache by key.
	 *
	 * When this function reports "no match", the model could still exist in the
	 * database, though.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no match in the cache yet
	 *
	 * @param string $key an existing key, must not be empty
	 * @param string $value
	 *        the value for the key of the model to find, must not be empty
	 *
	 * @return tx_oelib_Model the cached model
	 */
	protected function findOneByKeyFromCache($key, $value) {
		if ($key == '') {
			throw new InvalidArgumentException('$key must not be empty.');
		}
		if (!isset($this->cacheByKey[$key])) {
			throw new InvalidArgumentException('"' . $key . '" is not a valid key for this mapper.', 1331319882);
		}
		if ($value == '') {
			throw new InvalidArgumentException('$value must not be empty.', 1331319892);
		}

		if (!isset($this->cacheByKey[$key][$value])) {
			throw new tx_oelib_Exception_NotFound();
		}

		return $this->cacheByKey[$key][$value];
	}

	/**
	 * Puts a model in the cache-by-keys (if the model has any non-empty
	 * additional keys).
	 *
	 * @param tx_oelib_Model $model the model to cache
	 * @param array $data the data of the model as it is in the DB, may be empty
	 *
	 * @return void
	 */
	private function cacheModelByKeys(tx_oelib_Model $model, array $data) {
		foreach ($this->additionalKeys as $key) {
			if (isset($data[$key])) {
				$value = $data[$key];
				if ($value != '') {
					$this->cacheByKey[$key][$value] = $model;
				}
			}
		}

		$this->cacheModelByCombinedKeys($model, $data);
	}

	/**
	 * Caches a model by additional combined keys.
	 *
	 * This function can be overwritten in subclasses for additional caching.
	 *
	 * @param tx_oelib_Model $model the model to cache
	 * @param array $data the data of the model as it is in the DB, may be empty
	 *
	 * @return void
	 */
	protected function cacheModelByCombinedKeys(
		tx_oelib_Model $model, array $data
	) {}

	/**
	 * Looks up a model by key.
	 *
	 * This function will first check the cache-by-key and, if there is no match,
	 * will try to find the model in the database.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no match (neither in the
	 *                                     cache nor in the database)
	 *
	 * @param string $key an existing key, must not be empty
	 * @param string $value
	 *        the value for the key of the model to find, must not be empty
	 *
	 * @return tx_oelib_Model the cached model
	 */
	public function findOneByKey($key, $value) {
		try {
			$model = $this->findOneByKeyFromCache($key, $value);
		} catch (tx_oelib_Exception_NotFound $exception) {
			$model = $this->findSingleByWhereClause(array($key => $value));
		}

		return $model;
	}

	/**
	 * Finds all records that are related to $model via the field $key.
	 *
	 * @param tx_oelib_Model $model
	 *        the model to which the matches should be related
	 * @param string $relationKey
	 *        the key of the field in the matches that should contain the UID
	 *        of $model
	 * @param tx_oelib_List $ignoreList
	 *        related records that should _not_ be returned
	 *
	 * @return tx_oelib_List the related models, will be empty if there are no
	 *                       matches
	 */
	public function findAllByRelation(
		tx_oelib_Model $model, $relationKey, tx_oelib_List $ignoreList = NULL
	) {
		if (!$model->hasUid()) {
			throw new InvalidArgumentException('$model must have a UID.', 1331319915);
		}
		if ($relationKey == '') {
			throw new InvalidArgumentException('$key must not be empty.', 1331319921);
		}

		$ignoreClause = '';
		if (($ignoreList !== NULL) && !$ignoreList->isEmpty()) {
			$ignoreUids = $ignoreList->getUids();
			// deals with the case of $ignoreList having only models without UIDs
			if ($ignoreUids != '') {
				$ignoreClause = ' AND uid NOT IN(' . $ignoreUids . ')';
			}
		}

		return $this->findByWhereClause(
			$relationKey . ' = ' . $model->getUid() . $ignoreClause
		);
	}

	/**
	 * Returns the number of records matching the given WHERE clause.
	 *
	 * @param string $whereClause
	 *        WHERE clause for the number of records to retrieve, must be quoted
	 *        and SQL safe, may be empty
	 *
	 * @return integer the number of records matching the given WHERE clause
	 */
	public function countByWhereClause($whereClause = '') {
		$completeWhereClause = ($whereClause == '')
			? ''
			: $whereClause . ' AND ';

		return tx_oelib_db::count(
			$this->tableName,
			$completeWhereClause . $this->getUniversalWhereClause()
		);
	}

	/**
	 * Returns the number of records located on the given pages.
	 *
	 * @param string $pageUids
	 *        comma-separated UIDs of the pages on which the records should be
	 *        found, may be empty
	 *
	 * @return integer the number of records located on the given pages
	 */
	public function countByPageUid($pageUids) {
		if (($pageUids == '') || ($pageUids == '0')) {
			return $this->countByWhereClause('');
		}

		return $this->countByWhereClause(
			$this->tableName . '.pid IN (' . $pageUids . ')'
		);
	}
}
?>