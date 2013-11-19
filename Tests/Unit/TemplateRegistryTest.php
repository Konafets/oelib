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
class Tx_Oelib_TemplateRegistryTest extends Tx_Phpunit_TestCase {
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
			Tx_Oelib_TemplateRegistry::getInstance()
				instanceof Tx_Oelib_TemplateRegistry
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			Tx_Oelib_TemplateRegistry::getInstance(),
			Tx_Oelib_TemplateRegistry::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = Tx_Oelib_TemplateRegistry::getInstance();
		Tx_Oelib_TemplateRegistry::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			Tx_Oelib_TemplateRegistry::getInstance()
		);
	}


	///////////////////////////
	// Tests concerning get()
	///////////////////////////

	/**
	 * @test
	 */
	public function getForEmptyTemplateFileNameReturnsTemplateInstance() {
		$this->assertInstanceOf(
			'Tx_Oelib_Template',
			Tx_Oelib_TemplateRegistry::get('')
		);
	}

	/**
	 * @test
	 */
	public function getForEmptyTemplateFileNameCalledTwoTimesReturnsNewInstance() {
		$this->assertNotSame(
			Tx_Oelib_TemplateRegistry::get(''),
			Tx_Oelib_TemplateRegistry::get('')
		);
	}

	/**
	 * @test
	 */
	public function getForExistingTemplateFileNameReturnsTemplate() {
		$this->assertInstanceOf(
			'Tx_Oelib_Template',
			Tx_Oelib_TemplateRegistry::get('EXT:oelib/Tests/Unit/Fixtures/oelib.html')
		);
	}

	/**
	 * @test
	 */
	public function getForExistingTemplateFileNameCalledTwoTimesReturnsNewInstance() {
		$this->assertNotSame(
			Tx_Oelib_TemplateRegistry::get('EXT:oelib/Tests/Unit/Fixtures/oelib.html'),
			Tx_Oelib_TemplateRegistry::get('EXT:oelib/Tests/Unit/Fixtures/oelib.html')
		);
	}

	/**
	 * @test
	 */
	public function getForExistingTemplateFileNameReturnsProcessedTemplate() {
		$template = Tx_Oelib_TemplateRegistry::get('EXT:oelib/Tests/Unit/Fixtures/oelib.html');

		$this->assertSame(
			'Hello world!' . LF,
			$template->getSubpart()
		);
	}
}
?>