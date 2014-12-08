<?php
/**
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
 * @subpackage oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Benjamin Schulte <benj@minschulte.de>
 */
class Tx_Oelib_TranslatorRegistryTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var t3lib_beUserAuth
	 */
	protected $backEndUserBackup = NULL;

	public function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		$this->backEndUserBackup = $GLOBALS['BE_USER'];
		$backEndUser = new t3lib_beUserAuth();
		$backEndUser->user = array('uid' => $this->testingFramework->createBackEndUser());
		$GLOBALS['BE_USER'] = $backEndUser;

		$configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
		$configurationRegistry->set('config', new Tx_Oelib_Configuration());
		$configurationRegistry->set('page.config', new Tx_Oelib_Configuration());
		$configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG', new Tx_Oelib_Configuration());
		$configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG.default', new Tx_Oelib_Configuration());
		$configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG.de', new Tx_Oelib_Configuration());
		$configurationRegistry->set('plugin.tx_oelib._LOCAL_LANG.fr', new Tx_Oelib_Configuration());
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$GLOBALS['BE_USER'] = $this->backEndUserBackup;
	}


	////////////////////////////////////////////
	// Tests regarding the Singleton property.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsTranslatorRegistryInstance() {
		$this->assertTrue(
			Tx_Oelib_TranslatorRegistry::getInstance() instanceof
				Tx_Oelib_TranslatorRegistry
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			Tx_Oelib_TranslatorRegistry::getInstance(),
			Tx_Oelib_TranslatorRegistry::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = Tx_Oelib_TranslatorRegistry::getInstance();
		Tx_Oelib_TranslatorRegistry::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			Tx_Oelib_TranslatorRegistry::getInstance()
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
			'InvalidArgumentException',
			'The parameter $extensionName must not be empty.'
		);

		Tx_Oelib_TranslatorRegistry::get('');
	}

	/**
	 * @test
	 */
	public function getWithNotLoadedExtensionNameThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The extension with the name "user_oelib_test_does_not_exist" is not loaded.'
		);

		Tx_Oelib_TranslatorRegistry::get('user_oelib_test_does_not_exist');
	}

	/**
	 * @test
	 */
	public function getWithLoadedExtensionNameReturnsTranslatorInstance() {
		$this->assertTrue(
			Tx_Oelib_TranslatorRegistry::get('oelib') instanceof
				Tx_Oelib_Translator
		);
	}

	/**
	 * @test
	 */
	public function getTwoTimesWithSameExtensionNameReturnsSameInstance() {
		$this->assertSame(
			Tx_Oelib_TranslatorRegistry::get('oelib'),
			Tx_Oelib_TranslatorRegistry::get('oelib')
		);
	}


	/////////////////////////////////////////
	// Tests regarding initializeBackEnd().
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function initializeBackEndWithBackEndUserLanguageEnglishSetsLanguageEnglish() {
		$backEndUser = new Tx_Oelib_Model_BackEndUser();
		$backEndUser->setDefaultLanguage('default');
		Tx_Oelib_BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

		$this->assertSame(
			'default',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
	}

	/**
	 * @test
	 */
	public function initializeBackEndWithBackEndUserLanguageGermanSetsLanguageGerman() {
		$backEndUser = new Tx_Oelib_Model_BackEndUser();
		$backEndUser->setDefaultLanguage('de');
		Tx_Oelib_BackEndLoginManager::getInstance()->setLoggedInUser($backEndUser);

		$this->assertSame(
			'de',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
	}

	/**
	 * @test
	 */
	public function initializeBackEndDoesNotSetAlternativeLanguage() {
		$this->assertSame(
			'',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
		);
	}


	//////////////////////////////////////////
	// Tests regarding initializeFrontEnd().
	//////////////////////////////////////////

	/**
	 * A data provider used for the front end configuration namespaces
	 *
	 * @return array[]
	 */
	public function frontEndConfigurationDataProvider() {
		return array(
			'config' => array('config'),
			'page.config' => array('page.config'),
		);
	}

	/**
	 * @test
	 *
	 * @param string $namespace the configuration namespace
	 *
	 * @dataProvider frontEndConfigurationDataProvider
	 */
	public function initializeFrontEndWithoutFrontEndLanguageSetsLanguageDefault($namespace) {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(array());

		$this->assertSame(
			'default',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 *
	 * @param string $namespace the configuration namespace
	 *
	 * @dataProvider frontEndConfigurationDataProvider
	 */
	public function initializeFrontEndWithFrontEndLanguageEnglishSetsLanguageEnglish($namespace) {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(array('language' => 'default'));

		$this->assertSame(
			'default',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 *
	 * @param string $namespace the configuration namespace
	 *
	 * @dataProvider frontEndConfigurationDataProvider
	 */
	public function initializeFrontEndWithFrontEndLanguageGermanSetsLanguageGerman($namespace) {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(array('language' => 'de'));

		$this->assertSame(
			'de',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 *
	 * @param string $namespace the configuration namespace
	 *
	 * @dataProvider frontEndConfigurationDataProvider
	 */
	public function initializeFrontEndWithoutAlternativeFrontEndLanguageDoesNotSetAlternativeLanguage($namespace) {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(array());

		$this->assertSame(
			'',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 *
	 * @param string $namespace the configuration namespace
	 *
	 * @dataProvider frontEndConfigurationDataProvider
	 */
	public function initializeFrontEndWithAlternativeFrontEndLanguageEnglishSetsAlternativeLanguageEnglish($namespace) {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(array('language' => 'de', 'language_alt' => 'default'));

		$this->assertSame(
			'default',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 *
	 * @param string $namespace the configuration namespace
	 *
	 * @dataProvider frontEndConfigurationDataProvider
	 */
	public function initializeFrontEndWithAlternativeFrontEndLanguageGermanSetsAlternativeLanguageGerman($namespace) {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		Tx_Oelib_ConfigurationRegistry::get($namespace)->setData(array('language' => 'default', 'language_alt' => 'de'));

		$this->assertSame(
			'de',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function initializeFrontEndWithLanguageSetInConfigAndInPageConfigSetsLanguageFromPageConfig() {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		Tx_Oelib_ConfigurationRegistry::get('config')->setData(array('language' => 'de'));
		Tx_Oelib_ConfigurationRegistry::get('page.config')->setData(array('language' => 'fr'));

		$this->assertSame(
			'fr',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getLanguageKey()
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function initializeFrontEndWithAlternativeLanguageSetInConfigAndInPageConfigSetsAlternativeLanguageFromPageConfig() {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		Tx_Oelib_ConfigurationRegistry::get('config')->setData(array('language' => 'de', 'language_alt' => 'cz'));
		Tx_Oelib_ConfigurationRegistry::get('page.config')->setData(array('language' => 'fr', 'language_alt' => 'ja'));

		$this->assertSame(
			'ja',
			Tx_Oelib_TranslatorRegistry::get('oelib')->getAlternativeLanguageKey()
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
		$this->assertSame(
			'I am from file.',
			Tx_Oelib_TranslatorRegistry::get('oelib')->translate('label_test')
		);
	}

	/**
	 * @test
	 */
	public function getByExtensionNameInFrontEndOverridesLabelsFromFileWithLabelsFromTypoScript() {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		$GLOBALS['TSFE']->initLLvars();
		Tx_Oelib_ConfigurationRegistry::get('config')->set('language', 'default');
		Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->
			setData(array('default.' => array()));
		Tx_Oelib_ConfigurationRegistry::
			get('plugin.tx_oelib._LOCAL_LANG.default')->
				set('label_test', 'I am from TypoScript.');

		$this->assertSame(
			'I am from TypoScript.',
			Tx_Oelib_TranslatorRegistry::get('oelib')->translate('label_test')
		);
		$testingFramework->discardFakeFrontEnd();
	}

	/**
	 * @test
	 */
	public function getByExtensionNameInBackEndNotOverridesLabelsFromFileWithLabelsFromTypoScript() {
		Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->setData(array('default.' => array()));
		Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG.default')->set('label_test', 'I am from TypoScript.');

		$this->assertSame(
			'I am from file.',
			Tx_Oelib_TranslatorRegistry::get('oelib')->translate('label_test')
		);
	}

	/**
	 * @test
	 */
	public function getByExtensionNameDoesNotDeleteLanguageLabelsNotAffectedByTypoScript() {
		$testingFramework = new Tx_Oelib_TestingFramework('oelib');
		$testingFramework->createFakeFrontEnd();
		$GLOBALS['TSFE']->initLLvars();
		Tx_Oelib_ConfigurationRegistry::get('config')->set('language', 'default');
		Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG')->setData(array('default.' => array()));
		Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib._LOCAL_LANG.default')->set('label_test_2', 'I am from TypoScript.');

		$this->assertSame(
			'I am from file.',
			Tx_Oelib_TranslatorRegistry::get('oelib')->translate('label_test')
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
		Tx_Oelib_TranslatorRegistry::getInstance()->setLanguageKey('de');

		$this->assertSame(
			'de',
			Tx_Oelib_TranslatorRegistry::getInstance()->getLanguageKey('de')
		);
	}

	/**
	 * @test
	 */
	public function setLanguageKeyForEmptyStringGivenThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given language key must not be empty.'
		);

		Tx_Oelib_TranslatorRegistry::getInstance()->setLanguageKey('');
	}
}