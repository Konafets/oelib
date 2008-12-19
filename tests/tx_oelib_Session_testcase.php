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
 * Testcase for the tx_oelib_Session class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Session_testcase  extends tx_phpunit_testcase {
	/**
     * @var tx_oelib_testingFramework for creating a fake front end
     */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');

		tx_oelib_Session::purgeInstances();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->testingFramework);
	}


	/////////////////////////////////////////////////////////
	// Tests for setting and getting the Singleton instance
	/////////////////////////////////////////////////////////

	public function testGetInstanceThrowsExceptionWithoutFrontEnd() {
		$this->setExpectedException(
			'Exception',
			'This class must not be instantiated when there is no front end.'
		);

		$GLOBALS['TSFE'] = null;

		tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_USER);
	}

	public function testGetInstanceWithInvalidTypeThrowsException() {
		$this->setExpectedException(
			'Exception',
			'Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.'
		);

		$this->testingFramework->createFakeFrontEnd();

		tx_oelib_Session::getInstance(42);
	}

	public function testGetInstanceWithUserTypeReturnsSessionInstance() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertTrue(
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_USER)
				instanceof tx_oelib_Session
		);
	}

	public function testGetInstanceWithTemporaryTypeReturnsSessionInstance() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertTrue(
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_TEMPORARY)
				instanceof tx_oelib_Session
		);
	}

	public function testGetInstanceWithSameTypeReturnsSameInstance() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertSame(
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_USER),
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_USER)
		);
	}

	public function testGetInstanceWithDifferentTypesReturnsDifferentInstance() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertNotSame(
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_USER),
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_TEMPORARY)
		);
	}

	public function testGetInstanceWithSameTypesAfterPurgeInstancesReturnsNewInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$firstInstance = tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_USER);
		tx_oelib_Session::purgeInstances();

		$this->assertNotSame(
			$firstInstance,
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_USER)
		);
	}

	public function testSetInstanceWithInvalidTypeThrowsException() {
		$this->setExpectedException(
			'Exception',
			'Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.'
		);

		tx_oelib_Session::setInstance(42, new tx_oelib_FakeSession());
	}

	public function testGetInstanceWithUserTypeReturnsInstanceFromSetInstance() {
		$instance = new tx_oelib_FakeSession();
		tx_oelib_Session::setInstance(tx_oelib_Session::TYPE_USER, $instance);

		$this->assertSame(
			$instance,
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_USER)
		);
	}

	public function testGetInstanceWithTemporaryTypeReturnsInstanceFromSetInstance() {
		$instance = new tx_oelib_FakeSession();
		tx_oelib_Session::setInstance(
			tx_oelib_Session::TYPE_TEMPORARY, $instance
		);

		$this->assertSame(
			$instance,
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_TEMPORARY)
		);
	}

	public function testGetInstanceWithDifferentTypesReturnsDifferentInstancesSetViaSetInstance() {
		tx_oelib_Session::setInstance(
			tx_oelib_Session::TYPE_USER,
			new tx_oelib_FakeSession()
		);
		tx_oelib_Session::setInstance(
			tx_oelib_Session::TYPE_TEMPORARY,
			new tx_oelib_FakeSession()
		);

		$this->assertNotSame(
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_USER),
			tx_oelib_Session::getInstance(tx_oelib_Session::TYPE_TEMPORARY)
		);
	}
}
?>