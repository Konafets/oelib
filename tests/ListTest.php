<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_List class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_ListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_List
	 */
	private $fixture;

	/**
	 * @var array models that need to be cleaned up during tearDown.
	 */
	private $modelStorage = array();

	/**
	 * @var boolean
	 */
	private $deprecationLogEnabledBackup = FALSE;

	public function setUp() {
		$this->deprecationLogEnabledBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];

		$this->fixture = new tx_oelib_List();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		foreach($this->modelStorage as $key => $model ) {
			$model->__destruct();
			unset($this->modelStorage[$key]);
		}

		unset($this->fixture, $this->modelStorage);

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = $this->deprecationLogEnabledBackup;
	}


	///////////////////////
	// Utitlity functions
	///////////////////////

	public function sortByTitleAscending(
		tx_oelib_tests_fixtures_TestingModel $firstModel,
		tx_oelib_tests_fixtures_TestingModel $secondModel
	) {
		return strcmp($firstModel->getTitle(), $secondModel->getTitle());
	}

	public function sortByTitleDescending(
		tx_oelib_tests_fixtures_TestingModel $firstModel,
		tx_oelib_tests_fixtures_TestingModel $secondModel
	) {
		return strcmp($secondModel->getTitle(), $firstModel->getTitle());
	}

	/**
	 * Adds models with the given titles to the fixture, one for each title
	 * given in $titles.
	 *
	 * @param array<string> $titles
	 *        the titles for the models, must not be empty
	 *
	 * @return void
	 */
	private function addModelsToFixture(array $titles = array('')) {
		foreach ($titles as $title) {
			$model = new tx_oelib_tests_fixtures_TestingModel();
			$model->setTitle($title);
			$this->fixture->add($model);

			$this->modelStorage[] = $model;
		}
	}


	///////////////////////////////////////////
	// Tests concerning the utility functions
	///////////////////////////////////////////

	//////////////////////////////////////////
	// Tests concerning sortByTitleAscending
	//////////////////////////////////////////

	public function test_sortByTitleAscending_ForFirstModelTitleAlphaAndSecondModelTitleBeta_ReturnsMinusOne() {
		$firstModel = new tx_oelib_tests_fixtures_TestingModel();
		$firstModel->setTitle('alpha');
		$secondModel = new tx_oelib_tests_fixtures_TestingModel();
		$secondModel->setTitle('beta');

		$this->assertSame(
			-1,
			$this->sortByTitleAscending($firstModel, $secondModel)
		);

		$firstModel->__destruct();
		$secondModel->__destruct();
	}

	public function test_sortByTitleAscending_ForFirstModelTitleBetaAndSecondModelTitleAlpha_ReturnsOne() {
		$firstModel = new tx_oelib_tests_fixtures_TestingModel();
		$firstModel->setTitle('beta');
		$secondModel = new tx_oelib_tests_fixtures_TestingModel();
		$secondModel->setTitle('alpha');

		$this->assertSame(
			1,
			$this->sortByTitleAscending($firstModel, $secondModel)
		);

		$firstModel->__destruct();
		$secondModel->__destruct();
	}

	public function test_sortByTitleAscending_ForFirstAndSecontModelTitleSame_ReturnsZero() {
		$firstModel = new tx_oelib_tests_fixtures_TestingModel();
		$firstModel->setTitle('alpha');
		$secondModel = new tx_oelib_tests_fixtures_TestingModel();
		$secondModel->setTitle('alpha');

		$this->assertSame(
			0,
			$this->sortByTitleAscending($firstModel, $secondModel)
		);

		$firstModel->__destruct();
		$secondModel->__destruct();
	}


	///////////////////////////////////////////
	// Tests concerning sortByTitleDescending
	///////////////////////////////////////////

	public function test_sortByTitleDescending_ForFirstModelTitleAlphaAndSecondModelTitleBeta_ReturnsOne() {
		$firstModel = new tx_oelib_tests_fixtures_TestingModel();
		$firstModel->setTitle('alpha');
		$secondModel = new tx_oelib_tests_fixtures_TestingModel();
		$secondModel->setTitle('beta');

		$this->assertSame(
			1,
			$this->sortByTitleDescending($firstModel, $secondModel)
		);

		$firstModel->__destruct();
		$secondModel->__destruct();
	}

	public function test_sortByTitleDescending_ForFirstModelTitleBetaAndSecondModelTitleAlpha_ReturnsMinusOne() {
		$firstModel = new tx_oelib_tests_fixtures_TestingModel();
		$firstModel->setTitle('beta');
		$secondModel = new tx_oelib_tests_fixtures_TestingModel();
		$secondModel->setTitle('alpha');

		$this->assertSame(
			-1,
			$this->sortByTitleDescending($firstModel, $secondModel)
		);

		$firstModel->__destruct();
		$secondModel->__destruct();
	}

	public function test_sortByTitleDescending_ForFirstAndSecontModelTitleSame_ReturnsZero() {
		$firstModel = new tx_oelib_tests_fixtures_TestingModel();
		$firstModel->setTitle('alpha');
		$secondModel = new tx_oelib_tests_fixtures_TestingModel();
		$secondModel->setTitle('alpha');

		$this->assertSame(
			0,
			$this->sortByTitleDescending($firstModel, $secondModel)
		);

		$firstModel->__destruct();
		$secondModel->__destruct();
	}


	////////////////////////////////////////
	// Tests concerning addModelsToFixture
	////////////////////////////////////////

	public function test_AddModelsToFixture_ForOneGivenTitle_AddsOneModelToFixture() {
		$this->addModelsToFixture(array('foo'));

		$this->assertSame(
			1,
			$this->fixture->count()
		);
	}

	public function test_AddModelsToFixture_ForOneGivenTitle_AddsModelWithTitleGiven() {
		$this->addModelsToFixture(array('foo'));

		$this->assertSame(
			'foo',
			$this->fixture->first()->getTitle()
		);
	}

	public function test_AddModelsToFixture_ForTwoGivenTitles_AddsTwoModelsToFixture() {
		$this->addModelsToFixture(array('foo', 'bar'));

		$this->assertSame(
			2,
			$this->fixture->count()
		);
	}

	public function test_AddModelsToFixture_ForTwoGivenTitles_AddsFirstTitleToFirstModelFixture() {
		$this->addModelsToFixture(array('bar', 'foo'));

		$this->assertSame(
			'bar',
			$this->fixture->first()->getTitle()
		);
	}

	public function test_AddModelsToFixture_ForThreeGivenTitles_AddsThreeModelsToFixture() {
		$this->addModelsToFixture(array('foo', 'bar','fooBar'));

		$this->assertSame(
			3,
			$this->fixture->count()
		);
	}


	/////////////////////////////
	// Tests concerning isEmpty
	/////////////////////////////

	public function testIsEmptyForEmptyListReturnsTrue() {
		$this->assertTrue(
			$this->fixture->isEmpty()
		);
	}

	public function testIsEmptyAfterAddingModelReturnsFalse() {
		$this->addModelsToFixture();

		$this->assertFalse(
			$this->fixture->isEmpty()
		);
	}


	///////////////////////////
	// Tests concerning count
	///////////////////////////

	public function testCountForEmptyListReturnsZero() {
		$this->assertSame(
			0,
			$this->fixture->count()
		);
	}

	public function testCountWithOneModelWithoutUidReturnsOne() {
		$this->addModelsToFixture();

		$this->assertSame(
			1,
			$this->fixture->count()
		);
	}

	public function testCountWithOneModelWithUidReturnsOne() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(1);
		$this->fixture->add($model);

		$this->assertSame(
			1,
			$this->fixture->count()
		);

		$model->__destruct();
	}

	public function testCountWithTwoDifferentModelsReturnsTwo() {
		$this->addModelsToFixture(array('',''));

		$this->assertSame(
			2,
			$this->fixture->count()
		);
	}

	/**
	 * @test
	 */
	public function countAfterAddingTheSameModelTwiceReturnsOne() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);
		$this->fixture->add($model);

		$this->assertSame(
			1,
			$this->fixture->count()
		);

		$model->__destruct();
	}


	/////////////////////////////
	// Tests concerning current
	/////////////////////////////

	public function testCurrentForEmptyListReturnsNull() {
		$this->assertNull(
			$this->fixture->current()
		);
	}

	public function testCurrentWithOneItemReturnsThatItem() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->assertSame(
			$model,
			$this->fixture->current()
		);

		$model->__destruct();
	}

	public function testCurrentWithTwoItemsInitiallyReturnsTheFirstItem() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model2);

		$this->assertSame(
			$model1,
			$this->fixture->current()
		);

		$model1->__destruct();
		$model2->__destruct();
	}


	//////////////////////////////////
	// Tests concerning key and next
	//////////////////////////////////

	public function testKeyInitiallyReturnsZero() {
		$this->assertSame(
			0,
			$this->fixture->key()
		);
	}

	public function testKeyAfterNextInListWithOneElementReturnsOne() {
		$this->addModelsToFixture();
		$this->fixture->next();

		$this->assertSame(
			1,
			$this->fixture->key()
		);
	}

	public function testCurrentWithOneItemAfterNextReturnsNull() {
		$this->addModelsToFixture();

		$this->fixture->next();

		$this->assertNull(
			$this->fixture->current()
		);
	}

	public function testCurrentWithTwoItemsAfterNextReturnsTheSecondItem() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model2);

		$this->fixture->next();

		$this->assertSame(
			$model2,
			$this->fixture->current()
		);

		$model1->__destruct();
		$model2->__destruct();
	}


	////////////////////////////
	// Tests concerning rewind
	////////////////////////////

	public function testRewindAfterNextResetsKeyToZero() {
		$this->fixture->next();
		$this->fixture->rewind();

		$this->assertSame(
			0,
			$this->fixture->key()
		);
	}

	public function testRewindAfterNextForOneItemsResetsCurrentToTheOnlyItem() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->fixture->next();
		$this->fixture->rewind();

		$this->assertSame(
			$model,
			$this->fixture->current()
		);

		$model->__destruct();
	}


	///////////////////////////
	// Tests concerning first
	///////////////////////////

	public function testFirstForEmptyListReturnsNull() {
		$this->assertNull(
			$this->fixture->first()
		);
	}

	public function testFirstForListWithOneItemReturnsThatItem() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->assertSame(
			$model,
			$this->fixture->first()
		);

		$model->__destruct();
	}

	public function testFirstWithTwoItemsReturnsTheFirstItem() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model2);

		$this->assertSame(
			$model1,
			$this->fixture->first()
		);

		$model1->__destruct();
		$model2->__destruct();
	}

	public function testFirstWithTwoItemsAfterNextReturnsTheFirstItem() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model2);

		$this->fixture->next();

		$this->assertSame(
			$model1,
			$this->fixture->first()
		);

		$model1->__destruct();
		$model2->__destruct();
	}


	///////////////////////////
	// Tests concerning valid
	///////////////////////////

	public function testValidForEmptyListReturnsFalse() {
		$this->assertFalse(
			$this->fixture->valid()
		);
	}

	public function testValidForOneElementInitiallyReturnsTrue() {
		$this->addModelsToFixture();

		$this->assertTrue(
			$this->fixture->valid()
		);
	}

	public function testValidForOneElementAfterNextReturnsFalse() {
		$this->addModelsToFixture();

		$this->fixture->next();

		$this->assertFalse(
			$this->fixture->valid()
		);
	}

	public function testValidForOneElementAfterNextAndRewindReturnsTrue() {
		$this->addModelsToFixture();

		$this->fixture->next();
		$this->fixture->rewind();

		$this->assertTrue(
			$this->fixture->valid()
		);
	}


	////////////////////////////////////////////
	// Tests concerning the Interator property
	////////////////////////////////////////////

	public function testIsIterator() {
		$this->assertTrue(
			$this->fixture instanceof Iterator
		);
	}

	public function testIteratingOverOneItemDoesNotFail() {
		$this->addModelsToFixture();

		$this->fixture->next();
		$this->fixture->rewind();

		foreach ($this->fixture as $value);
	}


	/////////////////////////////
	// Tests concerning getUids
	/////////////////////////////

	public function testGetUidsForEmptyListReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->getUids()
		);
	}

	public function testGetUidsForOneItemsWithoutUidReturnsEmptyString() {
		$this->addModelsToFixture();

		$this->assertSame(
			'',
			$this->fixture->getUids()
		);
	}

	public function testGetUidsForOneItemsWithUidReturnsThatUid() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(1);
		$this->fixture->add($model);

		$this->assertSame(
			'1',
			$this->fixture->getUids()
		);

		$model->__destruct();
	}

	public function testGetUidsForTwoItemsWithUidReturnsCommaSeparatedItems() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$model2->setUid(42);
		$this->fixture->add($model2);

		$this->assertSame(
			'1,42',
			$this->fixture->getUids()
		);

		$model1->__destruct();
		$model2->__destruct();
	}

	public function testGetUidsForTwoItemsWithDecreasingUidReturnsItemsInOrdnerOfInsertion() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$model1->setUid(42);
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$model2->setUid(1);
		$this->fixture->add($model2);

		$this->assertSame(
			'42,1',
			$this->fixture->getUids()
		);

		$model1->__destruct();
		$model2->__destruct();
	}

	public function testGetUidsForDuplicateUidsReturnsUidsInOrdnerOfFirstInsertion() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$model2->setUid(2);
		$this->fixture->add($model2);

		$this->fixture->add($model1);

		$this->assertSame(
			'1,2',
			$this->fixture->getUids()
		);

		$model1->__destruct();
		$model2->__destruct();
	}

	/**
	 * @test
	 */
	public function getUidsForElementThatGotItsUidAfterAddingItReturnsItsUid() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);
		$model->setUid(42);

		$this->assertSame(
			'42',
			$this->fixture->getUids()
		);

		$model->__destruct();
	}


	////////////////////////////
	// Tests concerning hasUid
	////////////////////////////

	public function testHasUidForInexistentUidReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasUid(42)
		);
	}

	public function testHasUidForExistingUidReturnsTrue() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(42);
		$this->fixture->add($model);

		$this->assertTrue(
			$this->fixture->hasUid(42)
		);

		$model->__destruct();
	}

	/**
	 * @test
	 */
	public function hasUidForElementThatGotItsUidAfterAddingItReturnsTrue() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);
		$model->setUid(42);

		$this->assertTrue(
			$this->fixture->hasUid(42)
		);

		$model->__destruct();
	}


	//////////////////////////
	// Tests concerning sort
	//////////////////////////

	public function test_sort_WithTwoModelsAndSortByTitleAscendingFunction_SortsModelsByTitleAscending() {
		$this->addModelsToFixture(array('Beta', 'Alpha'));
		$this->fixture->sort(array($this, 'sortByTitleAscending'));

		$this->assertSame(
			'Alpha',
			$this->fixture->first()->getTitle()
		);
	}

	public function test_sort_WithThreeModelsAndSortByTitleAscendingFunction_SortsModelsByTitleAscending() {
		$this->addModelsToFixture(array('Zeta', 'Beta', 'Alpha'));
		$this->fixture->sort(array($this, 'sortByTitleAscending'));

		$this->assertSame(
			'Alpha',
			$this->fixture->first()->getTitle()
		);
	}

	public function test_sort_WithTwoModelsAndSortByTitleDescendingFunction_SortsModelsByTitleDescending() {
		$this->addModelsToFixture(array('Alpha', 'Beta'));
		$this->fixture->sort(array($this, 'sortByTitleDescending'));

		$this->assertSame(
			'Beta',
			$this->fixture->first()->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function sortMakesListDirty() {
		$fixture = $this->getMock('tx_oelib_List', array('markAsDirty'));
		$fixture->expects($this->once())->method('markAsDirty');

		$fixture->sort(array($this, 'sortByTitleAscending'));
	}


	////////////////////////////
	// Tests concerning append
	////////////////////////////

	/**
	 * @test
	 */
	public function appendEmptyListToEmptyListMakesEmptyList() {
		$otherList = new tx_oelib_List();
		$this->fixture->append($otherList);

		$this->assertTrue(
			$this->fixture->isEmpty()
		);

		$otherList->__destruct();
	}

	/**
	 * @test
	 */
	public function appendTwoItemListToEmptyListMakesTwoItemList() {
		$otherList = new tx_oelib_List();
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($model2);

		$this->fixture->append($otherList);

		$this->assertSame(
			2,
			$this->fixture->count()
		);

		$otherList->__destruct();
		$model1->__destruct();
		$model2->__destruct();
	}

	/**
	 * @test
	 */
	public function appendEmptyListToTwoItemListMakesTwoItemList() {
		$this->addModelsToFixture(array('First', 'Second'));

		$otherList = new tx_oelib_List();
		$this->fixture->append($otherList);

		$this->assertSame(
			2,
			$this->fixture->count()
		);

		$otherList->__destruct();
	}

	/**
	 * @test
	 */
	public function appendOneItemListToOneItemListWithTheSameItemMakesOneItemList() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(42);
		$this->fixture->add($model);

		$otherList = new tx_oelib_List();
		$otherList->add($model);

		$this->fixture->append($otherList);

		$this->assertSame(
			1,
			$this->fixture->count()
		);

		$otherList->__destruct();
		$model->__destruct();
	}

	/**
	 * @test
	 */
	public function appendTwoItemListKeepsOrderOfAppendedItems() {
		$otherList = new tx_oelib_List();
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($model2);

		$this->fixture->append($otherList);

		$this->assertSame(
			$model1,
			$this->fixture->first()
		);

		$otherList->__destruct();
		$model1->__destruct();
		$model2->__destruct();
	}

	/**
	 * @test
	 */
	public function appendAppendsItemAfterExistingItems() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$otherList = new tx_oelib_List();
		$otherModel = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($otherModel);

		$this->fixture->append($otherList);

		$this->assertSame(
			$model,
			$this->fixture->first()
		);

		$otherList->__destruct();
		$model->__destruct();
		$otherModel->__destruct();
	}


	//////////////////////////////////
	// Tests concerning appendUnique
	//////////////////////////////////

	/**
	 * @test
	 */
	public function appendUniqueForEmptyListToEmptyListMakesEmptyList() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$otherList = new tx_oelib_List();
		$this->fixture->appendUnique($otherList);

		$this->assertTrue(
			$this->fixture->isEmpty()
		);

		$otherList->__destruct();
	}

	/**
	 * @test
	 */
	public function appendUniqueForTwoItemListToEmptyListMakesTwoItemList() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$otherList = new tx_oelib_List();
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($model2);

		$this->fixture->appendUnique($otherList);

		$this->assertSame(
			2,
			$this->fixture->count()
		);

		$otherList->__destruct();
		$model1->__destruct();
		$model2->__destruct();
	}

	/**
	 * @test
	 */
	public function appendUniqueForEmptyListToTwoItemListMakesTwoItemList() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->addModelsToFixture(array('First', 'Second'));

		$otherList = new tx_oelib_List();
		$this->fixture->appendUnique($otherList);

		$this->assertSame(
			2,
			$this->fixture->count()
		);

		$otherList->__destruct();
	}

	/**
	 * @test
	 */
	public function appendUniqueForOneItemListToOneItemListWithTheSameItemMakesOneItemList() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(42);
		$this->fixture->add($model);

		$otherList = new tx_oelib_List();
		$otherList->add($model);

		$this->fixture->appendUnique($otherList);

		$this->assertSame(
			1,
			$this->fixture->count()
		);

		$otherList->__destruct();
		$model->__destruct();
	}

	/**
	 * @test
	 */
	public function appendUniqueForTwoItemListKeepsOrderOfAppendedItems() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$otherList = new tx_oelib_List();
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($model2);

		$this->fixture->appendUnique($otherList);

		$this->assertSame(
			$model1,
			$this->fixture->first()
		);

		$otherList->__destruct();
		$model1->__destruct();
		$model2->__destruct();
	}

	/**
	 * @test
	 */
	public function appendUniqueAppendsItemAfterExistingItems() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$otherList = new tx_oelib_List();
		$otherModel = new tx_oelib_tests_fixtures_TestingModel();
		$otherList->add($otherModel);

		$this->fixture->appendUnique($otherList);

		$this->assertSame(
			$model,
			$this->fixture->first()
		);

		$otherList->__destruct();
		$model->__destruct();
		$otherModel->__destruct();
	}


	//////////////////////////////////
	// Tests concerning purgeCurrent
	//////////////////////////////////

	/**
	 * @test
	 */
	public function purgeCurrentWithEmptyListDoesNotFail() {
		$this->fixture->purgeCurrent();
	}

	/**
	 * @test
	 */
	public function purgeCurrentWithRewoundOneElementListMakesListEmpty() {
		$this->addModelsToFixture();

		$this->fixture->rewind();
		$this->fixture->purgeCurrent();

		$this->assertTrue(
			$this->fixture->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentWithRewoundOneElementListMakesPointerInvalid() {
		$this->addModelsToFixture();

		$this->fixture->rewind();
		$this->fixture->purgeCurrent();

		$this->assertFalse(
			$this->fixture->valid()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentWithOneElementListAndPointerAfterLastItemLeavesListUntouched() {
		$this->addModelsToFixture();

		$this->fixture->rewind();
		$this->fixture->next();
		$this->fixture->purgeCurrent();

		$this->assertFalse(
			$this->fixture->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentForFirstOfTwoElementsMakesOneItemList() {
		$this->addModelsToFixture(array('', ''));

		$this->fixture->rewind();
		$this->fixture->purgeCurrent();

		$this->assertSame(
			1,
			$this->fixture->count()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentForSecondOfTwoElementsMakesOneItemList() {
		$this->addModelsToFixture(array('', ''));

		$this->fixture->rewind();
		$this->fixture->next();
		$this->fixture->purgeCurrent();

		$this->assertSame(
			1,
			$this->fixture->count()
		);
	}

	/**
	 * @test
	 */
	public function purgeCurrentForFirstOfTwoElementsSetsPointerToFormerSecondElement() {
		$this->addModelsToFixture();

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->fixture->rewind();
		$this->fixture->purgeCurrent();

		$this->assertSame(
			$model,
			$this->fixture->current()
		);

		$model->__destruct();
	}

	/**
	 * @test
	 */
	public function purgeCurrentForSecondOfTwoElementsInForeachLoopDoesNotChangeNumberOfIterations() {
		$this->addModelsToFixture(array('', ''));

		$completedIterations = 0;

		foreach ($this->fixture as $model) {
			if ($completedIterations == 1) {
				$this->fixture->purgeCurrent();
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

		while ($this->fixture->valid()) {
			if ($completedIterations == 1) {
				$this->fixture->purgeCurrent();
			}

			$completedIterations++;
			$this->fixture->next();
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
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(1);
		$this->fixture->add($model);
		$this->modelStorage[] = $model;

		$this->fixture->rewind();
		$this->fixture->purgeCurrent();

		$this->assertSame(
			'',
			$this->fixture->getUids()
		);
	}


	//////////////////////////////////
	// Tests concerning cloned lists
	//////////////////////////////////

	/**
	 * @test
	 */
	public function cloneDoesNotCrash() {
		if (floatval(PHP_VERSION) < 5.3) {
			$this->markTestSkipped(
				'Cloning SplObjectStorage instances would crash in PHP < 5.3.0.'
			);
		}

		clone $this->fixture;
	}

	/**
	 * @test
	 */
	public function purgeCurrentForClonedListNotRemovesItemFromOriginalList() {
		if (floatval(PHP_VERSION) < 5.3) {
			$this->markTestSkipped(
				'Cloning SplObjectStorage instances would crash in PHP < 5.3.0.'
			);
		}

		$this->addModelsToFixture();

		$clonedList = clone($this->fixture);
		$clonedList->rewind();
		$clonedList->purgeCurrent();

		$this->assertSame(
			1,
			$this->fixture->count()
		);

		$clonedList->__destruct();
	}

	/**
	 * @test
	 */
	public function purgeCurrentForClonedListNotRemovesUidFromOriginalList() {
		if (floatval(PHP_VERSION) < 5.3) {
			$this->markTestSkipped(
				'Cloning SplObjectStorage instances would crash in PHP < 5.3.0.'
			);
		}

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(1);
		$this->fixture->add($model);
		$this->modelStorage[] = $model;

		$clonedList = clone($this->fixture);
		$clonedList->rewind();
		$clonedList->purgeCurrent();

		$this->assertSame(
			'1',
			$this->fixture->getUids()
		);

		$clonedList->__destruct();
	}


	///////////////////////////////////
	// Tests concerning sortBySorting
	///////////////////////////////////

	/**
	 * @test
	 */
	public function sortBySortingMovesItemWithHigherSortingValueAfterItemWithLowerSortingValue() {
		$model1 = new tx_oelib_tests_fixtures_TestingChildModel();
		$model1->setSorting(2);
		$this->fixture->add($model1);

		$model2 = new tx_oelib_tests_fixtures_TestingChildModel();
		$model2->setSorting(1);
		$this->fixture->add($model2);

		$this->fixture->sortBySorting();

		$this->assertSame(
			$model2,
			$this->fixture->first()
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
		$this->fixture->at(-1);
	}

	/**
	 * @test
	 */
	public function atForPositionZeroWithEmptyListReturnsNull() {
		$this->assertNull(
			$this->fixture->at(0)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionOneWithEmptyListReturnsNull() {
		$this->assertNull(
			$this->fixture->at(1)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionZeroWithOneItemListReturnsItem() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->assertSame(
			$model,
			$this->fixture->at(0)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionOneWithOneItemListReturnsNull() {
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());

		$this->assertNull(
			$this->fixture->at(1)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionZeroWithTwoItemListReturnsFirstItem() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model1);
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());

		$this->assertSame(
			$model1,
			$this->fixture->at(0)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionOneWithTwoItemListReturnsSecondItem() {
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model2);

		$this->assertSame(
			$model2,
			$this->fixture->at(1)
		);
	}

	/**
	 * @test
	 */
	public function atForPositionTwoWithTwoItemListReturnsNull() {
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());

		$this->assertNull(
			$this->fixture->at(2)
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
		$this->fixture->inRange(-1, 1);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function inRangeWithNegativeLengthThrowsException() {
		$this->fixture->inRange(1, -1);
	}

	/**
	 * @test
	 */
	public function inRangeWithZeroLengthReturnsEmptyList() {
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());

		$this->assertTrue(
			$this->fixture->inRange(1, 0)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function inRangeCanReturnOneElementFromStartOfList() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());

		$result = $this->fixture->inRange(0, 1);
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
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());
		$this->fixture->add($model);

		$result = $this->fixture->inRange(1, 1);
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
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model2);

		$this->assertSame(
			2,
			$this->fixture->inRange(0, 2)->count()
		);
	}

	/**
	 * @test
	 */
	public function inRangeWithStartAfterListEndReturnsEmptyList() {
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());

		$this->assertTrue(
			$this->fixture->inRange(1, 1)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function inRangeWithRangeCrossingListEndReturnsElementUpToListEnd() {
		$this->fixture->add(new tx_oelib_tests_fixtures_TestingModel());
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$result = $this->fixture->inRange(1, 2);

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
			$this->fixture->toArray()
		);
	}

	/**
	 * @test
	 */
	public function toArrayWithOneElementReturnsArrayWithElement() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->assertSame(
			array($model),
			$this->fixture->toArray()
		);
	}

	/**
	 * @test
	 */
	public function toArrayWithTwoElementsReturnsArrayWithBothElementsInAddingOrder() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model2);

		$this->assertSame(
			array($model1, $model2),
			$this->fixture->toArray()
		);
	}
}
?>