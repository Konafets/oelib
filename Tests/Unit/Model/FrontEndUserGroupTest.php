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
 * @subpackage oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Oelib_Model_FrontEndUserGroupTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Model_FrontEndUserGroup
	 */
	private $subject;

	public function setUp() {
		$this->subject = new Tx_Oelib_Model_FrontEndUserGroup();
	}

	public function tearDown() {
		$this->subject->__destruct();
		unset($this->subject);
	}


	////////////////////////////////
	// Tests concerning getTitle()
	////////////////////////////////

	/**
	 * @test
	 */
	public function getTitleForNonEmptyGroupTitleReturnsGroupTitle() {
		$this->subject->setData(array('title' => 'foo'));

		$this->assertSame(
			'foo',
			$this->subject->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleForEmptyGroupTitleReturnsEmptyString() {
		$this->subject->setData(array('title' => ''));

		$this->assertSame(
			'',
			$this->subject->getTitle()
		);
	}


	//////////////////////////////////////
	// Tests concerning getDescription()
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getDescriptionForNonEmptyGroupDescriptionReturnsGroupDescription() {
		$this->subject->setData(array('description' => 'foo'));

		$this->assertSame(
			'foo',
			$this->subject->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForEmptyGroupDescriptionReturnsEmptyString() {
		$this->subject->setData(array('description' => ''));

		$this->assertSame(
			'',
			$this->subject->getDescription()
		);
	}
}