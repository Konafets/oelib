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
class Tx_Oelib_Mapper_FrontEndUserGroupTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework for creating dummy records
	 */
	private $testingFramework;
	/**
	 * @var tx_oelib_Mapper_FrontEndUserGroup the object to test
	 */
	private $subject;

	public function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		$this->subject = new tx_oelib_Mapper_FrontEndUserGroup();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->subject->__destruct();
		unset($this->subject, $this->testingFramework);
	}


	/////////////////////////////////////////
	// Tests concerning the basic functions
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsFrontEndUserGroupInstance() {
		$uid = $this->testingFramework->createFrontEndUserGroup();

		$this->assertTrue(
			$this->subject->find($uid)
				instanceof Tx_Oelib_Model_FrontEndUserGroup
		);
	}

	/**
	 * @test
	 */
	public function loadForExistingUserGroupCanLoadUserGroupData() {
		$userGroup = $this->subject->find(
			$this->testingFramework->createFrontEndUserGroup(
				array('title' => 'foo')
			)
		);

		$this->subject->load($userGroup);

		$this->assertSame(
			'foo',
			$userGroup->getTitle()
		);
	}
}
?>