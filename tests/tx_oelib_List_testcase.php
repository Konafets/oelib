<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_List class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_List_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_List
	 */
	private $fixture;

	/**
	 * @var array models that need to be cleaned up during tearDown.
	 */
	private $modelStorage = array();

	public function setUp() {
		$this->fixture = new tx_oelib_List;
	}

	public function tearDown() {
		$this->fixture->__destruct();
		foreach($this->modelStorage as $key => $model ) {
			$model->__destruct();
			unset($this->modelStorage[$key]);
		}

		unset($this->fixture, $this->modelStorage);
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
	 * @param array the titles for the models, must not be empty
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
			1,
			$this->fixture->count()
		);
	}

	public function test_AddModelsToFixture_ForOneGivenTitle_AddsModelWithTitleGiven() {
		$this->addModelsToFixture(array('foo'));

		$this->assertEquals(
			'foo',
			$this->fixture->first()->getTitle()
		);
	}

	public function test_AddModelsToFixture_ForTwoGivenTitles_AddsTwoModelsToFixture() {
		$this->addModelsToFixture(array('foo', 'bar'));

		$this->assertEquals(
			2,
			$this->fixture->count()
		);
	}

	public function test_AddModelsToFixture_ForTwoGivenTitles_AddsFirstTitleToFirstModelFixture() {
		$this->addModelsToFixture(array('bar', 'foo'));

		$this->assertEquals(
			'bar',
			$this->fixture->first()->getTitle()
		);
	}

	public function test_AddModelsToFixture_ForThreeGivenTitles_AddsThreeModelsToFixture() {
		$this->addModelsToFixture(array('foo', 'bar','fooBar'));

		$this->assertEquals(
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
		$this->assertEquals(
			0,
			$this->fixture->count()
		);
	}

	public function testCountWithOneModelWithoutUidReturnsOne() {
		$this->addModelsToFixture();

		$this->assertEquals(
			1,
			$this->fixture->count()
		);
	}

	public function testCountWithOneModelWithUidReturnsOne() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(1);
		$this->fixture->add($model);

		$this->assertEquals(
			1,
			$this->fixture->count()
		);

		$model->__destruct();
	}

	public function testCountWithTwoDifferentModelsReturnsTwo() {
		$this->addModelsToFixture(array('',''));

		$this->assertEquals(
			2,
			$this->fixture->count()
		);
	}

	public function testCountAfterAddingTheSameModelTwiceReturnsTwo() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);
		$this->fixture->add($model);

		$this->assertEquals(
			2,
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
		$this->assertEquals(
			0,
			$this->fixture->key()
		);
	}

	public function testKeyAfterNextInListWithOneElementReturnsOne() {
		$this->addModelsToFixture();
		$this->fixture->next();

		$this->assertEquals(
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

		$this->assertEquals(
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

		foreach ($this->fixture as $key => $value);
	}


	/////////////////////////////
	// Tests concerning getUids
	/////////////////////////////

	public function testGetUidsForEmptyListReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getUids()
		);
	}

	public function testGetUidsForOneItemsWithoutUidReturnsEmptyString() {
		$this->addModelsToFixture();

		$this->assertEquals(
			'',
			$this->fixture->getUids()
		);
	}

	public function testGetUidsForOneItemsWithUidReturnsThatUid() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(1);
		$this->fixture->add($model);

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
			'1,2',
			$this->fixture->getUids()
		);

		$model1->__destruct();
		$model2->__destruct();
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


	//////////////////////////
	// Tests concerning sort
	//////////////////////////

	public function test_sort_WithTwoModelsAndSortByTitleAscendingFunction_SortsModelsByTitleAscending() {
		$this->addModelsToFixture(array('Beta', 'Alpha'));
		$this->fixture->sort(array($this, 'sortByTitleAscending'));

		$this->assertEquals(
			'Alpha',
			$this->fixture->first()->getTitle()
		);
	}

	public function test_sort_WithThreeModelsAndSortByTitleAscendingFunction_SortsModelsByTitleAscending() {
		$this->addModelsToFixture(array('Zeta', 'Beta', 'Alpha'));
		$this->fixture->sort(array($this, 'sortByTitleAscending'));

		$this->assertEquals(
			'Alpha',
			$this->fixture->first()->getTitle()
		);
	}

	public function test_sort_WithTwoModelsAndSortByTitleDescendingFunction_SortsModelsByTitleDescending() {
		$this->addModelsToFixture(array('Alpha', 'Beta'));
		$this->fixture->sort(array($this, 'sortByTitleDescending'));

		$this->assertEquals(
			'Beta',
			$this->fixture->first()->getTitle()
		);
	}
}
?>