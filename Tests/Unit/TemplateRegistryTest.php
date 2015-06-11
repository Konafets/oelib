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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_Unit_TemplateRegistryTest extends Tx_Phpunit_TestCase {
	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsTemplateRegistryInstance() {
		self::assertTrue(
			Tx_Oelib_TemplateRegistry::getInstance()
				instanceof Tx_Oelib_TemplateRegistry
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		self::assertSame(
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

		self::assertNotSame(
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
		self::assertInstanceOf(
			'Tx_Oelib_Template',
			Tx_Oelib_TemplateRegistry::get('')
		);
	}

	/**
	 * @test
	 */
	public function getForEmptyTemplateFileNameCalledTwoTimesReturnsNewInstance() {
		self::assertNotSame(
			Tx_Oelib_TemplateRegistry::get(''),
			Tx_Oelib_TemplateRegistry::get('')
		);
	}

	/**
	 * @test
	 */
	public function getForExistingTemplateFileNameReturnsTemplate() {
		self::assertInstanceOf(
			'Tx_Oelib_Template',
			Tx_Oelib_TemplateRegistry::get('EXT:oelib/Tests/Unit/Fixtures/oelib.html')
		);
	}

	/**
	 * @test
	 */
	public function getForExistingTemplateFileNameCalledTwoTimesReturnsNewInstance() {
		self::assertNotSame(
			Tx_Oelib_TemplateRegistry::get('EXT:oelib/Tests/Unit/Fixtures/oelib.html'),
			Tx_Oelib_TemplateRegistry::get('EXT:oelib/Tests/Unit/Fixtures/oelib.html')
		);
	}

	/**
	 * @test
	 */
	public function getForExistingTemplateFileNameReturnsProcessedTemplate() {
		$template = Tx_Oelib_TemplateRegistry::get('EXT:oelib/Tests/Unit/Fixtures/oelib.html');

		self::assertSame(
			'Hello world!' . LF,
			$template->getSubpart()
		);
	}
}