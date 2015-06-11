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
 * @subpackage oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Model_BackEndUserGroupTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Model_BackEndUserGroup
	 */
	private $subject;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Model_BackEndUserGroup();
	}

	////////////////////////////////
	// Tests concerning getTitle()
	////////////////////////////////

	/**
	 * @test
	 */
	public function getTitleForNonEmptyGroupTitleReturnsGroupTitle() {
		$this->subject->setData(array('title' => 'foo'));

		self::assertSame(
			'foo',
			$this->subject->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleForEmptyGroupTitleReturnsEmptyString() {
		$this->subject->setData(array('title' => ''));

		self::assertSame(
			'',
			$this->subject->getTitle()
		);
	}


	/////////////////////////////////////
	// Tests concerning getSubgroups
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getSubgroupsReturnsListFromSubgroupField() {
		$groups = new Tx_Oelib_List();

		$this->subject->setData(array('subgroup' => $groups));

		self::assertSame(
			$groups,
			$this->subject->getSubgroups()
		);
	}
}