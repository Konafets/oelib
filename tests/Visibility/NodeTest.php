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
class tx_oelib_Visibility_NodeTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Visibility_Node
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_oelib_Visibility_Node();
	}

	protected function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	//////////////////////////////
	// Tests for the constructor
	//////////////////////////////

	/**
	 * @test
	 */
	public function isVisibleIfSetToVisibleConstructionReturnsVisibilityFromConstruction() {
		$fixture = new tx_oelib_Visibility_Node(TRUE);

		$this->assertTrue(
			$fixture->isVisible()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function isVisibleIfSetToHiddenConstructionReturnsVisibilityFromConstruction() {
		$fixture = new tx_oelib_Visibility_Node(FALSE);

		$this->assertFalse(
			$fixture->isVisible()
		);

		$fixture->__destruct();
	}


	//////////////////////////////
	// Tests concerning addChild
	//////////////////////////////

	/**
	 * @test
	 */
	public function getChildrenWithoutChildrenSetReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getChildren()
		);
	}

	/**
	 * @test
	 */
	public function addChildWithOneGivenChildrenAddsOneChildToNode() {
		$childNode = new tx_oelib_Visibility_Node();
		$this->fixture->addChild($childNode);

		$this->assertSame(
			array($childNode),
			$this->fixture->getChildren()
		);
	}

	/**
	 * @test
	 */
	public function addChildForNodeWithOneChildAndAnotherChildGivenAddsAnotherChildToNode() {
		$this->fixture->addChild(new tx_oelib_Visibility_Node());
		$this->fixture->addChild(new tx_oelib_Visibility_Node());

		$this->assertSame(
			2,
			count($this->fixture->getChildren())
		);
	}

	/**
	 * @test
	 */
	public function addChildAddsParentToChild() {
		$childNode = new tx_oelib_Visibility_Node();
		$this->fixture->addChild($childNode);

		$this->assertSame(
			$this->fixture,
			$childNode->getParent()
		);
	}


	///////////////////////////////
	// Tests concerning setParent
	///////////////////////////////

	/**
	 * @test
	 */
	public function getParentForNodeWithoutParentReturnsNull() {
		$this->assertNull(
			$this->fixture->getParent()
		);
	}

	/**
	 * @test
	 */
	public function setParentWithGivenParentSetsThisNodeAsParent() {
		$childNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->fixture);

		$this->assertSame(
			$this->fixture,
			$childNode->getParent()
		);
	}

	/**
	 * @test
	 */
	public function setParentForNodeWithAlreadySetParentAndGivenParentThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'This node already has a parent node.'
		);
		$childNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->fixture);

		$childNode->setParent($this->fixture);
	}


	///////////////////////////////////
	// Tests concerning markAsVisible
	///////////////////////////////////

	/**
	 * @test
	 */
	public function markAsVisibleForInvisibleNodeSetsVisibilityTrue() {
		$this->fixture->markAsVisible();

		$this->assertTrue(
			$this->fixture->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function markAsVisibleForVisibleNodeSetsVisibilityTrue() {
		$visibleNode = new tx_oelib_Visibility_Node(TRUE);
		$visibleNode->markAsVisible();

		$this->assertTrue(
			$visibleNode->isVisible()
		);

		$visibleNode->__destruct();
	}

	/**
	 * @test
	 */
	public function markAsVisibleForNodeWithParentMarksParentAsVisible() {
		$childNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->fixture);
		$childNode->markAsVisible();

		$this->assertTrue(
			$this->fixture->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function markAsVisibleForNodeWithParentAndGrandparentMarksGrandparentNodeAsVisible() {
		$childNode = new tx_oelib_Visibility_Node();
		$grandChildNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->fixture);
		$grandChildNode->setParent($childNode);
		$grandChildNode->markAsVisible();

		$this->assertTrue(
			$this->fixture->isVisible()
		);
	}
}
?>