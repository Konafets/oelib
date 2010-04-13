<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee (typo3-coding@oliverklee.de)
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
	 * @var tx_oelib_tests_fixtures_TestingMapper the data mapper to test
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');

		tx_oelib_MapperRegistry::getInstance()->activateTestingMode(
			$this->testingFramework
		);

		$this->fixture = tx_oelib_MapperRegistry::get(
			'tx_oelib_tests_fixtures_TestingMapper'
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		tx_oelib_MapperRegistry::purgeInstance();
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

	public function testFindWithUidReturnsModelWithThatUid() {
		$uid = 42;

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

	public function testFindWithUidCalledTwoTimesReturnsSameModel() {
		$uid = 42;

		$this->assertSame(
			$this->fixture->find($uid),
			$this->fixture->find($uid)
		);
	}


	//////////////////////////////
	// Tests concerning getModel
	//////////////////////////////

	public function testGetModelWithArrayWithoutUidElementProvidedThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$data must contain an element "uid".'
		);

		$this->fixture->getModel(array());
	}

	public function testGetModelForNonMappedUidReturnsModelInstance() {
		$this->assertTrue(
			$this->fixture->getModel(array('uid' => 2))
				instanceof tx_oelib_Model
		);
	}

	public function testGetModelForNonMappedUidReturnsLoadedModel() {
		$this->assertTrue(
			$this->fixture->getModel(array('uid' => 2))->isLoaded()
		);
	}

	public function testGetModelForMappedUidOfGhostReturnsModelInstance() {
		$mappedUid = $this->fixture->getNewGhost()->getUid();

		$this->assertTrue(
			$this->fixture->getModel(array('uid' => $mappedUid))
				instanceof tx_oelib_Model
		);
	}

	public function testGetModelForMappedUidOfGhostReturnsLoadedModel() {
		$mappedUid = $this->fixture->getNewGhost()->getUid();

		$this->assertTrue(
			$this->fixture->getModel(array('uid' => $mappedUid))->isLoaded()
		);
	}

	public function testGetModelForMappedUidOfGhostReturnsLoadedModelWithTheProvidedData() {
		$mappedModel = $this->fixture->getNewGhost();

		$this->assertEquals(
			'new title',
			$this->fixture->getModel(
				array('uid' => $mappedModel->getUid(), 'title' => 'new title')
			)->getTitle()
		);
	}

	public function testGetModelForMappedUidOfGhostReturnsThatModel() {
		$mappedModel = $this->fixture->getNewGhost();

		$this->assertSame(
			$mappedModel,
			$this->fixture->getModel(array('uid' => $mappedModel->getUid()))
		);
	}

	public function testGetModelForMappedUidOfLoadedModelReturnsThatModelInstance() {
		$mappedModel = $this->fixture->getNewGhost();
		$mappedModel->setData(array('title' => 'foo'));

		$this->assertSame(
			$mappedModel,
			$this->fixture->getModel(array('uid' => $mappedModel->getUid()))
		);
	}

	public function testGetModelForMappedUidOfLoadedModelAndNoNewDataProvidedReturnsModelWithTheInitialData() {
		$mappedModel = $this->fixture->getNewGhost();
		$mappedModel->setData(array('title' => 'foo'));

		$this->assertEquals(
			'foo',
			$this->fixture->getModel(array('uid' => $mappedModel->getUid()))->getTitle()
		);
	}

	public function testGetModelForMappedUidOfLoadedModelAndNewDataProvidedReturnsModelWithTheInitialData() {
		$mappedModel = $this->fixture->getNewGhost();
		$mappedModel->setData(array('title' => 'foo'));

		$this->assertEquals(
			'foo',
			$this->fixture->getModel(
				array('uid' => $mappedModel->getUid(), 'title' => 'new title')
			)->getTitle()
		);
	}

	public function testGetModelForMappedUidOfDeadModelReturnsDeadModel() {
		$mappedModel = $this->fixture->getNewGhost();
		$mappedModel->markAsDead();

		$this->assertTrue(
			$this->fixture->getModel(array('uid' => $mappedModel->getUid()))->isDead()
		);
	}

	public function testGetModelForNonMappedUidReturnsModelWithChildrenList() {
		$this->assertTrue(
			$this->fixture->getModel(array('uid' => 2))->getChildren()
				instanceof tx_oelib_List
		);
	}


	/////////////////////////////////////
	// Tests concerning getListOfModels
	/////////////////////////////////////

	public function testGetListOfModelsReturnsInstanceOfList() {
		$this->assertTrue(
			$this->fixture->getListOfModels(array(array('uid' => 1)))
				instanceof tx_oelib_List
		);
	}

	public function testGetListOfModelsForAnEmptyArrayProvidedReturnsEmptyList() {
		$this->assertTrue(
			$this->fixture->getListOfModels(array())->isEmpty()
		);
	}

	public function testGetListOfModelsForOneRecordsProvidedReturnsListWithOneElement() {
		$this->assertEquals(
			1,
			$this->fixture->getListOfModels(array(array('uid' => 1)))->count()
		);
	}

	public function testGetListOfModelsForTwoRecordsProvidedReturnsListWithTwoElements() {
		$this->assertEquals(
			2,
			$this->fixture->getListOfModels(array(array('uid' => 1), array('uid' => 2)))->count()
		);
	}

	public function testGetListOfModelsReturnsListOfModelInstances() {
		$this->assertTrue(
			$this->fixture->getListOfModels(array(array('uid' => 1)))->current()
				instanceof tx_oelib_Model
		);
	}

	public function testGetListOfModelsReturnsListOfModelWithProvidedTitel() {
		$this->assertEquals(
			'foo',
			$this->fixture->getListOfModels(array(array('uid' => 1, 'title' => 'foo')))
				->current()->getTitle()
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

	public function testLoadWithModelWithExistingUidFillsModelWithData() {
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

		$model->__destruct();
	}

	public function testLoadWithModelWithExistingUidOfHiddenRecordMarksModelAsLoaded() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid($uid);
		$this->fixture->load($model);

		$this->assertTrue(
			$model->isLoaded()
		);

		$model->__destruct();
	}

	public function testLoadForModelWithExistingUidMarksModelAsClean() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid($uid);
		$this->fixture->load($model);

		$this->assertFalse(
			$model->isDirty()
		);

		$model->__destruct();
	}


	//////////////////////////////////////
	// Tests concerning the model states
	//////////////////////////////////////

	public function testFindInitiallyReturnsGhostModel() {
		$uid = 42;

		$this->assertTrue(
			$this->fixture->find($uid)->isGhost()
		);
	}

	public function testFindAndAccessingDataLoadsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->fixture->find($uid)->getTitle();

		$this->assertTrue(
			$this->fixture->find($uid)->isLoaded()
		);
	}

	public function testIsHiddenOnGhostInDatabaseLoadsModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$model = $this->fixture->find($uid);
		$model->isHidden();

		$this->assertTrue(
			$model->isLoaded()
		);
	}

	public function testIsHiddenOnGhostNotInDatabaseThrowsException() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'This tx_oelib_tests_fixtures_TestingModel with the UID ' . $uid .
				' is dead and cannot have any data.'
		);

		$this->fixture->find($uid)->isHidden();
	}

	public function testLoadWithModelWithExistingUidLoadsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid($uid);
		$this->fixture->load($model);

		$this->assertTrue(
			$model->isLoaded()
		);

		$model->__destruct();
	}

	public function testLoadWithModelWithInexistentUidMarksModelAsDead() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid($uid);
		$this->fixture->load($model);

		$this->assertTrue(
			$model->isDead()
		);

		$model->__destruct();
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

	public function testExistsModelForExistentUidOfHiddenRecordReturnsFalse() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);

		$this->assertFalse(
			$this->fixture->existsModel($uid)
		);
	}

	public function testExistsModelForExistentUidOfHiddenRecordAndHiddenBeingAllowedReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);

		$this->assertTrue(
			$this->fixture->existsModel($uid, true)
		);
	}

	public function testExistsModelForExistentUidOfLoadedHiddenRecordAndHiddenNotBeingAllowedReturnsFalse() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);
		$this->fixture->load($this->fixture->find($uid));

		$this->assertFalse(
			$this->fixture->existsModel($uid)
		);
	}

	public function testExistsModelForExistentUidOfLoadedHiddenRecordAndHiddenBeingAllowedReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);
		$this->fixture->load($this->fixture->find($uid));

		$this->assertTrue(
			$this->fixture->existsModel($uid, true)
		);
	}

	public function testExistsModelForExistentUidOfLoadedNonHiddenRecordAndHiddenBeingAllowedReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 0)
		);
		$this->fixture->load($this->fixture->find($uid));

		$this->assertTrue(
			$this->fixture->existsModel($uid, true)
		);
	}

	public function testExistsModelForExistentUidOfHiddenRecordAfterLoadingAsNonHiddenAndHiddenBeingAllowedReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);
		$this->fixture->load($this->fixture->find($uid));

		$this->assertTrue(
			$this->fixture->existsModel($uid, true)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning the foreign key mapping
	/////////////////////////////////////////////

	public function testRelatedRecordWithZeroUidIsNull() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertNull(
			$this->fixture->find($uid)->getFriend()
		);
	}

	public function testRelatedRecordWithExistingUidReturnsRelatedRecord() {
		$friendUid = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);

		$this->assertEquals(
			$friendUid,
			$this->fixture->find($uid)->getFriend()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function relatedRecordWithRelationToSelfReturnsSelf() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->changeRecord(
			'tx_oelib_test', $uid, array('friend' => $uid)
		);
		$model = $this->fixture->find($uid);

		$this->assertSame(
			$model,
			$model->getFriend()
		);
	}

	public function testRelatedRecordWithExistingUidCanReturnOtherModelType() {
		$ownerUid = $this->testingFramework->createFrontEndUser();
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('owner' => $ownerUid)
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getOwner()
				instanceof tx_oelib_Model_FrontEndUser
		);
	}

	public function testRelatedRecordWithExistingUidReturnsRelatedRecordThatCanBeLoaded() {
		$friendUid = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);

		$this->fixture->find($uid)->getFriend()->getTitle();

		$this->assertTrue(
			$this->fixture->find($uid)->getFriend()->isLoaded()
		);
	}

	public function testRelatedRecordWithInexistentUidReturnsRelatedRecordAsGhost() {
		$friendUid = $this->testingFramework->getAutoIncrement('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);

		$this->assertEquals(
			$friendUid,
			$this->fixture->find($uid)->getFriend()->getUid()
		);
	}


	/////////////////////////////////////////////////////////////////////////
	// Tests concerning the m:n mapping with a comma-separated list of UIDs
	/////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function commaSeparatedRelationsWithEmptyStringCreatesEmptyList() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->fixture->find($uid)->getChildren()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function commaSeparatedRelationsWithOneUidReturnsListWithRelatedModel() {
		$childUid = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('children' => $childUid)
		);

		$this->assertEquals(
			(string) $childUid,
			$this->fixture->find($uid)->getChildren()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function commaSeparatedRelationsWithTwoUidsReturnsListWithBothRelatedModels() {
		$childUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$childUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('children' => $childUid1 . ',' . $childUid2)
		);

		$this->assertEquals(
			$childUid1 . ',' . $childUid2,
			$this->fixture->find($uid)->getChildren()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function commaSeparatedRelationsWithOneUidAndZeroIgnoresZero() {
		$childUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('children' => $childUid1 . ',0')
		);

		$this->assertEquals(
			(string) $childUid1,
			$this->fixture->find($uid)->getChildren()->getUids()
		);
	}


	////////////////////////////////////////////////////////
	// Tests concerning the m:n mapping using an m:n table
	////////////////////////////////////////////////////////

	public function testMNRelationsWithEmptyStringCreatesEmptyList() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->fixture->find($uid)->getRelatedRecords()->isEmpty()
		);
	}

	public function testMNRelationsWithOneRelatedModelReturnsListWithRelatedModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid, 'related_records'
		);

		$this->assertEquals(
			(string) $relatedUid,
			$this->fixture->find($uid)->getRelatedRecords()->getUids()
		);
	}

	public function testMNRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid1, 'related_records'
		);
		$relatedUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid2, 'related_records'
		);

		$this->assertEquals(
			$relatedUid1 . ',' . $relatedUid2,
			$this->fixture->find($uid)->getRelatedRecords()->getUids()
		);
	}

	public function testMNRelationsReturnsListSortedBySorting() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid2, 'related_records'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid1, 'related_records'
		);

		$this->assertEquals(
			$relatedUid2 . ',' . $relatedUid1,
			$this->fixture->find($uid)->getRelatedRecords()->getUids()
		);
	}


	///////////////////////////////////////////////////////////////////////
	// Tests concerning the bidirectional m:n mapping using an m:n table.
	///////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function bidirectionalMNRelationsWithEmptyStringCreatesEmptyList() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->fixture->find($uid)->getBidirectional()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function bidirectionalMNRelationsWithOneRelatedModelReturnsListWithRelatedModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $relatedUid, $uid, 'bidirectional'
		);

		$this->assertEquals(
			(string) $uid,
			$this->fixture->find($relatedUid)->getBidirectional()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function bidirectionalMNRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $relatedUid, $uid1, 'bidirectional'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $relatedUid, $uid2, 'bidirectional'
		);

		$this->assertEquals(
			$uid1 . ',' . $uid2,
			$this->fixture->find($relatedUid)->getBidirectional()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function bidirectionalMNRelationsReturnsListSortedByUid() {
		$uid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $relatedUid, $uid1, 'bidirectional'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $relatedUid, $uid2, 'bidirectional'
		);

		$this->assertEquals(
			$uid2 . ',' . $uid1,
			$this->fixture->find($relatedUid)->getBidirectional()->getUids()
		);
	}


	////////////////////////////////////////////////////////////
	// Tests concerning the 1:n mapping using a foreign field.
	////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function oneToManyRelationsWithEmptyStringCreatesEmptyList() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->fixture->find($uid)->getComposition()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function oneToManyRelationsWithOneRelatedModelReturnsListWithRelatedModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('composition' => 1)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $uid)
		);

		$this->assertEquals(
			(string) $relatedUid,
			$this->fixture->find($uid)->getComposition()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function oneToManyRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('composition' => 2)
		);
		$relatedUid1 = $this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $uid, 'title' => 'relation A')
		);
		$relatedUid2 = $this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $uid, 'title' => 'relation B')
		);

		$this->assertEquals(
			$relatedUid1 . ',' . $relatedUid2,
			$this->fixture->find($uid)->getComposition()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function oneToManyRelationsReturnsListSortedByForeignSortBy() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('composition' => 2)
		);
		$relatedUid1 = $this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $uid, 'title' => 'relation B')
		);
		$relatedUid2 = $this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $uid, 'title' => 'relation A')
		);

		$this->assertEquals(
			$relatedUid2 . ',' . $relatedUid1,
			$this->fixture->find($uid)->getComposition()->getUids()
		);
	}


	/////////////////////////////////
	// Tests concerning getNewGhost
	/////////////////////////////////

	public function testGetNewGhostReturnsModel() {
		$this->assertTrue(
			$this->fixture->getNewGhost() instanceof tx_oelib_Model
		);
	}

	public function testGetNewGhostReturnsGhost() {
		$this->assertTrue(
			$this->fixture->getNewGhost()->isGhost()
		);
	}

	public function testGetNewGhostReturnsModelWithUid() {
		$this->assertTrue(
			$this->fixture->getNewGhost()->hasUid()
		);
	}

	public function testGetNewGhostCreatesRegisteredModel() {
		$ghost = $this->fixture->getNewGhost();

		$this->assertSame(
			$ghost,
			$this->fixture->find($ghost->getUid())
		);
	}

	/**
	 * @test
	 */
	public function loadingAGhostCreatedWithGetNewGhostThrowsException() {
		$this->setExpectedException(
			'Exception',
			'This ghost was created via getNewGhost and must not be loaded.'
		);

		$ghost = $this->fixture->getNewGhost();
		$this->fixture->load($ghost);
	}


	///////////////////////////////////////////
	// Tests concerning getLoadedTestingModel
	///////////////////////////////////////////

	public function testGetLoadedTestingModelReturnsModel() {
		$this->fixture->disableDatabaseAccess();

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())
				instanceof tx_oelib_Model
		);
	}

	public function testGetLoadedTestingModelReturnsLoadedModel() {
		$this->fixture->disableDatabaseAccess();

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->isLoaded()
		);
	}

	public function testGetLoadedTestingModelReturnsModelWithUid() {
		$this->fixture->disableDatabaseAccess();

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->hasUid()
		);
	}

	public function testGetLoadedTestingModelCreatesRegisteredModel() {
		$this->fixture->disableDatabaseAccess();
		$model = $this->fixture->getLoadedTestingModel(array());

		$this->assertSame(
			$model,
			$this->fixture->find($model->getUid())
		);
	}

	public function testGetLoadedTestingModelSetsTheProvidedData() {
		$this->fixture->disableDatabaseAccess();

		$model = $this->fixture->getLoadedTestingModel(
			array('title' => 'foo')
		);

		$this->assertEquals(
			'foo',
			$model->getTitle()
		);
	}

	public function testGetLoadedTestingModelCreatesRelations() {
		$this->fixture->disableDatabaseAccess();

		$relatedModel = $this->fixture->getNewGhost();
		$model = $this->fixture->getLoadedTestingModel(
			array('friend' => $relatedModel->getUid())
		);

		$this->assertEquals(
			$relatedModel->getUid(),
			$model->getFriend()->getUid()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning findSingleByWhereClause().
	////////////////////////////////////////////////

	public function testFindSingleByWhereClauseWithEmptyWhereClausePartsThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The parameter $whereClauseParts must not be empty.'
		);

		$this->fixture->findSingleByWhereClause(array());
	}

	public function testFindSingleByWhereClauseWithUidOfInexistentRecordThrowsException() {
		$this->setExpectedException('tx_oelib_Exception_NotFound');

		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$this->fixture->findSingleByWhereClause(
			array('uid' => $uid)
		);
	}

	public function testFindSingleByWhereClauseWithUidOfExistentNotMappedRecordReturnsModelWithTheData() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertEquals(
			'foo',
			$this->fixture->findSingleByWhereClause(
				array('title' => 'foo', 'is_dummy_record' => '1')
			)->getTitle()
		);
	}

	public function testFindSingleByWhereClauseWithUidOfExistentYetMappedRecordReturnsModelWithTheMappedData() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->fixture->find($uid)->setTitle('bar');

		$this->assertEquals(
			'bar',
			$this->fixture->findSingleByWhereClause(
				array('title' => 'foo', 'is_dummy_record' => '1')
			)->getTitle()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning disabled database access
	//////////////////////////////////////////////

	public function testHasDatabaseAccessInitiallyReturnsTrue() {
		$this->assertTrue(
			$this->fixture->hasDatabaseAccess()
		);
	}

	public function testHasDatabaseAccessAfterDisableDatabaseAccessReturnsFalse() {
		$this->fixture->disableDatabaseAccess();

		$this->assertFalse(
			$this->fixture->hasDatabaseAccess()
		);
	}

	public function testLoadWithUidOfRecordInDatabaseAndDatabaseAccessDisabledMarksModelAsDead() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->disableDatabaseAccess();
		$this->fixture->load($this->fixture->find($uid));

		$this->assertTrue(
			$this->fixture->find($uid)->isDead()
		);
	}

	public function testLoadWithUidOfRecordNotInDatabaseAndDatabaseAccessDisabledMarksModelAsDead() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$this->fixture->disableDatabaseAccess();
		$this->fixture->load($this->fixture->find($uid));

		$this->assertTrue(
			$this->fixture->find($uid)->isDead()
		);
	}

	public function testFindSingleByWhereClauseAndDatabaseAccessDisabledThrowsException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'No record can be retrieved from the database because database ' .
				'access is disabled for this mapper instance.'
		);

		$this->fixture->disableDatabaseAccess();
		$this->fixture->findSingleByWhereClause(array('title' => 'foo'));
	}


	////////////////////////////
	// Tests concerning save()
	////////////////////////////

	public function testSaveForReadOnlyModelDoesNotCommitModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->setModelClassName('tx_oelib_tests_fixtures_ReadOnlyModel');
		$this->fixture->save($this->fixture->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "foo" AND tstamp = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	public function testSaveForDatabaseAccessDeniedDoesNotCommitDirtyLoadedModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->disableDatabaseAccess();
		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->save($this->fixture->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	public function testSaveForGhostDoesNotCommitModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->save($this->fixture->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'tstamp > 0'
			)
		);
	}

	public function testSaveForDeadModelDoesNotCommitDirtyModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->find($uid)->markAsDead();
		$this->fixture->save($this->fixture->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	public function testSaveForCleanLoadedModelDoesNotCommitModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->find($uid)->markAsClean();
		$this->fixture->save($this->fixture->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	public function testSaveForDirtyLoadedModelWithUidCommitsModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	public function testSaveForDirtyLoadedModelWithUidDoesNotChangeTheUid() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$model = $this->fixture->find($uid);
		$model->setTitle('bar');
		$this->fixture->save($model);

		$this->assertEquals(
			$uid,
			$model->getUid()
		);
	}

	public function testSaveForDirtyLoadedModelWithUidSetsTimestamp() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "bar" AND tstamp = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	public function testSaveForDirtyLoadedModelWithUidAndWithoutDataCommitsModelToDatabase() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid($uid);
		$model->setData(array());
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'tstamp = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	public function testSaveNewModelFromMemoryAndMapperInTestingModeMarksModelAsDummyModel() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setData(array('title' => 'foo'));
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsExactlyOneRecord(
				'tx_oelib_test', 'title = "foo"'
			)
		);
	}

	public function testSaveNewModelFromMemoryRegistersModelInMapper() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setData(array('title' => 'foo'));
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertSame(
			$model,
			$this->fixture->find($model->getUid())
		);
	}

	public function testIsDirtyAfterSaveForDirtyLoadedModelWithUidReturnsFalse() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->save($this->fixture->find($uid));

		$this->assertFalse(
			$this->fixture->find($uid)->isDirty()
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidAndWithoutRelationsCommitsModelToDatabase() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setData(array('is_dummy_record' => '1', 'title' => 'bar'));
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidAndWithRelationsCommitsModelToDatabase() {
		$model = new tx_oelib_tests_fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidAddsModelToMapAfterSave() {
		$model = new tx_oelib_tests_fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertSame(
			$model,
			$this->fixture->find($model->getUid())
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidSetsUidForModel() {
		$model = new tx_oelib_tests_fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertTrue(
			$model->hasUid()
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidSetsUidReceivedFromDatabaseForModel() {
		$model = new tx_oelib_tests_fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'uid = ' . $model->getUid()
			)
		);
	}

	public function testIsDirtyAfterSaveForDirtyLoadedModelWithoutUidReturnsFalse() {
		$model = new tx_oelib_tests_fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertFalse(
			$model->isDirty()
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidSetsTimestamp() {
		$model = new tx_oelib_tests_fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "bar" AND tstamp = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidSetsCrdate() {
		$model = new tx_oelib_tests_fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "bar" AND crdate = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	public function testSaveForDirtyLoadedModelWithNoDataDoesNotCommitModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "foo" AND tstamp > 0'
			)
		);

		$model = $this->fixture->find($uid);
		$model->markAsDirty();
		$this->fixture->save($model);

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "foo" AND tstamp > 0'
			)
		);
	}

	public function testIsDeadAfterSaveForDirtyLoadedModelWithDeletedFlagSetReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->find($uid)->setToDeleted();
		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->fixture->find($uid)->isDead()
		);
	}

	public function testSaveForModelWithN1RelationSavesUidOfRelatedRecord() {
		$friendUid = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);
		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar" AND friend = ' . $friendUid
			)
		);
	}

	public function testSaveForModelWithMNCommaSeparatedRelationSavesUidList() {
		$childUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$childUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('children' => $childUid1 . ',' . $childUid2)
		);
		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "bar" AND children = "' . $childUid1 . ',' . $childUid2 . '"'
			)
		);
	}

	public function testSaveForModelWithMNTableRelationSavesNumberOfRelatedRecords() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid1, 'related_records'
		);
		$relatedUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid2, 'related_records'
		);

		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar" AND related_records = 2'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithOneToManyRelationSavesNumberOfRelatedRecords() {
		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->setTitle('bar');

		$composition = $model->getComposition();
		$mapper = tx_oelib_MapperRegistry::
			get('tx_oelib_tests_fixtures_TestingChildMapper');
		$composition->add($mapper->find(
			$this->testingFramework->createRecord('tx_oelib_testchild')
		));
		$composition->add($mapper->find(
			$this->testingFramework->createRecord('tx_oelib_testchild')
		));

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar" AND composition = 2'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithOneToManyRelationSavesDirtyRelatedRecord() {
		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->setTitle('bar');

		$composition = $model->getComposition();
		$mapper = tx_oelib_MapperRegistry::
			get('tx_oelib_tests_fixtures_TestingChildMapper');
		$component = $mapper->find(
			$this->testingFramework->createRecord('tx_oelib_testchild')
		);
		$composition->add($component);

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_testchild',
				'uid = ' . $component->getUid() .
					' AND parent = ' . $model->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWith1NRelationSavesNewRelatedRecord() {
		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->setTitle('bar');

		$composition = $model->getComposition();
		$mapper = tx_oelib_MapperRegistry::
			get('tx_oelib_tests_fixtures_TestingChildMapper');
		$component = new tx_oelib_tests_fixtures_TestingChildModel();
		$component->markAsDummyModel();
		$composition->add($component);

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_testchild',
				'uid = ' . $component->getUid() .
					' AND parent = ' . $model->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithOneToManyRelationDeletesUnconnectedRecord() {
		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->markAsDirty();

		$composition = $model->getComposition();
		$mapper = tx_oelib_MapperRegistry::
			get('tx_oelib_tests_fixtures_TestingChildMapper');
		$component1 = $mapper->find(
			$this->testingFramework->createRecord(
				'tx_oelib_testchild', array('parent' => $model->getUid())
			)
		);
		$composition->add($component1);
		$component2 = $mapper->find(
			$this->testingFramework->createRecord(
				'tx_oelib_testchild', array('parent' => $model->getUid())
			)
		);

		$this->fixture->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_testchild',
				'uid = ' . $component2->getUid() . ' AND deleted = 1'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithN1RelationSavesDirtyRelatedRecord() {
		$friendUid = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);
		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->find($friendUid)->setTitle('foo');

		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "foo" AND uid = ' . $friendUid
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithN1RelationSavesNewRelatedRecord() {
		$friend = new tx_oelib_tests_fixtures_TestingModel();
		$friend->markAsDummyModel();
		$friend->setTitle('foo');

		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->fixture->find($uid)->setFriend($friend);

		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'uid = ' . $friend->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithMNCommaSeparatedRelationSavesDirtyRelatedRecord() {
		$childUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$childUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('children' => $childUid1 . ',' . $childUid2)
		);
		$this->fixture->find($uid)->setTitle('bar');
		$this->fixture->find($childUid1)->setTitle('foo');

		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "foo" AND uid = ' . $childUid1
			)
		);
	}

	/**
	 * @test
	 */
	public function addModelToListMarksParentModelAsDirty() {
		$parentUid = $this->testingFramework->createRecord('tx_oelib_test');

		$parent = $this->fixture->find($parentUid);
		$child = $this->fixture->getNewGhost();

		$parent->getChildren()->add($child);

		$this->assertTrue(
			$parent->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function appendListMarksParentModelAsDirty() {
		$parentUid = $this->testingFramework->createRecord('tx_oelib_test');

		$parent = $this->fixture->find($parentUid);
		$child = $this->fixture->getNewGhost();
		$list = new tx_oelib_List();
		$list->add($child);

		$parent->getChildren()->append($list);

		$this->assertTrue(
			$parent->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function appendListUniqueMarksParentModelAsDirty() {
		$parentUid = $this->testingFramework->createRecord('tx_oelib_test');

		$parent = $this->fixture->find($parentUid);
		$child = $this->fixture->getNewGhost();
		$list = new tx_oelib_List();
		$list->add($child);

		$parent->getChildren()->appendUnique($list);

		$this->assertTrue(
			$parent->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function purgeModelFromListMarksModelAsDirty() {
		$parentUid = $this->testingFramework->createRecord('tx_oelib_test');

		$parent = $this->fixture->find($parentUid);
		$child = $this->fixture->getNewGhost();
		$parent->getChildren()->add($child);
		$parent->getChildren()->rewind();

		$parent->getChildren()->purgeCurrent();

		$this->assertTrue(
			$parent->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithMNTableRelationCreatesIntermediateRelationRecord() {
		$parentUid = $this->testingFramework->createRecord('tx_oelib_test');
		$childUid = $this->testingFramework->createRecord('tx_oelib_test');

		$parent = $this->fixture->find($parentUid);
		$child = $this->fixture->find($childUid);

		$parent->getRelatedRecords()->add($child);
		$this->fixture->save($parent);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test_article_mm',
				'uid_local=' . $parentUid . ' AND uid_foreign=' . $childUid .
					' AND sorting=0'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithMNTableRelationsCreatesIntermediateRelationRecordAndIncrementsSorting() {
		$parentUid = $this->testingFramework->createRecord('tx_oelib_test');
		$childUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$childUid2 = $this->testingFramework->createRecord('tx_oelib_test');

		$parent = $this->fixture->find($parentUid);
		$child1 = $this->fixture->find($childUid1);
		$child2 = $this->fixture->find($childUid2);

		$parent->getRelatedRecords()->add($child1);
		$parent->getRelatedRecords()->add($child2);
		$this->fixture->save($parent);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test_article_mm',
				'uid_local=' . $parentUid . ' AND uid_foreign=' . $childUid2 .
					 ' AND sorting=1'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithBidirectionalMNRelationCreatesIntermediateRelationRecord() {
		$parentUid = $this->testingFramework->createRecord('tx_oelib_test');
		$childUid = $this->testingFramework->createRecord('tx_oelib_test');

		$parent = $this->fixture->find($parentUid);
		$child = $this->fixture->find($childUid);

		$child->getBidirectional()->add($parent);
		$this->fixture->save($child);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test_article_mm',
				'uid_local=' . $parentUid . ' AND uid_foreign=' . $childUid .
					' AND sorting=0'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithBidirectionalMNRelationCreatesIntermediateRelationRecordAndIncrementsSorting() {
		$parentUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$parentUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$childUid = $this->testingFramework->createRecord('tx_oelib_test');

		$parent1 = $this->fixture->find($parentUid1);
		$parent2 = $this->fixture->find($parentUid2);
		$child = $this->fixture->find($childUid);

		$child->getBidirectional()->add($parent1);
		$child->getBidirectional()->add($parent2);
		$this->fixture->save($child);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test_article_mm',
				'uid_local=' . $parentUid2 . ' AND uid_foreign=' . $childUid .
					' AND sorting=1'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForGhostFromGetNewGhostThrowsException() {
		$this->setExpectedException(
			'Exception',
			'This model is a memory-only dummy that must not be saved.'
		);

		$model = $this->fixture->getNewGhost();
		$this->fixture->save($model);
	}

	/**
	 * @test
	 */
	public function saveForLoadedTestingModelWithUidThrowsException() {
		$this->setExpectedException(
			'Exception',
			'This model is a memory-only dummy that must not be saved.'
		);

		$model = $this->fixture->getLoadedTestingModel(
			array('uid' => 42, 'title' => 'foo')
		);
		$this->fixture->save($model);
	}

	/**
	 * @test
	 */
	public function saveForLoadedTestingModelWithoutUidThrowsException() {
		$this->setExpectedException(
			'Exception',
			'This model is a memory-only dummy that must not be saved.'
		);

		$model = $this->fixture->getLoadedTestingModel(
			array('title' => 'foo')
		);
		$this->fixture->save($model);
	}


	/////////////////////////////
	// Tests concerning findAll
	/////////////////////////////

	/**
	 * @test
	 */
	public function findAllForNoRecordsReturnsEmptyList() {
		$this->assertTrue(
			$this->fixture->findAll()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllForOneRecordInDatabaseReturnsOneRecord() {
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			1,
			$this->fixture->findAll()->count()
		);
	}

	/**
	 * @test
	 */
	public function findAllForTwoRecordsInDatabaseReturnsTwoRecords() {
		$this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			2,
			$this->fixture->findAll()->count()
		);
	}

	/**
	 * @test
	 */
	public function findAllForOneRecordInDatabaseReturnsLoadedRecord() {
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->fixture->findAll()->first()->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function findAllIgnoresHiddenRecord() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);

		$this->assertTrue(
			$this->fixture->findAll()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllIgnoresDeletedRecord() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('deleted' => 1)
		);

		$this->assertTrue(
			$this->fixture->findAll()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllCanBeUsedForStaticTables() {
		tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->findAll();
	}

	/**
	 * @test
	 */
	public function findAllSortsRecordsBySorting() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid2 = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			min($uid1, $uid2),
			$this->fixture->findAll()->first()->getUid()
		);
	}

	public function test_findAllForGivenSortParameter_OverridesDefaultSorting() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'record a')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'record b')
		);

		$this->assertEquals(
			$uid,
			$this->fixture->findAll('title')->first()->getUid()
		);
	}

	public function test_findAllForGivenSortParameterWithSortDirection_SortsResultsBySortdirection() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'record b')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'record a')
		);

		$this->assertEquals(
			$uid,
			$this->fixture->findAll('title DESC')->first()->getUid()
		);
	}

	public function test_findAllForGivenSortParameter_FindsMultipleEntries() {
		$this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			2,
			$this->fixture->findAll('title ASC')->count()
		);
	}


	///////////////////////////////////////
	// Tests concerning findByWhereClause
	///////////////////////////////////////

	public function test_findByWhereClauseForNoGivenParameterAndTwoRecords_FindsBothRecords() {
		$this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			2,
			$this->fixture->findByWhereClause()->count()
		);
	}

	public function test_findByWhereClauseForGivenWhereClauseAndOneMatchingRecord_FindsThisRecord() {
		$foundRecordUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertEquals(
			$foundRecordUid,
			$this->fixture->findByWhereClause('title like "foo"')->first()->getUid()
		);
	}

	public function test_findByWhereClauseForGivenWhereClauseAndTwoRecordsOneMatchingOneNot_DoesNotFindNonMatchingRecord() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$notMatchingUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);

		$this->assertNotEquals(
			$notMatchingUid,
			$this->fixture->findByWhereClause('title like "foo"')->first()->getUid()
		);
	}

	public function test_findByWhereClauseForNoSortingProvided_SortsRecordsByDefaultSorting() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid2 = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			min($uid1, $uid2),
			$this->fixture->findByWhereClause()->first()->getUid()
		);
	}

	public function test_findByWhereClauseForSortingProvided_SortsRecordsByGivenSorting() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$firstEntryUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);

		$this->assertEquals(
			$firstEntryUid,
			$this->fixture->findByWhereClause('','title ASC')->first()->getUid()
		);
	}

	public function test_findByWhereClauseForSortingAndWhereClauseProvided_SortsMatchingRecords() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo', 'sorting' => 2)
		);
		$firstMatchingUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo', 'sorting' => 0)
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar', 'sorting' => 1)
		);

		$this->assertEquals(
			$firstMatchingUid,
			$this->fixture->findByWhereClause('title like "foo"','sorting ASC')
				->first()->getUid()
		);
	}


	///////////////////////////////////
	// Tests concerning findByPageUId
	///////////////////////////////////

	public function test_findByPageUid_ForPageUidZero_ReturnsEntryWithZeroPageUid() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			$uid,
			$this->fixture->findByPageUid(0)->first()->getUid()
		);
	}

	public function test_findByPageUid_ForPageUidZero_ReturnsEntryWithNonZeroPageUid() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 42)
		);

		$this->assertEquals(
			$uid,
			$this->fixture->findByPageUid(0)->first()->getUid()
		);
	}

	public function test_findByPageUid_ForPageUidEmpty_ReturnsRecordWithNonZeroPageUid() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 42)
		);

		$this->assertEquals(
			$uid,
			$this->fixture->findByPageUid('')->first()->getUid()
		);
	}

	public function test_findByPageUid_ForNonZeroPageUid_ReturnsEntryFromThatPage() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 1)
		);

		$this->assertEquals(
			$uid,
			$this->fixture->findByPageUid(1)->first()->getUid()
		);
	}

	public function test_findByPageUid_ForNonZeroPageUid_DoesNotReturnEntryWithDifferentPageUId() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 2)
		);

		$this->assertTrue(
			$this->fixture->findByPageUid(1)->isEmpty()
		);
	}

	public function test_findByPageUid_ForPageUidAndSortingGiven_ReturnEntrySortedBySorting() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 2, 'sorting' => 3)
		);

		$firstMatchingRecord = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 2, 'sorting' => 1)
		);

		$this->assertEquals(
			$firstMatchingRecord,
			$this->fixture->findByPageUid(2, 'sorting ASC')->first()->getUid()
		);
	}

	public function test_findByPageUid_ForTwoNonZeroPageUids_CanReturnRecordFromFirstPage() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 1)
		);

		$this->assertEquals(
			$uid,
			$this->fixture->findByPageUid('1,2')->first()->getUid()
		);
	}

	public function test_findByPageUid_ForTwoNonZeroPageUids_CanReturnRecordFromSecondPage() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 2)
		);

		$this->assertEquals(
			$uid,
			$this->fixture->findByPageUid('1,2')->first()->getUid()
		);
	}


	/////////////////////////////////////
	// Tests concerning additional keys
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function findOneByKeyFromCacheForEmptyKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '$key must not be empty.'
		);

		$this->fixture->findOneByKeyFromCache('', 'bar');
	}

	/**
	 * @test
	 */
	public function findOneByKeyFromCacheForInexistentKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '"foo" is not a valid key for this mapper.'
		);

		$this->fixture->findOneByKeyFromCache('foo', 'bar');
	}

	/**
	 * @test
	 */
	public function findOneByKeyFromCacheForEmptyValueThrowsException() {
		$this->setExpectedException(
			'Exception', '$value must not be empty.'
		);

		$this->fixture->findOneByKeyFromCache('title', '');
	}

	/**
	 * @test
	 */
	public function findOneByKeyFromCacheForModelNotInCacheThrowsException() {
		$this->setExpectedException('tx_oelib_Exception_NotFound');

		$this->fixture->findOneByKeyFromCache('title', 'bar');
	}

	/**
	 * @test
	 */
	public function findByKeyFindsLoadedModel() {
		$model = $this->fixture->getLoadedTestingModel(
			array('title' => 'Earl Grey')
		);

		$this->assertSame(
			$model,
			$this->fixture->findOneByKeyFromCache('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findByKeyFindsLastLoadedModelWithSameKey() {
		$this->fixture->getLoadedTestingModel(
			array('title' => 'Earl Grey')
		);
		$model = $this->fixture->getLoadedTestingModel(
			array('title' => 'Earl Grey')
		);

		$this->assertSame(
			$model,
			$this->fixture->findOneByKeyFromCache('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findByKeyFindsSavedModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$model = $this->fixture->find($uid);
		$model->setTitle('Earl Grey');
		$this->fixture->save($model);

		$this->assertSame(
			$model,
			$this->fixture->findOneByKeyFromCache('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findByKeyFindsLastSavedModelWithSameKey() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$model1 = $this->fixture->find($uid1);
		$model1->setTitle('Earl Grey');
		$this->fixture->save($model1);

		$uid2 = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Earl Grey')
		);
		$model2 = $this->fixture->find($uid2);
		$model2->setTitle('Earl Grey');
		$this->fixture->save($model2);

		$this->assertSame(
			$model2,
			$this->fixture->findOneByKeyFromCache('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findOneByKeyForEmptyKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '$key must not be empty.'
		);

		$this->fixture->findOneByKey('', 'bar');
	}

	/**
	 * @test
	 */
	public function findOneByKeyForInexistentKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '"foo" is not a valid key for this mapper.'
		);

		$this->fixture->findOneByKey('foo', 'bar');
	}

	/**
	 * @test
	 */
	public function findOneByKeyForEmptyValueThrowsException() {
		$this->setExpectedException(
			'Exception', '$value must not be empty.'
		);

		$this->fixture->findOneByKey('title', '');
	}

	/**
	 * @test
	 */
	public function findOneByKeyCanFindModelFromCache() {
		$model = $this->fixture->getLoadedTestingModel(
			array('title' => 'Earl Grey')
		);

		$this->assertSame(
			$model,
			$this->fixture->findOneByKey('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findOneByKeyCanLoadModelFromDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Earl Grey')
		);

		$this->assertEquals(
			$uid,
			$this->fixture->findOneByKey('title', 'Earl Grey')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findOneByKeyForInexistentThrowsException() {
		$this->setExpectedException('tx_oelib_Exception_NotFound');

		$this->fixture->findOneByKey('title', 'Darjeeling');
	}


	////////////////////////////
	// Tests concerning delete
	////////////////////////////

	/**
	 * @test
	 */
	public function deleteForDeadModelDoesNotThrowException() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->markAsDead();

		$this->fixture->delete($model);
	}

	/**
	 * @test
	 */
	public function deleteForModelWithoutUidMarksModelAsDead() {
		$model = new tx_oelib_tests_fixtures_TestingModel();

		$this->fixture->delete($model);

		$this->assertTrue(
			$model->isDead()
		);
	}

	/**
	 * @test
	 */
	public function deleteForModelWithUidMarksModelAsDead() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array()
		);
		$model = $this->fixture->find($uid);

		$this->fixture->delete($model);

		$this->assertTrue(
			$model->isDead()
		);
	}

	/**
	 * @test
	 */
	public function deleteForGhostFromGetNewGhostThrowsException() {
		$this->setExpectedException(
			'Exception',
			'This model is a memory-only dummy that must not be deleted.'
		);

		$model = $this->fixture->getNewGhost();
		$this->fixture->delete($model);
	}


	/**
	 * @test
	 */
	public function deleteForReadOnlyModelThrowsException() {
		$this->setExpectedException(
			'Exception', 'This model is read-only and must not be deleted.'
		);

		$model = new tx_oelib_tests_fixtures_ReadOnlyModel();
		$this->fixture->delete($model);
	}

	/**
	 * @test
	 */
	public function deleteForModelWithUidWritesModelAsDeletedToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array()
		);
		$model = $this->fixture->find($uid);

		$this->fixture->delete($model);

		$this->assertTrue(
			$this->testingFramework->existsExactlyOneRecord(
				'tx_oelib_test', 'uid = ' . $uid . ' AND deleted = 1'
			)
		);
	}

	/**
	 * @test
	 */
	public function deleteForModelWithUidStillKeepsModelAccessibleViaDataMapper() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$model = $this->fixture->find($uid);

		$this->fixture->delete($model);

		$this->assertSame(
			$model,
			$this->fixture->find($uid)
		);
	}

	/**
	 * @test
	 */
	public function deleteForModelWithOneToManyRelationDeletesRelatedElements() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('composition' => 1)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $uid)
		);

		$this->fixture->delete($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsExactlyOneRecord(
				'tx_oelib_testchild',
				'uid = ' . $relatedUid . ' AND deleted = 1'
			)
		);
	}

	/**
	 * @test
	 */
	public function deleteForDirtyModelWithOneToManyRelationToDirtyElementDoesNotCrash() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('composition' => 1)
		);
		$this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $uid)
		);

		$model = $this->fixture->find($uid);
		$relatedModel = $model->getComposition()->first();

		$model->setTitle('foo');
		$relatedModel->setTitle('bar');

		$this->fixture->delete($model);
	}


	///////////////////////////////////////
	// Tests concerning findAllByRelation
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function findAllByRelationWithModelWithoutUidThrowsException() {
		$this->setExpectedException(
			'Exception', '$model must have a UID.'
		);

		$model = new tx_oelib_tests_fixtures_TestingModel();

		tx_oelib_MapperRegistry
			::get('tx_oelib_tests_fixtures_TestingChildMapper')
			->findAllByRelation($model, 'parent');
	}

	/**
	 * @test
	 */
	public function findAllByRelationWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '$key must not be empty'
		);

		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);

		tx_oelib_MapperRegistry
			::get('tx_oelib_tests_fixtures_TestingChildMapper')
			->findAllByRelation($model, '');
	}

	/**
	 * @test
	 */
	public function findAllByRelationForNoMatchesReturnsEmptyList() {
		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);

		$mapper = tx_oelib_MapperRegistry::
			get('tx_oelib_tests_fixtures_TestingChildMapper');
		$this->assertTrue(
			$mapper->findAllByRelation($model, 'parent')->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllByRelationNotReturnsNotMatchingRecords() {
		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$anotherModel = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $anotherModel->getUid())
		);

		$mapper = tx_oelib_MapperRegistry::
			get('tx_oelib_tests_fixtures_TestingChildMapper');
		$this->assertTrue(
			$mapper->findAllByRelation($model, 'parent')->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllByRelationCanReturnOneMatch() {
		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$mapper = tx_oelib_MapperRegistry::
			get('tx_oelib_tests_fixtures_TestingChildMapper');
		$relatedModel = $mapper->find(
			$this->testingFramework->createRecord(
				'tx_oelib_testchild', array('parent' => $model->getUid())
			)
		);

		$result = $mapper->findAllByRelation($model, 'parent');
		$this->assertEquals(
			1,
			$result->count()
		);
		$this->assertSame(
			$relatedModel,
			$result->first()
		);
	}

	/**
	 * @test
	 */
	public function findAllByRelationCanReturnTwoMatches() {
		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $model->getUid())
		);
		$this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $model->getUid())
		);

		$result = tx_oelib_MapperRegistry
			::get('tx_oelib_tests_fixtures_TestingChildMapper')
			->findAllByRelation($model, 'parent');
		$this->assertEquals(
			2,
			$result->count()
		);
	}

	/**
	 * @test
	 */
	public function findAllByRelationIgnoresIgnoreList() {
		$model = $this->fixture->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$mapper = tx_oelib_MapperRegistry::
			get('tx_oelib_tests_fixtures_TestingChildMapper');
		$relatedModel = $mapper->find(
			$this->testingFramework->createRecord(
				'tx_oelib_testchild', array('parent' => $model->getUid())
			)
		);
		$ignoredRelatedModel = $mapper->find(
			$this->testingFramework->createRecord(
				'tx_oelib_testchild', array('parent' => $model->getUid())
			)
		);

		$ignoreList = tx_oelib_ObjectFactory::make('tx_oelib_List');
		$ignoreList->add($ignoredRelatedModel);

		$result = tx_oelib_MapperRegistry
			::get('tx_oelib_tests_fixtures_TestingChildMapper')
			->findAllByRelation($model, 'parent', $ignoreList);
		$this->assertEquals(
			1,
			$result->count()
		);
		$this->assertSame(
			$relatedModel,
			$result->first()
		);
	}
}
?>