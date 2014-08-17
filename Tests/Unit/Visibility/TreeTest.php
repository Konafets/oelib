<?php
/**
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
class Tx_Oelib_Visibility_TreeTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Visibility_Tree
	 */
	private $subject;

	protected function setUp() {}

	protected function tearDown() {
		unset($this->subject);
	}


	////////////////////////////////////////////////////////
	// Tests concerning the building of the tree structure
	////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function constructWithEmptyArrayCreatesRootNodeWithoutChildren() {
		$this->subject = new tx_oelib_Visibility_Tree(array());

		$this->assertSame(
			array(),
			$this->subject->getRootNode()->getChildren()
		);
	}

	/**
	 * @test
	 */
	public function constructWithOneElementInArrayAddsOneChildToRootNode() {
		$this->subject = new tx_oelib_Visibility_Tree(array('testNode' => FALSE));

		$children = $this->subject->getRootNode()->getChildren();

		$this->assertTrue(
			$children[0] instanceof tx_oelib_Visibility_Node
		);
	}

	/**
	 * @test
	 */
	public function constructWithTwoElementsInFirstArrayLevelAddsTwoChildrenToRootNode() {
		$this->subject = new tx_oelib_Visibility_Tree(array('testNode' => FALSE, 'testNode2' => FALSE));

		$this->assertSame(
			2,
			count($this->subject->getRootNode()->getChildren())
		);
	}

	/**
	 * @test
	 */
	public function constructWithTwoElementsInArrayOneFirstOneSecondLevelAddsGrandChildToRootNode() {
		$this->subject = new tx_oelib_Visibility_Tree(array('child' => array('grandChild' => FALSE)));

		$children = $this->subject->getRootNode()->getChildren();
		$this->assertSame(
			1,
			count($children[0]->getChildren())
		);
	}

	/**
	 * @test
	 */
	public function constructForOneVisibleElementStoresVisibilityStatus() {
		$this->subject = new tx_oelib_Visibility_Tree(array('visibleNode' => TRUE));

		$children = $this->subject->getRootNode()->getChildren();

		$this->assertTrue(
			$children[0]->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function constructForOneInvisibleElementStoresVisibilityStatus() {
		$this->subject = new tx_oelib_Visibility_Tree(array('hiddenNode' => FALSE));

		$children = $this->subject->getRootNode()->getChildren();

		$this->assertFalse(
			$children[0]->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function rootNodeWithoutChildIsInvisible() {
		$this->subject = new tx_oelib_Visibility_Tree(array());

		$this->assertFalse(
			$this->subject->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function rootNodeWithOneInvisibleChildIsInvisible() {
		$this->subject = new tx_oelib_Visibility_Tree(array('testNode' => FALSE));

		$this->assertFalse(
			$this->subject->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function rootNodeWithOneVisibleChildIsVisible() {
		$this->subject = new tx_oelib_Visibility_Tree(array('testNode' => TRUE));

		$this->assertTrue(
			$this->subject->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function rootNodeWithOneVisibleGrandChildIsVisible() {
		$this->subject = new tx_oelib_Visibility_Tree(array('child' => array('grandChild' => TRUE)));

		$this->assertTrue(
			$this->subject->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function childOfRootNodeWithOneVisibleChildIsVisible() {
		$this->subject = new tx_oelib_Visibility_Tree(array('child' => array('grandChild' => TRUE)));

		$children = $this->subject->getRootNode()->getChildren();

		$this->assertTrue(
			$children[0]->isVisible()
		);
	}


	//////////////////////////////////////
	// Tests concerning makeNodesVisible
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function makeNodesVisibleForEmptyArrayGivenDoesNotMakeRootVisible() {
		$this->subject = new tx_oelib_Visibility_Tree(array());
		$this->subject->makeNodesVisible(array());

		$this->assertFalse(
			$this->subject->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function makeNodesVisibleForGivenNodeMakesThisNodeVisible() {
		$this->subject = new tx_oelib_Visibility_Tree(array('testNode' => FALSE));
		$this->subject->makeNodesVisible(array('testNode'));

		$this->subject->getRootNode()->getChildren();

		$this->assertSame(
			array(),
			$this->subject->getKeysOfHiddenSubparts()
		);
	}

	/**
	 * @test
	 */
	public function makeNodesVisibleForInexistentNodeGivenDoesNotCrash() {
		$this->subject = new tx_oelib_Visibility_Tree(array('testNode' => FALSE));
		$this->subject->makeNodesVisible(array('foo'));
	}

	/**
	 * @test
	 */
	public function makeNodesVisibleForInexistentNodeGivenDoesNotMakeExistingNodeVisible() {
		$this->subject = new tx_oelib_Visibility_Tree(array('testNode' => FALSE));
		$this->subject->makeNodesVisible(array('foo'));

		$this->subject->getRootNode()->getChildren();

		$this->assertSame(
			array('testNode'),
			$this->subject->getKeysOfHiddenSubparts()
		);
	}


	/////////////////////////////////////////////
	// Tests concerning getKeysOfHiddenSubparts
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getKeysOfHiddenSubpartsForTreeWithoutNodesReturnsEmptyArray() {
		$this->subject = new tx_oelib_Visibility_Tree(array());

		$this->assertSame(
			array(),
			$this->subject->getKeysOfHiddenSubparts()
		);
	}

	/**
	 * @test
	 */
	public function getKeysOfHiddenSubpartsForTreeWithOneHiddenNodeReturnsArrayWithNodeName() {
		$this->subject = new tx_oelib_Visibility_Tree(array('testNode' => FALSE));

		$this->assertSame(
			array('testNode'),
			$this->subject->getKeysOfHiddenSubparts()
		);
	}

	/**
	 * @test
	 */
	public function getKeysOfHiddenSubpartsForTreeWithOneHiddenParentNodeAndOneHiddenChildNodeReturnsArrayWithBothNodeNames() {
		$this->subject = new tx_oelib_Visibility_Tree(array('child' => array('parent' => FALSE)));

		$this->assertSame(
			array('parent', 'child'),
			$this->subject->getKeysOfHiddenSubparts()
		);
	}

	/**
	 * @test
	 */
	public function getKeysOfHiddenSubpartsForTreeWithVisibleParentNodeAndOneHiddenChildNodeReturnsArrayWithChildNodeName() {
		$this->subject = new tx_oelib_Visibility_Tree(array('parent' => array('hidden' => FALSE, 'visible' => TRUE)));

		$this->assertSame(
			array('hidden'),
			$this->subject->getKeysOfHiddenSubparts()
		);
	}
}