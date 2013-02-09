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
class tx_oelib_Mapper_FrontEndUserTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework for creating dummy records
	 */
	private $testingFramework;
	/**
	 * @var tx_oelib_Mapper_FrontEndUser the object to test
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');

		$this->fixture = new tx_oelib_Mapper_FrontEndUser();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	public function testFindWithUidOfExistingRecordReturnsFrontEndUserInstance() {
		$uid = $this->testingFramework->createFrontEndUser();

		$this->assertTrue(
			$this->fixture->find($uid) instanceof tx_oelib_Model_FrontEndUser
		);
	}

	public function testFindWithUidOfExistingRecordReturnsModelWithThatUid() {
		$uid = $this->testingFramework->createFrontEndUser();

		$this->assertSame(
			$uid,
			$this->fixture->find($uid)->getUid()
		);
	}



	//////////////////////////////
	// Test concerning getGroups
	//////////////////////////////

	public function testGetUserGroupsGetsRelatedGroupsAsList() {
		$groupMapper
			= tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUserGroup');

		$group1 = $groupMapper->getNewGhost();
		$group2 = $groupMapper->getNewGhost();
		$groupUids = $group1->getUid() . ',' . $group2->getUid();

		$uid = $this->testingFramework->createFrontEndUser($groupUids);

		$this->assertSame(
			$groupUids,
			$this->fixture->find($uid)->getUserGroups()->getUids()
		);
	}


	/////////////////////////////////////
	// Tests concerning getGroupMembers
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getGroupMembersForEmptyStringThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$groupUids must not be an empty string.'
		);

		$this->fixture->getGroupMembers('');
	}

	/**
	 * @test
	 */
	public function getGroupMembersForNonExistingGroupUidReturnsEmptyList() {
		$this->assertTrue(
			$this->fixture->getGroupMembers(
				$this->testingFramework->getAutoIncrement('fe_groups')
			)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForGroupWithNoMembersReturnsInstanceOfOelibList() {
		$this->assertTrue(
			$this->fixture->getGroupMembers(
				$this->testingFramework->createFrontEndUserGroup()
			) instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForGroupWithNoMembersReturnsEmptyList() {
		$this->assertTrue(
			$this->fixture->getGroupMembers(
				$this->testingFramework->createFrontEndUserGroup()
			)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForGroupWithOneMemberReturnsOneElement() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser($feUserGroupUid);

		$this->assertSame(
			1,
			$this->fixture->getGroupMembers($feUserGroupUid)->count()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersIgnoresDeletedUser() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser(
			$feUserGroupUid,
			array('deleted' => 1)
		);

		$this->assertTrue(
			$this->fixture->getGroupMembers($feUserGroupUid)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersIgnoresDisabledUser() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser(
			$feUserGroupUid,
			array('disable' => 1)
		);

		$this->assertTrue(
			$this->fixture->getGroupMembers($feUserGroupUid)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupFirstReturnsOneElement() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$userGroups = $feUserGroupUid . ',' .
			$this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser($userGroups);

		$this->assertSame(
			1,
			$this->fixture->getGroupMembers($feUserGroupUid)->count()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupLastReturnsOneElement() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$userGroups = $this->testingFramework->createFrontEndUserGroup() . ',' .
			$feUserGroupUid;
		$this->testingFramework->createFrontEndUser($userGroups);

		$this->assertSame(
			1,
			$this->fixture->getGroupMembers($feUserGroupUid)->count()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForUserWithMultipleGroupsAndGivenGroupInTheMiddleReturnsOneElement() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$userGroups = $this->testingFramework->createFrontEndUserGroup() .
			',' .	$feUserGroupUid . ',' .
			$this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser($userGroups);

		$this->assertSame(
			1,
			$this->fixture->getGroupMembers($feUserGroupUid)->count()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForGroupWithOneMemberReturnsFrontEndUserList() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser($feUserGroupUid);

		$this->assertTrue(
			$this->fixture->getGroupMembers($feUserGroupUid)->first()
				instanceof tx_oelib_Model_FrontEndUser
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForGroupWithTwoMembersReturnsTwoUsers() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser($feUserGroupUid);
		$this->testingFramework->createFrontEndUser($feUserGroupUid);

		$this->assertSame(
			2,
			$this->fixture->getGroupMembers($feUserGroupUid)->count()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForGroupWithOneMemberDoesNotReturnsUserNotInGivenGroup() {
		$firstGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser($firstGroupUid);
		$secondUserUid = $this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup()
		);

		$this->assertFalse(
			$this->fixture->getGroupMembers($firstGroupUid)->hasUid(
				$secondUserUid
			)
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForTwoGroupsReturnsUsersOfBothGroups() {
		$firstGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$secondGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser($firstGroupUid);
		$this->testingFramework->createFrontEndUser($secondGroupUid);

		$this->assertSame(
			2,
			$this->fixture->getGroupMembers(
				$firstGroupUid . ',' . $secondGroupUid
			)->count()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForTwoGroupsReturnsUserInBothGroupsOnlyOnce() {
		$userGroups = $this->testingFramework->createFrontEndUserGroup() . ',' .
			$this->testingFramework->createFrontEndUserGroup();
		$this->testingFramework->createFrontEndUser($userGroups);

		$this->assertSame(
			1,
			$this->fixture->getGroupMembers($userGroups)->count()
		);
	}

	/**
	 * @test
	 */
	public function getGroupMembersForTwoGroupsCanReturnThreeUsersInGroups() {
		$firstGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$secondGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$userGroups = $firstGroupUid . ',' . $secondGroupUid;
		$this->testingFramework->createFrontEndUser($firstGroupUid);
		$this->testingFramework->createFrontEndUser($secondGroupUid);
		$this->testingFramework->createFrontEndUser($userGroups);

		$this->assertSame(
			3,
			$this->fixture->getGroupMembers($userGroups)->count()
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
	public function findByUserNameWithNameOfExistingUserReturnsFrontEndUserInstance() {
		$this->testingFramework->createFrontEndUser(
			'', array('username' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->findByUserName('foo')
				instanceof tx_oelib_Model_FrontEndUser
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createFrontEndUser(
				'', array('username' => 'foo')
			),
			$this->fixture->findByUserName('foo')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createFrontEndUser(
				'', array('username' => 'foo')
			),
			$this->fixture->findByUserName('FOO')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createFrontEndUser(
				'', array('username' => 'FOO')
			),
			$this->fixture->findByUserName('FOO')->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByUserNameWithLowercasedNameOfExistingUppercasedUserReturnsModelWithThatUid() {
		$this->assertSame(
			$this->testingFramework->createFrontEndUser(
				'', array('username' => 'FOO')
			),
			$this->fixture->findByUserName('foo')->getUid()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException tx_oelib_Exception_NotFound
	 */
	public function findByUserNameWithNameOfNonExistentUserThrowsException() {
		$this->testingFramework->createFrontEndUser(
			'',
			array('username' => 'foo', 'deleted' => 1)
		);

		$this->fixture->findByUserName('foo');
	}
}
?>