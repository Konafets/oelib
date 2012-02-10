<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_oelib_TranslatorRegistry class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage oelib
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_TranslatorRegistryTest extends tx_phpunit_testcase {
	public function setUp() {
		$configurationRegistry = tx_oelib_ConfigurationRegistry::getInstance();
		$configurationRegistry->set('config', new tx_oelib_Configuration());
		$configurationRegistry->set(
			'plugin.tx_oelib._LOCAL_LANG', new tx_oelib_Configuration()
		);
		$configurationRegistry->set(
			'plugin.tx_oelib._LOCAL_LANG.default', new tx_oelib_Configuration()
		);
		$configurationRegistry->set(
			'plugin.tx_oelib._LOCAL_LANG.de', new tx_oelib_Configuration()
		);
	}

	public function tearDown() {
		tx_oelib_TranslatorRegistry::purgeInstance();
		tx_oelib_ConfigurationRegistry::purgeInstance();
	}


	////////////////////////////////////////////
	// Tests regarding the Singleton property.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsTranslatorRegistryInstance() {
		$this->assertTrue(
			tx_oelib_TranslatorRegistry::getInstance() instanceof
				tx_oelib_TranslatorRegistry
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_TranslatorRegistry::getInstance(),
			tx_oelib_TranslatorRegistry::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = tx_oelib_TranslatorRegistry::getInstance();
		tx_oelib_TranslatorRegistry::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			tx_oelib_TranslatorRegistry::getInstance()
		);
	}


	///////////////////////////
	// Tests regarding get().
	///////////////////////////

	/**
	 * @test
	 */
	public function getWithEmptyExtensionNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The parameter $extensionName must not be empty.'
		);

		tx_oelib_TranslatorRegistry::get('');
	}

	/**
	 * @test
	 */
	public function getWithNotLoadedExtensionNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The extension with the name "user_oelib_test_does_not_exist" ' .
				'is not loaded.'
		);

		tx_oelib_TranslatorRegistry::get('user_oelib_test_does_not_exist');
	}

	/**
	 * @test
	 */
	public function getWithLoadedExtensionNameReturnsTranslatorInstance() {
		$this->assertTrue(
			tx_oelib_TranslatorRegistry::get('oelib') instanceof
				tx_oelib_Translator
		);
	}

	/**
	 * @test
	 */
	public function getTwoTimesWithSameExtensionNameReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_TranslatorRegistry::get('oelib'),
			tx_oelib_TranslatorRegistry::get('oelib')
		);
	}


	/////////////////////////////////////////
	// Tests regarding initializeBackEnd().
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function initializeBackEndWithBackEndUserLanguageEnglishSetsLanguageEnglish() {
		$backEndUser = new tx_oelib_Model_BackEndUser();
		$backEndUser->setDefaultLanguage('default');
		tx_oelib_BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

		$this->assertEquals(
			'default',
			tx_oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
	}

	/**
	 * @test
	 */
	public function initializeBackEndWithBackEndUserLanguageGermanSetsLanguageGerman() {
		$backEndUser = new tx_oelib_Model_BackEndUser();
		$backEndUser->setDefaultLanguage('de');
		tx_oelib_BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

		$this->assertEquals(
			'de',
			tx_oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
	}

	/**
	 * @test
	 */
	public function initializeBackEndDoesNotSetAlternativeLanguage() {
		$this->assertEquals(
			'',
			tx_oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
		);
	}


	//////////////////////////////////////////
	// Tests regarding initializeFrontEnd().
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function initializeFrontEndWithoutFrontEndLanguageSetsLanguageDefault() {
		$testingFramework = new tx_oelib_testingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		tx_oelib_ConfigurationRegistry::get('config')->setData(array());

		$this->assertEquals(
			'default',
			tx_oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function initializeFrontEndWithFrontEndLanguageEnglishSetsLanguageEnglish() {
		$testingFramework = new tx_oelib_testingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		tx_oelib_ConfigurationRegistry::get('config')->setData(
			array('language' => 'default')
		);

		$this->assertEquals(
			'default',
			tx_oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function initializeFrontEndWithFrontEndLanguageGermanSetsLanguageGerman() {
		$testingFramework = new tx_oelib_testingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		tx_oelib_ConfigurationRegistry::get('config')->setData(
			array('language' => 'de')
		);

		$this->assertEquals(
			'de',
			tx_oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function initializeFrontEndWithoutAlternativeFrontEndLanguageDoesNotSetAlternativeLanguage() {
		$testingFramework = new tx_oelib_testingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		tx_oelib_ConfigurationRegistry::get('config')->setData(array());

		$this->assertEquals(
			'',
			tx_oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function initializeFrontEndWithAlternativeFrontEndLanguageEnglishSetsAlternativeLanguageEnglish() {
		$testingFramework = new tx_oelib_testingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		tx_oelib_ConfigurationRegistry::get('config')->setData(
			array('language' => 'de', 'language_alt' => 'default')
		);

		$this->assertEquals(
			'default',
			tx_oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function initializeFrontEndWithAlternativeFrontEndLanguageGermanSetsAlternativeLanguageGerman() {
		$testingFramework = new tx_oelib_testingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		tx_oelib_ConfigurationRegistry::get('config')->setData(
			array('language' => 'default', 'language_alt' => 'de')
		);

		$this->assertEquals(
			'de',
			tx_oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}


	//////////////////////////////////////////
	// Tests regarding getByExtensionName().
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function getByExtensionNameLoadsLabelsFromFile() {
		$this->assertEquals(
			'I am from file.',
			tx_oelib_TranslatorRegistry::get('oelib')->translate('label_test')
		);
	}

	/**
	 * @test
	 */
	public function getByExtensionNameInFrontEndOverridesLabelsFromFileWithLabelsFromTypoScript() {
		$testingFramework = new tx_oelib_testingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		$GLOBALS['TSFE']->initLLvars();
		tx_oelib_ConfigurationRegistry::get('config')->set('language', 'default');
		tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->
			setData(array('default.' => array()));
		tx_oelib_ConfigurationRegistry::
			get('plugin.tx_oelib._LOCAL_LANG.default')->
				set('label_test', 'I am from TypoScript.');

		$this->assertEquals(
			'I am from TypoScript.',
			tx_oelib_TranslatorRegistry::get('oelib')->translate('label_test')
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function getByExtensionNameInBackEndNotOverridesLabelsFromFileWithLabelsFromTypoScript() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->
			setData(array('default.' => array()));
		tx_oelib_ConfigurationRegistry::
			get('plugin.tx_oelib._LOCAL_LANG.default')->
				set('label_test', 'I am from TypoScript.');

		$this->assertEquals(
			'I am from file.',
			tx_oelib_TranslatorRegistry::get('oelib')->translate('label_test')
		);
	}

	public function test_getByExtensionNameDoesNotDeleteLanguageLabelsNotAffectedByTyposcript() {
		$testingFramework = new tx_oelib_testingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		$GLOBALS['TSFE']->initLLvars();
		tx_oelib_ConfigurationRegistry::get('config')->set('language', 'default');
		tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->
			setData(array('default.' => array()));
		tx_oelib_ConfigurationRegistry::
			get('plugin.tx_oelib._LOCAL_LANG.default')->
				set('label_test_2', 'I am from TypoScript.');

		$this->assertEquals(
			'I am from file.',
			tx_oelib_TranslatorRegistry::get('oelib')->translate('label_test')
		);
		$testingFramework->discardFakeFrontEnd();
	}


	/////////////////////////////////////
	// Tests concerning the languageKey
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getLanguageKeyForSetKeyReturnsSetKey() {
		tx_oelib_TranslatorRegistry::getInstance()->setLanguageKey('de');

		$this->assertEquals(
			'de',
			tx_oelib_TranslatorRegistry::getInstance()->getLanguageKey('de')
		);
	}

	/**
	 * @test
	 */
	public function setLanguageKeyForEmptyStringGivenThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The given language key must not be empty.'
		);

		tx_oelib_TranslatorRegistry::getInstance()->setLanguageKey('');
	}
}
?>