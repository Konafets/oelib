<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_ConfigurationRegistry class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_ConfigurationRegistry_testcase extends tx_phpunit_testcase {
	public function setUp() {
	}

	public function tearDown() {
		tx_oelib_ConfigurationRegistry::purgeInstance();
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	public function testGetInstanceReturnsConfigurationRegistryInstance() {
		$this->assertTrue(
			tx_oelib_ConfigurationRegistry::getInstance()
				instanceof tx_oelib_ConfigurationRegistry
		);
	}

	public function testGetInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_ConfigurationRegistry::getInstance(),
			tx_oelib_ConfigurationRegistry::getInstance()
		);
	}

	public function testGetInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = tx_oelib_ConfigurationRegistry::getInstance();
		tx_oelib_ConfigurationRegistry::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			tx_oelib_ConfigurationRegistry::getInstance()
		);
	}


	////////////////////////////////
	// Test concerning get and set
	////////////////////////////////

	public function testGetForEmptyNamespaceThrowsException() {
		$this->setExpectedException(
			'Exception', '$namespace must not be empty.'
		);

		tx_oelib_ConfigurationRegistry::get('');
	}

	public function testGetForNonEmptyNamespaceReturnsConfigurationInstance() {
		$this->assertTrue(
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				instanceof tx_oelib_Configuration
		);
	}

	public function testGetForTheSameNamespaceCalledTwoTimesReturnsTheSameInstance() {
		$this->assertSame(
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib'),
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
		);
	}

	public function testSetWithEmptyNamespaceThrowsException() {
		$this->setExpectedException(
			'Exception', '$namespace must not be empty.'
		);

		tx_oelib_ConfigurationRegistry::getInstance()->set(
			'',  new tx_oelib_Configuration()
		);
	}

	public function testGetAfterSetReturnsTheSetInstance() {
		$configuration = new tx_oelib_Configuration();

		tx_oelib_ConfigurationRegistry::getInstance()
			->set('foo', $configuration);

		$this->assertSame(
			$configuration,
			tx_oelib_ConfigurationRegistry::get('foo')
		);
	}

	public function testSetTwoTimesForTheSameNamespaceDoesNotFail() {
		tx_oelib_ConfigurationRegistry::getInstance()->set(
			'foo',  new tx_oelib_Configuration()
		);
		tx_oelib_ConfigurationRegistry::getInstance()->set(
			'foo',  new tx_oelib_Configuration()
		);
	}
}
?>