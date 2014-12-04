<?php
/**
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
 * This class represents a mapper that maps database record to model instances.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class Tx_Oelib_DataMapper {
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
	 * @var Tx_Oelib_IdentityMap a map that holds the models that already
	 *                           have been retrieved
	 */
	protected $map = NULL;

	/**
	 * @var int[] UIDs of models that are memory-only models that must not be
	 *            saved, using the UIDs as keys and TRUE as value
	 */
	protected $uidsOfMemoryOnlyDummyModels = array();

	/**
	 * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
	 */
	protected $relations = array();

	/**
	 * @var string[] the column names of additional string keys
	 */
	protected $additionalKeys = array();

	/**
	 * The column names of an additional compound key.
	 * There can only be one compound key per data mapper.
	 *
	 * @var string[]
	 */
	protected $compoundKeyParts = array();

	/**
	 * @var array[] two-dimensional cache for the objects by key:
	 *            [key name][key value] => model
	 */
	private $cacheByKey = array();

	/**
	 * Cache for the objects by compound key:
	 * [compound key value] => model
	 * The column values are concatenated via a dot as compound key value.
	 *
	 * @var Tx_Oelib_Model[]
	 */
	protected $cacheByCompoundKey = array();

	/**
	 * @var boolean whether database access is denied for this mapper
	 */
	private $denyDatabaseAccess = FALSE;

	/**
	 * The constructor.
	 */
	public function __construct() {
		if ($this->getTableName() === '') {
			throw new InvalidArgumentException(get_class($this) . '::tableName must not be empty.', 1331319361);
		}
		if ($this->columns === '') {
			throw new InvalidArgumentException(get_class($this) . '::columns must not be empty.', 1331319374);
		}
		if ($this->modelClassName === '') {
			throw new InvalidArgumentException(get_class($this) . '::modelClassName must not be empty.', 1331319378);
		}

		$this->map = t3lib_div::makeInstance('Tx_Oelib_IdentityMap');

		foreach ($this->additionalKeys as $key) {
			$this->cacheByKey[$key] = array();
		}
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->cacheByKey, $this->map, $this->uidsOfMemoryOnlyDummyModels);
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
	 * @return Tx_Oelib_Model the model with the UID $uid
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
	 * @return Tx_Oelib_Model model for the provided UID, filled with the data
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
	 * @param array[] $dataOfModels
	 *        two-dimensional array, each inner array must at least contain the
	 *        element "uid", may be empty
	 *
	 * @return Tx_Oelib_List<Tx_Oelib_Model>
	 *         Models with the UIDs provided. The models will be filled with the
	 *         data provided in case they did not have any data before,
	 *         otherwise the already loaded data will be used. If $dataOfModels
	 *         was empty, an empty list will be returned.
	 *
	 * @see getModel()
	 */
	public function getListOfModels(array $dataOfModels) {
		$list = t3lib_div::makeInstance('Tx_Oelib_List');

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
	 * @param string[] $whereClauseParts
	 *        WHERE clause parts for the record to retrieve, each element must
	 *        consist of a column name as key and a value to search for as value
	 *        (will automatically get quoted), must not be empty
	 *
	 * @return Tx_Oelib_Model the model
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
	 * @param Tx_Oelib_Model $model
	 *        the model to fill, must already have a UID
	 *
	 * @return void
	 */
	public function load(Tx_Oelib_Model $model) {
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
	 * @param Tx_Oelib_Model $model
	 *        the model to fill, needs to have a UID
	 * @param array &$data
	 *        the model data to process as it comes from the DB, will be modified
	 *
	 * @return void
	 */
	private function fillModel(Tx_Oelib_Model $model, array &$data) {
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
	 * @param Tx_Oelib_Model $model
	 *        the model to create the relations for
	 *
	 * @return void
	 */
	protected function createRelations(array &$data, Tx_Oelib_Model $model) {
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
	 * @return array configuration for that relation, will not be empty if the TCA is valid
	 *
	 * @throws BadMethodCallException
	 */
	private function getRelationConfigurationFromTca($key) {
		$tca = Tx_Oelib_Db::getTcaForTable($this->getTableName());

		if (!isset($tca['columns'][$key])) {
			throw new BadMethodCallException(
				'In the table ' . $this->getTableName() . ', the column ' . $key . ' does not have a TCA entry.', 1331319627
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

		return ($cardinality === 1);
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
	 * @param Tx_Oelib_Model $model
	 *        the model to create the relation for
	 *
	 * @return void
	 */
	private function createOneToManyRelation(array &$data, $key, Tx_Oelib_Model $model) {
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
			$relationUids = Tx_Oelib_Db::selectMultiple(
				'uid', $foreignTable, $foreignField . ' = ' . $data['uid'], '',
				$foreignSortBy
			);
		}

		$data[$key] = Tx_Oelib_MapperRegistry::get($this->relations[$key])
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
			? Tx_Oelib_MapperRegistry::get($this->relations[$key])->find($uid)
			: NULL;
	}

	/**
	 * Creates an n:1 relation using a comma-separated list of UIDs.
	 *
	 * @param array &$data
	 *        the model data to process, will be modified
	 * @param string $key
	 *        the key of the data item for which the relation should be created, must not be empty
	 * @param Tx_Oelib_Model $model the model to create the relation for
	 *
	 * @return void
	 */
	private function createCommaSeparatedRelation(
		array &$data, $key, Tx_Oelib_Model $model
	) {
		$list = t3lib_div::makeInstance('Tx_Oelib_List');
		$list->setParentModel($model);

		$uidList = isset($data[$key]) ? trim($data[$key]) : '';
		if ($uidList !== '') {
			$mapper = Tx_Oelib_MapperRegistry::get($this->relations[$key]);
			$uids = t3lib_div::intExplode(',', $uidList);

			foreach ($uids as $uid) {
				// Some relations might have a junk 0 in it. We ignore it to
				// avoid crashing.
				if ($uid === 0) {
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
	 * @param Tx_Oelib_Model $model the model to create the relation for
	 *
	 * @return void
	 */
	private function createMToNRelation(
		array &$data, $key, Tx_Oelib_Model $model
	) {
		$list = t3lib_div::makeInstance('Tx_Oelib_List');
		$list->setParentModel($model);

		if ($data[$key] > 0) {
			$mapper = Tx_Oelib_MapperRegistry::get($this->relations[$key]);
			$relationConfiguration
				= $this->getRelationConfigurationFromTca($key);
			$mnTable = $relationConfiguration['MM'];

			if (!isset($relationConfiguration['MM_opposite_field'])) {
				$relationUids = Tx_Oelib_Db::selectColumnForMultiple(
					'uid_foreign', $mnTable, 'uid_local = ' . $data['uid'], '',
					'sorting'
				);
			} else {
				$relationUids = Tx_Oelib_Db::selectColumnForMultiple(
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
	 * @param string[] $whereClauseParts
	 *        WHERE clause parts for the record to retrieve, each element must consist of a column name as key and a value to
	 *        search for as value (will automatically get quoted), must not be empty
	 *
	 * @return string[] the record from the database, will not be empty
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
			$columnDefinition = Tx_Oelib_Db::getColumnDefinition($this->getTableName(), $key);

			$whereClauses[] = $key . ' = ' . (
				(strpos($columnDefinition['Type'], 'int') !== FALSE)
					? $value
					: $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $this->getTableName())
			);
		}
		$whereClause = implode(' AND ', $whereClauses);

		try {
			$data = Tx_Oelib_Db::selectSingle($this->columns, $this->getTableName(), $whereClause);
		} catch (tx_oelib_Exception_EmptyQueryResult $exception) {
			throw new tx_oelib_Exception_NotFound(
				'The record where "' . $whereClause . '" could not be retrieved from the table ' . $this->getTableName() . '.'
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
	 * @return string[] the record from the database, will not be empty
	 */
	protected function retrieveRecordByUid($uid) {
		return $this->retrieveRecord(array('uid' => $uid));
	}

	/**
	 * Creates a new ghost model with the UID $uid and registers it.
	 *
	 * @param integer $uid the UID of the to-create ghost
	 *
	 * @return Tx_Oelib_Model a ghost model with the UID $uid
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
	 * Important: As this ghost's UID has nothing to do with the real UIDs in
	 * the database, this ghost must not be loaded or saved.
	 *
	 * @return Tx_Oelib_Model a new ghost
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
	 * (Tx_Oelib_Model::setData works differently: There you need to provide the
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
	 * @param string[] $data the data as it would come from the database, may be empty
	 *
	 * @return Tx_Oelib_Model a new model loaded with $data
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
	 * @param Tx_Oelib_Model $model the model to write to the database
	 *
	 * @return void
	 */
	public function save(Tx_Oelib_Model $model) {
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
			Tx_Oelib_Db::update($this->getTableName(), 'uid = ' . $model->getUid(), $data);
			$this->deleteManyToManyRelationIntermediateRecords($model);
		} else {
			$this->prepareDataForNewRecord($data);
			$model->setUid(Tx_Oelib_Db::insert($this->getTableName(), $data));
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
	 * @param Tx_Oelib_Model $model the model to write to the database
	 *
	 * @return array the model's data prepared for the database, will not be empty
	 */
	private function getPreparedModelData(Tx_Oelib_Model $model) {
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

				if ($data[$key] instanceof Tx_Oelib_Model) {
					$this->saveManyToOneRelatedModels(
						$data[$key], Tx_Oelib_MapperRegistry::get($relation)
					);
				}
			} else {
				if ($this->isManyToManyRelationConfigured($key)) {
					$functionName = 'count';
				} else {
					$functionName = 'getUids';
				}

				if ($data[$key] instanceof Tx_Oelib_List) {
					$this->saveManyToManyAndCommaSeparatedRelatedModels(
						$data[$key], Tx_Oelib_MapperRegistry::get($relation)
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
	 * @param Tx_Oelib_Model $model the model to save
	 * @param Tx_Oelib_DataMapper $mapper the mapper to use for saving
	 *
	 * @return void
	 */
	private function saveManyToOneRelatedModels(
		Tx_Oelib_Model $model, Tx_Oelib_DataMapper $mapper
	) {
		$mapper->save($model);
	}

	/**
	 * Saves the related models of a comma-separated and a regular m:n relation.
	 *
	 * @param Tx_Oelib_List<Tx_Oelib_Model> $list the list of models to save
	 * @param Tx_Oelib_DataMapper $mapper the mapper to use for saving
	 *
	 * @return void
	 */
	private function saveManyToManyAndCommaSeparatedRelatedModels(
		Tx_Oelib_List $list, Tx_Oelib_DataMapper $mapper
	) {
		foreach ($list as $model) {
			$mapper->save($model);
		}
	}

	/**
	 * Deletes the records in the intermediate table of m:n relations for a
	 * given model.
	 *
	 * @param Tx_Oelib_Model $model the model to delete the records in the
	 *                              intermediate table of m:n relations for
	 *
	 * @return void
	 */
	private function deleteManyToManyRelationIntermediateRecords(
		Tx_Oelib_Model $model
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
				Tx_Oelib_Db::delete($mnTable, $where);
			}
		}
	}

	/**
	 * Creates records in the intermediate table of m:n relations for a given
	 * model.
	 *
	 * @param Tx_Oelib_Model $model the model to create the records in the
	 *                              intermediate table of m:n relations for
	 *
	 * @return void
	 */
	private function createManyToManyRelationIntermediateRecords(
		Tx_Oelib_Model $model
	) {
		$data = $model->getData();

		foreach (array_keys($this->relations) as $key) {
			if (
				$this->isManyToManyRelationConfigured($key)
				&& ($data[$key] instanceof Tx_Oelib_List)
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
					Tx_Oelib_Db::insert(
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
	 * @param Tx_Oelib_Model $model the model to save the related records for
	 *
	 * @return void
	 */
	private function saveOneToManyRelationRecords(Tx_Oelib_Model $model) {
		$data = $model->getData();

		foreach ($this->relations as $key => $relation) {
			if (!$this->isOneToManyRelationConfigured($key)) {
				continue;
			}
			$relatedModels = $data[$key];
			if (!($relatedModels instanceof Tx_Oelib_List)) {
				continue;
			}

			$relationConfiguration =
				$this->getRelationConfigurationFromTca($key);
			if (!isset($relationConfiguration['foreign_field'])) {
				throw new BadMethodCallException(
					'The relation ' . $this->getTableName() . ':' . $key . ' is missing the "foreign_field" setting.', 1331319719
				);
			}

			$relatedMapper = Tx_Oelib_MapperRegistry::get($relation);
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

			foreach ($relatedModels->toArray() as $relatedModel) {
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
	 * @return int[] the record data for an intermediate m:n-relation record
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
	 * @param Tx_Oelib_Model $model
	 *        the model to delete, must not be a memory-only dummy, must not be
	 *        read-only
	 *
	 * @return void
	 */
	public function delete(Tx_Oelib_Model $model) {
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
	 * @param Tx_Oelib_Model $model
	 *        the model for which to delete the related models
	 *
	 * @return void
	 */
	private function deleteOneToManyRelations(Tx_Oelib_Model $model) {
		$data = $model->getData();

		foreach ($this->relations as $key => $mapperName) {
			if ($this->isOneToManyRelationConfigured($key)) {
				$relatedModels = $data[$key];
				if (!is_object($relatedModels)) {
					continue;
				}

				$mapper = Tx_Oelib_MapperRegistry::get($mapperName);
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
	 * @return Tx_Oelib_List<Tx_Oelib_Model> all models from the DB, already loaded
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
		return '1 = 1' . Tx_Oelib_Db::enableFields($this->getTableName(), ($allowHiddenRecords ? 1 : -1));
	}

	/**
	 * Registers a model as a memory-only dummy that must not be saved.
	 *
	 * @param Tx_Oelib_Model $model the model to register
	 *
	 * @return void
	 */
	private function registerModelAsMemoryOnlyDummy(Tx_Oelib_Model $model) {
		if (!$model->hasUid()) {
			return;
		}

		$this->uidsOfMemoryOnlyDummyModels[$model->getUid()] = TRUE;
	}

	/**
	 * Checks whether $model is a memory-only dummy that must not be saved
	 *
	 * @param Tx_Oelib_Model $model the model to check
	 *
	 * @return boolean TRUE if $model is a memory-only dummy, FALSE otherwise
	 */
	private function isModelAMemoryOnlyDummy(Tx_Oelib_Model $model) {
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
	 * @return Tx_Oelib_List<Tx_Oelib_Model> all models found in DB for the given where clause,
	 *                       will be an empty list if no models were found
	 */
	protected function findByWhereClause($whereClause = '', $sorting = '', $limit = '') {
		$orderBy = '';

		$tca = Tx_Oelib_Db::getTcaForTable($this->getTableName());
		if ($sorting !== '') {
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

		$completeWhereClause = ($whereClause === '')
			? ''
			: $whereClause . ' AND ';

		$rows = Tx_Oelib_Db::selectMultiple(
			'*',
			$this->getTableName(),
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
	 * @return Tx_Oelib_List<Tx_Oelib_Model> all records with the matching page UIDs, will be
	 *                       empty if no records have been found
	 */
	public function findByPageUid($pageUids, $sorting = '', $limit = '') {
		if (($pageUids === '') || ($pageUids === '0') || ($pageUids === 0)) {
			return $this->findByWhereClause('', $sorting, $limit);
		}

		return $this->findByWhereClause($this->getTableName() . '.pid IN (' . $pageUids . ')', $sorting, $limit);
	}

	/**
	 * Looks up a model in the cache by key.
	 *
	 * When this function reports "no match", the model could still exist in the
	 * database, though.
	 *
	 * @param string $key an existing key, must not be empty
	 * @param string $value
	 *        the value for the key of the model to find, must not be empty
	 *
	 * @throws InvalidArgumentException
	 * @throws tx_oelib_Exception_NotFound if there is no match in the cache yet
	 *
	 * @return Tx_Oelib_Model the cached model
	 */
	protected function findOneByKeyFromCache($key, $value) {
		if ($key === '') {
			throw new InvalidArgumentException('$key must not be empty.', 1416847364);
		}
		if (!isset($this->cacheByKey[$key])) {
			throw new InvalidArgumentException('"' . $key . '" is not a valid key for this mapper.', 1331319882);
		}
		if ($value === '') {
			throw new InvalidArgumentException('$value must not be empty.', 1331319892);
		}

		if (!isset($this->cacheByKey[$key][$value])) {
			throw new tx_oelib_Exception_NotFound();
		}

		return $this->cacheByKey[$key][$value];
	}

	/**
	 * Looks up a model in the compound cache.
	 *
	 * When this function reports "no match", the model could still exist in the
	 * database, though.
	 *
	 * @param string $value
	 *        the value for the compound key of the model to find, must not be empty
	 *
	 * @throws InvalidArgumentException
	 * @throws tx_oelib_Exception_NotFound if there is no match in the cache yet
	 *
	 * @return Tx_Oelib_Model the cached model
	 */
	public function findOneByCompoundKeyFromCache($value) {
		if ($value === '') {
			throw new InvalidArgumentException('$value must not be empty.', 1331319992);
		}

		if (!isset($this->cacheByCompoundKey[$value])) {
			throw new tx_oelib_Exception_NotFound();
		}

		return $this->cacheByCompoundKey[$value];
	}

	/**
	 * Puts a model in the cache-by-keys (if the model has any non-empty
	 * additional keys).
	 *
	 * @param Tx_Oelib_Model $model the model to cache
	 * @param string[] $data the data of the model as it is in the DB, may be empty
	 *
	 * @return void
	 */
	private function cacheModelByKeys(Tx_Oelib_Model $model, array $data) {
		foreach ($this->additionalKeys as $key) {
			if (isset($data[$key])) {
				$value = $data[$key];
				if ($value !== '') {
					$this->cacheByKey[$key][$value] = $model;
				}
			}
		}

		$this->cacheModelByCombinedKeys($model, $data);
		if (!empty($this->compoundKeyParts)) {
			$this->cacheModelByCompoundKey($model, $data);
		}
	}

	/**
	 * Caches a model by an additional compound key.
	 *
	 * This method needs to be overwritten in subclasses to work. However, it is recommended to use cacheModelByCompoundKey
	 * instead. So this method primarily is here for backwards compatibility.
	 *
	 * @param Tx_Oelib_Model $model the model to cache
	 * @param string[] $data the data of the model as it is in the DB, may be empty
	 *
	 * @return void
	 *
	 * @see cacheModelByCompoundKey
	 */
	protected function cacheModelByCombinedKeys(Tx_Oelib_Model $model, array $data) {}

	/**
	 * Automatically caches a model by an additional compound key.
	 *
	 * It is cached only if all parts of the compound key have values.
	 *
	 * This method works automatically; it is not necessary to overwrite it.
	 *
	 * @param Tx_Oelib_Model $model the model to cache
	 * @param string[] $data the data of the model as it is in the DB, may be empty
	 *
	 * @throws BadMethodCallException
	 *
	 * @return void
	 */
	protected function cacheModelByCompoundKey(Tx_Oelib_Model $model, array $data) {
		if (empty($this->compoundKeyParts)) {
			throw new BadMethodCallException(
				'The compound key parts are not defined.', 1363806895
			);
		}
		$values = array();
		foreach ($this->compoundKeyParts as $key) {
			if (isset($data[$key])) {
				$values[] = $data[$key];
			}
		}
		if (count($this->compoundKeyParts) === count($values)) {
			$value = implode('.', $values);
			if ($value !== '') {
				$this->cacheByCompoundKey[$value] = $model;
			}
		}
	}

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
	 * @return Tx_Oelib_Model the cached model
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
	 * Looks up a model by a compound key.
	 *
	 * This function will first check the cache-by-key and, if there is no match,
	 * will try to find the model in the database.
	 *
	 * @param string[] $compoundKeyValues
	 *        existing key value pairs, must not be empty
	 *        The array must have all the keys that are set in the additionalCompoundKey array.
	 *        The array values contain the model data with which to look up.
	 *
	 * @throws InvalidArgumentException if parameter array $keyValue is empty
	 * @throws tx_oelib_Exception_NotFound if there is no match (neither in the cache nor in the database)
	 *
	 * @return Tx_Oelib_Model the cached model
	 */
	public function findOneByCompoundKey(array $compoundKeyValues) {
		if (empty($compoundKeyValues)) {
			throw new InvalidArgumentException(get_class($this) . '::compoundKeyValues must not be empty.', 1354976660);
		}

		try {
			$model = $this->findOneByCompoundKeyFromCache($this->extractCompoundKeyValues($compoundKeyValues));
		} catch (tx_oelib_Exception_NotFound $exception) {
			$model = $this->findSingleByWhereClause($compoundKeyValues);
		}

		return $model;
	}

	/**
	 * Extracting the key value from model data.
	 *
	 * @param string[] $compoundKeyValues
	 *        existing key value pairs, must not be empty
	 *        The array must have all the keys that are set in the additionalCompoundKey array.
	 *        The array values contain the model data with which to look up.
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return string Contains the values for the compound key parts concatenated with a dot.
	 *
	 */
	protected function extractCompoundKeyValues(array $compoundKeyValues) {
		$values = array();
		foreach ($this->compoundKeyParts as $key) {
			if (!isset($compoundKeyValues[$key])) {
				throw new InvalidArgumentException(get_class($this) . '::keyValue does not contain all compound keys.', 1354976661);
			}
			$values[] = $compoundKeyValues[$key];
		}

		return implode('.', $values);
	}

	/**
	 * Finds all records that are related to $model via the field $key.
	 *
	 * @param Tx_Oelib_Model $model
	 *        the model to which the matches should be related
	 * @param string $relationKey
	 *        the key of the field in the matches that should contain the UID
	 *        of $model
	 * @param Tx_Oelib_List<Tx_Oelib_Model> $ignoreList
	 *        related records that should _not_ be returned
	 *
	 * @return Tx_Oelib_List<Tx_Oelib_Model> the related models, will be empty if there are no matches
	 */
	public function findAllByRelation(
		Tx_Oelib_Model $model, $relationKey, Tx_Oelib_List $ignoreList = NULL
	) {
		if (!$model->hasUid()) {
			throw new InvalidArgumentException('$model must have a UID.', 1331319915);
		}
		if ($relationKey === '') {
			throw new InvalidArgumentException('$key must not be empty.', 1331319921);
		}

		$ignoreClause = '';
		if (($ignoreList !== NULL) && !$ignoreList->isEmpty()) {
			$ignoreUids = $ignoreList->getUids();
			// deals with the case of $ignoreList having only models without UIDs
			if ((string) $ignoreUids !== '') {
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
		$completeWhereClause = ($whereClause === '')
			? ''
			: $whereClause . ' AND ';

		return Tx_Oelib_Db::count($this->getTableName(), $completeWhereClause . $this->getUniversalWhereClause());
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
		if (($pageUids === '') || ($pageUids === '0')) {
			return $this->countByWhereClause('');
		}

		return $this->countByWhereClause($this->getTableName() . '.pid IN (' . $pageUids . ')');
	}

	/**
	 * Returns the table name of this mapper.
	 *
	 * @return string the table name, will not be empty for correctly build data mappers
	 */
	public function getTableName() {
		return $this->tableName;
	}
}