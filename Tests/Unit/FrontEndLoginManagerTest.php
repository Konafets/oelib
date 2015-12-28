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
class Tx_Oelib_Tests_Unit_FrontEndLoginManagerTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_FrontEndLoginManager
	 */
	private $subject;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		$this->subject = Tx_Oelib_FrontEndLoginManager::getInstance();
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
	public function getInstanceReturnsFrontEndLoginManagerInstance() {
		self::assertTrue(
			$this->subject instanceof Tx_Oelib_FrontEndLoginManager
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		self::assertSame(
			$this->subject,
			Tx_Oelib_FrontEndLoginManager::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		Tx_Oelib_FrontEndLoginManager::purgeInstance();

		self::assertNotSame(
			$this->subject,
			Tx_Oelib_FrontEndLoginManager::getInstance()
		);
	}


	////////////////////////////////
	// Tests concerning isLoggedIn
	////////////////////////////////

	/**
	 * @test
	 */
	public function isLoggedInForNoFrontEndReturnsFalse() {
		self::assertFalse(
			$this->subject->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function isLoggedInForFrontEndWithoutLoggedInUserReturnsFalse() {
		$this->testingFramework->createFakeFrontEnd();

		self::assertFalse(
			$this->subject->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function isLoggedInWithLoggedInFrontEndUserReturnsTrue() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		self::assertTrue(
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
	public function getLoggedInUserWithoutFrontEndReturnsNull() {
		$this->testingFramework->discardFakeFrontEnd();

		self::assertNull(
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithoutLoggedInUserReturnsNull() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->logoutFrontEndUser();

		self::assertNull(
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithLoggedInUserReturnsFrontEndUserInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		self::assertTrue(
			$this->subject->getLoggedInUser()
				instanceof Tx_Oelib_Model_FrontEndUser
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithOtherMapperNameAndLoggedInUserReturnsCorrespondingModel() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		self::assertTrue(
			$this->subject->getLoggedInUser('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')
				instanceof Tx_Oelib_Tests_Unit_Fixtures_TestingModel
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithLoggedInUserReturnsFrontEndUserWithUidOfLoggedInUser() {
		$this->testingFramework->createFakeFrontEnd();
		$uid = $this->testingFramework->createAndLoginFrontEndUser();

		self::assertSame(
			$uid,
			$this->subject->getLoggedInUser()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$uid = $this->testingFramework->createAndLoginFrontEndUser();
		/** @var tx_oelib_Mapper_FrontEndUser $mapper */
		$mapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUser');
		/** @var tx_oelib_Model_FrontEndUser $user */
		$user = $mapper->find($uid);

		self::assertSame(
			$user,
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserWithLoadedModelOfUserNotInDatabaseReturnsThatInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$user = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$user->setData(array());
		$this->testingFramework->loginFrontEndUser($user->getUid());

		self::assertSame(
			$user,
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function getLoggedInUserUsesMappedUserDataFromMemory() {
		$this->testingFramework->createFakeFrontEnd();
		$feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
			'', array('name' => 'John Doe')
		);

		/** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontEndController */
		$frontEndController = $GLOBALS['TSFE'];
		$frontEndController->fe_user->user['name'] = 'Jane Doe';
		$this->testingFramework->changeRecord(
			'fe_users', $feUserUid, array('name' => 'James Doe')
		);

		self::assertSame(
			'John Doe',
			$this->subject->getLoggedInUser()->getName()
		);
	}


	///////////////////////////////
	// Tests concerning logInUser
	///////////////////////////////

	/**
	 * @test
	 */
	public function logInUserChangesToLoggedInStatus() {
		$user = new Tx_Oelib_Model_FrontEndUser();
		$this->subject->logInUser($user);

		self::assertTrue(
			$this->subject->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function logInUserSetsLoggedInUser() {
		$user = new Tx_Oelib_Model_FrontEndUser();
		$this->subject->logInUser($user);

		self::assertSame(
			$user,
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function logInUserOverwritesFormerSimulatedLoggedInUser() {
		$oldUser = new Tx_Oelib_Model_FrontEndUser();
		$this->subject->logInUser($oldUser);
		$newUser = new Tx_Oelib_Model_FrontEndUser();
		$this->subject->logInUser($newUser);

		self::assertSame(
			$newUser,
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function logInUserOverwritesFormerRealLoggedInUser() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		$user = new Tx_Oelib_Model_FrontEndUser();
		$this->subject->logInUser($user);

		self::assertSame(
			$user,
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function logInUserWithNullSetsUserToNull() {
		$user = new Tx_Oelib_Model_FrontEndUser();
		$this->subject->logInUser($user);

		$this->subject->logInUser(NULL);

		self::assertNull(
			$this->subject->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function logInUserWithNullSetsStatusToNotLoggedIn() {
		$user = new Tx_Oelib_Model_FrontEndUser();
		$this->subject->logInUser($user);

		$this->subject->logInUser(NULL);

		self::assertFalse(
			$this->subject->isLoggedIn()
		);
	}
}