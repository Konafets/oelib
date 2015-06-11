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
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_ConfigurationRegistryTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsConfigurationRegistryInstance() {
		self::assertTrue(
			Tx_Oelib_ConfigurationRegistry::getInstance()
				instanceof Tx_Oelib_ConfigurationRegistry
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		self::assertSame(
			Tx_Oelib_ConfigurationRegistry::getInstance(),
			Tx_Oelib_ConfigurationRegistry::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = Tx_Oelib_ConfigurationRegistry::getInstance();
		Tx_Oelib_ConfigurationRegistry::purgeInstance();

		self::assertNotSame(
			$firstInstance,
			Tx_Oelib_ConfigurationRegistry::getInstance()
		);
	}


	////////////////////////////////
	// Test concerning get and set
	////////////////////////////////

	/**
	 * @test
	 */
	public function getForEmptyNamespaceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$namespace must not be empty.'
		);

		Tx_Oelib_ConfigurationRegistry::get('');
	}

	/**
	 * @test
	 */
	public function getForNonEmptyNamespaceReturnsConfigurationInstance() {
		Tx_Oelib_PageFinder::getInstance()->setPageUid(
			$this->testingFramework->createFrontEndPage()
		);

		self::assertTrue(
			Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				instanceof Tx_Oelib_Configuration
		);
	}

	/**
	 * @test
	 */
	public function getForTheSameNamespaceCalledTwoTimesReturnsTheSameInstance() {
		Tx_Oelib_PageFinder::getInstance()->setPageUid(
			$this->testingFramework->createFrontEndPage()
		);

		self::assertSame(
			Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib'),
			Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
		);
	}

	/**
	 * @test
	 */
	public function setWithEmptyNamespaceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$namespace must not be empty.'
		);

		Tx_Oelib_ConfigurationRegistry::getInstance()->set(
			'',  new Tx_Oelib_Configuration()
		);
	}

	/**
	 * @test
	 */
	public function getAfterSetReturnsTheSetInstance() {
		$configuration = new Tx_Oelib_Configuration();

		Tx_Oelib_ConfigurationRegistry::getInstance()
			->set('foo', $configuration);

		self::assertSame(
			$configuration,
			Tx_Oelib_ConfigurationRegistry::get('foo')
		);
	}

	/**
	 * @test
	 */
	public function setTwoTimesForTheSameNamespaceDoesNotFail() {
		Tx_Oelib_ConfigurationRegistry::getInstance()->set(
			'foo',  new Tx_Oelib_Configuration()
		);
		Tx_Oelib_ConfigurationRegistry::getInstance()->set(
			'foo',  new Tx_Oelib_Configuration()
		);
	}


	//////////////////////////////////////
	// Tests concerning TypoScript setup
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getReturnsDataFromTypoScriptSetupFromManuallySetPage() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createTemplate(
			$pageUid,
			array('config' => 'plugin.tx_oelib.test = 42')
		);

		Tx_Oelib_PageFinder::getInstance()->setPageUid($pageUid);

		self::assertSame(
			42,
			Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				->getAsInteger('test')
		);
	}

	/**
	 * @test
	 */
	public function getReturnsDataFromTypoScriptSetupFromBackEndPage() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createTemplate(
			$pageUid,
			array('config' => 'plugin.tx_oelib.test = 42')
		);
		$_POST['id'] = $pageUid;

		Tx_Oelib_PageFinder::getInstance()->forceSource(
			Tx_Oelib_PageFinder::SOURCE_BACK_END
		);

		self::assertSame(
			42,
			Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				->getAsInteger('test')
		);

		unset($_POST['id']);
	}

	/**
	 * @test
	 */
	public function getReturnsDataFromTypoScriptSetupFromFrontEndPage() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createTemplate(
			$pageUid,
			array('config' => 'plugin.tx_oelib.test = 42')
		);

		$this->testingFramework->createFakeFrontEnd($pageUid);
		Tx_Oelib_PageFinder::getInstance()->forceSource(
			Tx_Oelib_PageFinder::SOURCE_FRONT_END
		);

		self::assertSame(
			42,
			Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				->getAsInteger('test')
		);
	}

	/**
	 * @test
	 */
	public function readsDataFromTypoScriptSetupEvenForFrontEndWithoutLoadedTemplate() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createTemplate(
			$pageUid,
			array('config' => 'plugin.tx_oelib.test = 42')
		);

		$this->testingFramework->createFakeFrontEnd($pageUid);
		Tx_Oelib_PageFinder::getInstance()->forceSource(
			Tx_Oelib_PageFinder::SOURCE_FRONT_END
		);
		/** @var tslib_fe $frontEndController */
		$frontEndController = $GLOBALS['TSFE'];
		$frontEndController->tmpl->rootId = 0;
		$frontEndController->tmpl->rootLine = FALSE;
		$frontEndController->tmpl->setup = array();
		$frontEndController->tmpl->loaded = 0;

		self::assertSame(
			42,
			Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				->getAsInteger('test')
		);
	}

	/**
	 * @test
	 */
	public function getAfterSetReturnsManuallySetConfigurationEvenIfThereIsAPage() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createTemplate(
			$pageUid,
			array('config' => 'plugin.tx_oelib.bar = 42')
		);
		Tx_Oelib_PageFinder::getInstance()->setPageUid($pageUid);

		$configuration = new Tx_Oelib_Configuration();
		Tx_Oelib_ConfigurationRegistry::getInstance()
			->set('plugin.tx_oelib', $configuration);

		self::assertSame(
			$configuration,
			Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
		);
	}
}