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
 * @package    TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Functional_ModelTest extends Tx_Phpunit_TestCase {
	/**
	 * @var string
	 */
	const TEST_RECORD_TITLE = 'Hello world';

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var Tx_Oelib_Tests_Unit_Fixtures_TestingModel
	 */
	private $subject = NULL;

	/**
	 * @var Tx_Oelib_Tests_Unit_Fixtures_TestingMapper
	 */
	protected $dataMapper = NULL;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');
		Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);
		$this->dataMapper = Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper');

		$uid = $this->createTestRecord();
		$this->subject = $this->dataMapper->find($uid);
	}

	/*
	 * Tests concerning __clone
	 */

	/**
	 * Creates a test record.
	 *
	 * @return int the UID
	 */
	private function createTestRecord() {
		return $this->testingFramework->createRecord('tx_oelib_test', array('title' => self::TEST_RECORD_TITLE));
	}

	/**
	 * @test
	 */
	public function cloneReturnsInstanceOfSameClass() {
		self::assertInstanceOf(
			get_class($this->subject),
			clone $this->subject
		);
	}

	/**
	 * @test
	 */
	public function cloneReturnsNewInstance() {
		self::assertNotSame(
			$this->subject,
			clone $this->subject
		);
	}

	/**
	 * @return int[][]
	 */
	public function cloneableStatusDataProvider() {
		return array(
			'virgin' => array(Tx_Oelib_Model::STATUS_VIRGIN),
			'ghost' => array(Tx_Oelib_Model::STATUS_GHOST),
			'loaded' => array(Tx_Oelib_Model::STATUS_LOADED),
		);
	}

	/**
	 * @test
	 *
	 * @param string $status
	 *
	 * @dataProvider cloneableStatusDataProvider
	 */
	public function cloneReturnsDirtyModel($status) {
		$this->subject->setLoadStatus($status);

		$clone = clone $this->subject;
		self::assertTrue(
			$clone->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function cloningVirginModelReturnsVirginModel() {
		$subject = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		self::assertTrue($subject->isVirgin());

		$clone = clone $subject;

		self::assertTrue($clone->isVirgin());
	}

	/**
	 * @test
	 */
	public function cloningGhostLoadsModel() {
		self::assertTrue($this->subject->isGhost());

		$clone = clone $this->subject;

		self::assertTrue($clone->isLoaded());
	}

	/**
	 * @test
	 */
	public function cloningLoadedModelReturnsLoadedModel() {
		self::assertSame(self::TEST_RECORD_TITLE, $this->subject->getTitle());
		self::assertTrue($this->subject->isLoaded());

		$clone = clone $this->subject;

		self::assertTrue($clone->isLoaded());
	}

	/**
	 * @test
	 */
	public function cloningModelWithUidReturnsModelWithoutUid() {
		self::assertTrue($this->subject->hasUid());

		$clone = clone $this->subject;

		self::assertFalse($clone->hasUid());
	}

	/**
	 * @test
	 */
	public function clonedModelHasStringDataFromOriginal() {
		$clone = clone $this->subject;

		self::assertSame($this->subject->getTitle(), $clone->getTitle());
	}

	/**
	 * @test
	 */
	public function clonedModelHasNto1RelationFromOriginal() {
		$relatedRecord = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$relatedRecord->setData(array());
		$this->subject->setFriend($relatedRecord);
		$this->dataMapper->save($this->subject);

		$clone = clone $this->subject;

		self::assertSame($this->subject->getFriend(), $clone->getFriend());
	}

	/**
	 * @test
	 */
	public function clonedModelHasModelsFromMtoNRelationFromOriginal() {
		$relatedRecord = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$relatedRecord->setData(array());
		$this->subject->addRelatedRecord($relatedRecord);
		$this->dataMapper->save($this->subject);

		$clone = clone $this->subject;

		self::assertSame($relatedRecord, $clone->getRelatedRecords()->first());
	}

	/**
	 * @test
	 */
	public function clonedModelHasNewInstanceOfMtoNRelation() {
		$relatedRecord = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$relatedRecord->setData(array());
		$this->subject->addRelatedRecord($relatedRecord);
		$this->dataMapper->save($this->subject);

		$clone = clone $this->subject;

		self::assertNotSame($clone->getRelatedRecords(), $this->subject->getRelatedRecords());
	}

	/**
	 * @test
	 */
	public function clonedModelHasMtoNRelationWithCloneAsParentModel() {
		$relatedRecord = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$relatedRecord->setData(array());
		$this->subject->addRelatedRecord($relatedRecord);
		$this->dataMapper->save($this->subject);
		self::assertSame($this->subject, $this->subject->getRelatedRecords()->getParentModel());

		$clone = clone $this->subject;

		self::assertSame($clone, $clone->getRelatedRecords()->getParentModel());
	}

	/**
	 * @test
	 */
	public function clonedModelHasClonesOfModelsFrom1toNRelationFromOriginal() {
		$childRecord = new Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel();
		$childRecordTitle = 'bubble bobble';
		$childRecord->setTitle($childRecordTitle);
		$this->subject->addCompositionRecord($childRecord);
		$this->dataMapper->save($this->subject);

		$clone = clone $this->subject;

		/** @var Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel $firstCloneChild */
		$firstCloneChild = $clone->getComposition()->first();
		self::assertSame($childRecord->getTitle(), $firstCloneChild->getTitle());
		self::assertNotSame($childRecord, $firstCloneChild);
	}

	/**
	 * @test
	 */
	public function clonedModelHasNewInstanceOf1toNRelation() {
		$childRecord = new Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel();
		$childRecord->setData(array());
		$this->subject->addCompositionRecord($childRecord);
		$this->dataMapper->save($this->subject);

		$clone = clone $this->subject;

		self::assertNotSame($clone->getRelatedRecords(), $this->subject->getComposition());
	}

	/**
	 * @test
	 */
	public function clonedModelHas1toNRelationWithCloneAsParentModel() {
		$childRecord = new Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel();
		$childRecord->setData(array());
		$this->subject->addCompositionRecord($childRecord);
		$this->dataMapper->save($this->subject);
		self::assertSame($this->subject, $this->subject->getRelatedRecords()->getParentModel());

		$clone = clone $this->subject;

		self::assertSame($clone, $clone->getComposition()->getParentModel());
	}
}