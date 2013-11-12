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
	 * @var tx_oelib_Model_FrontEndUserGroup
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Model_FrontEndUserGroup();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	////////////////////////////////
	// Tests concerning getTitle()
	////////////////////////////////

	/**
	 * @test
	 */
	public function getTitleForNonEmptyGroupTitleReturnsGroupTitle() {
		$this->fixture->setData(array('title' => 'foo'));

		$this->assertSame(
			'foo',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleForEmptyGroupTitleReturnsEmptyString() {
		$this->fixture->setData(array('title' => ''));

		$this->assertSame(
			'',
			$this->fixture->getTitle()
		);
	}


	//////////////////////////////////////
	// Tests concerning getDescription()
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getDescriptionForNonEmptyGroupDescriptionReturnsGroupDescription() {
		$this->fixture->setData(array('description' => 'foo'));

		$this->assertSame(
			'foo',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForEmptyGroupDescriptionReturnsEmptyString() {
		$this->fixture->setData(array('description' => ''));

		$this->assertSame(
			'',
			$this->fixture->getDescription()
		);
	}
}
?>