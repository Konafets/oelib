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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Mapper_BackEndUserGroupTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework for creating dummy records
	 */
	private $testingFramework;
	/**
	 * @var tx_oelib_Mapper_BackEndUserGroup the object to test
	 */
	private $subject;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

		$this->subject = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUserGroup');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	/////////////////////////////////////////
	// Tests concerning the basic functions
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function findReturnsBackEndUserGroupInstance() {
		$uid = $this->subject->getNewGhost()->getUid();

		self::assertTrue(
			$this->subject->find($uid)
				instanceof Tx_Oelib_Model_BackEndUserGroup
		);
	}

	/**
	 * @test
	 */
	public function loadForExistingUserGroupCanLoadUserGroupData() {
		/** @var Tx_Oelib_Model_FrontEndUserGroup $userGroup */
		$userGroup = $this->subject->find(
			$this->testingFramework->createBackEndUserGroup(array('title' => 'foo'))
		);

		$this->subject->load($userGroup);

		self::assertSame(
			'foo',
			$userGroup->getTitle()
		);
	}


	///////////////////////////////////
	// Tests concerning the relations
	///////////////////////////////////

	/**
	 * @test
	 */
	public function subgroupRelationIsUserGroupList() {
		$subgroup = $this->subject->getNewGhost();
		$group = $this->subject->getLoadedTestingModel(
			array('subgroup' => $subgroup->getUid())
		);

		/** @var Tx_Oelib_Model_BackEndUserGroup $group */
		$group = $this->subject->find($group->getUid());
		self::assertTrue(
			$group->getSubgroups()->first()
				instanceof Tx_Oelib_Model_BackEndUserGroup
		);
	}
}