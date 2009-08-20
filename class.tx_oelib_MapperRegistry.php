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
 * Class 'tx_oelib_MapperRegistry' for the 'oelib' extension.
 *
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
class tx_oelib_MapperRegistry {
	/**
	 * @var tx_oelib_MapperRegistry the Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var array already created mappers (by class name)
	 */
	private $mappers = array();

	/**
	 * @var boolean whether database access should be denied for mappers
	 */
	private $denyDatabaseAccess = false;

	/**
	 * @var boolean whether this MapperRegistry is used in testing mode
	 */
	private $testingMode = false;

	/**
	 * @var tx_oelib_testingFramework the testingFramework to use in testing mode
	 */
	private $testingFramework = null;

	/**
	 * The constructor. Use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		foreach ($this->mappers as $key => $mapper) {
			$mapper->__destruct();
			unset($this->mappers[$key]);
		}
		unset($this->testingFramework);
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @return tx_oelib_MapperRegistry the current Singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_oelib_MapperRegistry();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 */
	public static function purgeInstance() {
		if (self::$instance) {
			self::$instance->__destruct();
		}
		self::$instance = null;
	}

	/**
	 * Retrieves a dataMapper by class name.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no such mapper
	 *
	 * @param string the name of an existing mapper class
	 *
	 * @return tx_oelib_DataMapper the mapper with the class $className
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
	 * @param string the name of an existing mapper class
	 *
	 * @return tx_oelib_DataMapper the mapper with the class $className
	 */
	private function getByClassName($className) {
		if ($className == '') {
			throw new Exception('$key must not be empty.');
		}
		if (!preg_match('/^tx_[a-z0-9]+_[a-zA-Z_]+/', $className)) {
			throw new Exception(
				'$className must be in the format ' .
					'tx_extensionname[_Folder]_ClassName, but was "' .
					$className . '".'
			);
		}

		if (!isset($this->mappers[$className])) {
			if (!tx_oelib_Autoloader::load($className)) {
				throw new tx_oelib_Exception_NotFound(
					'No mapper class "' . $className . '" could be found.'
				);
			}

			if ($this->testingMode) {
				$testingClassName = $className . 'Testing';
				if (!class_exists($testingClassName)) {
					eval(
						'class ' . $testingClassName . ' extends ' . $className .
							' {' .
							'private $testingFramework;' .
							'public function __destruct() {' .
							'parent::__destruct();' .
							'unset($this->testingFramework);' .
							'}' .
							'public function setTestingFramework(tx_oelib_testingFramework $testingFramework) {' .
							'$this->testingFramework = $testingFramework;' .
							'}' .
							'protected function getManyToManyRelationIntermediateRecordData($mnTable, $uidLocal, $uidForeign, $sorting) {' .
							'$this->testingFramework->markTableAsDirty($mnTable);' .
							'return array_merge(parent::getManyToManyRelationIntermediateRecordData($mnTable, $uidLocal, $uidForeign, $sorting), array($this->testingFramework->getDummyColumnName($this->tableName) => 1));' .
							'}' .
							'protected function prepareDataForNewRecord(array &$data) {' .
							'$this->testingFramework->markTableAsDirty($this->tableName);' .
							'$data[$this->testingFramework->getDummyColumnName($this->tableName)] = 1;' .
							'}' .
							'protected function getUniversalWhereClause($allowHiddenRecords = false) {' .
							'$dummyColumnName = $this->testingFramework->getDummyColumnName($this->tableName);' .
							'$additionalWhere = tx_oelib_db::tableHasColumn($this->tableName, $dummyColumnName) ' .
							'? $dummyColumnName . \' = 1 AND \' : \'\';' .
							'return $additionalWhere . parent::getUniversalWhereClause($allowHiddenRecords);' .
							'}' .
							'}'
					);
				}
				$this->mappers[$className] = new $testingClassName();
				$this->mappers[$className]->setTestingFramework($this->testingFramework);
			} else {
				$this->mappers[$className]
					= tx_oelib_ObjectFactory::make($className);
			}
		}

		if ($this->denyDatabaseAccess) {
			$this->mappers[$className]->disableDatabaseAccess();
		}

		return $this->mappers[$className];
	}

	/**
	 * Disables database access for all mappers received with get().
	 */
	public static function denyDatabaseAccess() {
		self::getInstance()->denyDatabaseAccess = true;
	}

	/**
	 * Activates the testing mode of this MapperRegistry.
	 *
	 * @param tx_oelib_testingFramework $testingFramework the testingFramework
	 *                                                    to use in testing mode
	 */
	public function activateTestingMode(tx_oelib_testingFramework $testingFramework) {
		$this->testingMode = true;
		$this->testingFramework = $testingFramework;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_MapperRegistry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_MapperRegistry.php']);
}
?>