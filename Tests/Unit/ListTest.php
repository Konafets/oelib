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
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ListTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_List
	 */
	private $subject;

	/**
	 * @var Tx_Oelib_Model[] models that need to be cleaned up during tearDown.
	 */
	private $modelStorage = array();

	/**
	 * @var bool
	 */
	private $deprecationLogEnabledBackup = FALSE;

	protected function setUp() {
		$this->deprecationLogEnabledBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];

		$this->subject = new Tx_Oelib_List();
	}

	protected function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = $this->deprecationLogEnabledBackup;
	}


	///////////////////////
	// Utility functions
	///////////////////////

	/**
	 * @param Tx_Oelib_Tests_Unit_Fixtures_TestingModel $firstModel
	 * @param Tx_Oelib_Tests_Unit_Fixtures_TestingModel $secondModel
	 *
	 * @return int
	 */
	public function sortByTitleAscending(
		Tx_Oelib_Tests_Unit_Fixtures_TestingModel $firstModel,
		Tx_Oelib_Tests_Unit_Fixtures_TestingModel $secondModel
	) {
		return strcmp($firstModel->getTitle(), $secondModel->getTitle());
	}

	/**
	 * @param Tx_Oelib_Tests_Unit_Fixtures_TestingModel $firstModel
	 * @param Tx_Oelib_Tests_Unit_Fixtures_TestingModel $secondModel
	 *
	 * @return int
	 */
	public function sortByTitleDescending(
		Tx_Oelib_Tests_Unit_Fixtures_TestingModel $firstModel,
		Tx_Oelib_Tests_Unit_Fixtures_TestingModel $secondModel
	) {
		return strcmp($secondModel->getTitle(), $firstModel->getTitle());
	}

	/**
	 * Adds models with the given titles to the subject, one for each title
	 * given in $titles.
	 *
	 * @param string[] $titles
	 *        the titles for the models, must not be empty
	 *
	 * @return void
	 */
	private function addModelsToFixture(array $titles = array('')) {
		foreach ($titles as $title) {
			$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
			$model->setTitle($title);
			$this->subject->add($model);

			$this->modelStorage[] = $model;
		}
	}


	///////////////////////////////////////////
	// Tests concerning the utility functions
	///////////////////////////////////////////

	//////////////////////////////////////////
	// Tests concerning sortByTitleAscending
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function sortByTitleAscendingForFirstModelTitleAlphaAndSecondModelTitleBetaReturnsMinusOne() {
		$firstModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$firstModel->setTitle('alpha');
		$secondModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$secondModel->setTitle('beta');

		$this->assertSame(
			-1,
			$this->sortByTitleAscending($firstModel, $secondModel)
		);
	}

	/**
	 * @test
	 */
	public function sortByTitleAscendingForFirstModelTitleBetaAndSecondModelTitleAlphaReturnsOne() {
		$firstModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$firstModel->setTitle('beta');
		$secondModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$secondModel->setTitle('alpha');

		$this->assertSame(
			1,
			$this->sortByTitleAscending($firstModel, $secondModel)
		);
	}

	/**
	 * @test
	 */
	public function sortByTitleAscendingForFirstAndSecondModelTitleSameReturnsZero() {
		$firstModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$firstModel->setTitle('alpha');
		$secondModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$secondModel->setTitle('alpha');

		$this->assertSame(
			0,
			$this->sortByTitleAscending($firstModel, $secondModel)
		);
	}


	///////////////////////////////////////////
	// Tests concerning sortByTitleDescending
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function sortByTitleDescendingForFirstModelTitleAlphaAndSecondModelTitleBetaReturnsOne() {
		$firstModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$firstModel->setTitle('alpha');
		$secondModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$secondModel->setTitle('beta');

		$this->assertSame(
			1,
			$this->sortByTitleDescending($firstModel, $secondModel)
		);
	}

	/**
	 * @test
	 */
	public function sortByTitleDescendingForFirstModelTitleBetaAndSecondModelTitleAlphaReturnsMinusOne() {
		$firstModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$firstModel->setTitle('beta');
		$secondModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$secondModel->setTitle('alpha');

		$this->assertSame(
			-1,
			$this->sortByTitleDescending($firstModel, $secondModel)
		);
	}

	/**
	 * @test
	 */
	public function sortByTitleDescendingForFirstAndSecondModelTitleSameReturnsZero() {
		$firstModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$firstModel->setTitle('alpha');
		$secondModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$secondModel->setTitle('alpha');

		$this->assertSame(
			0,
			$this->sortByTitleDescending($firstModel, $secondModel)
		);
	}


	////////////////////////////////////////
	// Tests concerning addModelsToFixture
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function addModelsToFixtureForOneGivenTitleAddsOneModelToFixture() {
		$this->addModelsToFixture(array('foo'));

		$this->assertSame(
			1,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function addModelsToFixtureForOneGivenTitleAddsModelWithTitleGiven() {
		$this->addModelsToFixture(array('foo'));

		$this->assertSame(
			'foo',
			$this->subject->first()->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function addModelsToFixtureForTwoGivenTitlesAddsTwoModelsToFixture() {
		$this->addModelsToFixture(array('foo', 'bar'));

		$this->assertSame(
			2,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function addModelsToFixtureForTwoGivenTitlesAddsFirstTitleToFirstModelFixture() {
		$this->addModelsToFixture(array('bar', 'foo'));

		$this->assertSame(
			'bar',
			$this->subject->first()->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function addModelsToFixtureForThreeGivenTitlesAddsThreeModelsToFixture() {
		$this->addModelsToFixture(array('foo', 'bar','fooBar'));

		$this->assertSame(
			3,
			$this->subject->count()
		);
	}


	/////////////////////////////
	// Tests concerning isEmpty
	/////////////////////////////

	/**
	 * @test
	 */
	public function isEmptyForEmptyListReturnsTrue() {
		$this->assertTrue(
			$this->subject->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function isEmptyAfterAddingModelReturnsFalse() {
		$this->addModelsToFixture();

		$this->assertFalse(
			$this->subject->isEmpty()
		);
	}


	///////////////////////////
	// Tests concerning count
	///////////////////////////

	/**
	 * @test
	 */
	public function countForEmptyListReturnsZero() {
		$this->assertSame(
			0,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function countWithOneModelWithoutUidReturnsOne() {
		$this->addModelsToFixture();

		$this->assertSame(
			1,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function countWithOneModelWithUidReturnsOne() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(1);
		$this->subject->add($model);

		$this->assertSame(
			1,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function countWithTwoDifferentModelsReturnsTwo() {
		$this->addModelsToFixture(array('',''));

		$this->assertSame(
			2,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function countAfterAddingTheSameModelTwiceReturnsOne() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);
		$this->subject->add($model);

		$this->assertSame(
			1,
			$this->subject->count()
		);
	}


	/////////////////////////////
	// Tests concerning current
	/////////////////////////////

	/**
	 * @test
	 */
	public function currentForEmptyListReturnsNull() {
		$this->assertNull(
			$this->subject->current()
		);
	}

	/**
	 * @test
	 */
	public function currentWithOneItemReturnsThatItem() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);

		$this->assertSame(
			$model,
			$this->subject->current()
		);
	}

	/**
	 * @test
	 */
	public function currentWithTwoItemsInitiallyReturnsTheFirstItem() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model2);

		$this->assertSame(
			$model1,
			$this->subject->current()
		);
	}


	//////////////////////////////////
	// Tests concerning key and next
	//////////////////////////////////

	/**
	 * @test
	 */
	public function keyInitiallyReturnsZero() {
		$this->assertSame(
			0,
			$this->subject->key()
		);
	}

	/**
	 * @test
	 */
	public function keyAfterNextInListWithOneElementReturnsOne() {
		$this->addModelsToFixture();
		$this->subject->next();

		$this->assertSame(
			1,
			$this->subject->key()
		);
	}

	/**
	 * @test
	 */
	public function currentWithOneItemAfterNextReturnsNull() {
		$this->addModelsToFixture();

		$this->subject->next();

		$this->assertNull(
			$this->subject->current()
		);
	}

	/**
	 * @test
	 */
	public function currentWithTwoItemsAfterNextReturnsTheSecondItem() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model2);

		$this->subject->next();

		$this->assertSame(
			$model2,
			$this->subject->current()
		);
	}


	////////////////////////////
	// Tests concerning rewind
	////////////////////////////

	/**
	 * @test
	 */
	public function rewindAfterNextResetsKeyToZero() {
		$this->subject->next();
		$this->subject->rewind();

		$this->assertSame(
			0,
			$this->subject->key()
		);
	}

	/**
	 * @test
	 */
	public function rewindAfterNextForOneItemsResetsCurrentToTheOnlyItem() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);

		$this->subject->next();
		$this->subject->rewind();

		$this->assertSame(
			$model,
			$this->subject->current()
		);
	}


	///////////////////////////
	// Tests concerning first
	///////////////////////////

	/**
	 * @test
	 */
	public function firstForEmptyListReturnsNull() {
		$this->assertNull(
			$this->subject->first()
		);
	}

	/**
	 * @test
	 */
	public function firstForListWithOneItemReturnsThatItem() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);

		$this->assertSame(
			$model,
			$this->subject->first()
		);
	}

	/**
	 * @test
	 */
	public function firstWithTwoItemsReturnsTheFirstItem() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model2);

		$this->assertSame(
			$model1,
			$this->subject->first()
		);
	}

	/**
	 * @test
	 */
	public function firstWithTwoItemsAfterNextReturnsTheFirstItem() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model2);

		$this->subject->next();

		$this->assertSame(
			$model1,
			$this->subject->first()
		);
	}


	///////////////////////////
	// Tests concerning valid
	///////////////////////////

	/**
	 * @test
	 */
	public function validForEmptyListReturnsFalse() {
		$this->assertFalse(
			$this->subject->valid()
		);
	}

	/**
	 * @test
	 */
	public function validForOneElementInitiallyReturnsTrue() {
		$this->addModelsToFixture();

		$this->assertTrue(
			$this->subject->valid()
		);
	}

	/**
	 * @test
	 */
	public function validForOneElementAfterNextReturnsFalse() {
		$this->addModelsToFixture();

		$this->subject->next();

		$this->assertFalse(
			$this->subject->valid()
		);
	}

	/**
	 * @test
	 */
	public function validForOneElementAfterNextAndRewindReturnsTrue() {
		$this->addModelsToFixture();

		$this->subject->next();
		$this->subject->rewind();

		$this->assertTrue(
			$this->subject->valid()
		);
	}


	///////////////////////////////////////////
	// Tests concerning the Iterator property
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function isIterator() {
		$this->assertTrue(
			$this->subject instanceof Iterator
		);
	}

	/**
	 * @test
	 */
	public function iteratingOverOneItemDoesNotFail() {
		$this->addModelsToFixture();

		$this->subject->next();
		$this->subject->rewind();

		foreach ($this->subject as $value);
	}


	/////////////////////////////
	// Tests concerning getUids
	/////////////////////////////

	/**
	 * @test
	 */
	public function getUidsForEmptyListReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getUidsForOneItemsWithoutUidReturnsEmptyString() {
		$this->addModelsToFixture();

		$this->assertSame(
			'',
			$this->subject->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getUidsForOneItemsWithUidReturnsThatUid() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(1);
		$this->subject->add($model);

		$this->assertSame(
			'1',
			$this->subject->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getUidsForTwoItemsWithUidReturnsCommaSeparatedItems() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model1->setUid(1);
		$this->subject->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model2->setUid(42);
		$this->subject->add($model2);

		$this->assertSame(
			'1,42',
			$this->subject->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getUidsForTwoItemsWithDecreasingUidReturnsItemsInOrdnerOfInsertion() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model1->setUid(42);
		$this->subject->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model2->setUid(1);
		$this->subject->add($model2);

		$this->assertSame(
			'42,1',
			$this->subject->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getUidsForDuplicateUidsReturnsUidsInOrdnerOfFirstInsertion() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model1->setUid(1);
		$this->subject->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model2->setUid(2);
		$this->subject->add($model2);

		$this->subject->add($model1);

		$this->assertSame(
			'1,2',
			$this->subject->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getUidsForElementThatGotItsUidAfterAddingItReturnsItsUid() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);
		$model->setUid(42);

		$this->assertSame(
			'42',
			$this->subject->getUids()
		);
	}


	////////////////////////////
	// Tests concerning hasUid
	////////////////////////////

	/**
	 * @test
	 */
	public function hasUidForInexistentUidReturnsFalse() {
		$this->assertFalse(
			$this->subject->hasUid(42)
		);
	}

	/**
	 * @test
	 */
	public function hasUidForExistingUidReturnsTrue() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(42);
		$this->subject->add($model);

		$this->assertTrue(
			$this->subject->hasUid(42)
		);
	}

	/**
	 * @test
	 */
	public function hasUidForElementThatGotItsUidAfterAddingItReturnsTrue() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);
		$model->setUid(42);

		$this->assertTrue(
			$this->subject->hasUid(42)
		);
	}


	//////////////////////////
	// Tests concerning sort
	//////////////////////////

	/**
	 * @test
	 */
	public function sortWithTwoModelsAndSortByTitleAscendingFunctionSortsModelsByTitleAscending() {
		$this->addModelsToFixture(array('Beta', 'Alpha'));
		$this->subject->sort(array($this, 'sortByTitleAscending'));

		$this->assertSame(
			'Alpha',
			$this->subject->first()->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function sortWithThreeModelsAndSortByTitleAscendingFunctionSortsModelsByTitleAscending() {
		$this->addModelsToFixture(array('Zeta', 'Beta', 'Alpha'));
		$this->subject->sort(array($this, 'sortByTitleAscending'));

		$this->assertSame(
			'Alpha',
			$this->subject->first()->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function sortWithTwoModelsAndSortByTitleDescendingFunctionSortsModelsByTitleDescending() {
		$this->addModelsToFixture(array('Alpha', 'Beta'));
		$this->subject->sort(array($this, 'sortByTitleDescending'));

		$this->assertSame(
			'Beta',
			$this->subject->first()->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function sortMakesListDirty() {
		$subject = $this->getMock('Tx_Oelib_List', array('markAsDirty'));
		$subject->expects($this->once())->method('markAsDirty');

		$subject->sort(array($this, 'sortByTitleAscending'));
	}


	////////////////////////////
	// Tests concerning append
	////////////////////////////

	/**
	 * @test
	 */
	public function appendEmptyListToEmptyListMakesEmptyList() {
		$otherList = new Tx_Oelib_List();
		$this->subject->append($otherList);

		$this->assertTrue(
			$this->subject->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function appendTwoItemListToEmptyListMakesTwoItemList() {
		$otherList = new Tx_Oelib_List();
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($model2);

		$this->subject->append($otherList);

		$this->assertSame(
			2,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function appendEmptyListToTwoItemListMakesTwoItemList() {
		$this->addModelsToFixture(array('First', 'Second'));

		$otherList = new Tx_Oelib_List();
		$this->subject->append($otherList);

		$this->assertSame(
			2,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function appendOneItemListToOneItemListWithTheSameItemMakesOneItemList() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(42);
		$this->subject->add($model);

		$otherList = new Tx_Oelib_List();
		$otherList->add($model);

		$this->subject->append($otherList);

		$this->assertSame(
			1,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function appendTwoItemListKeepsOrderOfAppendedItems() {
		$otherList = new Tx_Oelib_List();
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($model2);

		$this->subject->append($otherList);

		$this->assertSame(
			$model1,
			$this->subject->first()
		);
	}

	/**
	 * @test
	 */
	public function appendAppendsItemAfterExistingItems() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);

		$otherList = new Tx_Oelib_List();
		$otherModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($otherModel);

		$this->subject->append($otherList);

		$this->assertSame(
			$model,
			$this->subject->first()
		);
	}


	//////////////////////////////////
	// Tests concerning appendUnique
	//////////////////////////////////

	/**
	 * @test
	 */
	public function appendUniqueForEmptyListToEmptyListMakesEmptyList() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$otherList = new Tx_Oelib_List();
		$this->subject->appendUnique($otherList);

		$this->assertTrue(
			$this->subject->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function appendUniqueForTwoItemListToEmptyListMakesTwoItemList() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$otherList = new Tx_Oelib_List();
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($model2);

		$this->subject->appendUnique($otherList);

		$this->assertSame(
			2,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function appendUniqueForEmptyListToTwoItemListMakesTwoItemList() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->addModelsToFixture(array('First', 'Second'));

		$otherList = new Tx_Oelib_List();
		$this->subject->appendUnique($otherList);

		$this->assertSame(
			2,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function appendUniqueForOneItemListToOneItemListWithTheSameItemMakesOneItemList() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(42);
		$this->subject->add($model);

		$otherList = new Tx_Oelib_List();
		$otherList->add($model);

		$this->subject->appendUnique($otherList);

		$this->assertSame(
			1,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function appendUniqueForTwoItemListKeepsOrderOfAppendedItems() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$otherList = new Tx_Oelib_List();
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($model2);

		$this->subject->appendUnique($otherList);

		$this->assertSame(
			$model1,
			$this->subject->first()
		);
	}

	/**
	 * @test
	 */
	public function appendUniqueAppendsItemAfterExistingItems() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);

		$otherList = new Tx_Oelib_List();
		$otherModel = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$otherList->add($otherModel);

		$this->subject->appendUnique($otherList);

		$this->assertSame(
			$model,
			$this->subject->first()
		);
	}


	//////////////////////////////////
	// Tests concerning purgeCurrent
	//////////////////////////////////

	/**
	 * @test
	 */
	public function purgeCurrentWithEmptyListDoesNotFail() {
		$this->subject->purgeCurrent();
	}

	/**
	 * @test
	 */
	public function purgeCurrentWithRewoundOneElementListMakesListEmpty() {
		$this->addModelsToFixture();

		$this->subject->rewind();
		$this->subject->purgeCurrent();

		$this->assertTrue(
			$this->subject->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentWithRewoundOneElementListMakesPointerInvalid() {
		$this->addModelsToFixture();

		$this->subject->rewind();
		$this->subject->purgeCurrent();

		$this->assertFalse(
			$this->subject->valid()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentWithOneElementListAndPointerAfterLastItemLeavesListUntouched() {
		$this->addModelsToFixture();

		$this->subject->rewind();
		$this->subject->next();
		$this->subject->purgeCurrent();

		$this->assertFalse(
			$this->subject->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentForFirstOfTwoElementsMakesOneItemList() {
		$this->addModelsToFixture(array('', ''));

		$this->subject->rewind();
		$this->subject->purgeCurrent();

		$this->assertSame(
			1,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentForSecondOfTwoElementsMakesOneItemList() {
		$this->addModelsToFixture(array('', ''));

		$this->subject->rewind();
		$this->subject->next();
		$this->subject->purgeCurrent();

		$this->assertSame(
			1,
			$this->subject->count()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentForFirstOfTwoElementsSetsPointerToFormerSecondElement() {
		$this->addModelsToFixture();

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);

		$this->subject->rewind();
		$this->subject->purgeCurrent();

		$this->assertSame(
			$model,
			$this->subject->current()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentForSecondOfTwoElementsInForeachLoopDoesNotChangeNumberOfIterations() {
		$this->addModelsToFixture(array('', ''));

		$completedIterations = 0;

		foreach ($this->subject as $model) {
			if ($completedIterations === 1) {
				$this->subject->purgeCurrent();
			}

			$completedIterations++;
		}

		$this->assertSame(
			2,
			$completedIterations
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentForSecondOfTwoElementsInWhileLoopDoesNotChangeNumberOfIterations() {
		$this->addModelsToFixture(array('', ''));

		$completedIterations = 0;

		while ($this->subject->valid()) {
			if ($completedIterations === 1) {
				$this->subject->purgeCurrent();
			}

			$completedIterations++;
			$this->subject->next();
		}

		$this->assertSame(
			2,
			$completedIterations
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentForModelWithUidRemovesModelFromGetUids() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(1);
		$this->subject->add($model);
		$this->modelStorage[] = $model;

		$this->subject->rewind();
		$this->subject->purgeCurrent();

		$this->assertSame(
			'',
			$this->subject->getUids()
		);
	}


	///////////////////////////////////
	// Tests concerning sortBySorting
	///////////////////////////////////

	/**
	 * @test
	 */
	public function sortBySortingMovesItemWithHigherSortingValueAfterItemWithLowerSortingValue() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel();
		$model1->setSorting(2);
		$this->subject->add($model1);

		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingChildModel();
		$model2->setSorting(1);
		$this->subject->add($model2);

		$this->subject->sortBySorting();

		$this->assertSame(
			$model2,
			$this->subject->first()
		);
	}


	////////////////////////
	// Tests concerning at
	////////////////////////

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function atForNegativePositionThrowsException() {
		$this->subject->at(-1);
	}

	/**
	 * @test
	 */
	public function atForPositionZeroWithEmptyListReturnsNull() {
		$this->assertNull(
			$this->subject->at(0)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionOneWithEmptyListReturnsNull() {
		$this->assertNull(
			$this->subject->at(1)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionZeroWithOneItemListReturnsItem() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);

		$this->assertSame(
			$model,
			$this->subject->at(0)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionOneWithOneItemListReturnsNull() {
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());

		$this->assertNull(
			$this->subject->at(1)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionZeroWithTwoItemListReturnsFirstItem() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model1);
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());

		$this->assertSame(
			$model1,
			$this->subject->at(0)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionOneWithTwoItemListReturnsSecondItem() {
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model2);

		$this->assertSame(
			$model2,
			$this->subject->at(1)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionTwoWithTwoItemListReturnsNull() {
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());

		$this->assertNull(
			$this->subject->at(2)
		);
	}


	/////////////////////////////
	// Tests concerning inRange
	/////////////////////////////

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function inRangeWithNegativeStartThrowsException() {
		$this->subject->inRange(-1, 1);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function inRangeWithNegativeLengthThrowsException() {
		$this->subject->inRange(1, -1);
	}

	/**
	 * @test
	 */
	public function inRangeWithZeroLengthReturnsEmptyList() {
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());

		$this->assertTrue(
			$this->subject->inRange(1, 0)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function inRangeCanReturnOneElementFromStartOfList() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());

		$result = $this->subject->inRange(0, 1);
		$this->assertSame(
			1,
			$result->count()
		);
		$this->assertSame(
			$model,
			$result->first()
		);
	}

	/**
	 * @test
	 */
	public function inRangeCanReturnOneElementAfterStartOfList() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());
		$this->subject->add($model);

		$result = $this->subject->inRange(1, 1);
		$this->assertSame(
			1,
			$result->count()
		);
		$this->assertSame(
			$model,
			$result->first()
		);
	}

	/**
	 * @test
	 */
	public function inRangeCanReturnTwoElementsFromStartOfList() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model2);

		$this->assertSame(
			2,
			$this->subject->inRange(0, 2)->count()
		);
	}

	/**
	 * @test
	 */
	public function inRangeWithStartAfterListEndReturnsEmptyList() {
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());

		$this->assertTrue(
			$this->subject->inRange(1, 1)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function inRangeWithRangeCrossingListEndReturnsElementUpToListEnd() {
		$this->subject->add(new Tx_Oelib_Tests_Unit_Fixtures_TestingModel());
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);

		$result = $this->subject->inRange(1, 2);

		$this->assertSame(
			1,
			$result->count()
		);
		$this->assertSame(
			$model,
			$result->first()
		);
	}


	/*
	/* Tests concerning toArray
	 */

	/**
	 * @test
	 */
	public function toArrayForNoElementsReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->toArray()
		);
	}

	/**
	 * @test
	 */
	public function toArrayWithOneElementReturnsArrayWithElement() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model);

		$this->assertSame(
			array($model),
			$this->subject->toArray()
		);
	}

	/**
	 * @test
	 */
	public function toArrayWithTwoElementsReturnsArrayWithBothElementsInAddingOrder() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model1);
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$this->subject->add($model2);

		$this->assertSame(
			array($model1, $model2),
			$this->subject->toArray()
		);
	}
}