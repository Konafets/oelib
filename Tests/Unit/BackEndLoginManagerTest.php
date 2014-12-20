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

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		$this->subject = Tx_Oelib_BackEndLoginManager::getInstance();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
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
		/** @var tx_oelib_Model_BackEndUser $ghostUser */
		$ghostUser = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$this->subject->setLoggedInUser($ghostUser);

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
			(int)$GLOBALS['BE_USER']->user['uid'],
			$this->subject->getLoggedInUser()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance() {
		/** @var tx_oelib_Mapper_BackEndUser $mapper */
		$mapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser');
		/** @var tx_oelib_Model_BackEndUser $user */
		$user = $mapper->find($GLOBALS['BE_USER']->user['uid']);

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
		/** @var tx_oelib_Model_BackEndUser $backEndUser */
		$backEndUser = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->getNewGhost();
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
		/** @var tx_oelib_Model_BackEndUser $oldBackEndUser */
		$oldBackEndUser = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$this->subject->setLoggedInUser($oldBackEndUser);
		/** @var tx_oelib_Model_BackEndUser $newBackEndUser */
		$newBackEndUser = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$this->subject->setLoggedInUser($newBackEndUser);

		$this->assertSame(
			$newBackEndUser,
			$this->subject->getLoggedInUser()
		);
	}
}