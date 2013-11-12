<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Niels Pardon (mail@niels-pardon.de)
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
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_TemplateRegistryTest extends tx_phpunit_testcase {
	public function setUp() {
	}

	public function tearDown() {
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsTemplateRegistryInstance() {
		$this->assertTrue(
			tx_oelib_TemplateRegistry::getInstance()
				instanceof tx_oelib_TemplateRegistry
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_TemplateRegistry::getInstance(),
			tx_oelib_TemplateRegistry::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = tx_oelib_TemplateRegistry::getInstance();
		tx_oelib_TemplateRegistry::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			tx_oelib_TemplateRegistry::getInstance()
		);
	}


	///////////////////////////
	// Tests concerning get()
	///////////////////////////

	/**
	 * @test
	 */
	public function getForEmptyTemplateFileNameReturnsTemplateInstance() {
		$this->assertTrue(
			tx_oelib_TemplateRegistry::get('') instanceof tx_oelib_template
		);
	}

	/**
	 * @test
	 */
	public function getForEmptyTemplateFileNameCalledTwoTimesReturnsNewInstance() {
		$this->assertNotSame(
			tx_oelib_TemplateRegistry::get(''),
			tx_oelib_TemplateRegistry::get('')
		);
	}

	/**
	 * @test
	 */
	public function getForExistingTemplateFileNameReturnsTemplate() {
		$this->assertTrue(
			tx_oelib_TemplateRegistry::get('EXT:oelib/tests/fixtures/oelib.html')
				instanceof tx_oelib_template
		);
	}

	/**
	 * @test
	 */
	public function getForExistingTemplateFileNameCalledTwoTimesReturnsNewInstance() {
		$this->assertNotSame(
			tx_oelib_TemplateRegistry::get('EXT:oelib/tests/fixtures/oelib.html'),
			tx_oelib_TemplateRegistry::get('EXT:oelib/tests/fixtures/oelib.html')
		);
	}

	/**
	 * @test
	 */
	public function getForExistingTemplateFileNameReturnsProcessedTemplate() {
		$template = tx_oelib_TemplateRegistry::get(
			'EXT:oelib/tests/fixtures/oelib.html'
		);

		$this->assertSame(
			'Hello world!' . LF,
			$template->getSubpart()
		);
	}
}
?>