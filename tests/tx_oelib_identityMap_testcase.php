<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'tests/fixtures/class.tx_oelib_testingModel.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_identityMap.php');

/**
 * Testcase for the tx_oelib_identityMap class in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_identityMap_testcase extends tx_phpunit_testcase {
	/**
	 * @var	tx_oelib_identityMap	the indentity map to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_identityMap();
	}

	public function tearDown() {
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

		$model = new tx_oelib_testingModel();
		$model->setData(array());

		$this->fixture->add($model);
	}

	public function testGetWithExistingUidAfterAddWithModelWithUidReturnsSameObject() {
		$model = new tx_oelib_testingModel();
		$model->setUid(42);
		$this->fixture->add($model);

		$this->assertSame(
			$model,
			$this->fixture->get(42)
		);
	}

	public function testAddForExistingUidReturnsModelWithGivenUidForSeveralUids() {
		$model1 = new tx_oelib_testingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);

		$model2 = new tx_oelib_testingModel();
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
		$model1 = new tx_oelib_testingModel();
		$model1->setUid(1);
		$this->fixture->add($model1);

		$model2 = new tx_oelib_testingModel();
		$model2->setUid(1);
		$this->fixture->add($model2);

		$this->assertEquals(
			$model2,
			$this->fixture->get(1)
		);
	}

	public function testGetForInexistentUidThrowsNotFoundException() {
		$this->setExpectedException(
			'tx_oelib_notFoundException',
			'This map currently does not contain a model with the UID 42.'
		);

		$this->fixture->get(42);
	}
}
?>