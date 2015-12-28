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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_ObjectFactoryTest extends Tx_Phpunit_TestCase {
	/**
	 * @var bool
	 */
	protected $deprecationLogEnabledBackup = FALSE;

	protected function setUp() {
		$this->deprecationLogEnabledBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;
	}

	protected function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = $this->deprecationLogEnabledBackup;
	}

	/**
	 * @test
	 */
	public function canCreateInstanceOfClassWithConstructorWithoutParameters() {
		self::assertInstanceOf(
			'Tx_Oelib_Tests_Unit_Fixtures_TestingModel',
			Tx_Oelib_ObjectFactory::make('Tx_Oelib_Tests_Unit_Fixtures_TestingModel')
		);
	}

	/**
	 * @test
	 */
	public function canCreateInstanceOfClassWithConstructorWithParameters() {
		$object = Tx_Oelib_ObjectFactory::make(
			'Tx_Oelib_Translator', 'de', '', array()
		);

		self::assertTrue(
			$object instanceof Tx_Oelib_Translator
		);

		self::assertSame(
			'de',
			$object->getLanguageKey()
		);
	}
}