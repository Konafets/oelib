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

		$this->assertEquals(
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

	public function testGetNewGhostCreatesRegisteredGhost() {
		$ghost = $this->fixture->getNewGhost();

		$this->assertSame(
			$ghost,
			$this->fixture->find($ghost->getUid())
		);
	}


	////////////////////////////////////////////////
	// Tests concerning findSingleByWhereClause().
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function findSingleByWhereClauseWithEmptyWhereClauseThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $whereClause must not be empty.'
		);

		$this->fixture->brokenFindSingleByWhereClause();
	}
}
?>