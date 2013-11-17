<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Bernd Schönbach <bernd@oliverklee.de>
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
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Oelib_PageFinderTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_oelib_PageFinder
	 */
	private $subject;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');
		$this->subject = tx_oelib_PageFinder::getInstance();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->testingFramework, $this->subject);
	}


	////////////////////////////////////////////
	// Tests concerning the Singleton property
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsPageFinderInstance() {
		$this->assertTrue(
			tx_oelib_PageFinder::getInstance()
				instanceof tx_oelib_PageFinder
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			tx_oelib_PageFinder::getInstance(),
			tx_oelib_PageFinder::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
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

	/**
	 * @test
	 */
	public function getPageUidWithFrontEndPageUidReturnsFrontEndPageUid() {
		$pageUid = $this->testingFramework->createFakeFrontEnd();

		$this->assertSame(
			$pageUid,
			$this->subject->getPageUid()
		);
	}

	/**
	 * @test
	 */
	public function getPageUidWithoutFrontEndAndWithBackendPageUidReturnsBackEndPageUid() {
		$_POST['id'] = 42;

		$pageUid = $this->subject->getPageUid();
		unset($_POST['id']);

		$this->assertSame(
			42,
			$pageUid
		);
	}

	/**
	 * @test
	 */
	public function getPageUidWithFrontEndAndBackendPageUidReturnsFrontEndPageUid() {
		$frontEndPageUid = $this->testingFramework->createFakeFrontEnd();

		$_POST['id'] = $frontEndPageUid + 1;

		$pageUid = $this->subject->getPageUid();

		unset($_POST['id']);

		$this->assertSame(
			$frontEndPageUid,
			$pageUid
		);
	}

	/**
	 * @test
	 */
	public function getPageUidForManuallySetPageUidAndSetFrontEndPageUidReturnsManuallySetPageUid() {
		$frontEndPageUid = $this->testingFramework->createFakeFrontEnd();
		$this->subject->setPageUid($frontEndPageUid + 1);

		$this->assertSame(
			$frontEndPageUid + 1,
			$this->subject->getPageUid()
		);
	}


	////////////////////////////////
	// tests concerning setPageUid
	////////////////////////////////

	/**
	 * @test
	 */
	public function getPageUidWithSetPageUidViaSetPageUidReturnsSetPageUid() {
		$this->subject->setPageUid(42);

		$this->assertSame(
			42,
			$this->subject->getPageUid()
		);
	}

	/**
	 * @test
	 */
	public function setPageUidWithZeroGivenThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given page UID was "0". Only integer values greater than zero are allowed.'
		);

		$this->subject->setPageUid(0);
	}

	/**
	 * @test
	 */
	public function setPageUidWithNegativeNumberGivenThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given page UID was "-21". Only integer values greater than zero are allowed.'
		);

		$this->subject->setPageUid(-21);
	}


	/////////////////////////////////
	// Tests concerning forceSource
	/////////////////////////////////

	/**
	 * @test
	 */
	public function forceSourceWithSourceSetToFrontEndAndManuallySetPageUidReturnsFrontEndPageUid() {
		$this->subject->forceSource(tx_oelib_PageFinder::SOURCE_FRONT_END);
		$frontEndPageUid = $this->testingFramework->createFakeFrontEnd();

		$this->subject->setPageUid($frontEndPageUid + 1);

		$this->assertSame(
			$frontEndPageUid,
			$this->subject->getPageUid()
		);
	}

	/**
	 * @test
	 */
	public function forceSourceWithSourceSetToBackEndAndSetFrontEndUidReturnsBackEndEndPageUid() {
		$this->subject->forceSource(tx_oelib_PageFinder::SOURCE_BACK_END);
		$this->testingFramework->createFakeFrontEnd();

		$_POST['id'] = 42;
		$pageUid = $this->subject->getPageUid();
		unset($_POST['id']);

		$this->assertSame(
			42,
			$pageUid
		);
	}

	/**
	 * @test
	 */
	public function forceSourceWithSourceSetToFrontEndAndManuallySetPageUidButNoFrontEndUidSetReturnsZero() {
		$this->subject->forceSource(tx_oelib_PageFinder::SOURCE_FRONT_END);

		$this->subject->setPageUid(15);

		$this->assertSame(
			0,
			$this->subject->getPageUid()
		);
	}


	//////////////////////////////////////
	// Tests concerning getCurrentSource
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getCurrentSourceForNoSourceForcedAndNoPageUidSetReturnsNoSourceFound() {
		$this->assertSame(
			tx_oelib_PageFinder::NO_SOURCE_FOUND,
			$this->subject->getCurrentSource()
		);
	}

	/**
	 * @test
	 */
	public function getCurrentSourceForSourceForcedToFrontEndReturnsSourceFrontEnd() {
		$this->subject->forceSource(tx_oelib_PageFinder::SOURCE_FRONT_END);

		$this->assertSame(
			tx_oelib_PageFinder::SOURCE_FRONT_END,
			$this->subject->getCurrentSource()
		);
	}

	/**
	 * @test
	 */
	public function getCurrentSourceForSourceForcedToBackEndReturnsSourceBackEnd() {
		$this->subject->forceSource(tx_oelib_PageFinder::SOURCE_BACK_END);

		$this->assertSame(
			tx_oelib_PageFinder::SOURCE_BACK_END,
			$this->subject->getCurrentSource()
		);
	}

	/**
	 * @test
	 */
	public function getCurrentSourceForManuallySetPageIdReturnsSourceManual() {
		$this->subject->setPageUid(42);

		$this->assertSame(
			tx_oelib_PageFinder::SOURCE_MANUAL,
			$this->subject->getCurrentSource()
		);
	}

	/**
	 * @test
	 */
	public function getCurrentSourceForSetFrontEndPageUidReturnsSourceFrontEnd() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertSame(
			tx_oelib_PageFinder::SOURCE_FRONT_END,
			$this->subject->getCurrentSource()
		);
	}

	/**
	 * @test
	 */
	public function getCurrentSourceForSetBackEndPageUidReturnsSourceBackEnd() {
		$_POST['id'] = 42;
		$pageSource = $this->subject->getCurrentSource();
		unset($_POST['id']);

		$this->assertSame(
			tx_oelib_PageFinder::SOURCE_BACK_END,
			$pageSource
		);
	}
}
?>