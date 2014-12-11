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
 * This class represents a registry for mappers. The mappers must be located in
 * the directory Mapper/ in each extension. Extension can use mappers from
 * other extensions as well.
 *
 * Note: This does not work with user_ extensions yet.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_MapperRegistry {
	/**
	 * @var Tx_Oelib_MapperRegistry the Singleton instance
	 */
	private static $instance = NULL;

	/**
	 * @var Tx_Oelib_DataMapper[] already created mappers (by class name)
	 */
	private $mappers = array();

	/**
	 * @var boolean whether database access should be denied for mappers
	 */
	private $denyDatabaseAccess = FALSE;

	/**
	 * @var boolean whether this MapperRegistry is used in testing mode
	 */
	private $testingMode = FALSE;

	/**
	 * @var Tx_Oelib_TestingFramework the testingFramework to use in testing mode
	 */
	private $testingFramework = NULL;

	/**
	 * The constructor. Use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->mappers, $this->testingFramework);
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return Tx_Oelib_MapperRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new Tx_Oelib_MapperRegistry();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 *
	 * @return void
	 */
	public static function purgeInstance() {
		self::$instance = NULL;
	}

	/**
	 * Retrieves a dataMapper by class name.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no such mapper
	 *
	 * @param string $className the name of an existing mapper class, must not be empty
	 *
	 * @return Tx_Oelib_DataMapper the mapper with the class $className
	 *
	 * @see getByClassName
	 */
	public static function get($className) {
		return self::getInstance()->getByClassName($className);
	}

	/**
	 * Retrieves a dataMapper by class name.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no such mapper class
	 *
	 * @param string $className the name of an existing mapper class, must not be empty
	 *
	 * @return Tx_Oelib_DataMapper the mapper with the class $className
	 *
	 * @throws InvalidArgumentException
	 */
	private function getByClassName($className) {
		if ($className === '') {
			throw new InvalidArgumentException('$className must not be empty.', 1331488868);
		}
		if (!preg_match('/^[tT]x_[A-Za-z0-9]+_[a-zA-Z_]+/', $className)) {
			throw new InvalidArgumentException(
				'$className must be in the format tx_extensionname[_Folder]_ClassName, but was "' . $className . '".',
				1331488887
			);
		}
		$unifiedClassName = self::unifyClassName($className);

		if (!isset($this->mappers[$unifiedClassName])) {
			if (!class_exists($className, TRUE)) {
				throw new tx_oelib_Exception_NotFound(
					'No mapper class "' . $className . '" could be found.'
				);
			}

			if ($this->testingMode) {
				$testingClassName = $unifiedClassName . 'Testing';
				if (!class_exists($testingClassName, FALSE)) {
					eval(
						'class ' . $testingClassName . ' extends ' . $unifiedClassName .
							' {' .
							'private $testingFramework;' .
							'public function __destruct() {' .
							'parent::__destruct();' .
							'unset($this->testingFramework);' .
							'}' .
							'public function setTestingFramework(Tx_Oelib_TestingFramework $testingFramework) {' .
							'$this->testingFramework = $testingFramework;' .
							'}' .
							'protected function getManyToManyRelationIntermediateRecordData($mnTable, $uidLocal, $uidForeign, $sorting) {' .
							'$this->testingFramework->markTableAsDirty($mnTable);' .
							'return array_merge(parent::getManyToManyRelationIntermediateRecordData($mnTable, $uidLocal, $uidForeign, $sorting), array($this->testingFramework->getDummyColumnName($this->getTableName()) => 1));' .
							'}' .
							'protected function prepareDataForNewRecord(array &$data) {' .
							'$this->testingFramework->markTableAsDirty($this->getTableName());' .
							'$data[$this->testingFramework->getDummyColumnName($this->getTableName())] = 1;' .
							'}' .
							'protected function getUniversalWhereClause($allowHiddenRecords = FALSE) {' .
							'$dummyColumnName = $this->testingFramework->getDummyColumnName($this->getTableName());' .
							'$additionalWhere = Tx_Oelib_Db::tableHasColumn($this->getTableName(), $dummyColumnName) ' .
							'? $dummyColumnName . \' = 1 AND \' : \'\';' .
							'return $additionalWhere . parent::getUniversalWhereClause($allowHiddenRecords);' .
							'}' .
							'}'
					);
				}
				$this->mappers[$unifiedClassName] = new $testingClassName();
				$this->mappers[$unifiedClassName]->setTestingFramework($this->testingFramework);
			} else {
				/** @var Tx_Oelib_DataMapper $mapper */
				$mapper = t3lib_div::makeInstance($unifiedClassName);;
				$this->mappers[$unifiedClassName] = $mapper;
			}
		}

		if ($this->denyDatabaseAccess) {
			$this->mappers[$unifiedClassName]->disableDatabaseAccess();
		}

		return $this->mappers[$unifiedClassName];
	}

	/**
	 * Unifies a class name to a common format.
	 *
	 * @param string $className the class name to unify, must not be empty
	 *
	 * @return string the unified class name
	 */
	protected static function unifyClassName($className) {
		return strtolower($className);
	}

	/**
	 * Disables database access for all mappers received with get().
	 *
	 * @return void
	 */
	public static function denyDatabaseAccess() {
		self::getInstance()->denyDatabaseAccess = TRUE;
	}

	/**
	 * Activates the testing mode of this MapperRegistry.
	 *
	 * @param Tx_Oelib_TestingFramework $testingFramework the testingFramework
	 *                                                    to use in testing mode
	 *
	 * @return void
	 */
	public function activateTestingMode(Tx_Oelib_TestingFramework $testingFramework) {
		$this->testingMode = TRUE;
		$this->testingFramework = $testingFramework;
	}

	/**
	 * Sets a mapper that can be returned via get.
	 *
	 * This function is a static public convenience wrapper for setByClassName.
	 *
	 * This function is to be used for testing purposes only.
	 *
	 * @param string $className the class name of the mapper to set
	 * @param Tx_Oelib_DataMapper $mapper
	 *        the mapper to set, must be an instance of $className
	 *
	 * @see setByClassName
	 *
	 * @return void
	 */
	static public function set($className, Tx_Oelib_DataMapper $mapper) {
		self::getInstance()->setByClassName(self::unifyClassName($className), $mapper);
	}

	/**
	 * Sets a mapper that can be returned via get.
	 *
	 * This function is to be used for testing purposes only.
	 *
	 * @param string $className the class name of the mapper to set
	 * @param Tx_Oelib_DataMapper $mapper
	 *        the mapper to set, must be an instance of $className
	 *
	 * @return void
	 */
	private function setByClassName($className, Tx_Oelib_DataMapper $mapper) {
		if (!($mapper instanceof $className)) {
			throw new InvalidArgumentException(
				'The provided mapper is not an instance of '. $className . '.', 1331488915
			);
		}
		if (isset($this->mappers[$className])) {
			throw new BadMethodCallException(
				'There already is a ' . $className . ' mapper registered. Overwriting existing wrappers is not allowed.',
				1331488928
			);
		}

		$this->mappers[$className] = $mapper;
	}
}