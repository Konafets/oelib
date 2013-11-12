<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Saskia Metzler <saskia@merlin.owl.de>
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
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Mapper_BackEndUserTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_testingFramework for creating dummy records
	 */
	private $testingFramework;
	/**
	 * @var tx_oelib_Mapper_BackEndUser the object to test
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');

		$this->fixture = new tx_oelib_Mapper_BackEndUser();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsBackEndUserInstance() {
		$this->assertTrue(
			$this->fixture->find($this->testingFramework->createBackEndUser())
				instanceof tx_oelib_Model_BackEndUser
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsModelWithThatUid() {
		$uid = $this->testingFramework->createBackEndUser();

		$this->assertSame(
			$uid,
			$this->fixture->find($uid)->getUid()
		);
	}


	////////////////////////////////////
	// Tests concerning findByUserName
	////////////////////////////////////

	/**
	 * @test
	 */
	public function findByUserNameForEmptyUserNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$value must not be empty.'
		);

		$this->fixture->findByUserName('');
	}

	/**
	 * @test
	 */
	public function findByUserNameWithNameOfExistingUserReturnsBackEndUserInstance() {
		$this->testingFramework->createBackEndUser(array('username' => 'foo'));

		$this->assertTrue(
			$this->fixture->findByUserName('foo')
				instanceof tx_oelib_Model_BackEndUser
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createBackEndUser(array('username' => 'foo')),
			$this->fixture->findByUserName('foo')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createBackEndUser(array('username' => 'foo')),
			$this->fixture->findByUserName('FOO')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createBackEndUser(array('username' => 'FOO')),
			$this->fixture->findByUserName('FOO')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithLowercaseNameOfExistingUppercaseUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createBackEndUser(array('username' => 'FOO')),
			$this->fixture->findByUserName('foo')->getUid()
		);
	}

	/**
	 * @test
	 *
 	 * @expectedException tx_oelib_Exception_NotFound
	 */
	public function findByUserNameWithNameOfNonExistentUserThrowsException() {
		$this->testingFramework->createBackEndUser(
			array('username' => 'foo', 'deleted' => 1)
		);

		$this->fixture->findByUserName('foo');
	}


	//////////////////////////////////
	// Tests concerning findByCliKey
	//////////////////////////////////

	/**
	 * @test
	 */
	public function findByCliKeyForCliKeyDefinedReturnsBackEndUserInstance() {
		$this->testingFramework->createBackEndUser(array('username' => 'foo'));
		// fakes the CLI definition
		define('TYPO3_cliKey', 'oelib_mapper_test');
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']
			['cliKeys'][TYPO3_cliKey][1] = 'foo';

		$this->assertTrue(
			$this->fixture->findByCliKey() instanceof tx_oelib_Model_BackEndUser
		);
	}


	///////////////////////////////////
	// Tests concerning the relations
	///////////////////////////////////

	/**
	 * @test
	 */
	public function usergroupRelationIsUserGroupList() {
		$groupUid = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getNewGhost()->getUid();
		$userUid = $this->fixture->getLoadedTestingModel(
			array('usergroup' => $groupUid)
		)->getUid();

		$this->assertTrue(
			$this->fixture->find($userUid)->getGroups()->first()
				instanceof tx_oelib_Model_BackEndUserGroup
		);
	}
}
?>