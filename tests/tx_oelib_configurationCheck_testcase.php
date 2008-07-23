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
 * Testcase for the configuration check class in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 */

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_configcheck.php');
require_once(t3lib_extMgm::extPath('oelib') . 'tests/fixtures/class.tx_oelib_dummyObjectToCheck.php');

class tx_oelib_configurationCheck_testcase extends tx_phpunit_testcase {
	/** configuration check object to be tested */
	private $fixture;

	/** dummy object to be checked by the configuration check object */
	private $objectToCheck;

	protected function setUp() {
		$this->objectToCheck = new tx_oelib_dummyObjectToCheck(
			array(
				'emptyString' => '',
				'nonEmptyString' => 'foo',
				'validEmail' => 'any-address@valid-email.org',
				'internalEmail' => 'user@servername'
			)
		);
		$this->fixture = new tx_oelib_configcheck($this->objectToCheck);
	}

	protected function tearDown() {
		unset($this->fixture, $this->objectToCheck);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Sets the configuration value for the locale to $localeKey.
	 *
	 * @param	string		key for the locale, to receive a non-configured
	 * 						locale, provide an empty string
	 */
	private function setConfigurationForLocale($localeKey) {
		$GLOBALS['TSFE']->config['config']['locale_all'] = $localeKey;
	}

	/**
	 * Returns a key of an installed locales which contains "utf".
	 *
	 * @return	string		installed locale with "utf" in the key (e.g.
	 * 						"en_US.utf8"), may be empty if none of the installed
	 * 						locales contains "utf"
	 */
	private function getInstalledUtfLocale() {
		$result = '';
		foreach ($this->fixture->getInstalledLocales() as $key) {
			if (stripos($key, 'utf') !== false) {
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

		$this->assertEquals(
			'foo',
			$GLOBALS['TSFE']->config['config']['locale_all']
		);
	}

	public function testSetConfigurationForLocaleToAnEmptyString() {
		$this->setConfigurationForLocale('');

		$this->assertEquals(
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

		$this->assertEquals(
			'foo',
			$this->fixture->getFlavor()
		);
	}


	//////////////////////////////////////
	// Tests concerning values to check.
	//////////////////////////////////////

	public function testCheckForNonEmptyStringWithNonEmptyString() {
		$this->fixture->checkForNonEmptyString('nonEmptyString', false, '', '');

		$this->assertEquals(
			'',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckForNonEmptyStringWithEmptyString() {
		$this->fixture->checkForNonEmptyString('emptyString', false, '', '');

		$this->assertContains(
			'emptyString',
			$this->fixture->getRawMessage()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning the e-mail address check.
	///////////////////////////////////////////////

	public function testCheckIsValidEmailOrEmptyWithAnEmptyString() {
		$this->fixture->checkIsValidEmailOrEmpty('emptyString', false, '', false, '');

		$this->assertEquals(
			'',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckIsValidEmailOrEmptyWithAValidEmail() {
		$this->fixture->checkIsValidEmailOrEmpty('validEmail', false, '', false, '');

		$this->assertEquals(
			'',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckIsValidEmailOrEmptyWithAnInvalidEmail() {
		$this->fixture->checkIsValidEmailOrEmpty('nonEmptyString', false, '', false, '');

		$this->assertContains(
			'nonEmptyString',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckIsValidEmailOrEmptyWithAnInternalEmailIfInternalEmailsAreNotAllowed() {
		$this->fixture->checkIsValidEmailOrEmpty('internalEmail', false, '', false, '');

		$this->assertContains(
			'internalEmail',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckIsValidEmailOrEmptyWithAnInternalEmailIfInternalEmailsAreAllowed() {
		$this->fixture->checkIsValidEmailOrEmpty('internalEmail', false, '', true, '');

		$this->assertEquals(
			'',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckIsValidEmailNotEmptyWithAnEmptyString() {
		$this->fixture->checkIsValidEmailNotEmpty('emptyString', false, '', false, '');

		$this->assertContains(
			'emptyString',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckIsValidEmailNotEmptyWithAValidEmail() {
		$this->fixture->checkIsValidEmailNotEmpty('validEmail', false, '', false, '');

		$this->assertEquals(
			'',
			$this->fixture->getRawMessage()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning the check of the locale.
	//////////////////////////////////////////////

	public function testGetInstalledLocalesReturnsAtLeastOneLocale() {
		$this->assertGreaterThan(
			0,
			count($this->fixture->getInstalledLocales()),
			'Tests concerning the locale will not proceed successfully because '
				.'there is no locale installed on this web server.'
		);
	}

	public function testCheckLocaleIfLocaleIsSetCorrectly() {
		$locales = $this->fixture->getInstalledLocales();
		$this->setConfigurationForLocale($locales[0]);

		$this->fixture->checkLocale();

		$this->assertEquals(
			'',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckLocaleIfLocaleIsSetCorrectlyAndContainsAHyphen() {
		$this->setConfigurationForLocale(
			str_ireplace('f8', 'f-8', $this->getInstalledUtfLocale())
		);

		$this->fixture->checkLocale();

		$this->assertEquals(
			'',
			$this->fixture->getRawMessage()
		);
	}

	public function testCheckLocaleIfLocaleIsSetCorrectlyAndContainsNoHyphen() {
		$this->setConfigurationForLocale(
			str_ireplace('f-8', 'f8', $this->getInstalledUtfLocale())
		);

		$this->fixture->checkLocale();

		$this->assertEquals(
			'',
			$this->fixture->getRawMessage()
		);
	}


	public function testCheckLocaleIfLocaleIsNotSet() {
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
}
?>