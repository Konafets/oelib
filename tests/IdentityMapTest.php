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
 * Testcase for the tx_oelib_IdentityMap class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_IdentityMapTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_IdentityMap the indentity map to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_IdentityMap();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	//////////////////////////
	// Tests for get and add
	//////////////////////////

	public function testGetWithZeroUidThrowsException(){
		$this->setExpectedException(
			'Exception', '$uid must be > 0.'
		);

		$this->fixture->get(0);
	}

	public function testGetWithNegativeUidThrowsException(){
		$this->setExpectedException(
			'Exception', '$uid must be > 0.'
		);

		$this->fixture->get(-1);
	}

	public function testAddWithModelWithoutUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'Add() requires a model that has a UID.'
		);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setData(array());

		$this->fixture->add($model);
	}

	public function testGetWithExistingUidAfterAddWithModelHavingAUidReturnsSameObject() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(42);
		$this->fixture->add($model);

		$this->assertSame(
			$model,
			$this->fixture->get(42)
		);
	}

	public function testAddForExistingUidReturnsModelWithGivenUidForSeveralUids() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);

		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$model2->setUid(4);
		$this->fixture->add($model2);

		$this->assertEquals(
			1,
			$this->fixture->get(1)->getUid()
		);
		$this->assertEquals(
			4,
			$this->fixture->get(4)->getUid()
		);
	}

	public function testGetForExistingUidAfterAddingTwoModelsWithSameUidReturnsTheLastAddedModel() {
		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);

		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$model2->setUid(1);
		$this->fixture->add($model2);

		$this->assertSame(
			$model2,
			$this->fixture->get(1)
		);
	}

	public function testGetForInexistentUidThrowsNotFoundException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'This map currently does not contain a model with the UID 42.'
		);

		$this->fixture->get(42);
	}


	///////////////////////////////
	// Tests concerning getNewUid
	///////////////////////////////

	public function testGetNewUidForEmptyMapReturnsOne() {
		$this->assertEquals(
			1,
			$this->fixture->getNewUid()
		);
	}

	public function testGetNewUidForNonEmptyMapReturnsUidNotInMap() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound'
		);

		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(1);
		$this->fixture->add($model);

		$newUid = $this->fixture->getNewUid();

		$this->fixture->get($newUid);
	}

	public function testGetNewUidForNonEmptyMapReturnsUidGreaterThanGreatestUid() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(42);
		$this->fixture->add($model);

		$this->assertGreaterThan(
			42,
			$this->fixture->getNewUid()
		);
	}

	public function testGetNewUidForMapWithTwoItemsInReverseOrderReturnsUidGreaterThanTheGreatesUid() {
		$model2 = new tx_oelib_tests_fixtures_TestingModel();
		$model2->setUid(2);
		$this->fixture->add($model2);

		$model1 = new tx_oelib_tests_fixtures_TestingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);

		$this->assertGreaterThan(
			2,
			$this->fixture->getNewUid()
		);
	}
}
?>