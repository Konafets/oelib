<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the checking of the installation of phpMyAdmin.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_oelib_PhpMyAdminTest extends tx_phpunit_testcase {
	public function setUp() {}

	public function tearDown() {}


	///////////////////////////////////////////////////////
	// Test if phpMyAdmin is installed
	///////////////////////////////////////////////////////

	public function test_phpMyAdminMustNotBeInstalled() {
		$this->assertFalse(
			t3lib_extMgm::isLoaded('phpmyadmin'),
			'For the oelib unit tests to run, the phpMyAdmin extension ' .
				'must not be installed because that extension adds some HTTP ' .
				'headers which break some tests.'
		);
	}
}
?>