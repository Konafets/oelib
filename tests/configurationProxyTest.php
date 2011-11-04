<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2011 Saskia Metzler <saskia@merlin.owl.de> All rights reserved
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

define('OELIB_EXTENSION_KEY', 'oelib');

/**
 * Testcase for the tx_oelib_configurationProxy class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_configurationProxyTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_configurationProxy
	 */
	private $fixture;

	/**
	 * @var array
	 */
	private $testConfiguration = array(
		'testValueString' => 'foo',
		'testValueEmptyString' => '',
		'testValuePositiveInteger' => 2,
		'testValueNegativeInteger' => -1,
		'testValueZeroInteger' => 0,
		'testValueTrue' => 1,
		'testValueFalse' => 0
	);

	public function setUp() {
		$this->fixture
			= tx_oelib_configurationProxy::getInstance(OELIB_EXTENSION_KEY);
		// ensures the same configuration at the beginning of each test
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]
			= serialize($this->testConfiguration);
		$this->fixture->retrieveConfiguration();
	}

	public function tearDown() {
		tx_oelib_configurationProxy::purgeInstances();
		unset($this->fixture);
	}


	public function testGetInstanceReturnsObject() {
		$this->assertTrue(
			is_object($this->fixture)
		);
	}

	public function testGetInstanceThrowsExeptionIfNoExtensionKeyGiven() {
		$this->setExpectedException('Exception', 'The extension key was not set.');
		tx_oelib_configurationProxy::getInstance('');
	}

	public function testGetInstanceReturnsTheSameObjectWhenCalledInTheSameClass() {
		$this->assertSame(
			$this->fixture,
			tx_oelib_configurationProxy::getInstance(OELIB_EXTENSION_KEY)
		);
	}

	public function testInstanciateOfAnotherProxyCreatesNewObject() {
		$otherConfiguration = tx_oelib_configurationProxy::getInstance('other_extension');

		$this->assertNotSame(
			$this->fixture,
			$otherConfiguration
		);
	}

	/**
	 * @test
	 */
	public function extendsPublicObject() {
		$this->assertTrue(
			$this->fixture instanceof tx_oelib_PublicObject
		);
	}

	public function testGetCompleteConfigurationReturnsAllTestConfigurationData() {
		$this->assertEquals(
			$this->testConfiguration,
			$this->fixture->getCompleteConfiguration()
		);
	}

	public function testRetrieveConfigurationIfThereIsNone() {
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]);
		$this->fixture->retrieveConfiguration();

		$this->assertFalse(
			$this->fixture->getCompleteConfiguration()
		);
	}

	public function testRetrieveConfigurationIfThereIsNoneAndSetNewConfigurationValue() {
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]);
		$this->fixture->retrieveConfiguration();
		$this->fixture->setAsString('testValue', 'foo');

		$this->assertEquals(
			'foo',
			$this->fixture->getAsString('testValue')
		);
	}

	public function testInstanciateAnotherProxyAndSetValueNotAffectsThisFixture() {
		$otherConfiguration = tx_oelib_configurationProxy::getInstance('other_extension');
		$otherConfiguration->setAsString('testValue', 'foo');

		$this->assertEquals(
			'foo',
			$otherConfiguration->getAsString('testValue')
		);

		$this->assertEquals(
			$this->testConfiguration,
			$this->fixture->getCompleteConfiguration()
		);
	}
}
?>