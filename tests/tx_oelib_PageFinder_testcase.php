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
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_oelib_PageFinder
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');
		$this->fixture = tx_oelib_PageFinder::getInstance();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->testingFramework, $this->fixture);
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


	////////////////////////////////
	// Tests concerning getPageUid
	////////////////////////////////

	public function test_getPageUid_WithFrontEndPageUid_ReturnsFrontEndPageUid() {
		$pageUid = $this->testingFramework->createFakeFrontEnd();

		$this->assertEquals(
			$pageUid,
			$this->fixture->getPageUid()
		);
	}

	public function test_getPageUid_WithoutFrontEndAndWithBackendPageUid_ReturnsBackEndPageUid() {
		$_POST['id'] = 42;

		$pageUid = $this->fixture->getPageUid();
		unset($_POST['id']);

		$this->assertEquals(
			42,
			$pageUid
		);
	}

	public function test_getPageUid_WithFrontEndAndBackendPageUid_ReturnsFrontEndPageUid() {
		$frontEndPageUid = $this->testingFramework->createFakeFrontEnd();

		$_POST['id'] = $frontEndPageUid + 1;

		$pageUid = $this->fixture->getPageUid();

		unset($_POST['id']);

		$this->assertEquals(
			$frontEndPageUid,
			$pageUid
		);
	}

	public function test_getPageUid_ForManuallySetPageUidAndSetFrontEndPageUid_ReturnsManuallySetPageUid() {
		$frontEndPageUid = $this->testingFramework->createFakeFrontEnd();
		$this->fixture->setPageUid($frontEndPageUid + 1);

		$this->assertEquals(
			$frontEndPageUid + 1,
			$this->fixture->getPageUid()
		);
	}


	////////////////////////////////
	// tests concerning setPageUid
	////////////////////////////////

	public function test_GetPageUid_WithSetPageUidViaSetPageUid_ReturnsSetPageUid() {
		$this->fixture->setPageUid(42);

		$this->assertEquals(
			42,
			$this->fixture->getPageUid()
		);
	}

	public function test_setPageUid_WithZeroGiven_ThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The given page UID was "0". Only integer values greater ' .
				'than zero are allowed.'
		);

		$this->fixture->setPageUid(0);
	}

	public function test_setPageUid_WithNegativeNumberGiven_ThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The given page UID was "-21". Only integer values greater ' .
				'than zero are allowed.'
		);

		$this->fixture->setPageUid(-21);
	}


	/////////////////////////////////
	// Tests concerning forceSource
	/////////////////////////////////

	public function test_ForceSource_WithSourceSetToFrontEndAndManuallySetPageUid_ReturnsFrontEndPageUid() {
		$this->fixture->forceSource(tx_oelib_PageFinder::SOURCE_FRONT_END);
		$frontEndPageUid = $this->testingFramework->createFakeFrontEnd();

		$this->fixture->setPageUid($frontEndPageUid + 1);

		$this->assertEquals(
			$frontEndPageUid,
			$this->fixture->getPageUid()
		);
	}

	public function test_ForceSource_WithSourceSetToBackEndAndSetFrontEndUid_ReturnsBackEndEndPageUid() {
		$this->fixture->forceSource(tx_oelib_PageFinder::SOURCE_BACK_END);
		$this->testingFramework->createFakeFrontEnd();

		$_POST['id'] = 42;
		$pageUid = $this->fixture->getPageUid();
		unset($_POST['id']);

		$this->assertEquals(
			42,
			$pageUid
		);
	}

	public function test_ForceSource_WithSourceSetToFrontEndAndManuallySetPageUidButNoFrontEndUidSet_ReturnsZero() {
		$this->fixture->forceSource(tx_oelib_PageFinder::SOURCE_FRONT_END);

		$this->fixture->setPageUid(15);

		$this->assertEquals(
			0,
			$this->fixture->getPageUid()
		);
	}


	//////////////////////////////////////
	// Tests concerning getCurrentSource
	//////////////////////////////////////

	public function test_GetCurrentSource_ForNoSourceForcedAndNoPageUidSet_ReturnsNoSourceFound() {
		$this->assertEquals(
			tx_oelib_PageFinder::NO_SOURCE_FOUND,
			$this->fixture->getCurrentSource()
		);
	}

	public function test_GetCurrentSource_ForSourceForcedToFrontEnd_ReturnsSourceFrontEnd() {
		$this->fixture->forceSource(tx_oelib_PageFinder::SOURCE_FRONT_END);

		$this->assertEquals(
			tx_oelib_PageFinder::SOURCE_FRONT_END,
			$this->fixture->getCurrentSource()
		);
	}

	public function test_GetCurrentSource_ForSourceForcedToBackEnd_ReturnsSourceBackEnd() {
		$this->fixture->forceSource(tx_oelib_PageFinder::SOURCE_BACK_END);

		$this->assertEquals(
			tx_oelib_PageFinder::SOURCE_BACK_END,
			$this->fixture->getCurrentSource()
		);
	}

	public function test_GetCurrentSource_ForManuallySetPageId_ReturnsSourceManual() {
		$this->fixture->setPageUid(42);

		$this->assertEquals(
			tx_oelib_PageFinder::SOURCE_MANUAL,
			$this->fixture->getCurrentSource()
		);
	}

	public function test_GetCurrentSource_ForSetFrontEndPageUid_ReturnsSourceFrontEnd() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertEquals(
			tx_oelib_PageFinder::SOURCE_FRONT_END,
			$this->fixture->getCurrentSource()
		);
	}

	public function test_GetCurrentSource_ForSetBackEndPageUid_ReturnsSourceBackEnd() {
		$_POST['id'] = 42;
		$pageSource = $this->fixture->getCurrentSource();
		unset($_POST['id']);

		$this->assertEquals(
			tx_oelib_PageFinder::SOURCE_BACK_END,
			$pageSource
		);
	}
}
?>