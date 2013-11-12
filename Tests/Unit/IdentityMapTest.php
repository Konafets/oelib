<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_IdentityMapTest extends Tx_Phpunit_TestCase {
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

	/**
	 * @test
	 */
	public function getWithZeroUidThrowsException(){
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->fixture->get(0);
	}

	/**
	 * @test
	 */
	public function getWithNegativeUidThrowsException(){
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uid must be > 0.'
		);

		$this->fixture->get(-1);
	}

	/**
	 * @test
	 */
	public function addWithModelWithoutUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'Add() requires a model that has a UID.'
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setData(array());

		$this->fixture->add($model);
	}

	/**
	 * @test
	 */
	public function getWithExistingUidAfterAddWithModelHavingAUidReturnsSameObject() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(42);
		$this->fixture->add($model);

		$this->assertSame(
			$model,
			$this->fixture->get(42)
		);
	}

	/**
	 * @test
	 */
	public function addForExistingUidReturnsModelWithGivenUidForSeveralUids() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);

		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model2->setUid(4);
		$this->fixture->add($model2);

		$this->assertSame(
			1,
			$this->fixture->get(1)->getUid()
		);
		$this->assertSame(
			4,
			$this->fixture->get(4)->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getForExistingUidAfterAddingTwoModelsWithSameUidReturnsTheLastAddedModel() {
		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);

		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model2->setUid(1);
		$this->fixture->add($model2);

		$this->assertSame(
			$model2,
			$this->fixture->get(1)
		);
	}

	/**
	 * @test
	 */
	public function getForInexistentUidThrowsNotFoundException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'This map currently does not contain a model with the UID 42.'
		);

		$this->fixture->get(42);
	}


	///////////////////////////////
	// Tests concerning getNewUid
	///////////////////////////////

	/**
	 * @test
	 */
	public function getNewUidForEmptyMapReturnsOne() {
		$this->assertSame(
			1,
			$this->fixture->getNewUid()
		);
	}

	/**
	 * @test
	 */
	public function getNewUidForNonEmptyMapReturnsUidNotInMap() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound'
		);

		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(1);
		$this->fixture->add($model);

		$newUid = $this->fixture->getNewUid();

		$this->fixture->get($newUid);
	}

	/**
	 * @test
	 */
	public function getNewUidForNonEmptyMapReturnsUidGreaterThanGreatestUid() {
		$model = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model->setUid(42);
		$this->fixture->add($model);

		$this->assertGreaterThan(
			42,
			$this->fixture->getNewUid()
		);
	}

	/**
	 * @test
	 */
	public function getNewUidForMapWithTwoItemsInReverseOrderReturnsUidGreaterThanTheGreatesUid() {
		$model2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model2->setUid(2);
		$this->fixture->add($model2);

		$model1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);

		$this->assertGreaterThan(
			2,
			$this->fixture->getNewUid()
		);
	}
}
?>