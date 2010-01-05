<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_BackEndLoginManager class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_BackEndLoginManager_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_BackEndLoginManager
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');

		$this->fixture = tx_oelib_BackEndLoginManager::getInstance();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	public function testGetInstanceReturnsBackEndLoginManagerInstance() {
		$this->assertTrue(
			$this->fixture instanceof tx_oelib_BackEndLoginManager
		);
	}

	public function testGetInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			$this->fixture,
			tx_oelib_BackEndLoginManager::getInstance()
		);
	}

	public function testGetInstanceAfterPurgeInstanceReturnsNewInstance() {
		tx_oelib_BackEndLoginManager::purgeInstance();

		$this->assertNotSame(
			$this->fixture,
			tx_oelib_BackEndLoginManager::getInstance()
		);
	}


	////////////////////////////////
	// Tests concerning isLoggedIn
	////////////////////////////////

	public function testIsLoggedInWithLoggedInBackEndUserReturnsTrue() {
		// We assume that the tests are run when logged in in the BE.
		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	public function testIsLoggedInForFakedUserReturnsTrue() {
		$this->fixture->setLoggedInUser(
			tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')
				->getNewGhost()
		);

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	/////////////////////////////////////
	// Tests concerning getLoggedInUser
	/////////////////////////////////////

	public function testGetLoggedInUserWithEmptyMapperNameThrowsException() {
		$this->setExpectedException(
			'Exception', '$mapperName must not be empty.'
		);

		$this->fixture->getLoggedInUser('');
	}

	public function testGetLoggedInUserWithLoggedInUserReturnsBackEndUserInstance() {
		$this->assertTrue(
			$this->fixture->getLoggedInUser()
				instanceof tx_oelib_Model_BackEndUser
		);
	}

	public function testGetLoggedInUserWithOtherMapperNameAndLoggedInUserReturnsCorrespondingModel() {
		$this->assertTrue(
			$this->fixture->getLoggedInUser('tx_oelib_tests_fixtures_TestingMapper')
				instanceof tx_oelib_tests_fixtures_TestingModel
		);
	}

	public function testGetLoggedInUserWithLoggedInUserReturnsBackEndUserWithUidOfLoggedInUser() {
		$this->assertEquals(
			$GLOBALS['BE_USER']->user['uid'],
			$this->fixture->getLoggedInUser()->getUid()
		);
	}

	public function testGetLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance() {
		$user = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')
			->find($GLOBALS['BE_USER']->user['uid']);

		$this->assertSame(
			$user,
			$this->fixture->getLoggedInUser()
		);
	}

	public function testGetLoggedInUserUsesMappedUserDataFromMemory() {
		$backedUpName = $GLOBALS['BE_USER']->user['realName'];
		$GLOBALS['BE_USER']->user['realName'] = 'John Doe';

		$this->assertEquals(
			'John Doe',
			$this->fixture->getLoggedInUser()->getName()
		);

		$GLOBALS['BE_USER']->user['realName'] = $backedUpName;
	}


	////////////////////////////////////
	// Tests concerning setLoggedInUser
	////////////////////////////////////

	public function test_SetLoggedInUserForUserGiven_SetsTheLoggedInUser() {
		$backEndUser = tx_oelib_MapperRegistry::get(
			'tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$this->fixture->setLoggedInUser($backEndUser);

		$this->assertSame(
			$backEndUser,
			$this->fixture->getLoggedInUser()
		);
	}

	public function test_SetLoggedInUserForUserGivenAndAlreadyStoredLoggedInUser_OverridesTheOldUserWithTheNewOne() {
		$oldBackEndUser = tx_oelib_MapperRegistry::get(
			'tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$this->fixture->setLoggedInUser($oldBackEndUser);
		$newBackEndUser = tx_oelib_MapperRegistry::get(
			'tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$this->fixture->setLoggedInUser($newBackEndUser);

		$this->assertSame(
			$newBackEndUser,
			$this->fixture->getLoggedInUser()
		);
	}
}
?>