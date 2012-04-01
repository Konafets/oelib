<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Saskia Metzler <saskia@merlin.owl.de> All rights reserved
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
 * Testcase for the configuration proxy class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_configurationProxy_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_configurationProxy
	 */
	private $fixture;

	/**
	 * @var boolean
	 */
	private $deprecationLogEnabledBackup = FALSE;

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
		$this->deprecationLogEnabledBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];

		$this->fixture = tx_oelib_configurationProxy::getInstance(OELIB_EXTENSION_KEY);
		// ensures the same configuration at the beginning of each test
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]
			= serialize($this->testConfiguration);
		$this->fixture->retrieveConfiguration();
	}

	public function tearDown() {
		tx_oelib_configurationProxy::purgeInstances();
		unset($this->fixture);

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = $this->deprecationLogEnabledBackup;
	}


	public function testGetInstanceReturnsObject() {
		$this->assertTrue(
			is_object($this->fixture)
		);
	}

	public function testGetInstanceThrowsExeptionIfNoExtensionKeyGiven() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The extension key was not set.'
		);
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
	public function fixtureExtendsPublicObject() {
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

	public function testGetConfigurationValueStringIfValueExists() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			'foo',
			$this->fixture->getConfigurationValueString('testValueString')
		);
	}

	public function testGetConfigurationValueStringReturnsEmptyStringEmptyStringGiven() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			'',
			$this->fixture->getConfigurationValueString('testValueEmptyString')
		);
	}

	public function testGetConfigurationValueStringReturnsEmptyStringIfValueNotExists() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			'',
			$this->fixture->getConfigurationValueString('foo')
		);
	}

	public function testGetConfigurationValueBooleanTrue() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			TRUE,
			$this->fixture->getConfigurationValueBoolean('testValueTrue')
		);
	}

	public function testGetConfigurationValueBooleanFalse() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			FALSE,
			$this->fixture->getConfigurationValueBoolean('testValueFalse')
		);
	}

	public function testGetConfigurationValueBooleanFalseIfValueWasNotSet() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			FALSE,
			$this->fixture->getConfigurationValueBoolean('foo')
		);
	}

	public function testGetConfigurationValueIntegerIfPositiveIntegerGiven() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			2,
			$this->fixture->getConfigurationValueInteger(
				'testValuePositiveInteger'
			)
		);
	}

	public function testGetConfigurationValueIntegerIfNegativeIntegerGiven() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			-1,
			$this->fixture->getConfigurationValueInteger(
				'testValueNegativeInteger'
			)
		);
	}

	public function testGetConfigurationValueIntegerIfZeroGiven() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			0,
			$this->fixture->getConfigurationValueInteger('testValueZeroInteger')
		);
	}

	public function testGetConfigurationValueIntegerIfValueNotExists() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertEquals(
			0,
			$this->fixture->getConfigurationValueInteger('foo')
		);
	}

	public function testSetConfigurationValueStringChangesAnExistingValue() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->fixture->setConfigurationValueString('testValueString', 'bar');

		$this->assertEquals(
			'bar',
			$this->fixture->getConfigurationValueString('testValueString')
		);
	}

	public function testSetConfigurationValueStringAddsNewValue() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->fixture->setConfigurationValueString('testValue', 'foo');

		$this->assertEquals(
			'foo',
			$this->fixture->getConfigurationValueString('testValue')
		);
	}

	public function testSetConfigurationValueBooleanTrue() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->fixture->setConfigurationValueBoolean('testValue', TRUE);

		$this->assertEquals(
			TRUE,
			$this->fixture->getConfigurationValueBoolean('testValue')
		);
	}

	public function testSetConfigurationValueBooleanFalse() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->fixture->setConfigurationValueBoolean('testValue', FALSE);

		$this->assertEquals(
			FALSE,
			$this->fixture->getConfigurationValueBoolean('testValue')
		);
	}

	public function testSetConfigurationValueIntegerIfValuePositive() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->fixture->setConfigurationValueInteger('testValue', 2);

		$this->assertEquals(
			2,
			$this->fixture->getConfigurationValueInteger('testValue')
		);
	}

	public function testSetConfigurationValueIntegerIfValueNegative() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->fixture->setConfigurationValueInteger('testValue', -2);

		$this->assertEquals(
			-2,
			$this->fixture->getConfigurationValueInteger('testValue')
		);
	}

	public function testSetConfigurationValueIntegerIfZero() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->fixture->setConfigurationValueInteger('testValue', 0);

		$this->assertEquals(
			0,
			$this->fixture->getConfigurationValueInteger('testValue')
		);
	}

	public function testRetrieveConfigurationSetsOriginalValuesAgainIfValueWasChanged() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->fixture->setConfigurationValueString('testValueString', 'bar');
		$this->fixture->retrieveConfiguration();

		$this->assertEquals(
			$this->testConfiguration,
			$this->fixture->getCompleteConfiguration()
		);
	}

	public function testRetrieveConfigurationSetsOriginalValuesAgainIfValueWasAdded() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->fixture->setConfigurationValueString('testValue', 'foo');
		$this->fixture->retrieveConfiguration();

		$this->assertEquals(
			'',
			$this->fixture->getConfigurationValueString('testValue')
		);
	}

	public function testRetrieveConfigurationIfThereIsNone() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]);
		$this->fixture->retrieveConfiguration();

		$this->assertFalse(
			$this->fixture->getCompleteConfiguration()
		);
	}

	public function testRetrieveConfigurationIfThereIsNoneAndSetNewConfigurationValue() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]);
		$this->fixture->retrieveConfiguration();
		$this->fixture->setConfigurationValueString('testValue', 'foo');

		$this->assertEquals(
			'foo',
			$this->fixture->getConfigurationValueString('testValue')
		);
	}

	public function testInstanciateAnotherProxyAndSetValueNotAffectsThisFixture() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$otherConfiguration = tx_oelib_configurationProxy::getInstance('other_extension');
		$otherConfiguration->setConfigurationValueString('testValue', 'foo');

		$this->assertEquals(
			'foo',
			$otherConfiguration->getConfigurationValueString('testValue')
		);

		$this->assertEquals(
			$this->testConfiguration,
			$this->fixture->getCompleteConfiguration()
		);
	}
}
?>