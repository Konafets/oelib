<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Oliver Klee (typo3-coding@oliverklee.de)
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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_ConfigurationRegistryTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->testingFramework);
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsConfigurationRegistryInstance() {
		$this->assertTrue(
			tx_oelib_ConfigurationRegistry::getInstance()
				instanceof tx_oelib_ConfigurationRegistry
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_ConfigurationRegistry::getInstance(),
			tx_oelib_ConfigurationRegistry::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = tx_oelib_ConfigurationRegistry::getInstance();
		tx_oelib_ConfigurationRegistry::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			tx_oelib_ConfigurationRegistry::getInstance()
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

		tx_oelib_ConfigurationRegistry::get('');
	}

	/**
	 * @test
	 */
	public function getForNonEmptyNamespaceReturnsConfigurationInstance() {
		tx_oelib_PageFinder::getInstance()->setPageUid(
			$this->testingFramework->createFrontEndPage()
		);

		$this->assertTrue(
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				instanceof tx_oelib_Configuration
		);
	}

	/**
	 * @test
	 */
	public function getForTheSameNamespaceCalledTwoTimesReturnsTheSameInstance() {
		tx_oelib_PageFinder::getInstance()->setPageUid(
			$this->testingFramework->createFrontEndPage()
		);

		$this->assertSame(
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib'),
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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

		tx_oelib_ConfigurationRegistry::getInstance()->set(
			'',  new tx_oelib_Configuration()
		);
	}

	/**
	 * @test
	 */
	public function getAfterSetReturnsTheSetInstance() {
		$configuration = new tx_oelib_Configuration();

		tx_oelib_ConfigurationRegistry::getInstance()
			->set('foo', $configuration);

		$this->assertSame(
			$configuration,
			tx_oelib_ConfigurationRegistry::get('foo')
		);
	}

	/**
	 * @test
	 */
	public function setTwoTimesForTheSameNamespaceDoesNotFail() {
		tx_oelib_ConfigurationRegistry::getInstance()->set(
			'foo',  new tx_oelib_Configuration()
		);
		tx_oelib_ConfigurationRegistry::getInstance()->set(
			'foo',  new tx_oelib_Configuration()
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

		tx_oelib_PageFinder::getInstance()->setPageUid($pageUid);

		$this->assertSame(
			42,
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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

		tx_oelib_PageFinder::getInstance()->forceSource(
			tx_oelib_PageFinder::SOURCE_BACK_END
		);

		$this->assertSame(
			42,
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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
		tx_oelib_PageFinder::getInstance()->forceSource(
			tx_oelib_PageFinder::SOURCE_FRONT_END
		);

		$this->assertSame(
			42,
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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
		tx_oelib_PageFinder::getInstance()->forceSource(
			tx_oelib_PageFinder::SOURCE_FRONT_END
		);
		$GLOBALS['TSFE']->tmpl->rootId = 0;
		$GLOBALS['TSFE']->tmpl->rootLine = FALSE;
		$GLOBALS['TSFE']->tmpl->setup = array();
		$GLOBALS['TSFE']->tmpl->loaded = 0;

		$this->assertSame(
			42,
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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
		tx_oelib_PageFinder::getInstance()->setPageUid($pageUid);

		$configuration = new tx_oelib_Configuration();
		tx_oelib_ConfigurationRegistry::getInstance()
			->set('plugin.tx_oelib', $configuration);

		$this->assertSame(
			$configuration,
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
		);
	}
}
?>