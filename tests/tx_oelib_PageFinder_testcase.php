<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the tx_oelib_PageFinder class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_oelib_PageFinder_testcase extends tx_phpunit_testcase {
	public function setUp() {
	}

	public function tearDown() {
		tx_oelib_PageFinder::purgeInstance();
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	public function test_GetInstance_ReturnsPageFinderInstance() {
		$this->assertTrue(
			tx_oelib_PageFinder::getInstance()
				instanceof tx_oelib_PageFinder
		);
	}

	public function test_GetInstance_TwoTimes_ReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_PageFinder::getInstance(),
			tx_oelib_PageFinder::getInstance()
		);
	}

	public function test_GetInstance_AfterPurgeInstance_ReturnsNewInstance() {
		$firstInstance = tx_oelib_PageFinder::getInstance();
		tx_oelib_PageFinder::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			tx_oelib_PageFinder::getInstance()
		);
	}
}
?>