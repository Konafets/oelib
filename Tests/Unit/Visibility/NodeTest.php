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
class Tx_Oelib_Tests_Unit_Visibility_NodeTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Visibility_Node
	 */
	private $subject;

	protected function setUp() {
		$this->subject = new tx_oelib_Visibility_Node();
	}

	//////////////////////////////
	// Tests for the constructor
	//////////////////////////////

	/**
	 * @test
	 */
	public function isVisibleIfSetToVisibleConstructionReturnsVisibilityFromConstruction() {
		$subject = new tx_oelib_Visibility_Node(TRUE);

		$this->assertTrue(
			$subject->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function isVisibleIfSetToHiddenConstructionReturnsVisibilityFromConstruction() {
		$subject = new tx_oelib_Visibility_Node(FALSE);

		$this->assertFalse(
			$subject->isVisible()
		);
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
			$this->subject->getChildren()
		);
	}

	/**
	 * @test
	 */
	public function addChildWithOneGivenChildrenAddsOneChildToNode() {
		$childNode = new tx_oelib_Visibility_Node();
		$this->subject->addChild($childNode);

		$this->assertSame(
			array($childNode),
			$this->subject->getChildren()
		);
	}

	/**
	 * @test
	 */
	public function addChildForNodeWithOneChildAndAnotherChildGivenAddsAnotherChildToNode() {
		$this->subject->addChild(new tx_oelib_Visibility_Node());
		$this->subject->addChild(new tx_oelib_Visibility_Node());

		$this->assertSame(
			2,
			count($this->subject->getChildren())
		);
	}

	/**
	 * @test
	 */
	public function addChildAddsParentToChild() {
		$childNode = new tx_oelib_Visibility_Node();
		$this->subject->addChild($childNode);

		$this->assertSame(
			$this->subject,
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
			$this->subject->getParent()
		);
	}

	/**
	 * @test
	 */
	public function setParentWithGivenParentSetsThisNodeAsParent() {
		$childNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->subject);

		$this->assertSame(
			$this->subject,
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
		$childNode->setParent($this->subject);

		$childNode->setParent($this->subject);
	}


	///////////////////////////////////
	// Tests concerning markAsVisible
	///////////////////////////////////

	/**
	 * @test
	 */
	public function markAsVisibleForInvisibleNodeSetsVisibilityTrue() {
		$this->subject->markAsVisible();

		$this->assertTrue(
			$this->subject->isVisible()
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
	}

	/**
	 * @test
	 */
	public function markAsVisibleForNodeWithParentMarksParentAsVisible() {
		$childNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->subject);
		$childNode->markAsVisible();

		$this->assertTrue(
			$this->subject->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function markAsVisibleForNodeWithParentAndGrandparentMarksGrandparentNodeAsVisible() {
		$childNode = new tx_oelib_Visibility_Node();
		$grandChildNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->subject);
		$grandChildNode->setParent($childNode);
		$grandChildNode->markAsVisible();

		$this->assertTrue(
			$this->subject->isVisible()
		);
	}
}