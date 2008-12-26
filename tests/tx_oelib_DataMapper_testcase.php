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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the tx_oelib_DataMapper class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_DataMapper_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework for creating dummy records
	 */
	private $testingFramework;
	/**
	 * @var tx_oelib_DataMapper the data mapper to test
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');

		$this->fixture = new tx_oelib_tests_fixtures_TestingMapper();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////////
	// Tests concerning the instantiation
	///////////////////////////////////////

	public function testInstantiationOfSubclassWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'tx_oelib_tests_fixtures_TableLessTestingMapper::tableName must not be empty.'
		);

		new tx_oelib_tests_fixtures_TableLessTestingMapper();
	}

	public function testInstantiationOfSubclassWithEmptyColumnListThrowsException() {
		$this->setExpectedException(
			'Exception',
			'tx_oelib_tests_fixtures_ColumnLessTestingMapper::columns must not be empty.'
		);

		new tx_oelib_tests_fixtures_ColumnLessTestingMapper();
	}

	public function testInstantiationOfSubclassWithEmptyModelNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'tx_oelib_tests_fixtures_ModelLessTestingMapper::modelClassName must not be empty.'
		);

		new tx_oelib_tests_fixtures_ModelLessTestingMapper();
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	public function testFindWithZeroUidThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$uid must be > 0.'
		);

		$this->fixture->find(0);
	}

	public function testFindWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$uid must be > 0.'
		);

		$this->fixture->find(-1);
	}

	public function testFindWithUidOfCachedModelReturnsThatModel() {
		$model = new tx_oelib_tests_fixtures_TestingModel();
		$model->setUid(1);

		$map = new tx_oelib_IdentityMap();
		$map->add($model);
		$this->fixture->setMap($map);

		$this->assertSame(
			$model,
			$this->fixture->find(1)
		);
	}

	public function testFindWithUidOfExistingRecordReturnsModelWithThatUid() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			$uid,
			$this->fixture->find($uid)->getUid()
		);
	}

	public function testFindWithUidOfExistingRecordReturnsModelDataFromDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_oelib_test', array('title' => 'foo')
		);

		$this->assertEquals(
			'foo',
			$this->fixture->find($uid)->getTitle()
		);
	}

	public function testFindWithUidOfExistingRecordCalledTwoTimesReturnsSameModel() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');

		$this->assertEquals(
			$this->fixture->find($uid),
			$this->fixture->find($uid)
		);
	}

	public function testFindWithUidOfInexistentRecordThrowsNotFoundException() {
		$uid = $this->testingFramework->createRecord('tx_oelib_test');
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'The record with the UID ' . $uid . ' could not be retrieved ' .
					'from the table tx_oelib_test.'
		);

		$this->testingFramework->deleteRecord('tx_oelib_test', $uid);

		$this->fixture->find($uid);
	}
}
?>