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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_oelib_configcheckTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_configcheck configuration check object to be tested
	 */
	private $fixture;

	/**
	 * @var tx_oelib_dummyObjectToCheck dummy object to be checked by the
	 *                                  configuration check object
	 */
	private $objectToCheck;

	protected function setUp() {
		$this->objectToCheck = new tx_oelib_dummyObjectToCheck(
			array(
				'emptyString' => '',
				'nonEmptyString' => 'foo',
				'validEmail' => 'any-address@valid-email.org',
				'existingColumn' => 'title',
				'inexistentColumn' => 'does_not_exist',
			)
		);
		$this->fixture = new tx_oelib_configcheck($this->objectToCheck);
	}

	protected function tearDown() {
		$this->fixture->__destruct();
		$this->objectToCheck->__destruct();

		unset($this->fixture, $this->objectToCheck);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Sets the configuration value for the locale to $localeKey.
	 *
	 * @param string $localeKey
	 *        key for the locale, to receive a non-configured locale, provide
	 *        an empty string
	 *
	 * @return void
	 */
	private function setConfigurationForLocale($localeKey) {
		$GLOBALS['TSFE']->config['config']['locale_all'] = $localeKey;
	}

	/**
	 * Returns a key of an installed locales which contains "utf".
	 *
	 * @return string installed locale with "utf" in the key (e.g.
	 *                "en_US.utf8"), may be empty if none of the installed
	 *                locales contains "utf"
	 */
	private function getInstalledUtfLocale() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$result = '';
		foreach ($this->fixture->getInstalledLocales() as $key) {
			if (stripos($key, 'utf') !== FALSE) {
				$result = $key;
				break;
			}
		}

		return $result;
	}


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testSetConfigurationForLocaleToANonEmptyValue() {
		$this->setConfigurationForLocale('foo');

		$this->assertSame(
			'foo',
			$GLOBALS['TSFE']->config['config']['locale_all']
		);
	}

	public function testSetConfigurationForLocaleToAnEmptyString() {
		$this->setConfigurationForLocale('');

		$this->assertSame(
			'',
			$GLOBALS['TSFE']->config['config']['locale_all']
		);
	}

	public function testGetInstalledUtfLocale() {
		$locale = $this->getInstalledUtfLocale();

		$this->assertTrue(
			in_array($locale, $this->fixture->getInstalledLocales())
		);
		$this->assertContains(
			'utf',
			strtolower($locale)
		);
	}


	/////////////////////////////////
	// Tests concerning the flavor.
	/////////////////////////////////

	public function testSetAndGetFlavor() {
		$this->fixture->setFlavor('foo');

		$this->assertSame(
			'foo',
			$this->fixture->getFlavor()
		);
	}


	//////////////////////////////////////
	// Tests concerning values to check.
	//////////////////////////////////////

	public function testCheckForNonEmptyStringWithNonEmptyString() {
		$this->fixture->checkForNonEmptyString('nonEmptyString', FALSE, '', '');

		$this->assertSame(
			'',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckForNonEmptyStringWithEmptyString() {
		$this->fixture->checkForNonEmptyString('emptyString', FALSE, '', '');

		$this->assertContains(
			'emptyString',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckIfSingleInTableNotEmptyForValueNotInTableComplains() {
		$this->fixture->checkIfSingleInTableNotEmpty(
			'inexistentColumn', FALSE, '', '', 'tx_oelib_test'
		);

		$this->assertContains(
			'inexistentColumn',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckIfSingleInTableNotEmptyForValueNotInTableNotComplains() {
		$this->fixture->checkIfSingleInTableNotEmpty(
			'existingColumn', FALSE, '', '', 'tx_oelib_test'
		);

		$this->assertSame(
			'',
			$this->fixture->getRawMessage()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning the e-mail address check.
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function checkIsValidEmailOrEmptyWithEmptyString() {
		$this->fixture->checkIsValidEmailOrEmpty('emptyString', FALSE, '', FALSE, '');

		$this->assertSame(
			'',
			$this->fixture->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailOrEmptyWithValidEmail() {
		$this->fixture->checkIsValidEmailOrEmpty('validEmail', FALSE, '', FALSE, '');

		$this->assertSame(
			'',
			$this->fixture->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailOrEmptyWithInvalidEmail() {
		$this->fixture->checkIsValidEmailOrEmpty('nonEmptyString', FALSE, '', FALSE, '');

		$this->assertContains(
			'nonEmptyString',
			$this->fixture->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailNotEmptyWithEmptyString() {
		$this->fixture->checkIsValidEmailNotEmpty('emptyString', FALSE, '', FALSE, '');

		$this->assertContains(
			'emptyString',
			$this->fixture->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailNotEmptyWithValidEmail() {
		$this->fixture->checkIsValidEmailNotEmpty('validEmail', FALSE, '', FALSE, '');

		$this->assertSame(
			'',
			$this->fixture->getRawMessage()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning the check of the locale.
	//////////////////////////////////////////////

	public function testGetInstalledLocalesReturnsAtLeastOneLocale() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->assertGreaterThan(
			0,
			count($this->fixture->getInstalledLocales()),
			'Tests concerning the locale will not proceed successfully because '
				.'there is no locale installed on this web server.'
		);
	}

	public function testCheckLocaleIfLocaleIsSetCorrectly() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$locales = $this->fixture->getInstalledLocales();
		$this->setConfigurationForLocale($locales[0]);

		$this->fixture->checkLocale();

		$this->assertSame(
			'',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckLocaleIfLocaleIsSetCorrectlyAndContainsAHyphen() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->setConfigurationForLocale(
			str_ireplace('f8', 'f-8', $this->getInstalledUtfLocale())
		);

		$this->fixture->checkLocale();

		$this->assertSame(
			'',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckLocaleIfLocaleIsSetCorrectlyAndContainsNoHyphen() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->setConfigurationForLocale(
			str_ireplace('f-8', 'f8', $this->getInstalledUtfLocale())
		);

		$this->fixture->checkLocale();

		$this->assertSame(
			'',
			$this->fixture->getRawMessage()
		);
	}


	public function testCheckLocaleIfLocaleIsNotSet() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->setConfigurationForLocale('');
		$this->fixture->checkLocale();

		$this->assertContains(
			'locale',
			$this->fixture->getRawMessage()
		);
		$this->assertContains(
			'not configured',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckLocaleIfLocaleIsSetToANonInstalledLocale() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->setConfigurationForLocale('xy_XY');
		$this->fixture->checkLocale();

		$this->assertContains(
			'locale',
			$this->fixture->getRawMessage()
		);
		$this->assertContains(
			'not installed',
			$this->fixture->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkLocaleDoesNotCheckLocalesOnWindows() {
		if (TYPO3_OS != 'WIN') {
			$this->markTestSkipped(
				'This test does not run properly on non Windows systems.'
			);
		}

		$configCheckMock = $this->getMock(
			'tx_oelib_configcheck',
			array('getInstalledLocales'),
			array($this->objectToCheck)
		);

		$configCheckMock->expects($this->never())->method('getInstalledLocales');

		$configCheckMock->checkLocale();
	}
}
?>