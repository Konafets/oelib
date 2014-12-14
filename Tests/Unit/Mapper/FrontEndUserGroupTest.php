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

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		$this->subject = new tx_oelib_Mapper_FrontEndUserGroup();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

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