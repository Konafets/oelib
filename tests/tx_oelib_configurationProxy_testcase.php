<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Saskia Metzler <saskia@merlin.owl.de> All rights reserved
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
 * Testcase for the configuration proxy class in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 */

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_configurationProxy.php');

define('OELIB_EXTENSION_KEY', 'oelib');

class tx_oelib_configurationProxy_testcase extends tx_phpunit_testcase {
	private $fixture;

	private $testConfiguration = array(
		'testValueString' => 'foo',
		'testValueEmptyString' => '',
		'testValuePositiveInteger' => 2,
		'testValueNegativeInteger' => -1,
		'testValueZeroInteger' => 0,
		'testValueTrue' => 1,
		'testValueFalse' => 0
	);

	private $originalConfiguration = '';

	protected function setUp() {
		$this->fixture = tx_oelib_configurationProxy::getInstance(OELIB_EXTENSION_KEY);
		// saves the original configuration
		$this->originalConfiguration
			= $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY];
		// ensures the same configuration at the beginning of each test
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]
			= serialize($this->testConfiguration);
		$this->fixture->retrieveConfiguration();
	}

	protected function tearDown() {
		unset($this->fixture);
		// restores the original configuration as it is not restored
		// automatically until all oelib tests have finished
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][OELIB_EXTENSION_KEY]
			= $this->originalConfiguration;
	}


	public function testGetInstance() {
		$this->assertTrue(is_object($this->fixture));
	}

	public function testGetInstanceThrowsExeptionIfNoExtensionKeyGiven() {
		try {
			tx_oelib_configurationProxy::getInstance('');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail('The expected exception was not caught!');
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

	public function testGetCompleteConfigurationReturnsAllTestConfigurationData() {
		$this->assertEquals(
			$this->testConfiguration,
			$this->fixture->getCompleteConfiguration()
		);
	}

	public function testGetConfigurationValueStringIfValueExists() {
		$this->assertEquals(
			'foo',
			$this->fixture->getConfigurationValueString('testValueString')
		);
	}

	public function testGetConfigurationValueStringReturnsEmptyStringEmptyStringGiven() {
		$this->assertEquals(
			'',
			$this->fixture->getConfigurationValueString('testValueEmptyString')
		);
	}

	public function testGetConfigurationValueStringReturnsEmptyStringIfValueNotExists() {
		$this->assertEquals(
			'',
			$this->fixture->getConfigurationValueString('foo')
		);
	}

	public function testGetConfigurationValueBooleanTrue() {
		$this->assertEquals(
			true,
			$this->fixture->getConfigurationValueBoolean('testValueTrue')
		);
	}

	public function testGetConfigurationValueBooleanFalse() {
		$this->assertEquals(
			false,
			$this->fixture->getConfigurationValueBoolean('testValueFalse')
		);
	}

	public function testGetConfigurationValueBooleanFalseIfValueWasNotSet() {
		$this->assertEquals(
			false,
			$this->fixture->getConfigurationValueBoolean('foo')
		);
	}

	public function testGetConfigurationValueIntegerIfPositiveIntegerGiven() {
		$this->assertEquals(
			2,
			$this->fixture->getConfigurationValueInteger(
				'testValuePositiveInteger'
			)
		);
	}

	public function testGetConfigurationValueIntegerIfNegativeIntegerGiven() {
		$this->assertEquals(
			-1,
			$this->fixture->getConfigurationValueInteger(
				'testValueNegativeInteger'
			)
		);
	}

	public function testGetConfigurationValueIntegerIfZeroGiven() {
		$this->assertEquals(
			0,
			$this->fixture->getConfigurationValueInteger('testValueZeroInteger')
		);
	}

	public function testGetConfigurationValueIntegerIfValueNotExists() {
		$this->assertEquals(
			0,
			$this->fixture->getConfigurationValueInteger('foo')
		);
	}

	public function testSetConfigurationValueStringChangesAnExistingValue() {
		$this->fixture->setConfigurationValueString('testValueString', 'bar');

		$this->assertEquals(
			'bar',
			$this->fixture->getConfigurationValueString('testValueString')
		);
	}

	public function testSetConfigurationValueStringAddsNewValue() {
		$this->fixture->setConfigurationValueString('testValue', 'foo');

		$this->assertEquals(
			'foo',
			$this->fixture->getConfigurationValueString('testValue')
		);
	}

	public function testSetConfigurationValueBooleanTrue() {
		$this->fixture->setConfigurationValueBoolean('testValue', true);

		$this->assertEquals(
			true,
			$this->fixture->getConfigurationValueBoolean('testValue')
		);
	}

	public function testSetConfigurationValueBooleanFalse() {
		$this->fixture->setConfigurationValueBoolean('testValue', false);

		$this->assertEquals(
			false,
			$this->fixture->getConfigurationValueBoolean('testValue')
		);
	}

	public function testSetConfigurationValueIntegerIfValuePositive() {
		$this->fixture->setConfigurationValueInteger('testValue', 2);

		$this->assertEquals(
			2,
			$this->fixture->getConfigurationValueInteger('testValue')
		);
	}

	public function testSetConfigurationValueIntegerIfValueNegative() {
		$this->fixture->setConfigurationValueInteger('testValue', -2);

		$this->assertEquals(
			-2,
			$this->fixture->getConfigurationValueInteger('testValue')
		);
	}

	public function testSetConfigurationValueIntegerIfZero() {
		$this->fixture->setConfigurationValueInteger('testValue', 0);

		$this->assertEquals(
			0,
			$this->fixture->getConfigurationValueInteger('testValue')
		);
	}

	public function testRetrieveConfigurationSetsOriginalValuesAgainIfValueWasChanged() {
		$this->fixture->setConfigurationValueString('testValueString', 'bar');
		$this->fixture->retrieveConfiguration();

		$this->assertEquals(
			$this->testConfiguration,
			$this->fixture->getCompleteConfiguration()
		);
	}

	public function testRetrieveConfigurationSetsOriginalValuesAgainIfValueWasAdded() {
		$this->fixture->setConfigurationValueString('testValue', 'foo');
		$this->fixture->retrieveConfiguration();

		$this->assertEquals(
			'',
			$this->fixture->getConfigurationValueString('testValue')
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
		$this->fixture->setConfigurationValueString('testValue', 'foo');

		$this->assertEquals(
			'foo',
			$this->fixture->getConfigurationValueString('testValue')
		);
	}

	public function testInstanciateAnotherProxyAndSetValueNotAffectsThisFixture() {
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
