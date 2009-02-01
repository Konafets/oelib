<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the tx_oelib_DataMapper class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_DataMapper_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework for creating dummy records
	 */
	private $testingFramework;
	/**
	 * @var tx_oelib_DataMapper the data mapper to test
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');

		$this->fixture = new tx_oelib_tests_fixtures_TestingMapper();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////////
	// Tests concerning the instantiation
	///////////////////////////////////////

	public function testInstantiationOfSubclassWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'tx_oelib_tests_fixtures_TableLessTestingMapper::tableName must not be empty.'
		);

		new tx_oelib_tests_fixtures_TableLessTestingMapper();
	}

	public function testInstantiationOfSubclassWithEmptyColumnListThrowsException() {
		$this->setExpectedException(
			'Exception',
			'tx_oelib_tests_fixtures_ColumnLessTestingMapper::columns must not be empty.'
		);

		new tx_oelib_tests_fixtures_ColumnLessTestingMapper();
	}

	public function testInstantiationOfSubclassWithEmptyModelNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'tx_oelib_tests_fixtures_ModelLessTestingMapper::modelClassName must not be empty.'
		);

		new tx_oelib_tests_fixtures_ModelLessTestingMapper();
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	public function testFindWithZeroUidThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$uid must be > 0.'
		);

		$this->fixture->find(0);
	}

	public function testFindWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$uid must be > 0.'
		);

		$this->fixture->find(-1);
	}

	public function testFindWithUidOfCachedModelReturnsThatModel() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(1);

		$map = new tx_oelib_IdentityMap();
		$map->add($model);
		$this->fixture->setMap($map);

		$this->assertSame(
			$model,
			$this->fixture->find(1)
		);
	}

	public function testFindWithUidOfExistingRecordReturnsModelWithThatUid() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			$uid,
			$this->fixture->find($uid)->getUid()
		);
	}

	public function testFindWithUidOfExistingRecordReturnsModelDataFromDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertEquals(
			'foo',
			$this->fixture->find($uid)->getTitle()
		);
	}

	public function testFindWithUidOfExistingRecordCalledTwoTimesReturnsSameModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			$this->fixture->find($uid),
			$this->fixture->find($uid)
		);
	}


	//////////////////////////
	// Tests concerning load
	//////////////////////////

	public function testLoadWithModelWithoutUidThrowsException() {
		$this->setExpectedException(
			'Exception',
			'load must only be called with models that already have a UID.'
		);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->load($model);
	}

	public function testLoadWithModelWithUidFillsModelWithData() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid($uid);
		$this->fixture->load($model);

		$this->assertEquals(
			'foo',
			$model->getTitle()
		);
	}


	//////////////////////////////////////
	// Tests concerning the model states
	//////////////////////////////////////

	public function testFindInitiallyReturnsGhostModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->fixture->find($uid)->isGhost()
		);
	}

	public function testFindAndAccessingDataMakesModelLoaded() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->fixture->find($uid)->getTitle();

		$this->assertTrue(
			$this->fixture->find($uid)->isLoaded()
		);
	}


	/////////////////////////////////
	// Tests concerning existsModel
	/////////////////////////////////

	public function testExistsModelForUidOfLoadedModelReturnsTrue() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->fixture->load($this->fixture->find($uid));

		$this->assertTrue(
			$this->fixture->existsModel($uid)
		);
	}

	public function testExistsModelForUidOfNotLoadedModelInDatabaseReturnsTrue() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->fixture->existsModel($uid)
		);
	}

	public function testExistsModelForInexistentUidReturnsFalse() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$this->assertFalse(
			$this->fixture->existsModel($uid)
		);
	}

	public function testExistsModelForGhostModelWithInexistentUidReturnsFalse() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');
		$this->fixture->find($uid);

		$this->assertFalse(
			$this->fixture->existsModel($uid)
		);
	}

	public function testExistsModelForExistingUidLoadsModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->fixture->existsModel($uid);

		$this->assertTrue(
			$this->fixture->find($uid)->isLoaded()
		);
	}
}
?>