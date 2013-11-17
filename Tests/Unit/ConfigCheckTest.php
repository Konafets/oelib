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
class Tx_Oelib_ConfigCheckTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_configcheck configuration check object to be tested
	 */
	private $subject;

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
		$this->subject = new tx_oelib_configcheck($this->objectToCheck);
	}

	protected function tearDown() {
		$this->subject->__destruct();
		$this->objectToCheck->__destruct();

		unset($this->subject, $this->objectToCheck);
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
		foreach ($this->subject->getInstalledLocales() as $key) {
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

	/**
	 * @test
	 */
	public function setConfigurationForLocaleToANonEmptyValue() {
		$this->setConfigurationForLocale('foo');

		$this->assertSame(
			'foo',
			$GLOBALS['TSFE']->config['config']['locale_all']
		);
	}

	/**
	 * @test
	 */
	public function setConfigurationForLocaleToAnEmptyString() {
		$this->setConfigurationForLocale('');

		$this->assertSame(
			'',
			$GLOBALS['TSFE']->config['config']['locale_all']
		);
	}

	/**
	 * @test
	 */
	public function getInstalledLocalesForInstalledUtf8LocaleReturnsUtf8Locale() {
		$locale = $this->getInstalledUtfLocale();

		$this->assertTrue(
			in_array($locale, $this->subject->getInstalledLocales())
		);
		$this->assertContains(
			'utf',
			strtolower($locale)
		);
	}


	/////////////////////////////////
	// Tests concerning the flavor.
	/////////////////////////////////

	/**
	 * @test
	 */
	public function setFlavorReturnsFlavor() {
		$this->subject->setFlavor('foo');

		$this->assertSame(
			'foo',
			$this->subject->getFlavor()
		);
	}


	//////////////////////////////////////
	// Tests concerning values to check.
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function checkForNonEmptyStringWithNonEmptyString() {
		$this->subject->checkForNonEmptyString('nonEmptyString', FALSE, '', '');

		$this->assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkForNonEmptyStringWithEmptyString() {
		$this->subject->checkForNonEmptyString('emptyString', FALSE, '', '');

		$this->assertContains(
			'emptyString',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIfSingleInTableNotEmptyForValueNotInTableComplains() {
		$this->subject->checkIfSingleInTableNotEmpty(
			'inexistentColumn', FALSE, '', '', 'tx_oelib_test'
		);

		$this->assertContains(
			'inexistentColumn',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIfSingleInTableNotEmptyForValueNotInTableNotComplains() {
		$this->subject->checkIfSingleInTableNotEmpty(
			'existingColumn', FALSE, '', '', 'tx_oelib_test'
		);

		$this->assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning the e-mail address check.
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function checkIsValidEmailOrEmptyWithEmptyString() {
		$this->subject->checkIsValidEmailOrEmpty('emptyString', FALSE, '', FALSE, '');

		$this->assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailOrEmptyWithValidEmail() {
		$this->subject->checkIsValidEmailOrEmpty('validEmail', FALSE, '', FALSE, '');

		$this->assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailOrEmptyWithInvalidEmail() {
		$this->subject->checkIsValidEmailOrEmpty('nonEmptyString', FALSE, '', FALSE, '');

		$this->assertContains(
			'nonEmptyString',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailNotEmptyWithEmptyString() {
		$this->subject->checkIsValidEmailNotEmpty('emptyString', FALSE, '', FALSE, '');

		$this->assertContains(
			'emptyString',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailNotEmptyWithValidEmail() {
		$this->subject->checkIsValidEmailNotEmpty('validEmail', FALSE, '', FALSE, '');

		$this->assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning the check of the locale.
	//////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstalledLocalesReturnsAtLeastOneLocale() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->assertGreaterThan(
			0,
			count($this->subject->getInstalledLocales()),
			'Tests concerning the locale will not proceed successfully because '
				.'there is no locale installed on this web server.'
		);
	}

	/**
	 * @test
	 */
	public function checkLocaleIfLocaleIsSetCorrectly() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$locales = $this->subject->getInstalledLocales();
		$this->setConfigurationForLocale($locales[0]);

		$this->subject->checkLocale();

		$this->assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkLocaleIfLocaleIsSetCorrectlyAndContainsAHyphen() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->setConfigurationForLocale(
			str_ireplace('f8', 'f-8', $this->getInstalledUtfLocale())
		);

		$this->subject->checkLocale();

		$this->assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkLocaleIfLocaleIsSetCorrectlyAndContainsNoHyphen() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->setConfigurationForLocale(
			str_ireplace('f-8', 'f8', $this->getInstalledUtfLocale())
		);

		$this->subject->checkLocale();

		$this->assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}


	/**
	 * @test
	 */
	public function checkLocaleIfLocaleIsNotSet() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->setConfigurationForLocale('');
		$this->subject->checkLocale();

		$this->assertContains(
			'locale',
			$this->subject->getRawMessage()
		);
		$this->assertContains(
			'not configured',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkLocaleIfLocaleIsSetToANonInstalledLocale() {
		if (TYPO3_OS == 'WIN') {
			$this->markTestSkipped('This test does not run properly on Windows.');
		}

		$this->setConfigurationForLocale('xy_XY');
		$this->subject->checkLocale();

		$this->assertContains(
			'locale',
			$this->subject->getRawMessage()
		);
		$this->assertContains(
			'not installed',
			$this->subject->getRawMessage()
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