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
class Tx_Oelib_MapperRegistryTest extends Tx_Phpunit_TestCase {
	public function setUp() {
	}

	public function tearDown() {
		tx_oelib_MapperRegistry::purgeInstance();
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsMapperRegistryInstance() {
		$this->assertTrue(
			tx_oelib_MapperRegistry::getInstance()
				instanceof tx_oelib_MapperRegistry
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_MapperRegistry::getInstance(),
			tx_oelib_MapperRegistry::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
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

	/**
	 * @test
	 */
	public function getForEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$className must not be empty.'
		);

		tx_oelib_MapperRegistry::get('');
	}

	/**
	 * @test
	 */
	public function getForMalformedKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$className must be in the format tx_extensionname[_Folder]_ClassName, but was "foo".'
		);

		tx_oelib_MapperRegistry::get('foo');
	}

	/**
	 * @test
	 */
	public function getForInexistentClassThrowsNotFoundException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'No mapper class "tx_oelib_inexistentMapper" could be found.'
		);

		tx_oelib_MapperRegistry::get('tx_oelib_inexistentMapper');
	}

	/**
	 * @test
	 */
	public function getForExistingClassReturnsObjectOfRequestedClass() {
		$this->assertTrue(
			tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper')
				instanceof tx_oelib_Tests_Unit_Fixtures_TestingMapper
		);
	}

	/**
	 * @test
	 */
	public function getForExistingClassCalledTwoTimesReturnsTheSameInstance() {
		$this->assertSame(
			tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper'),
			tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper')
		);
	}


	////////////////////////////////////////////
	// Tests concerning denied database access
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAfterDenyDatabaseAccessReturnsNewMapperInstanceWithDatabaseAccessDisabled() {
		tx_oelib_MapperRegistry::denyDatabaseAccess();

		$this->assertFalse(
			tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper')->hasDatabaseAccess()
		);
	}

	/**
	 * @test
	 */
	public function getAfterDenyDatabaseAccessReturnsExistingMapperInstanceWithDatabaseAccessDisabled() {
		tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper');
		tx_oelib_MapperRegistry::denyDatabaseAccess();

		$this->assertFalse(
			tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper')->hasDatabaseAccess()
		);
	}

	/**
	 * @test
	 */
	public function getAfterInstanceWithDeniedDatabaseAccessWasPurgedReturnsMapperWithDatabaseAccessGranted() {
		tx_oelib_MapperRegistry::getInstance();
		tx_oelib_MapperRegistry::denyDatabaseAccess();
		tx_oelib_MapperRegistry::purgeInstance();

		$this->assertTrue(
			tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper')->hasDatabaseAccess()
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
			tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper')
				instanceof tx_oelib_Tests_Unit_Fixtures_TestingMapperTesting
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
			tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper')
				instanceof tx_oelib_Tests_Unit_Fixtures_TestingMapperTesting
		);
	}


	/////////////////////////
	// Tests concerning set
	/////////////////////////

	/**
	 * @test
	 */
	public function getReturnsMapperSetViaSet() {
		$mapper = new tx_oelib_Tests_Unit_Fixtures_TestingMapper();
		tx_oelib_MapperRegistry::set(
			'tx_oelib_Tests_Unit_Fixtures_TestingMapper', $mapper
		);

		$this->assertSame(
			$mapper,
			tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper')
		);
	}

	/**
	 * @test
	 */
	public function setThrowsExceptionForMismatchingWrapperClass() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The provided mapper is not an instance of tx_oelib_Mapper_Foo.'
		);

		$mapper = new tx_oelib_Tests_Unit_Fixtures_TestingMapper();
		tx_oelib_MapperRegistry::set(
			'tx_oelib_Mapper_Foo', $mapper
		);
	}

	/**
	 * @test
	 */
	public function setThrowsExceptionIfTheMapperTypeAlreadyIsRegistered() {
		$this->setExpectedException(
			'BadMethodCallException',
			'There already is a tx_oelib_Tests_Unit_Fixtures_TestingMapper mapper registered. ' .
				'Overwriting existing wrappers is not allowed.'
		);

		tx_oelib_MapperRegistry::get('tx_oelib_Tests_Unit_Fixtures_TestingMapper');

		$mapper = new tx_oelib_Tests_Unit_Fixtures_TestingMapper();
		tx_oelib_MapperRegistry::set(
			'tx_oelib_Tests_Unit_Fixtures_TestingMapper', $mapper
		);
	}
}
?>