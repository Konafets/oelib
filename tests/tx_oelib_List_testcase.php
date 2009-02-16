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

	public function setUp() {
		$this->fixture = new tx_oelib_List;
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
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
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->assertFalse(
			$this->fixture->isEmpty()
		);

		$model->__destruct();
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
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->assertEquals(
			1,
			$this->fixture->count()
		);

		$model->__destruct();
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
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model1);
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model2);

		$this->assertEquals(
			2,
			$this->fixture->count()
		);

		$model1->__destruct();
		$model2->__destruct();
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
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);
		$this->fixture->next();

		$this->assertEquals(
			1,
			$this->fixture->key()
		);

		$model->__destruct();
	}

	public function testCurrentWithOneItemAfterNextReturnsNull() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->fixture->next();

		$this->assertNull(
			$this->fixture->current()
		);

		$model->__destruct();
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
	// Tests concerning valid
	///////////////////////////

	public function testValidForEmptyListReturnsFalse() {
		$this->assertFalse(
			$this->fixture->valid()
		);
	}

	public function testValidForOneElementInitiallyReturnsTrue() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->assertTrue(
			$this->fixture->valid()
		);

		$model->__destruct();
	}

	public function testValidForOneElementAfterNextReturnsFalse() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->fixture->next();

		$this->assertFalse(
			$this->fixture->valid()
		);

		$model->__destruct();
	}

	public function testValidForOneElementAfterNextAndRewindReturnsTrue() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->fixture->next();
		$this->fixture->rewind();

		$this->assertTrue(
			$this->fixture->valid()
		);

		$model->__destruct();
	}


	////////////////////////////////////////////
	// Tests concerning the Interator property
	////////////////////////////////////////////

	public function testIsIterator() {
		$this->assertTrue(
			$this->fixture instanceof Iterator
		);
	}

	public function testIteratingOverOneItemDoesNotFair() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->fixture->next();
		$this->fixture->rewind();

		foreach ($this->fixture as $key => $vaulue);

		$model->__destruct();
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
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->add($model);

		$this->assertEquals(
			'',
			$this->fixture->getUids()
		);

		$model->__destruct();
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
}
?>