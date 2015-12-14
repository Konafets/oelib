<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_Tests_Unit_ConfigCheckTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_ConfigCheck configuration check object to be tested
	 */
	private $subject = NULL;

	/**
	 * @var Tx_Oelib_Tests_Unit_Fixtures_DummyObjectToCheck dummy object to be checked by the configuration check object
	 */
	private $objectToCheck = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework = NULL;

	/**
	 * @var bool
	 */
	protected $deprecationLogEnabledBackup = FALSE;

	protected function setUp() {
		$this->deprecationLogEnabledBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];

		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');
		$this->testingFramework->createFakeFrontEnd();

		$this->objectToCheck = new Tx_Oelib_Tests_Unit_Fixtures_DummyObjectToCheck(
			array(
				'emptyString' => '',
				'nonEmptyString' => 'foo',
				'validEmail' => 'any-address@valid-email.org',
				'existingColumn' => 'title',
				'inexistentColumn' => 'does_not_exist',
			)
		);
		$this->subject = new Tx_Oelib_ConfigCheck($this->objectToCheck);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = $this->deprecationLogEnabledBackup;
	}

	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Returns the current front-end instance.
	 *
	 * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	private function getFrontEndController() {
		return $GLOBALS['TSFE'];
	}

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
		$this->getFrontEndController()->config['config']['locale_all'] = $localeKey;
	}

	/**
	 * Returns a key of an installed locales which contains "utf".
	 *
	 * @return string installed locale with "utf" in the key (e.g.
	 *                "en_US.utf8"), may be empty if none of the installed
	 *                locales contains "utf"
	 */
	private function getInstalledUtfLocale() {
		if (TYPO3_OS === 'WIN') {
			self::markTestSkipped('This test does not run properly on Windows.');
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

		self::assertSame(
			'foo',
			$this->getFrontEndController()->config['config']['locale_all']
		);
	}

	/**
	 * @test
	 */
	public function setConfigurationForLocaleToAnEmptyString() {
		$this->setConfigurationForLocale('');

		self::assertSame(
			'',
			$this->getFrontEndController()->config['config']['locale_all']
		);
	}

	/**
	 * @test
	 */
	public function getInstalledLocalesForInstalledUtf8LocaleReturnsUtf8Locale() {
		$locale = $this->getInstalledUtfLocale();

		self::assertTrue(
			in_array($locale, $this->subject->getInstalledLocales(), TRUE)
		);
		self::assertContains(
			'utf',
			strtolower($locale)
		);
	}

	/*
	 * Tests concerning the basics
	 */

	/**
	 * @test
	 */
	public function objectToCheckIsCheckable() {
		self::assertInstanceOf(
			'Tx_Oelib_Interface_ConfigurationCheckable',
			$this->objectToCheck
		);
	}

	/**
	 * @test
	 */
	public function checkContainsNamespaceInErrorMessage() {
		$this->subject->checkForNonEmptyString('', FALSE, '', '');

		self::assertContains(
			'plugin.tx_oelib_test.',
			$this->subject->getRawMessage()
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

		self::assertSame(
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

		self::assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkForNonEmptyStringWithEmptyString() {
		$this->subject->checkForNonEmptyString('emptyString', FALSE, '', '');

		self::assertContains(
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

		self::assertContains(
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

		self::assertSame(
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

		self::assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailOrEmptyWithValidEmail() {
		$this->subject->checkIsValidEmailOrEmpty('validEmail', FALSE, '', FALSE, '');

		self::assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailOrEmptyWithInvalidEmail() {
		$this->subject->checkIsValidEmailOrEmpty('nonEmptyString', FALSE, '', FALSE, '');

		self::assertContains(
			'nonEmptyString',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailNotEmptyWithEmptyString() {
		$this->subject->checkIsValidEmailNotEmpty('emptyString', FALSE, '', FALSE, '');

		self::assertContains(
			'emptyString',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkIsValidEmailNotEmptyWithValidEmail() {
		$this->subject->checkIsValidEmailNotEmpty('validEmail', FALSE, '', FALSE, '');

		self::assertSame(
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
		if (TYPO3_OS === 'WIN') {
			self::markTestSkipped('This test does not run properly on Windows.');
		}

		self::assertGreaterThan(
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
		if (TYPO3_OS === 'WIN') {
			self::markTestSkipped('This test does not run properly on Windows.');
		}

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$locales = $this->subject->getInstalledLocales();
		$this->setConfigurationForLocale($locales[0]);

		$this->subject->checkLocale();

		self::assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkLocaleIfLocaleIsSetCorrectlyAndContainsAHyphen() {
		if (TYPO3_OS === 'WIN') {
			self::markTestSkipped('This test does not run properly on Windows.');
		}

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->setConfigurationForLocale(
			str_ireplace('f8', 'f-8', $this->getInstalledUtfLocale())
		);

		$this->subject->checkLocale();

		self::assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkLocaleIfLocaleIsSetCorrectlyAndContainsNoHyphen() {
		if (TYPO3_OS === 'WIN') {
			self::markTestSkipped('This test does not run properly on Windows.');
		}

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->setConfigurationForLocale(
			str_ireplace('f-8', 'f8', $this->getInstalledUtfLocale())
		);

		$this->subject->checkLocale();

		self::assertSame(
			'',
			$this->subject->getRawMessage()
		);
	}


	/**
	 * @test
	 */
	public function checkLocaleIfLocaleIsNotSet() {
		if (TYPO3_OS === 'WIN') {
			self::markTestSkipped('This test does not run properly on Windows.');
		}

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->setConfigurationForLocale('');
		$this->subject->checkLocale();

		self::assertContains(
			'locale',
			$this->subject->getRawMessage()
		);
		self::assertContains(
			'not configured',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkLocaleIfLocaleIsSetToANonInstalledLocale() {
		if (TYPO3_OS === 'WIN') {
			self::markTestSkipped('This test does not run properly on Windows.');
		}

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->setConfigurationForLocale('xy_XY');
		$this->subject->checkLocale();

		self::assertContains(
			'locale',
			$this->subject->getRawMessage()
		);
		self::assertContains(
			'not installed',
			$this->subject->getRawMessage()
		);
	}

	/**
	 * @test
	 */
	public function checkLocaleDoesNotCheckLocalesOnWindows() {
		if (TYPO3_OS !== 'WIN') {
			self::markTestSkipped(
				'This test does not run properly on non Windows systems.'
			);
		}

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		/** @var Tx_Oelib_ConfigCheck|PHPUnit_Framework_MockObject_MockObject $configCheckMock */
		$configCheckMock = $this->getMock(
			'Tx_Oelib_ConfigCheck',
			array('getInstalledLocales'),
			array($this->objectToCheck)
		);

		$configCheckMock->expects(self::never())->method('getInstalledLocales');

		$configCheckMock->checkLocale();
	}
}