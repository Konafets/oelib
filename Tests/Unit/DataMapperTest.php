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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_DataMapperTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;
	/**
	 * @var tx_oelib_Tests_Unit_Fixtures_TestingMapper
	 */
	protected $subject = NULL;

	/**
	 * @var bool
	 */
	protected $deprecationLogEnabledBackup = FALSE;

	public function setUp() {
		$this->deprecationLogEnabledBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];

		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

		$this->subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper');
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		Tx_Oelib_MapperRegistry::purgeInstance();
		unset($this->subject, $this->testingFramework);

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = $this->deprecationLogEnabledBackup;
	}


	///////////////////////////////////////
	// Tests concerning the instantiation
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function instantiationOfSubclassWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'Tx_Oelib_Tests_Unit_Fixtures_TableLessTestingMapper::tableName must not be empty.'
		);

		new Tx_Oelib_Tests_Unit_Fixtures_TableLessTestingMapper();
	}

	/**
	 * @test
	 */
	public function instantiationOfSubclassWithEmptyColumnListThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'Tx_Oelib_Tests_Unit_Fixtures_ColumnLessTestingMapper::columns must not be empty.'
		);

		new Tx_Oelib_Tests_Unit_Fixtures_ColumnLessTestingMapper();
	}

	/**
	 * @test
	 */
	public function instantiationOfSubclassWithEmptyModelNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'Tx_Oelib_Tests_Unit_Fixtures_ModelLessTestingMapper::modelClassName must not be empty.'
		);

		new Tx_Oelib_Tests_Unit_Fixtures_ModelLessTestingMapper();
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithZeroUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->subject->find(0);
	}

	/**
	 * @test
	 */
	public function findWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->subject->find(-1);
	}

	/**
	 * @test
	 */
	public function findWithUidOfCachedModelReturnsThatModel() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(1);

		$map = new Tx_Oelib_IdentityMap();
		$map->add($model);
		$this->subject->setMap($map);

		$this->assertSame(
			$model,
			$this->subject->find(1)
		);
	}

	/**
	 * @test
	 */
	public function findWithUidReturnsModelWithThatUid() {
		$uid = 42;

		$this->assertSame(
			$uid,
			$this->subject->find($uid)->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsModelDataFromDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertSame(
			'foo',
			$this->subject->find($uid)->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function findWithUidCalledTwoTimesReturnsSameModel() {
		$uid = 42;

		$this->assertSame(
			$this->subject->find($uid),
			$this->subject->find($uid)
		);
	}


	//////////////////////////////
	// Tests concerning getModel
	//////////////////////////////

	/**
	 * @test
	 */
	public function getModelWithArrayWithoutUidElementProvidedThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$data must contain an element "uid".'
		);

		$this->subject->getModel(array());
	}

	/**
	 * @test
	 */
	public function getModelForNonMappedUidReturnsModelInstance() {
		$this->assertTrue(
			$this->subject->getModel(array('uid' => 2))
				instanceof Tx_Oelib_Model
		);
	}

	/**
	 * @test
	 */
	public function getModelForNonMappedUidReturnsLoadedModel() {
		$this->assertTrue(
			$this->subject->getModel(array('uid' => 2))->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function getModelForMappedUidOfGhostReturnsModelInstance() {
		$mappedUid = $this->subject->getNewGhost()->getUid();

		$this->assertTrue(
			$this->subject->getModel(array('uid' => $mappedUid))
				instanceof Tx_Oelib_Model
		);
	}

	/**
	 * @test
	 */
	public function getModelForMappedUidOfGhostReturnsLoadedModel() {
		$mappedUid = $this->subject->getNewGhost()->getUid();

		$this->assertTrue(
			$this->subject->getModel(array('uid' => $mappedUid))->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function getModelForMappedUidOfGhostReturnsLoadedModelWithTheProvidedData() {
		$mappedModel = $this->subject->getNewGhost();

		$this->assertSame(
			'new title',
			$this->subject->getModel(
				array('uid' => $mappedModel->getUid(), 'title' => 'new title')
			)->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getModelForMappedUidOfGhostReturnsThatModel() {
		$mappedModel = $this->subject->getNewGhost();

		$this->assertSame(
			$mappedModel,
			$this->subject->getModel(array('uid' => $mappedModel->getUid()))
		);
	}

	/**
	 * @test
	 */
	public function getModelForMappedUidOfLoadedModelReturnsThatModelInstance() {
		$mappedModel = $this->subject->getNewGhost();
		$mappedModel->setData(array('title' => 'foo'));

		$this->assertSame(
			$mappedModel,
			$this->subject->getModel(array('uid' => $mappedModel->getUid()))
		);
	}

	/**
	 * @test
	 */
	public function getModelForMappedUidOfLoadedModelAndNoNewDataProvidedReturnsModelWithTheInitialData() {
		$mappedModel = $this->subject->getNewGhost();
		$mappedModel->setData(array('title' => 'foo'));

		$this->assertSame(
			'foo',
			$this->subject->getModel(array('uid' => $mappedModel->getUid()))->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getModelForMappedUidOfLoadedModelAndNewDataProvidedReturnsModelWithTheInitialData() {
		$mappedModel = $this->subject->getNewGhost();
		$mappedModel->setData(array('title' => 'foo'));

		$this->assertSame(
			'foo',
			$this->subject->getModel(
				array('uid' => $mappedModel->getUid(), 'title' => 'new title')
			)->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getModelForMappedUidOfDeadModelReturnsDeadModel() {
		$mappedModel = $this->subject->getNewGhost();
		$mappedModel->markAsDead();

		$this->assertTrue(
			$this->subject->getModel(array('uid' => $mappedModel->getUid()))->isDead()
		);
	}

	/**
	 * @test
	 */
	public function getModelForNonMappedUidReturnsModelWithChildrenList() {
		$this->assertTrue(
			$this->subject->getModel(array('uid' => 2))->getChildren()
				instanceof Tx_Oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getModelSavesModelToCacheByKeys() {
		$model = $this->subject->getModel(array('uid' => 2));

		$this->assertSame(
			array($model),
			$this->subject->getCachedModels()
		);
	}


	/////////////////////////////////////
	// Tests concerning getListOfModels
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getListOfModelsReturnsInstanceOfList() {
		$this->assertTrue(
			$this->subject->getListOfModels(array(array('uid' => 1)))
				instanceof Tx_Oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getListOfModelsForAnEmptyArrayProvidedReturnsEmptyList() {
		$this->assertTrue(
			$this->subject->getListOfModels(array())->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getListOfModelsForOneRecordsProvidedReturnsListWithOneElement() {
		$this->assertSame(
			1,
			$this->subject->getListOfModels(array(array('uid' => 1)))->count()
		);
	}

	/**
	 * @test
	 */
	public function getListOfModelsForTwoRecordsProvidedReturnsListWithTwoElements() {
		$this->assertSame(
			2,
			$this->subject->getListOfModels(array(array('uid' => 1), array('uid' => 2)))->count()
		);
	}

	/**
	 * @test
	 */
	public function getListOfModelsReturnsListOfModelInstances() {
		$this->assertTrue(
			$this->subject->getListOfModels(array(array('uid' => 1)))->current()
				instanceof Tx_Oelib_Model
		);
	}

	/**
	 * @test
	 */
	public function getListOfModelsReturnsListOfModelWithProvidedTitel() {
		$this->assertSame(
			'foo',
			$this->subject->getListOfModels(array(array('uid' => 1, 'title' => 'foo')))
				->current()->getTitle()
		);
	}


	//////////////////////////
	// Tests concerning load
	//////////////////////////

	/**
	 * @test
	 */
	public function loadWithModelWithoutUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'load must only be called with models that already have a UID.'
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->load($model);
	}

	/**
	 * @test
	 */
	public function loadWithModelWithExistingUidFillsModelWithData() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$this->subject->load($model);

		$this->assertSame(
			'foo',
			$model->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function loadWithModelWithExistingUidOfHiddenRecordMarksModelAsLoaded() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$this->subject->load($model);

		$this->assertTrue(
			$model->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function loadForModelWithExistingUidMarksModelAsClean() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$this->subject->load($model);

		$this->assertFalse(
			$model->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function loadCanReadFloatDataFromFloatColumn() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('float_data' => 12.5)
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$this->subject->load($model);

		$this->assertSame(
			12.5,
			$model->getFloatFromFloatData()
		);
	}

	/**
	 * @test
	 */
	public function loadCanReadFloatDataFromDecimalColumn() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('decimal_data' => 12.5)
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$this->subject->load($model);

		$this->assertSame(
			12.5,
			$model->getFloatFromDecimalData()
		);
	}

	/**
	 * @test
	 */
	public function loadCanReadFloatDataFromStringColumn() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('string_data' => 12.5)
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$this->subject->load($model);

		$this->assertSame(
			12.5,
			$model->getFloatFromStringData()
		);
	}


	//////////////////////////////////////
	// Tests concerning the model states
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function findInitiallyReturnsGhostModel() {
		$uid = 42;

		$this->assertTrue(
			$this->subject->find($uid)->isGhost()
		);
	}

	/**
	 * @test
	 */
	public function findAndAccessingDataLoadsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->subject->find($uid)->getTitle();

		$this->assertTrue(
			$this->subject->find($uid)->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function isHiddenOnGhostInDatabaseLoadsModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$model = $this->subject->find($uid);
		$model->isHidden();

		$this->assertTrue(
			$model->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function isHiddenOnGhostNotInDatabaseThrowsException() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'The Tx_Oelib_Tests_Unit_Fixtures_TestingModel with the UID ' . $uid .
				' either has been deleted (or has never existed), but still is accessed.'
		);

		$this->subject->find($uid)->isHidden();
	}

	/**
	 * @test
	 */
	public function loadWithModelWithExistingUidLoadsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$this->subject->load($model);

		$this->assertTrue(
			$model->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function loadWithModelWithInexistentUidMarksModelAsDead() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$this->subject->load($model);

		$this->assertTrue(
			$model->isDead()
		);
	}


	/////////////////////////////////
	// Tests concerning existsModel
	/////////////////////////////////

	/**
	 * @test
	 */
	public function existsModelForUidOfLoadedModelReturnsTrue() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->subject->load($this->subject->find($uid));

		$this->assertTrue(
			$this->subject->existsModel($uid)
		);
	}

	/**
	 * @test
	 */
	public function existsModelForUidOfNotLoadedModelInDatabaseReturnsTrue() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->subject->existsModel($uid)
		);
	}

	/**
	 * @test
	 */
	public function existsModelForInexistentUidReturnsFalse() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$this->assertFalse(
			$this->subject->existsModel($uid)
		);
	}

	/**
	 * @test
	 */
	public function existsModelForGhostModelWithInexistentUidReturnsFalse() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');
		$this->subject->find($uid);

		$this->assertFalse(
			$this->subject->existsModel($uid)
		);
	}

	/**
	 * @test
	 */
	public function existsModelForExistingUidLoadsModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->subject->existsModel($uid);

		$this->assertTrue(
			$this->subject->find($uid)->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function existsModelForExistentUidOfHiddenRecordReturnsFalse() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);

		$this->assertFalse(
			$this->subject->existsModel($uid)
		);
	}

	/**
	 * @test
	 */
	public function existsModelForExistentUidOfHiddenRecordAndHiddenBeingAllowedReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);

		$this->assertTrue(
			$this->subject->existsModel($uid, TRUE)
		);
	}

	/**
	 * @test
	 */
	public function existsModelForExistentUidOfLoadedHiddenRecordAndHiddenNotBeingAllowedReturnsFalse() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);
		$this->subject->load($this->subject->find($uid));

		$this->assertFalse(
			$this->subject->existsModel($uid)
		);
	}

	/**
	 * @test
	 */
	public function existsModelForExistentUidOfLoadedHiddenRecordAndHiddenBeingAllowedReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);
		$this->subject->load($this->subject->find($uid));

		$this->assertTrue(
			$this->subject->existsModel($uid, TRUE)
		);
	}

	/**
	 * @test
	 */
	public function existsModelForExistentUidOfLoadedNonHiddenRecordAndHiddenBeingAllowedReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 0)
		);
		$this->subject->load($this->subject->find($uid));

		$this->assertTrue(
			$this->subject->existsModel($uid, TRUE)
		);
	}

	/**
	 * @test
	 */
	public function existsModelForExistentUidOfHiddenRecordAfterLoadingAsNonHiddenAndHiddenBeingAllowedReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('hidden' => 1)
		);
		$this->subject->load($this->subject->find($uid));

		$this->assertTrue(
			$this->subject->existsModel($uid, TRUE)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning the foreign key mapping
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function relatedRecordWithZeroUidIsNull() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertNull(
			$this->subject->find($uid)->getFriend()
		);
	}

	/**
	 * @test
	 */
	public function relatedRecordWithExistingUidReturnsRelatedRecord() {
		$friendUid = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);

		$this->assertSame(
			$friendUid,
			$this->subject->find($uid)->getFriend()->getUid()
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
		$model = $this->subject->find($uid);

		$this->assertSame(
			$model,
			$model->getFriend()
		);
	}

	/**
	 * @test
	 */
	public function relatedRecordWithExistingUidCanReturnOtherModelType() {
		$ownerUid = $this->testingFramework->createFrontEndUser();
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('owner' => $ownerUid)
		);

		$this->assertTrue(
			$this->subject->find($uid)->getOwner()
				instanceof Tx_Oelib_Model_FrontEndUser
		);
	}

	/**
	 * @test
	 */
	public function relatedRecordWithExistingUidReturnsRelatedRecordThatCanBeLoaded() {
		$friendUid = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);

		$this->subject->find($uid)->getFriend()->getTitle();

		$this->assertTrue(
			$this->subject->find($uid)->getFriend()->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function relatedRecordWithInexistentUidReturnsRelatedRecordAsGhost() {
		$friendUid = $this->testingFramework->getAutoIncrement('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);

		$this->assertSame(
			$friendUid,
			$this->subject->find($uid)->getFriend()->getUid()
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
			$this->subject->find($uid)->getChildren()->isEmpty()
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

		$this->assertSame(
			(string) $childUid,
			$this->subject->find($uid)->getChildren()->getUids()
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

		$this->assertSame(
			$childUid1 . ',' . $childUid2,
			$this->subject->find($uid)->getChildren()->getUids()
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

		$this->assertSame(
			(string) $childUid1,
			$this->subject->find($uid)->getChildren()->getUids()
		);
	}


	////////////////////////////////////////////////////////
	// Tests concerning the m:n mapping using an m:n table
	////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function mnRelationsWithEmptyStringCreatesEmptyList() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->subject->find($uid)->getRelatedRecords()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function mnRelationsWithOneRelatedModelReturnsListWithRelatedModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid, 'related_records'
		);

		$this->assertSame(
			(string) $relatedUid,
			$this->subject->find($uid)->getRelatedRecords()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function mnRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid1, 'related_records'
		);
		$relatedUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid2, 'related_records'
		);

		$this->assertSame(
			$relatedUid1 . ',' . $relatedUid2,
			$this->subject->find($uid)->getRelatedRecords()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function mnRelationsReturnsListSortedBySorting() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid2, 'related_records'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid1, 'related_records'
		);

		$this->assertSame(
			$relatedUid2 . ',' . $relatedUid1,
			$this->subject->find($uid)->getRelatedRecords()->getUids()
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
			$this->subject->find($uid)->getBidirectional()->isEmpty()
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

		$this->assertSame(
			(string) $uid,
			$this->subject->find($relatedUid)->getBidirectional()->getUids()
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

		$this->assertSame(
			$uid1 . ',' . $uid2,
			$this->subject->find($relatedUid)->getBidirectional()->getUids()
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

		$this->assertSame(
			$uid2 . ',' . $uid1,
			$this->subject->find($relatedUid)->getBidirectional()->getUids()
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
			$this->subject->find($uid)->getComposition()->isEmpty()
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

		$this->assertSame(
			(string) $relatedUid,
			$this->subject->find($uid)->getComposition()->getUids()
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

		$this->assertSame(
			$relatedUid1 . ',' . $relatedUid2,
			$this->subject->find($uid)->getComposition()->getUids()
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

		$this->assertSame(
			$relatedUid2 . ',' . $relatedUid1,
			$this->subject->find($uid)->getComposition()->getUids()
		);
	}


	/////////////////////////////////
	// Tests concerning getNewGhost
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getNewGhostReturnsModel() {
		$this->assertTrue(
			$this->subject->getNewGhost() instanceof Tx_Oelib_Model
		);
	}

	/**
	 * @test
	 */
	public function getNewGhostReturnsGhost() {
		$this->assertTrue(
			$this->subject->getNewGhost()->isGhost()
		);
	}

	/**
	 * @test
	 */
	public function getNewGhostReturnsModelWithUid() {
		$this->assertTrue(
			$this->subject->getNewGhost()->hasUid()
		);
	}

	/**
	 * @test
	 */
	public function getNewGhostCreatesRegisteredModel() {
		$ghost = $this->subject->getNewGhost();

		$this->assertSame(
			$ghost,
			$this->subject->find($ghost->getUid())
		);
	}

	/**
	 * @test
	 */
	public function loadingAGhostCreatedWithGetNewGhostThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'This ghost was created via getNewGhost and must not be loaded.'
		);

		$ghost = $this->subject->getNewGhost();
		$this->subject->load($ghost);
	}


	///////////////////////////////////////////
	// Tests concerning getLoadedTestingModel
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getLoadedTestingModelReturnsModel() {
		$this->subject->disableDatabaseAccess();

		$this->assertTrue(
			$this->subject->getLoadedTestingModel(array())
				instanceof Tx_Oelib_Model
		);
	}

	/**
	 * @test
	 */
	public function getLoadedTestingModelReturnsLoadedModel() {
		$this->subject->disableDatabaseAccess();

		$this->assertTrue(
			$this->subject->getLoadedTestingModel(array())->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedTestingModelReturnsModelWithUid() {
		$this->subject->disableDatabaseAccess();

		$this->assertTrue(
			$this->subject->getLoadedTestingModel(array())->hasUid()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedTestingModelCreatesRegisteredModel() {
		$this->subject->disableDatabaseAccess();
		$model = $this->subject->getLoadedTestingModel(array());

		$this->assertSame(
			$model,
			$this->subject->find($model->getUid())
		);
	}

	/**
	 * @test
	 */
	public function getLoadedTestingModelSetsTheProvidedData() {
		$this->subject->disableDatabaseAccess();

		$model = $this->subject->getLoadedTestingModel(
			array('title' => 'foo')
		);

		$this->assertSame(
			'foo',
			$model->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedTestingModelCreatesRelations() {
		$this->subject->disableDatabaseAccess();

		$relatedModel = $this->subject->getNewGhost();
		$model = $this->subject->getLoadedTestingModel(
			array('friend' => $relatedModel->getUid())
		);

		$this->assertSame(
			$relatedModel->getUid(),
			$model->getFriend()->getUid()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning findSingleByWhereClause().
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function findSingleByWhereClauseWithEmptyWhereClausePartsThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $whereClauseParts must not be empty.'
		);

		$this->subject->findSingleByWhereClause(array());
	}

	/**
	 * @test
	 */
	public function findSingleByWhereClauseWithUidOfInexistentRecordThrowsException() {
		$this->setExpectedException('tx_oelib_Exception_NotFound');

		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$this->subject->findSingleByWhereClause(
			array('uid' => $uid)
		);
	}

	/**
	 * @test
	 */
	public function findSingleByWhereClauseWithUidOfExistentNotMappedRecordReturnsModelWithTheData() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertSame(
			'foo',
			$this->subject->findSingleByWhereClause(
				array('title' => 'foo', 'is_dummy_record' => '1')
			)->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function findSingleByWhereClauseWithUidOfExistentYetMappedRecordReturnsModelWithTheMappedData() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->subject->find($uid)->setTitle('bar');

		$this->assertSame(
			'bar',
			$this->subject->findSingleByWhereClause(
				array('title' => 'foo', 'is_dummy_record' => '1')
			)->getTitle()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning disabled database access
	//////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasDatabaseAccessInitiallyReturnsTrue() {
		$this->assertTrue(
			$this->subject->hasDatabaseAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasDatabaseAccessAfterDisableDatabaseAccessReturnsFalse() {
		$this->subject->disableDatabaseAccess();

		$this->assertFalse(
			$this->subject->hasDatabaseAccess()
		);
	}

	/**
	 * @test
	 */
	public function loadWithUidOfRecordInDatabaseAndDatabaseAccessDisabledMarksModelAsDead() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->disableDatabaseAccess();
		$this->subject->load($this->subject->find($uid));

		$this->assertTrue(
			$this->subject->find($uid)->isDead()
		);
	}

	/**
	 * @test
	 */
	public function loadWithUidOfRecordNotInDatabaseAndDatabaseAccessDisabledMarksModelAsDead() {
		$uid = $this->testingFramework->getAutoIncrement('tx_oelib_test');

		$this->subject->disableDatabaseAccess();
		$this->subject->load($this->subject->find($uid));

		$this->assertTrue(
			$this->subject->find($uid)->isDead()
		);
	}

	/**
	 * @test
	 */
	public function findSingleByWhereClauseAndDatabaseAccessDisabledThrowsException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'No record can be retrieved from the database because database ' .
				'access is disabled for this mapper instance.'
		);

		$this->subject->disableDatabaseAccess();
		$this->subject->findSingleByWhereClause(array('title' => 'foo'));
	}


	////////////////////////////
	// Tests concerning save()
	////////////////////////////

	/**
	 * @test
	 */
	public function saveForReadOnlyModelDoesNotCommitModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->setModelClassName('Tx_Oelib_Tests_Unit_Fixtures_ReadOnlyModel');
		$this->subject->save($this->subject->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "foo" AND tstamp = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForDatabaseAccessDeniedDoesNotCommitDirtyLoadedModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->disableDatabaseAccess();
		$this->subject->find($uid)->setTitle('bar');
		$this->subject->save($this->subject->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForGhostDoesNotCommitModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->save($this->subject->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'tstamp > 0'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForDeadModelDoesNotCommitDirtyModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->find($uid)->setTitle('bar');
		$this->subject->find($uid)->markAsDead();
		$this->subject->save($this->subject->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForCleanLoadedModelDoesNotCommitModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->find($uid)->setTitle('bar');
		$this->subject->find($uid)->markAsClean();
		$this->subject->save($this->subject->find($uid));

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithUidCommitsModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->find($uid)->setTitle('bar');
		$this->subject->save($this->subject->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithUidDoesNotChangeTheUid() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$model = $this->subject->find($uid);
		$model->setTitle('bar');
		$this->subject->save($model);

		$this->assertSame(
			$uid,
			$model->getUid()
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithUidSetsTimestamp() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->find($uid)->setTitle('bar');
		$this->subject->save($this->subject->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "bar" AND tstamp = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithUidAndWithoutDataCommitsModelToDatabase() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$model->setData(array());
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'tstamp = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	/**
	 * @test
	 */
	public function saveNewModelFromMemoryAndMapperInTestingModeMarksModelAsDummyModel() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setData(array('title' => 'foo'));
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertTrue(
			$this->testingFramework->existsExactlyOneRecord(
				'tx_oelib_test', 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveNewModelFromMemoryRegistersModelInMapper() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setData(array('title' => 'foo'));
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertSame(
			$model,
			$this->subject->find($model->getUid())
		);
	}

	/**
	 * @test
	 */
	public function isDirtyAfterSaveForDirtyLoadedModelWithUidReturnsFalse() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->find($uid)->setTitle('bar');
		$this->subject->save($this->subject->find($uid));

		$this->assertFalse(
			$this->subject->find($uid)->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithoutUidAndWithoutRelationsCommitsModelToDatabase() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setData(array('is_dummy_record' => '1', 'title' => 'bar'));
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithoutUidAndWithRelationsCommitsModelToDatabase() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->subject->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar"'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithoutUidAddsModelToMapAfterSave() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->subject->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertSame(
			$model,
			$this->subject->find($model->getUid())
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithoutUidSetsUidForModel() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->subject->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertTrue(
			$model->hasUid()
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithoutUidSetsUidReceivedFromDatabaseForModel() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->subject->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'uid = ' . $model->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function isDirtyAfterSaveForDirtyLoadedModelWithoutUidReturnsFalse() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->subject->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertFalse(
			$model->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithoutUidSetsTimestamp() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->subject->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "bar" AND tstamp = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithoutUidSetsCreationDate() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();

		$data = array('is_dummy_record' => '1', 'title' => 'bar');
		$this->subject->createRelations($data, $model);

		$model->setData($data);
		$model->markAsDirty();

		$this->subject->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "bar" AND crdate = ' . $GLOBALS['SIM_EXEC_TIME']
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForDirtyLoadedModelWithNoDataDoesNotCommitModelToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "foo" AND tstamp > 0'
			)
		);

		$model = $this->subject->find($uid);
		$model->markAsDirty();
		$this->subject->save($model);

		$this->assertFalse(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "foo" AND tstamp > 0'
			)
		);
	}

	/**
	 * @test
	 */
	public function isDeadAfterSaveForDirtyLoadedModelWithDeletedFlagSetReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->subject->find($uid)->setTitle('bar');
		$this->subject->find($uid)->setToDeleted();
		$this->subject->save($this->subject->find($uid));

		$this->assertTrue(
			$this->subject->find($uid)->isDead()
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithN1RelationSavesUidOfRelatedRecord() {
		$friendUid = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);
		$this->subject->find($uid)->setTitle('bar');
		$this->subject->save($this->subject->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test', 'title = "bar" AND friend = ' . $friendUid
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithMNCommaSeparatedRelationSavesUidList() {
		$childUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$childUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('children' => $childUid1 . ',' . $childUid2)
		);
		$this->subject->find($uid)->setTitle('bar');
		$this->subject->save($this->subject->find($uid));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_test',
				'title = "bar" AND children = "' . $childUid1 . ',' . $childUid2 . '"'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithMNTableRelationSavesNumberOfRelatedRecords() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid1, 'related_records'
		);
		$relatedUid2 = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid2, 'related_records'
		);

		$this->subject->find($uid)->setTitle('bar');
		$this->subject->save($this->subject->find($uid));

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
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->setTitle('bar');

		$composition = $model->getComposition();
		$mapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper');
		$composition->add($mapper->find(
			$this->testingFramework->createRecord('tx_oelib_testchild')
		));
		$composition->add($mapper->find(
			$this->testingFramework->createRecord('tx_oelib_testchild')
		));

		$this->subject->save($model);

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
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->setTitle('bar');

		$composition = $model->getComposition();
		$mapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper');
		$component = $mapper->find(
			$this->testingFramework->createRecord('tx_oelib_testchild')
		);
		$composition->add($component);

		$this->subject->save($model);

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
	public function saveForModelWith1NRelationSavesFirstNewRelatedRecord() {
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->setTitle('bar');

		$component = new Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel();
		$component->markAsDummyModel();
		$model->getComposition()->add($component);

		$this->subject->save($model);

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
	public function saveForModelWith1NRelationSavesSecondNewRelatedRecord() {
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->setTitle('bar');

		$newComponent1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel();
		$newComponent1->markAsDummyModel();
		$model->getComposition()->add($newComponent1);

		$newComponent2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel();
		$newComponent2->markAsDummyModel();
		$model->getComposition()->add($newComponent2);

		$this->subject->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_testchild',
				'uid = ' . $newComponent2->getUid() .
				' AND parent = ' . $model->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWith1NRelationSavesNewRelatedRecordWithPrefixInForeignKey() {
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->setTitle('bar');

		$component = new Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel();
		$component->markAsDummyModel();
		$model->getComposition2()->add($component);

		$this->subject->save($model);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_oelib_testchild',
				'uid = ' . $component->getUid() .
					' AND tx_oelib_parent2 = ' . $model->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function saveForModelWithOneToManyRelationDeletesUnconnectedRecord() {
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$model->markAsDirty();

		$composition = $model->getComposition();
		$mapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper');
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

		$this->subject->save($model);

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
		$this->subject->find($uid)->setTitle('bar');
		$this->subject->find($friendUid)->setTitle('foo');

		$this->subject->save($this->subject->find($uid));

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
		$friend = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$friend->markAsDummyModel();
		$friend->setTitle('foo');

		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->subject->find($uid)->setFriend($friend);

		$this->subject->save($this->subject->find($uid));

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
		$this->subject->find($uid)->setTitle('bar');
		$this->subject->find($childUid1)->setTitle('foo');

		$this->subject->save($this->subject->find($uid));

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
	public function saveAddsModelToCache() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);


		$model = $this->subject->find($uid);
		$model->setTitle('bar');
		$this->subject->save($model);

		$cachedModels = $this->subject->getCachedModels();
		$this->assertSame(
			$model->getUid(),
			$cachedModels[0]->getUid()
		);
	}


	/**
	 * @test
	 */
	public function addModelToListMarksParentModelAsDirty() {
		$parentUid = $this->testingFramework->createRecord('tx_oelib_test');

		$parent = $this->subject->find($parentUid);
		$child = $this->subject->getNewGhost();

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

		$parent = $this->subject->find($parentUid);
		$child = $this->subject->getNewGhost();
		$list = new Tx_Oelib_List();
		$list->add($child);

		$parent->getChildren()->append($list);

		$this->assertTrue(
			$parent->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function appendUniqueMarksParentModelAsDirty() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$parentUid = $this->testingFramework->createRecord('tx_oelib_test');

		$parent = $this->subject->find($parentUid);
		$child = $this->subject->getNewGhost();
		$list = new Tx_Oelib_List();
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

		$parent = $this->subject->find($parentUid);
		$child = $this->subject->getNewGhost();
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

		$parent = $this->subject->find($parentUid);
		$child = $this->subject->find($childUid);

		$parent->getRelatedRecords()->add($child);
		$this->subject->save($parent);

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

		$parent = $this->subject->find($parentUid);
		$child1 = $this->subject->find($childUid1);
		$child2 = $this->subject->find($childUid2);

		$parent->getRelatedRecords()->add($child1);
		$parent->getRelatedRecords()->add($child2);
		$this->subject->save($parent);

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

		$parent = $this->subject->find($parentUid);
		$child = $this->subject->find($childUid);

		$child->getBidirectional()->add($parent);
		$this->subject->save($child);

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

		$parent1 = $this->subject->find($parentUid1);
		$parent2 = $this->subject->find($parentUid2);
		$child = $this->subject->find($childUid);

		$child->getBidirectional()->add($parent1);
		$child->getBidirectional()->add($parent2);
		$this->subject->save($child);

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
			'InvalidArgumentException',
			'This model is a memory-only dummy that must not be saved.'
		);

		$model = $this->subject->getNewGhost();
		$this->subject->save($model);
	}

	/**
	 * @test
	 */
	public function saveForLoadedTestingModelWithUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'This model is a memory-only dummy that must not be saved.'
		);

		$model = $this->subject->getLoadedTestingModel(
			array('uid' => 42, 'title' => 'foo')
		);
		$this->subject->save($model);
	}

	/**
	 * @test
	 */
	public function saveForLoadedTestingModelWithoutUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'This model is a memory-only dummy that must not be saved.'
		);

		$model = $this->subject->getLoadedTestingModel(
			array('title' => 'foo')
		);
		$this->subject->save($model);
	}

	/**
	 * @test
	 */
	public function saveCanSaveFloatDataToFloatColumn() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setData(array('float_data' => 9.5));
		$this->subject->save($model);

		$this->assertSame(
			array('float_data' => '9.500000'),
			Tx_Oelib_Db::selectSingle(
				'float_data', 'tx_oelib_test', 'uid = ' . $model->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function saveCanSaveFloatDataToDecimalColumn() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setData(array('decimal_data' => 9.5));
		$this->subject->save($model);

		$this->assertSame(
			array('decimal_data' => '9.500'),
			Tx_Oelib_Db::selectSingle(
				'decimal_data', 'tx_oelib_test', 'uid = ' . $model->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function saveCanSaveFloatDataToStringColumn() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setData(array('string_data' => 9.5));
		$this->subject->save($model);

		$this->assertSame(
			array('string_data' => '9.5'),
			Tx_Oelib_Db::selectSingle(
				'string_data', 'tx_oelib_test', 'uid = ' . $model->getUid()
			)
		);
	}


	/////////////////////////////
	// Tests concerning findAll
	/////////////////////////////

	/**
	 * @test
	 */
	public function findAllForNoRecordsReturnsEmptyList() {
		$this->assertTrue(
			$this->subject->findAll()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllForOneRecordInDatabaseReturnsOneRecord() {
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertSame(
			1,
			$this->subject->findAll()->count()
		);
	}

	/**
	 * @test
	 */
	public function findAllForTwoRecordsInDatabaseReturnsTwoRecords() {
		$this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertSame(
			2,
			$this->subject->findAll()->count()
		);
	}

	/**
	 * @test
	 */
	public function findAllForOneRecordInDatabaseReturnsLoadedRecord() {
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertTrue(
			$this->subject->findAll()->first()->isLoaded()
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
			$this->subject->findAll()->isEmpty()
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
			$this->subject->findAll()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllCanBeUsedForStaticTables() {
		Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->findAll();
	}

	/**
	 * @test
	 */
	public function findAllSortsRecordsBySorting() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid2 = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertSame(
			min($uid1, $uid2),
			$this->subject->findAll()->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findAllForGivenSortParameterOverridesDefaultSorting() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'record a')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'record b')
		);

		$this->assertSame(
			$uid,
			$this->subject->findAll('title')->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findAllForGivenSortParameterWithSortDirectionSortsResultsBySortdirection() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'record b')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'record a')
		);

		$this->assertSame(
			$uid,
			$this->subject->findAll('title DESC')->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findAllForGivenSortParameterFindsMultipleEntries() {
		$this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertSame(
			2,
			$this->subject->findAll('title ASC')->count()
		);
	}


	///////////////////////////////////////
	// Tests concerning findByWhereClause
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function findByWhereClauseForNoGivenParameterAndTwoRecordsFindsBothRecords() {
		$this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertSame(
			2,
			$this->subject->findByWhereClause()->count()
		);
	}

	/**
	 * @test
	 */
	public function findByWhereClauseForGivenWhereClauseAndOneMatchingRecordFindsThisRecord() {
		$foundRecordUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertSame(
			$foundRecordUid,
			$this->subject->findByWhereClause('title like "foo"')->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByWhereClauseForGivenWhereClauseAndTwoRecordsOneMatchingOneNotDoesNotFindNonMatchingRecord() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$notMatchingUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);

		$this->assertNotSame(
			$notMatchingUid,
			$this->subject->findByWhereClause('title like "foo"')->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByWhereClauseForNoSortingProvidedSortsRecordsByDefaultSorting() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$uid2 = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertSame(
			min($uid1, $uid2),
			$this->subject->findByWhereClause()->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByWhereClauseForSortingProvidedSortsRecordsByGivenSorting() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$firstEntryUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);

		$this->assertSame(
			$firstEntryUid,
			$this->subject->findByWhereClause('','title ASC')->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByWhereClauseForSortingAndWhereClauseProvidedSortsMatchingRecords() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo', 'sorting' => 2)
		);
		$firstMatchingUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo', 'sorting' => 0)
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar', 'sorting' => 1)
		);

		$this->assertSame(
			$firstMatchingUid,
			$this->subject->findByWhereClause('title like "foo"','sorting ASC')
				->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByWhereClauseWithoutLimitFindsAllRecords() {
		$firstUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$secondUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);

		$this->assertSame(
			$firstUid . ',' . $secondUid,
			$this->subject->findByWhereClause('', '', '')->getUids()
		);
	}

	/**
	 * @test
	 */
	public function findByWhereClauseWithTwoRecordsAndLimitOneFindsOnlyFirstRecord() {
		$firstUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);

		$this->assertSame(
			(string) $firstUid,
			$this->subject->findByWhereClause('', '', '1')->getUids()
		);
	}

	/**
	 * @test
	 */
	public function findByWhereClauseWithThreeRecordsAndLimitBeginOneAndMaximumOneFindsOnlySecondRecord() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$secondUid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertSame(
			(string) $secondUid,
			$this->subject->findByWhereClause('', '', '1,1')->getUids()
		);
	}


	///////////////////////////////////
	// Tests concerning findByPageUId
	///////////////////////////////////

	/**
	 * @test
	 */
	public function findByPageUidForPageUidZeroReturnsEntryWithZeroPageUid() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertSame(
			$uid,
			$this->subject->findByPageUid(0)->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByPageUidForPageUidZeroReturnsEntryWithNonZeroPageUid() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 42)
		);

		$this->assertSame(
			$uid,
			$this->subject->findByPageUid(0)->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByPageUidForPageUidEmptyReturnsRecordWithNonZeroPageUid() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 42)
		);

		$this->assertSame(
			$uid,
			$this->subject->findByPageUid('')->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByPageUidForNonZeroPageUidReturnsEntryFromThatPage() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 1)
		);

		$this->assertSame(
			$uid,
			$this->subject->findByPageUid(1)->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByPageUidForNonZeroPageUidDoesNotReturnEntryWithDifferentPageUId() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 2)
		);

		$this->assertTrue(
			$this->subject->findByPageUid(1)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findByPageUidForPageUidAndSortingGivenReturnEntrySortedBySorting() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 2, 'sorting' => 3)
		);

		$firstMatchingRecord = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 2, 'sorting' => 1)
		);

		$this->assertSame(
			$firstMatchingRecord,
			$this->subject->findByPageUid(2, 'sorting ASC')->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByPageUidForTwoNonZeroPageUidsCanReturnRecordFromFirstPage() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 1)
		);

		$this->assertSame(
			$uid,
			$this->subject->findByPageUid('1,2')->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByPageUidForTwoNonZeroPageUidsCanReturnRecordFromSecondPage() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('pid' => 2)
		);

		$this->assertSame(
			$uid,
			$this->subject->findByPageUid('1,2')->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByPageUidWithoutPageUidAndWithoutLimitCallsFindByWhereClauseWithoutLimit() {
		/** @var $subject tx_oelib_Tests_Unit_Fixtures_TestingMapper */
		$subject = $this->getMock(
			'tx_oelib_Tests_Unit_Fixtures_TestingMapper',
			array('findByWhereClause')
		);

		$subject->expects($this->once())
			->method('findByWhereClause')
			->with('', '', '');

		$subject->findByPageUid('');
	}

	/**
	 * @test
	 */
	public function findByPageUidWithoutPageUidWithLimitCallsFindByWhereClauseWithLimit() {
		$subject = $this->getMock(
			'tx_oelib_Tests_Unit_Fixtures_TestingMapper',
			array('findByWhereClause')
		);

		$subject->expects($this->once())
			->method('findByWhereClause')
			->with('', '', '1,1');

		$subject->findByPageUid('', '', '1,1');
	}

	/**
	 * @test
	 */
	public function findByPageUidWithPageUidWithoutLimitCallsFindByWhereClauseWithoutLimit() {
		$subject = $this->getMock(
			'tx_oelib_Tests_Unit_Fixtures_TestingMapper',
			array('findByWhereClause')
		);

		$subject->expects($this->once())
			->method('findByWhereClause')
			->with('tx_oelib_test.pid IN (42)', '', '');

		$subject->findByPageUid('42', '', '');
	}

	/**
	 * @test
	 */
	public function findByPageUidWithPageUidAndLimitCallsFindByWhereClauseWithLimit() {
		$subject = $this->getMock(
			'tx_oelib_Tests_Unit_Fixtures_TestingMapper',
			array('findByWhereClause')
		);

		$subject->expects($this->once())
			->method('findByWhereClause')
			->with('tx_oelib_test.pid IN (42)', '', '1,1');

		$subject->findByPageUid('42', '', '1,1');
	}


	/////////////////////////////////////
	// Tests concerning additional keys
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function findOneByKeyFromCacheForEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->findOneByKeyFromCache('', 'bar');
	}

	/**
	 * @test
	 */
	public function findOneByKeyFromCacheForInexistentKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'"foo" is not a valid key for this mapper.'
		);

		$this->subject->findOneByKeyFromCache('foo', 'bar');
	}

	/**
	 * @test
	 */
	public function findOneByKeyFromCacheForEmptyValueThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$value must not be empty.'
		);

		$this->subject->findOneByKeyFromCache('title', '');
	}

	/**
	 * @test
	 */
	public function findOneByKeyFromCacheForModelNotInCacheThrowsException() {
		$this->setExpectedException('tx_oelib_Exception_NotFound');

		$this->subject->findOneByKeyFromCache('title', 'bar');
	}

	/**
	 * @test
	 */
	public function findByKeyFindsLoadedModel() {
		$model = $this->subject->getLoadedTestingModel(
			array('title' => 'Earl Grey')
		);

		$this->assertSame(
			$model,
			$this->subject->findOneByKeyFromCache('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findByKeyFindsLastLoadedModelWithSameKey() {
		$this->subject->getLoadedTestingModel(
			array('title' => 'Earl Grey')
		);
		$model = $this->subject->getLoadedTestingModel(
			array('title' => 'Earl Grey')
		);

		$this->assertSame(
			$model,
			$this->subject->findOneByKeyFromCache('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findByKeyFindsSavedModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$model = $this->subject->find($uid);
		$model->setTitle('Earl Grey');
		$this->subject->save($model);

		$this->assertSame(
			$model,
			$this->subject->findOneByKeyFromCache('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findByKeyFindsLastSavedModelWithSameKey() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$model1 = $this->subject->find($uid1);
		$model1->setTitle('Earl Grey');
		$this->subject->save($model1);

		$uid2 = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Earl Grey')
		);
		$model2 = $this->subject->find($uid2);
		$model2->setTitle('Earl Grey');
		$this->subject->save($model2);

		$this->assertSame(
			$model2,
			$this->subject->findOneByKeyFromCache('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findOneByKeyForEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->findOneByKey('', 'bar');
	}

	/**
	 * @test
	 */
	public function findOneByKeyForInexistentKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'"foo" is not a valid key for this mapper.'
		);

		$this->subject->findOneByKey('foo', 'bar');
	}

	/**
	 * @test
	 */
	public function findOneByKeyForEmptyValueThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$value must not be empty.'
		);

		$this->subject->findOneByKey('title', '');
	}

	/**
	 * @test
	 */
	public function findOneByKeyCanFindModelFromCache() {
		$model = $this->subject->getLoadedTestingModel(
			array('title' => 'Earl Grey')
		);

		$this->assertSame(
			$model,
			$this->subject->findOneByKey('title', 'Earl Grey')
		);
	}

	/**
	 * @test
	 */
	public function findOneByKeyCanLoadModelFromDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Earl Grey')
		);

		$this->assertSame(
			$uid,
			$this->subject->findOneByKey('title', 'Earl Grey')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findOneByKeyForInexistentThrowsException() {
		$this->setExpectedException('tx_oelib_Exception_NotFound');

		$this->subject->findOneByKey('title', 'Darjeeling');
	}


	/*
	 * Tests concerning compound key
	 */

	/**
	 * @test
	 *
	 * @expectedException tx_oelib_Exception_NotFound
	 */
	public function findOneByCompoundKeyFromCacheForEmptyCompoundKeyThrowsException() {
		$this->subject->findOneByCompoundKeyFromCache('bar');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findOneByCompoundKeyFromCacheForEmptyValueThrowsException() {
		$this->subject->findOneByCompoundKeyFromCache('');
	}

	/**
	 * @test
	 *
	 * @expectedException tx_oelib_Exception_NotFound
	 */
	public function findOneByCompoundKeyFromCacheForModelNotInCacheThrowsException() {
		$this->subject->findOneByCompoundKeyFromCache('foo.bar');
	}

	/**
	 * @test
	 */
	public function findByCompoundKeyFindsLoadedModel() {
		$model = $this->subject->getLoadedTestingModel(
			array('title' => 'Earl Grey', 'header' => 'Tea Time')
		);

		$this->assertSame(
			$model,
			$this->subject->findOneByCompoundKeyFromCache('Earl Grey.Tea Time')
		);
	}

	/**
	 * @test
	 */
	public function findByCompoundKeyFindsLastLoadedModelWithSameCompoundKey() {
		$this->subject->getLoadedTestingModel(
			array('title' => 'Earl Grey', 'header' => 'Tea Time')
		);
		$model = $this->subject->getLoadedTestingModel(
			array('title' => 'Earl Grey', 'header' => 'Tea Time')
		);

		$this->assertSame(
			$model,
			$this->subject->findOneByCompoundKeyFromCache('Earl Grey.Tea Time')
		);
	}

	/**
	 * @test
	 */
	public function findByCompoundKeyFindsSavedModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$model = $this->subject->find($uid);
		$model->setTitle('Earl Grey');
		$model->setHeader('Tea Time');
		$this->subject->save($model);

		$this->assertSame(
			$model,
			$this->subject->findOneByCompoundKeyFromCache('Earl Grey.Tea Time')
		);
	}

	/**
	 * @test
	 */
	public function findByCompoundKeyFindsLastSavedModelWithSameCompoundKey() {
		$uid1 = $this->testingFramework->createRecord('tx_oelib_test');
		$model1 = $this->subject->find($uid1);
		$model1->setTitle('Earl Grey');
		$model1->setHeader('Tea Time');
		$this->subject->save($model1);

		$uid2 = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Earl Grey', 'header' => 'Tea Time')
		);
		$model2 = $this->subject->find($uid2);
		$model2->setTitle('Earl Grey');
		$model2->setHeader('Tea Time');
		$this->subject->save($model2);

		$this->assertSame(
			$model2,
			$this->subject->findOneByCompoundKeyFromCache('Earl Grey.Tea Time')
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findOneByCompoundKeyForEmptyCompoundKeyThrowsException() {
		$this->subject->findOneByCompoundKey(array());
	}

	/**
	 * @test
	 */
	public function findOneByCompoundKeyCanFindModelFromCache() {
		$model = $this->subject->getLoadedTestingModel(
			array('title' => 'Earl Grey', 'header' => 'Tea Time')
		);

		$this->assertSame(
			$model,
			$this->subject->findOneByCompoundKey(array('title' => 'Earl Grey', 'header' => 'Tea Time'))
		);
	}

	/**
	 * @test
	 */
	public function findOneByCompoundKeyCanLoadModelFromDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'Earl Grey', 'header' => 'Tea Time')
		);

		$this->assertSame(
			$uid,
			$this->subject->findOneByCompoundKey(array('title' => 'Earl Grey', 'header' => 'Tea Time'))->getUid()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException tx_oelib_Exception_NotFound
	 */
	public function findOneByCompoundKeyForNonExistentThrowsException() {
		$this->subject->findOneByCompoundKey(array('title' => 'Darjeeling', 'header' => 'Tea Time'));
	}


	////////////////////////////
	// Tests concerning delete
	////////////////////////////

	/**
	 * @test
	 */
	public function deleteForDeadModelDoesNotThrowException() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->markAsDead();

		$this->subject->delete($model);
	}

	/**
	 * @test
	 */
	public function deleteForModelWithoutUidMarksModelAsDead() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();

		$this->subject->delete($model);

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
		$model = $this->subject->find($uid);

		$this->subject->delete($model);

		$this->assertTrue(
			$model->isDead()
		);
	}

	/**
	 * @test
	 */
	public function deleteForGhostFromGetNewGhostThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'This model is a memory-only dummy that must not be deleted.'
		);

		$model = $this->subject->getNewGhost();
		$this->subject->delete($model);
	}


	/**
	 * @test
	 */
	public function deleteForReadOnlyModelThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'This model is read-only and must not be deleted.'
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_ReadOnlyModel();
		$this->subject->delete($model);
	}

	/**
	 * @test
	 */
	public function deleteForModelWithUidWritesModelAsDeletedToDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array()
		);
		$model = $this->subject->find($uid);

		$this->subject->delete($model);

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
		$model = $this->subject->find($uid);

		$this->subject->delete($model);

		$this->assertSame(
			$model,
			$this->subject->find($uid)
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

		$this->subject->delete($this->subject->find($uid));

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

		$model = $this->subject->find($uid);
		$relatedModel = $model->getComposition()->first();

		$model->setTitle('foo');
		$relatedModel->setTitle('bar');

		$this->subject->delete($model);
	}


	///////////////////////////////////////
	// Tests concerning findAllByRelation
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function findAllByRelationWithModelWithoutUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$model must have a UID.'
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();

		Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper')
			->findAllByRelation($model, 'parent');
	}

	/**
	 * @test
	 */
	public function findAllByRelationWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty'
		);

		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);

		Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper')
			->findAllByRelation($model, '');
	}

	/**
	 * @test
	 */
	public function findAllByRelationForNoMatchesReturnsEmptyList() {
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);

		$mapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper');
		$this->assertTrue(
			$mapper->findAllByRelation($model, 'parent')->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllByRelationNotReturnsNotMatchingRecords() {
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$anotherModel = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $anotherModel->getUid())
		);

		$mapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper');
		$this->assertTrue(
			$mapper->findAllByRelation($model, 'parent')->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllByRelationCanReturnOneMatch() {
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$mapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper');
		$relatedModel = $mapper->find(
			$this->testingFramework->createRecord(
				'tx_oelib_testchild', array('parent' => $model->getUid())
			)
		);

		$result = $mapper->findAllByRelation($model, 'parent');
		$this->assertSame(
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
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $model->getUid())
		);
		$this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $model->getUid())
		);

		$result = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper')
			->findAllByRelation($model, 'parent');
		$this->assertSame(
			2,
			$result->count()
		);
	}

	/**
	 * @test
	 */
	public function findAllByRelationIgnoresIgnoreList() {
		$model = $this->subject->find(
			$this->testingFramework->createRecord('tx_oelib_test')
		);
		$mapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper');
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

		$ignoreList = new Tx_Oelib_List();
		$ignoreList->add($ignoredRelatedModel);

		$result = Tx_Oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingChildMapper')
			->findAllByRelation($model, 'parent', $ignoreList);
		$this->assertSame(
			1,
			$result->count()
		);
		$this->assertSame(
			$relatedModel,
			$result->first()
		);
	}


	//////////////////////////////////////////
	// Tests concerning countByWhereClause()
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function countByWhereClauseWithoutWhereClauseCountsAllRecords() {
		$this->testingFramework->createRecord('tx_oelib_test');

		$this->assertSame(
			1,
			$this->subject->countByWhereClause()
		);
	}

	/**
	 * @test
	 */
	public function countByWhereClauseWithoutMatchingRecordReturnsZero() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertSame(
			0,
			$this->subject->countByWhereClause('title = "bar"')
		);
	}

	/**
	 * @test
	 */
	public function countByWhereClauseWithOneMatchingRecordsReturnsOne() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);

		$this->assertSame(
			1,
			$this->subject->countByWhereClause('title = "bar"')
		);
	}

	/**
	 * @test
	 */
	public function countByWhereClauseWithTwoMatchingRecordsReturnsTwo() {
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);
		$this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'bar')
		);

		$this->assertSame(
			2,
			$this->subject->countByWhereClause('title = "bar"')
		);
	}


	/////////////////////////////////////
	// Tests regarding countByPageUid()
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function countByPageUidWithEmptyStringCallsCountByWhereClauseWithEmptyString() {
		$subject = $this->getMock(
			'tx_oelib_Tests_Unit_Fixtures_TestingMapper',
			array('countByWhereClause')
		);
		$subject->expects($this->once())
			->method('countByWhereClause')
			->with('');

		$subject->countByPageUid('');
	}

	/**
	 * @test
	 */
	public function countByPageUidWithZeroCallsCountByWhereClauseWithEmptyString() {
		$subject = $this->getMock(
			'tx_oelib_Tests_Unit_Fixtures_TestingMapper',
			array('countByWhereClause')
		);
		$subject->expects($this->once())
			->method('countByWhereClause')
			->with('');

		$subject->countByPageUid('0');
	}

	/**
	 * @test
	 */
	public function countByPageUidWithPageUidCallsCountByWhereClauseWithWhereClauseContainingPageUid() {
		$subject = $this->getMock(
			'tx_oelib_Tests_Unit_Fixtures_TestingMapper',
			array('countByWhereClause')
		);
		$subject->expects($this->once())
			->method('countByWhereClause')
			->with('tx_oelib_test.pid IN (42)');

		$subject->countByPageUid('42');
	}

	/**
	 * @test
	 */
	public function getTableNameReturnsTableName() {
		$this->assertSame(
			'tx_oelib_test',
			$this->subject->getTableName()
		);
	}
}