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
		Tx_Oelib_MapperRegistry::purgeInstance();
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsMapperRegistryInstance() {
		$this->assertTrue(
			Tx_Oelib_MapperRegistry::getInstance()
				instanceof Tx_Oelib_MapperRegistry
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			Tx_Oelib_MapperRegistry::getInstance(),
			Tx_Oelib_MapperRegistry::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = Tx_Oelib_MapperRegistry::getInstance();
		Tx_Oelib_MapperRegistry::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			Tx_Oelib_MapperRegistry::getInstance()
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

		Tx_Oelib_MapperRegistry::get('');
	}

	/**
	 * @test
	 */
	public function getForMalformedKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$className must be in the format tx_extensionname[_Folder]_ClassName, but was "foo".'
		);

		Tx_Oelib_MapperRegistry::get('foo');
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Oelib_Exception_NotFound
	 */
	public function getForInexistentClassThrowsNotFoundException() {
		Tx_Oelib_MapperRegistry::get('Tx_Oelib_InexistentMapper');
	}

	/**
	 * @test
	 */
	public function getForExistingClassReturnsObjectOfRequestedClass() {
		$this->assertTrue(
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')
				instanceof tx_oelib_Tests_Unit_Fixtures_TestingMapper
		);
	}

	/**
	 * @test
	 */
	public function getForExistingClassWithExtbaseCapitalizationReturnsObjectOfRequestedClass() {
		$this->assertTrue(
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')
			instanceof tx_oelib_Tests_Unit_Fixtures_TestingMapper
		);
	}

	/**
	 * @test
	 */
	public function getForExistingClassWithAllLowercaseReturnsObjectOfRequestedClass() {
		$this->assertTrue(
			Tx_Oelib_MapperRegistry::get('tx_oelib_tests_unit_fixtures_testingmapper')
			instanceof tx_oelib_Tests_Unit_Fixtures_TestingMapper
		);
	}

	/**
	 * @test
	 */
	public function getForExistingClassCalledTwoTimesReturnsTheSameInstance() {
		$this->assertSame(
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper'),
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')
		);
	}


	////////////////////////////////////////////
	// Tests concerning denied database access
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAfterDenyDatabaseAccessReturnsNewMapperInstanceWithDatabaseAccessDisabled() {
		Tx_Oelib_MapperRegistry::denyDatabaseAccess();

		$this->assertFalse(
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')->hasDatabaseAccess()
		);
	}

	/**
	 * @test
	 */
	public function getAfterDenyDatabaseAccessReturnsExistingMapperInstanceWithDatabaseAccessDisabled() {
		Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper');
		Tx_Oelib_MapperRegistry::denyDatabaseAccess();

		$this->assertFalse(
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')->hasDatabaseAccess()
		);
	}

	/**
	 * @test
	 */
	public function getAfterInstanceWithDeniedDatabaseAccessWasPurgedReturnsMapperWithDatabaseAccessGranted() {
		Tx_Oelib_MapperRegistry::getInstance();
		Tx_Oelib_MapperRegistry::denyDatabaseAccess();
		Tx_Oelib_MapperRegistry::purgeInstance();

		$this->assertTrue(
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')->hasDatabaseAccess()
		);
	}

	/**
	 * @test
	 */
	public function getWithActivatedTestingModeReturnsMapperWithTestingLayer() {
		Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode(
			new Tx_Oelib_TestingFramework('tx_oelib')
		);

		$this->assertTrue(
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')
				instanceof Tx_Oelib_Tests_Unit_Fixtures_TestingMapper
		);
	}

	/**
	 * @test
	 */
	public function getAfterInstanceWithActivatedTestingModeWasPurgedReturnsMapperWithoutTestingLayer() {
		Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode(
			new Tx_Oelib_TestingFramework('tx_oelib')
		);
		Tx_Oelib_MapperRegistry::purgeInstance();

		$this->assertFalse(
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')
				instanceof Tx_Oelib_Tests_Unit_Fixtures_TestingMapperTesting
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
		Tx_Oelib_MapperRegistry::set(
			'Tx_Oelib_Tests_Unit_Fixtures_TestingMapper', $mapper
		);

		$this->assertSame(
			$mapper,
			Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')
		);
	}

	/**
	 * @test
	 */
	public function getWithAllLowercaseReturnsMapperSetViaSetWithExtbaseCasing() {
		$className = 'Tx_Oelib_AnotherTestingMapper';

		/** @var $mapper Tx_Oelib_Tests_Unit_Fixtures_TestingMapper */
		$mapper = $this->getMock('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper', array(), array(), $className);
		Tx_Oelib_MapperRegistry::set($className, $mapper);

		$this->assertSame(
			$mapper,
			Tx_Oelib_MapperRegistry::get(strtolower($className))
		);
	}

	/**
	 * @test
	 */
	public function getWithExtbaseCasingReturnsMapperSetViaSetWithAllLowercase() {
		$className = 'Tx_Oelib_AnotherTestingMapper';

		/** @var $mapper Tx_Oelib_Tests_Unit_Fixtures_TestingMapper */
		$mapper = $this->getMock('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper', array(), array(), $className);
		Tx_Oelib_MapperRegistry::set(strtolower($className), $mapper);

		$this->assertSame(
			$mapper,
			Tx_Oelib_MapperRegistry::get($className)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setThrowsExceptionForMismatchingWrapperClass() {
		$mapper = new tx_oelib_Tests_Unit_Fixtures_TestingMapper();
		Tx_Oelib_MapperRegistry::set(
			'Tx_Oelib_Mapper_Foo', $mapper
		);
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function setThrowsExceptionIfTheMapperTypeAlreadyIsRegistered() {
		Tx_Oelib_MapperRegistry::get('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper');

		$mapper = new tx_oelib_Tests_Unit_Fixtures_TestingMapper();
		Tx_Oelib_MapperRegistry::set(
			'Tx_Oelib_Tests_Unit_Fixtures_TestingMapper', $mapper
		);
	}
}
?>