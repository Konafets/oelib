<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Saskia Metzler <saskia@merlin.owl.de> All rights reserved
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

define('OELIB_EXTENSION_KEY', 'oelib');

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_ConfigurationProxyTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_configurationProxy
	 */
	private $subject;

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
		$this->subject
			= tx_oelib_configurationProxy::getInstance(OELIB_EXTENSION_KEY);
		// ensures the same configuration at the beginning of each test
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]
			= serialize($this->testConfiguration);
		$this->subject->retrieveConfiguration();
	}

	public function tearDown() {
		tx_oelib_configurationProxy::purgeInstances();
		unset($this->subject);
	}


	/**
	 * @test
	 */
	public function getInstanceReturnsObject() {
		$this->assertTrue(
			is_object($this->subject)
		);
	}

	/**
	 * @test
	 */
	public function getInstanceThrowsExceptionIfNoExtensionKeyGiven() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The extension key was not set.'
		);
		tx_oelib_configurationProxy::getInstance('');
	}

	/**
	 * @test
	 */
	public function getInstanceReturnsTheSameObjectWhenCalledInTheSameClass() {
		$this->assertSame(
			$this->subject,
			tx_oelib_configurationProxy::getInstance(OELIB_EXTENSION_KEY)
		);
	}

	/**
	 * @test
	 */
	public function instantiateOfAnotherProxyCreatesNewObject() {
		$otherConfiguration = tx_oelib_configurationProxy::getInstance('other_extension');

		$this->assertNotSame(
			$this->subject,
			$otherConfiguration
		);
	}

	/**
	 * @test
	 */
	public function extendsPublicObject() {
		$this->assertTrue(
			$this->subject instanceof tx_oelib_PublicObject
		);
	}

	/**
	 * @test
	 */
	public function getCompleteConfigurationReturnsAllTestConfigurationData() {
		$this->assertSame(
			$this->testConfiguration,
			$this->subject->getCompleteConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function retrieveConfigurationIfThereIsNone() {
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]);
		$this->subject->retrieveConfiguration();

		$this->assertFalse(
			$this->subject->getCompleteConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function retrieveConfigurationIfThereIsNoneAndSetNewConfigurationValue() {
		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]);
		$this->subject->retrieveConfiguration();
		$this->subject->setAsString('testValue', 'foo');

		$this->assertSame(
			'foo',
			$this->subject->getAsString('testValue')
		);
	}

	/**
	 * @test
	 */
	public function instantiateAnotherProxyAndSetValueNotAffectsThisFixture() {
		$otherConfiguration = tx_oelib_configurationProxy::getInstance('other_extension');
		$otherConfiguration->setAsString('testValue', 'foo');

		$this->assertSame(
			'foo',
			$otherConfiguration->getAsString('testValue')
		);

		$this->assertSame(
			$this->testConfiguration,
			$this->subject->getCompleteConfiguration()
		);
	}
}
?>