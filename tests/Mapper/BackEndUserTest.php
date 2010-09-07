<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Saskia Metzler <saskia@merlin.owl.de>
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
 * Testcase for the tx_oelib_Mapper_BackEndUser class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Mapper_BackEndUserTest extends tx_phpunit_testcase {
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

	public function testFindWithUidOfExistingRecordReturnsBackEndUserInstance() {
		$this->assertTrue(
			$this->fixture->find($this->testingFramework->createBackEndUser())
				instanceof tx_oelib_Model_BackEndUser
		);
	}

	public function testFindWithUidOfExistingRecordReturnsModelWithThatUid() {
		$uid = $this->testingFramework->createBackEndUser();

		$this->assertEquals(
			$uid,
			$this->fixture->find($uid)->getUid()
		);
	}


	////////////////////////////////////
	// Tests concerning findByUserName
	////////////////////////////////////

	public function testFindByUserNameForEmptyUserNameThrowsException() {
		$this->setExpectedException(Exception, '$value must not be empty.');

		$this->fixture->findByUserName('');
	}

	public function testFindByUserNameWithNameOfExistingUserReturnsBackEndUserInstance() {
		$this->testingFramework->createBackEndUser(array('username' => 'foo'));

		$this->assertTrue(
			$this->fixture->findByUserName('foo')
				instanceof tx_oelib_Model_BackEndUser
		);
	}

	public function testFindByUserNameWithNameOfExistingUserReturnsModelWithThatUid() {
		$this->assertEquals(
			$this->testingFramework->createBackEndUser(array('username' => 'foo')),
			$this->fixture->findByUserName('foo')->getUid()
		);
	}

	public function testFindByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid() {
		$this->assertEquals(
			$this->testingFramework->createBackEndUser(array('username' => 'foo')),
			$this->fixture->findByUserName('FOO')->getUid()
		);
	}

	public function testFindByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid() {
		$this->assertEquals(
			$this->testingFramework->createBackEndUser(array('username' => 'FOO')),
			$this->fixture->findByUserName('FOO')->getUid()
		);
	}

	public function testFindByUserNameWithLowercasedNameOfExistingUppercasedUserReturnsModelWithThatUid() {
		$this->assertEquals(
			$this->testingFramework->createBackEndUser(array('username' => 'FOO')),
			$this->fixture->findByUserName('foo')->getUid()
		);
	}

	public function testFindByUserNameWithNameOfNonExistentUserThrowsException() {
		$this->setExpectedException('tx_oelib_Exception_NotFound');

		$this->testingFramework->createBackEndUser(
			array('username' => 'foo', 'deleted' => 1)
		);

		$this->assertNull(
			$this->fixture->findByUserName('foo')
		);
	}


	//////////////////////////////////
	// Tests concerning findByCliKey
	//////////////////////////////////

	public function testFindByCliKeyForCliKeyDefinedReturnsBackEndUserInstance() {
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