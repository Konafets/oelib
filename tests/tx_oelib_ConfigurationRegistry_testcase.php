<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_ConfigurationRegistry class in the 'oelib'
 * extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_ConfigurationRegistry_testcase extends tx_phpunit_testcase {
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

	public function testGetInstanceReturnsConfigurationRegistryInstance() {
		$this->assertTrue(
			tx_oelib_ConfigurationRegistry::getInstance()
				instanceof tx_oelib_ConfigurationRegistry
		);
	}

	public function testGetInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_ConfigurationRegistry::getInstance(),
			tx_oelib_ConfigurationRegistry::getInstance()
		);
	}

	public function testGetInstanceAfterPurgeInstanceReturnsNewInstance() {
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

	public function testGetForEmptyNamespaceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$namespace must not be empty.'
		);

		tx_oelib_ConfigurationRegistry::get('');
	}

	public function testGetForNonEmptyNamespaceReturnsConfigurationInstance() {
		tx_oelib_PageFinder::getInstance()->setPageUid(
			$this->testingFramework->createFrontEndPage()
		);

		$this->assertTrue(
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				instanceof tx_oelib_Configuration
		);
	}

	public function testGetForTheSameNamespaceCalledTwoTimesReturnsTheSameInstance() {
		tx_oelib_PageFinder::getInstance()->setPageUid(
			$this->testingFramework->createFrontEndPage()
		);

		$this->assertSame(
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib'),
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
		);
	}

	public function testSetWithEmptyNamespaceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$namespace must not be empty.'
		);

		tx_oelib_ConfigurationRegistry::getInstance()->set(
			'',  new tx_oelib_Configuration()
		);
	}

	public function testGetAfterSetReturnsTheSetInstance() {
		$configuration = new tx_oelib_Configuration();

		tx_oelib_ConfigurationRegistry::getInstance()
			->set('foo', $configuration);

		$this->assertSame(
			$configuration,
			tx_oelib_ConfigurationRegistry::get('foo')
		);
	}

	public function testSetTwoTimesForTheSameNamespaceDoesNotFail() {
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

	public function testGetReturnsDataFromTypoScriptSetupFromManuallySetPage() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createTemplate(
			$pageUid,
			array('config' => 'plugin.tx_oelib.test = 42')
		);

		tx_oelib_PageFinder::getInstance()->setPageUid($pageUid);

		$this->assertEquals(
			42,
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				->getAsInteger('test')
		);
	}

	public function testGetReturnsDataFromTypoScriptSetupFromBackEndPage() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createTemplate(
			$pageUid,
			array('config' => 'plugin.tx_oelib.test = 42')
		);
		$_POST['id'] = $pageUid;

		tx_oelib_PageFinder::getInstance()->forceSource(
			tx_oelib_PageFinder::SOURCE_BACK_END
		);

		$this->assertEquals(
			42,
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				->getAsInteger('test')
		);

		unset($_POST['id']);
	}

	public function testGetReturnsDataFromTypoScriptSetupFromFrontEndPage() {
		$pageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createTemplate(
			$pageUid,
			array('config' => 'plugin.tx_oelib.test = 42')
		);

		$this->testingFramework->createFakeFrontEnd($pageUid);
		tx_oelib_PageFinder::getInstance()->forceSource(
			tx_oelib_PageFinder::SOURCE_FRONT_END
		);

		$this->assertEquals(
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

		$this->assertEquals(
			42,
			tx_oelib_ConfigurationRegistry::get('plugin.tx_oelib')
				->getAsInteger('test')
		);
	}

	public function testGetAfterSetReturnsManuallySetConfigurationEvenIfThereIsAPage() {
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