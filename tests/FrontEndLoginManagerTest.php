<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2012 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_FrontEndLoginManager class in the "oelib"
 * extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_FrontEndLoginManagerTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_FrontEndLoginManager
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');

		$this->fixture = tx_oelib_FrontEndLoginManager::getInstance();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	public function testGetInstanceReturnsFrontEndLoginManagerInstance() {
		$this->assertTrue(
			$this->fixture instanceof tx_oelib_FrontEndLoginManager
		);
	}

	public function testGetInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			$this->fixture,
			tx_oelib_FrontEndLoginManager::getInstance()
		);
	}

	public function testGetInstanceAfterPurgeInstanceReturnsNewInstance() {
		tx_oelib_FrontEndLoginManager::purgeInstance();

		$this->assertNotSame(
			$this->fixture,
			tx_oelib_FrontEndLoginManager::getInstance()
		);
	}


	////////////////////////////////
	// Tests concerning isLoggedIn
	////////////////////////////////

	public function testIsLoggedInForNoFrontEndReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}

	public function testIsLoggedInForFrontEndWithoutLoggedInUserReturnsFalse() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}

	public function testIsLoggedInWithLoggedInFrontEndUserReturnsTrue() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}


	/////////////////////////////////////
	// Tests concerning getLoggedInUser
	/////////////////////////////////////

	public function testGetLoggedInUserWithEmptyMapperNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$mapperName must not be empty.'
		);

		$this->fixture->getLoggedInUser('');
	}

	public function testGetLoggedInUserWithoutFrontEndReturnsNull() {
		$this->testingFramework->discardFakeFrontEnd();

		$this->assertNull(
			$this->fixture->getLoggedInUser()
		);
	}

	public function testGetLoggedInUserWithoutLoggedInUserReturnsNull() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->logoutFrontEndUser();

		$this->assertNull(
			$this->fixture->getLoggedInUser()
		);
	}

	public function testGetLoggedInUserWithLoggedInUserReturnsFrontEndUserInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertTrue(
			$this->fixture->getLoggedInUser()
				instanceof tx_oelib_Model_FrontEndUser
		);
	}

	public function testGetLoggedInUserWithOtherMapperNameAndLoggedInUserReturnsCorrespondingModel() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertTrue(
			$this->fixture->getLoggedInUser('tx_oelib_tests_fixtures_TestingMapper')
				instanceof tx_oelib_tests_fixtures_TestingModel
		);
	}

	public function testGetLoggedInUserWithLoggedInUserReturnsFrontEndUserWithUidOfLoggedInUser() {
		$this->testingFramework->createFakeFrontEnd();
		$uid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->assertSame(
			$uid,
			$this->fixture->getLoggedInUser()->getUid()
		);
	}

	public function testGetLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$uid = $this->testingFramework->createAndLoginFrontEndUser();
		$user = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUser')
			->find($uid);

		$this->assertSame(
			$user,
			$this->fixture->getLoggedInUser()
		);
	}

	public function testGetLoggedInUserWithLoadedModelOfUserNotInDatabaseReturnsThatInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$user = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$user->setData(array());
		$this->testingFramework->loginFrontEndUser($user->getUid());

		$this->assertSame(
			$user,
			$this->fixture->getLoggedInUser()
		);
	}

	public function testGetLoggedInUserUsesMappedUserDataFromMemory() {
		$this->testingFramework->createFakeFrontEnd();
		$feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
			'', array('name' => 'John Doe')
		);

		$GLOBALS['TSFE']->fe_user->user['name'] = 'Jane Doe';
		$this->testingFramework->changeRecord(
			'fe_users', $feUserUid, array('name' => 'James Doe')
		);

		$this->assertSame(
			'John Doe',
			$this->fixture->getLoggedInUser()->getName()
		);
	}


	///////////////////////////////
	// Tests concerning logInUser
	///////////////////////////////

	/**
	 * @test
	 */
	public function logInUserChangesToLoggedInStatus() {
		$user = new tx_oelib_Model_FrontEndUser();
		$this->fixture->logInUser($user);

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function logInUserSetsLoggedInUser() {
		$user = new tx_oelib_Model_FrontEndUser();
		$this->fixture->logInUser($user);

		$this->assertSame(
			$user,
			$this->fixture->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function logInUserOverwritesFormerSimulatedLoggedInUser() {
		$oldUser = new tx_oelib_Model_FrontEndUser();
		$this->fixture->logInUser($oldUser);
		$newUser = new tx_oelib_Model_FrontEndUser();
		$this->fixture->logInUser($newUser);

		$this->assertSame(
			$newUser,
			$this->fixture->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function logInUserOverwritesFormerRealLoggedInUser() {
		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		$user = new tx_oelib_Model_FrontEndUser();
		$this->fixture->logInUser($user);

		$this->assertSame(
			$user,
			$this->fixture->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function logInUserWithNullSetsUserToNull() {
		$user = new tx_oelib_Model_FrontEndUser();
		$this->fixture->logInUser($user);

		$this->fixture->logInUser(NULL);

		$this->assertNull(
			$this->fixture->getLoggedInUser()
		);
	}

	/**
	 * @test
	 */
	public function logInUserWithNullSetsStatusToNotLoggedIn() {
		$user = new tx_oelib_Model_FrontEndUser();
		$this->fixture->logInUser($user);

		$this->fixture->logInUser(NULL);

		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}
}
?>