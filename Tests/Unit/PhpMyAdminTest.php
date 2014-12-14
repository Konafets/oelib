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
 * Testcase for the checking of the installation of phpMyAdmin.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Oelib_PhpMyAdminTest extends Tx_Phpunit_TestCase {
	protected function setUp() {}

	protected function tearDown() {}


	///////////////////////////////////////////////////////
	// Test if phpMyAdmin is installed
	///////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function phpMyAdminMustNotBeInstalled() {
		$this->assertFalse(
			t3lib_extMgm::isLoaded('phpmyadmin'),
			'For the oelib unit tests to run, the phpMyAdmin extension ' .
				'must not be installed because that extension adds some HTTP ' .
				'headers which break some tests.'
		);
	}
}