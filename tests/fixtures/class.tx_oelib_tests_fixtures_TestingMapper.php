<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_tests_fixtures_TestingMapper' for the 'oelib' extension.
 *
 * This class represents a mapper for a testing model.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_tests_fixtures_TestingMapper extends tx_oelib_DataMapper {
	/**
	 * @var string the name of the database table for this mapper
	 */
	protected $tableName = 'tx_oelib_test';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'tx_oelib_tests_fixtures_TestingModel';

	/**
	 * @var array the (possible) relations of the created models in the format
	 *            DB column name => mapper name
	 */
	protected $relations = array(
		'friend' => 'tx_oelib_tests_fixtures_TestingMapper',
		'owner' => 'tx_oelib_Mapper_FrontEndUser',
		'children' => 'tx_oelib_tests_fixtures_TestingMapper',
		'related_records' => 'tx_oelib_tests_fixtures_TestingMapper',
		'composition' => 'tx_oelib_tests_fixtures_TestingChildMapper',
		'bidirectional' => 'tx_oelib_tests_fixtures_TestingMapper',
	);

	/**
	 * Sets the map for this mapper.
	 *
	 * This function is intendend to be used for testing purposes only.
	 *
	 * @param tx_oelib_IdentityMap the map to set
	 */
	public function setMap(tx_oelib_IdentityMap $map) {
		$this->map = $map;
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
	public function findSingleByWhereClause(array $whereClauseParts) {
		return parent::findSingleByWhereClause($whereClauseParts);
	}

	/**
	 * Sets the model class name.
	 *
	 * @param string model class name, must not be empty
	 */
	public function setModelClassName($className) {
		$this->modelClassName = $className;
	}

	/**
	 * Processes a model's data and creates any relations that are hidden within
	 * it using foreign key mapping.
	 *
	 * @param array the model data to process, might be modified
	 */
	public function createRelations(array &$data) {
		parent::createRelations($data);
	}
}
?>