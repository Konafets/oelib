<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2011 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_MapperRegistry class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_MapperRegistryTest extends tx_phpunit_testcase {
	public function setUp() {
	}

	public function tearDown() {
		tx_oelib_MapperRegistry::purgeInstance();
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	public function testGetInstanceReturnsMapperRegistryInstance() {
		$this->assertTrue(
			tx_oelib_MapperRegistry::getInstance()
				instanceof tx_oelib_MapperRegistry
		);
	}

	public function testGetInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_MapperRegistry::getInstance(),
			tx_oelib_MapperRegistry::getInstance()
		);
	}

	public function testGetInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = tx_oelib_MapperRegistry::getInstance();
		tx_oelib_MapperRegistry::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			tx_oelib_MapperRegistry::getInstance()
		);
	}


	////////////////////////////////////////
	// Test concerning get and setMappings
	////////////////////////////////////////

	public function testGetForEmptyKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '$key must not be empty.'
		);

		tx_oelib_MapperRegistry::get('');
	}

	public function testGetForMalformedKeyThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$className must be in the format ' .
				'tx_extensionname[_Folder]_ClassName, but was "foo".'
		);

		tx_oelib_MapperRegistry::get('foo');
	}

	public function testGetForInexistentClassThrowsNotFoundException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'No mapper class "tx_oelib_inexistentMapper" could be found.'
		);

		tx_oelib_MapperRegistry::get('tx_oelib_inexistentMapper');
	}

	public function testGetForExistingClassReturnsObjectOfRequestedClass() {
		$this->assertTrue(
			tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper')
				instanceof tx_oelib_tests_fixtures_TestingMapper
		);
	}

	public function testGetForExistingClassCalledTwoTimesReturnsTheSameInstance() {
		$this->assertSame(
			tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper'),
			tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper')
		);
	}


	////////////////////////////////////////////
	// Tests concerning denied database access
	////////////////////////////////////////////

	public function testGetAfterDenyDatabaseAccessReturnsNewMapperInstanceWithDatabaseAccessDisabled() {
		tx_oelib_MapperRegistry::denyDatabaseAccess();

		$this->assertFalse(
			tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper')->hasDatabaseAccess()
		);
	}

	public function testGetAfterDenyDatabaseAccessReturnsExistingMapperInstanceWithDatabaseAccessDisabled() {
		tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper');
		tx_oelib_MapperRegistry::denyDatabaseAccess();

		$this->assertFalse(
			tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper')->hasDatabaseAccess()
		);
	}

	public function testGetAfterInstanceWithDeniedDatabaseAccessWasPurgedReturnsMapperWithDatabaseAccessGranted() {
		tx_oelib_MapperRegistry::getInstance();
		tx_oelib_MapperRegistry::denyDatabaseAccess();
		tx_oelib_MapperRegistry::purgeInstance();

		$this->assertTrue(
			tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper')->hasDatabaseAccess()
		);
	}

	/**
	 * @test
	 */
	public function getWithActivatedTestingModeReturnsMapperWithTestingLayer() {
		tx_oelib_MapperRegistry::getInstance()->activateTestingMode(
			new tx_oelib_testingFramework('tx_oelib')
		);

		$this->assertTrue(
			tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper')
				instanceof tx_oelib_tests_fixtures_TestingMapperTesting
		);
	}

	/**
	 * @test
	 */
	public function getAfterInstanceWithActivatedTestingModeWasPurgedReturnsMapperWithoutTestingLayer() {
		tx_oelib_MapperRegistry::getInstance()->activateTestingMode(
			new tx_oelib_testingFramework('tx_oelib')
		);
		tx_oelib_MapperRegistry::purgeInstance();

		$this->assertFalse(
			tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper')
				instanceof tx_oelib_tests_fixtures_TestingMapperTesting
		);
	}


	/////////////////////////
	// Tests concerning set
	/////////////////////////

	/**
	 * @test
	 */
	public function getReturnsMapperSetViaSet() {
		$mapper = new tx_oelib_tests_fixtures_TestingMapper();
		tx_oelib_MapperRegistry::set(
			'tx_oelib_tests_fixtures_TestingMapper', $mapper
		);

		$this->assertSame(
			$mapper,
			tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper')
		);
	}

	/**
	 * @test
	 */
	public function setThrowsExceptionForMismatchingWrapperClass() {
		$this->setExpectedException(
			'Exception',
			'The provided mapper is not an instance of tx_oelib_Mapper_Foo.'
		);

		$mapper = new tx_oelib_tests_fixtures_TestingMapper();
		tx_oelib_MapperRegistry::set(
			'tx_oelib_Mapper_Foo', $mapper
		);
	}

	/**
	 * @test
	 */
	public function setThrowsExceptionIfTheMapperTypeAlreadyIsRegistered() {
		$this->setExpectedException(
			'Exception',
			'There already exists a mapper of the same type. ' .
				'Overwriting existing wrappers is not allowed.'
		);

		tx_oelib_MapperRegistry::get('tx_oelib_tests_fixtures_TestingMapper');

		$mapper = new tx_oelib_tests_fixtures_TestingMapper();
		tx_oelib_MapperRegistry::set(
			'tx_oelib_tests_fixtures_TestingMapper', $mapper
		);
	}
}
?>