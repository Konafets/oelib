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

	public function testCommaSeparatedRelationsWithEmptyStringCreatesEmptyList() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->fixture->find($uid)->getChildren()->isEmpty()
		);
	}

	public function testCommaSeparatedRelationsWithOneUidReturnsListWithRelatedModel() {
		$childUid = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('children' => $childUid)
		);

		$this->assertEquals(
			(string) $childUid,
			$this->fixture->find($uid)->getChildren()->getUids()
		);
	}

	public function testCommaSeparatedRelationsWithTwoUidsReturnsListWithBothRelatedModels() {
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
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$this->setExpectedException(
			'Exception',
			'The record where "uid = ' . $uid . '" could not be retrieved' .
				' from the table tx_oelib_test.'
		);

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
		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data);

		$model = new tx_oelib_tests_fixtures_TestingModel();
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
		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertSame(
			$model,
			$this->fixture->find($model->getUid())
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidSetsUidForModel() {
		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertTrue(
			$model->hasUid()
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidSetsUidReceivedFromDatabaseForModel() {
		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data);

		$model = new tx_oelib_tests_fixtures_TestingModel();
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
		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setData($data);
		$model->markAsDirty();

		$this->fixture->save($model);

		$this->assertFalse(
			$model->isDirty()
		);
	}

	public function testSaveForDirtyLoadedModelWithoutUidSetsTimestamp() {
		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data);

		$model = new tx_oelib_tests_fixtures_TestingModel();
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
		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->fixture->createRelations($data);

		$model = new tx_oelib_tests_fixtures_TestingModel();
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
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->fixture->find($uid)->setTitle('bar');

		$mapper = new tx_oelib_tests_fixtures_TestingChildMapper();
		$this->fixture->find($uid)->getComposition()->add($mapper->getNewGhost());
		$this->fixture->find($uid)->getComposition()->add($mapper->getNewGhost());

		$this->fixture->save($this->fixture->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar" AND composition = 2'
			)
		);
	}
}
?>