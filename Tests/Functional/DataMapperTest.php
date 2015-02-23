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
class Tx_Oelib_Tests_Functional_DataMapperTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;
	/**
	 * @var Tx_Oelib_Tests_Unit_Fixtures_TestingMapper
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

		$this->subject = Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		Tx_Oelib_MapperRegistry::purgeInstance();
	}

	/*
	 * Tests concerning load
	 */

	/**
	 * @test
	 */
	public function loadWithModelWithExistingUidFillsModelWithData() {
		$title = 'Assassin of Kings';
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => $title)
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid($uid);
		$this->subject->load($model);

		$this->assertSame(
			$title,
			$model->getTitle()
		);
	}

	/*
	 * Tests concerning find
	 */

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsModelDataFromDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel $model */
		$model = $this->subject->find($uid);
		$this->assertSame(
			'foo',
			$model->getTitle()
		);
	}

	/*
	 * Tests concerning n:1 association mapping
	 */

	/**
	 * @test
	 */
	public function relatedRecordWithExistingUidReturnsRelatedRecordWithData() {
		$friendTitle = 'Brianna';
		$friendUid = $this->testingFramework->createRecord('tx_oelib_test', array('title' => $friendTitle));
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('friend' => $friendUid)
		);

		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel $model */
		$model = $this->subject->find($uid);
		$this->assertSame(
			$friendTitle,
			$model->getFriend()->getTitle()
		);
	}

	/*
	 * Tests concerning the m:n mapping with a comma-separated list of UIDs
	 */

	/**
	 * @test
	 */
	public function commaSeparatedRelationsWithOneUidReturnsListWithRelatedModelWithData() {
		$childTitle = 'Abraham';
		$childUid = $this->testingFramework->createRecord('tx_oelib_test', array('title' => $childTitle));
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('children' => (string)$childUid)
		);

		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel $model */
		$model = $this->subject->find($uid);
		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel $firstChild */
		$firstChild = $model->getChildren()->first();
		$this->assertSame(
			$childTitle,
			$firstChild->getTitle()
		);
	}

	/*
	 * Tests concerning the m:n mapping using an m:n table
	 */

	/**
	 * @test
	 */
	public function mnRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData() {
		$relatedTitle = 'Geralt of Rivia';
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid = $this->testingFramework->createRecord('tx_oelib_test', array('title' => $relatedTitle));
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $uid, $relatedUid, 'related_records'
		);

		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel $model */
		$model = $this->subject->find($uid);
		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel $firstRelatedModel */
		$firstRelatedModel = $model->getRelatedRecords()->first();
		$this->assertSame(
			$relatedTitle,
			$firstRelatedModel->getTitle()
		);
	}

	/*
	 * Tests concerning the bidirectional m:n mapping using an m:n table.
	 */

	/**
	 * @test
	 */
	public function bidirectionalMNRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$relatedUid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_oelib_test', $relatedUid, $uid, 'bidirectional'
		);

		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel $model */
		$model = $this->subject->find($relatedUid);
		$this->assertSame(
			(string) $uid,
			$model->getBidirectional()->getUids()
		);
	}

	/*
	 * Tests concerning the 1:n mapping using a foreign field.
	 */

	/**
	 * @test
	 */
	public function oneToManyRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData() {
		$relatedTitle = 'Triss Merrigold';
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('composition' => 1)
		);
		$this->testingFramework->createRecord(
			'tx_oelib_testchild', array('parent' => $uid, 'title' => $relatedTitle)
		);

		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel $model */
		$model = $this->subject->find($uid);
		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel $firstChildModel */
		$firstChildModel = $model->getComposition()->first();
		$this->assertSame(
			$relatedTitle,
			$firstChildModel->getTitle()
		);
	}
}