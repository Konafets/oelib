<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_BackEndLoginManagerTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_BackEndLoginManager
	 */
	private $subject;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		$this->subject = Tx_Oelib_BackEndLoginManager::getInstance();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->subject, $this->testingFramework);
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsBackEndLoginManagerInstance() {
		$this->assertTrue(
			$this->subject instanceof Tx_Oelib_BackEndLoginManager
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			$this->subject,
			Tx_Oelib_BackEndLoginManager::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		Tx_Oelib_BackEndLoginManager::purgeInstance();

		$this->assertNotSame(
			$this->subject,
			Tx_Oelib_BackEndLoginManager::getInstance()
		);
	}


	////////////////////////////////
	// Tests concerning isLoggedIn
	////////////////////////////////

	/**
	 * @test
	 */
	public function isLoggedInWithLoggedInBackEndUserReturnsTrue() {
		// We assume that the tests are run when logged in in the BE.
		$this->assertTrue(
			$this->subject->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function isLoggedInForFakedUserReturnsTrue() {
		$this->subject->setLoggedInUser(
			Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')
				->getNewGhost()
		);

		$this->assertTrue(
			$this->subject->isLoggedIn()
		);
	}


	/////////////////////////////////////
	// Tests concerning getLoggedInUser
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getLoggedInUserWithEmptyMapperNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$mapperName must not be empty.'
		);

		$this->subject->getLoggedInUser('');
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithLoggedInUserReturnsBackEndUserInstance() {
		$this->assertTrue(
			$this->subject->getLoggedInUser()
				instanceof Tx_Oelib_Model_BackEndUser
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithOtherMapperNameAndLoggedInUserReturnsCorrespondingModel() {
		$this->assertTrue(
			$this->subject->getLoggedInUser('tx_oelib_Tests_Unit_Fixtures_TestingMapper')
				instanceof Tx_Oelib_Tests_Unit_Fixtures_TestingModel
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithLoggedInUserReturnsBackEndUserWithUidOfLoggedInUser() {
		$this->assertSame(
			intval($GLOBALS['BE_USER']->user['uid']),
			$this->subject->getLoggedInUser()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance() {
		$user = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')
			->find($GLOBALS['BE_USER']->user['uid']);

		$this->assertSame(
			$user,
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserUsesMappedUserDataFromMemory() {
		$backedUpName = $GLOBALS['BE_USER']->user['realName'];
		$GLOBALS['BE_USER']->user['realName'] = 'John Doe';

		$this->assertSame(
			'John Doe',
			$this->subject->getLoggedInUser()->getName()
		);

		$GLOBALS['BE_USER']->user['realName'] = $backedUpName;
	}


	////////////////////////////////////
	// Tests concerning setLoggedInUser
	////////////////////////////////////

	/**
	 * @test
	 */
	public function setLoggedInUserForUserGivenSetsTheLoggedInUser() {
		$backEndUser = Tx_Oelib_MapperRegistry::get(
			'tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$this->subject->setLoggedInUser($backEndUser);

		$this->assertSame(
			$backEndUser,
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function setLoggedInUserForUserGivenAndAlreadyStoredLoggedInUserOverridesTheOldUserWithTheNewOne() {
		$oldBackEndUser = Tx_Oelib_MapperRegistry::get(
			'tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$this->subject->setLoggedInUser($oldBackEndUser);
		$newBackEndUser = Tx_Oelib_MapperRegistry::get(
			'tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$this->subject->setLoggedInUser($newBackEndUser);

		$this->assertSame(
			$newBackEndUser,
			$this->subject->getLoggedInUser()
		);
	}
}