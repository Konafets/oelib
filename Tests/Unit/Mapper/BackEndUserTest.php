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
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Mapper_BackEndUserTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework for creating dummy records
	 */
	private $testingFramework;
	/**
	 * @var tx_oelib_Mapper_BackEndUser the object to test
	 */
	private $subject;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		$this->subject = new tx_oelib_Mapper_BackEndUser();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsBackEndUserInstance() {
		$this->assertTrue(
			$this->subject->find($this->testingFramework->createBackEndUser())
				instanceof Tx_Oelib_Model_BackEndUser
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsModelWithThatUid() {
		$uid = $this->testingFramework->createBackEndUser();

		$this->assertSame(
			$uid,
			$this->subject->find($uid)->getUid()
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

		$this->subject->findByUserName('');
	}

	/**
	 * @test
	 */
	public function findByUserNameWithNameOfExistingUserReturnsBackEndUserInstance() {
		$this->testingFramework->createBackEndUser(array('username' => 'foo'));

		$this->assertTrue(
			$this->subject->findByUserName('foo')
				instanceof Tx_Oelib_Model_BackEndUser
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createBackEndUser(array('username' => 'foo')),
			$this->subject->findByUserName('foo')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createBackEndUser(array('username' => 'foo')),
			$this->subject->findByUserName('FOO')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createBackEndUser(array('username' => 'FOO')),
			$this->subject->findByUserName('FOO')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithLowercaseNameOfExistingUppercaseUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createBackEndUser(array('username' => 'FOO')),
			$this->subject->findByUserName('foo')->getUid()
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

		$this->subject->findByUserName('foo');
	}


	//////////////////////////////////
	// Tests concerning findByCliKey
	//////////////////////////////////

	/**
	 * @test
	 */
	public function findByCliKeyForCliKeyDefinedReturnsBackEndUserInstance() {
		$this->testingFramework->createBackEndUser(array('username' => 'foo'));
		/**
		 * fakes the CLI definition
		 *
		 * @var string
		 */
		define('TYPO3_cliKey', 'oelib_mapper_test');
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']
			['cliKeys'][TYPO3_cliKey][1] = 'foo';

		$this->assertTrue(
			$this->subject->findByCliKey() instanceof Tx_Oelib_Model_BackEndUser
		);
	}


	///////////////////////////////////
	// Tests concerning the relations
	///////////////////////////////////

	/**
	 * @test
	 */
	public function userGroupRelationIsUserGroupList() {
		/** @var Tx_Oelib_Model_BackEndUserGroup $group */
		$group = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUserGroup')->getNewGhost();
		$groupUid = $group->getUid();
		$userUid = $this->subject->getLoadedTestingModel(array('usergroup' => $groupUid))->getUid();

		/** @var Tx_Oelib_Model_BackEndUser $user */
		$user = $this->subject->find($userUid);
		$this->assertInstanceOf(
			'Tx_Oelib_Model_BackEndUserGroup',
			$user->getGroups()->first()
		);
	}
}