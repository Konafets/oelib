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
 * Testcase for the tx_oelib_Visibility_Tree class of the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_oelib_Visibility_TreeTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Visibility_Tree
	 */
	private $fixture;

	protected function setUp() {}

	protected function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	////////////////////////////////////////////////////////
	// Tests concerning the building of the tree structure
	////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function constructWithEmptyArray_CreatesRootNodeWithoutChildren() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array()
		);

		$this->assertSame(
			array(),
			$this->fixture->getRootNode()->getChildren()
		);
	}

	/**
	 * @test
	 */
	public function constructWithOneElementInArray_AddsOneChildToRootNode() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array('testNode' => FALSE)
		);

		$children = $this->fixture->getRootNode()->getChildren();

		$this->assertTrue(
			$children[0] instanceof tx_oelib_Visibility_Node
		);
	}

	/**
	 * @test
	 */
	public function constructWithTwoElementsInFirstArrayLevel_AddsTwoChildsToRootNode() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => FALSE, 'testNode2' => FALSE)
		);

		$this->assertSame(
			2,
			count($this->fixture->getRootNode()->getChildren())
		);
	}

	/**
	 * @test
	 */
	public function constructWithTwoElementsInArrayOneFirstOneSecondLevel_AddsGrandChildToRootNode() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('child' => array('grandChild' => FALSE))
		);

		$children = $this->fixture->getRootNode()->getChildren();
		$this->assertSame(
			1,
			count($children[0]->getChildren())
		);
	}

	/**
	 * @test
	 */
	public function constructForOneVisibleElement_StoresVisibilityStatus() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('visibleNode' => TRUE)
		);

		$children = $this->fixture->getRootNode()->getChildren();

		$this->assertTrue(
			$children[0]->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function constructForOneInvisibleElement_StoresVisibilityStatus() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('hiddenNode' => FALSE)
		);

		$children = $this->fixture->getRootNode()->getChildren();

		$this->assertFalse(
			$children[0]->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function rootNodeWithoutChild_IsInvisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array()
		);

		$this->assertFalse(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function rootNodeWithOneInvisibleChild_IsInvisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => FALSE)
		);

		$this->assertFalse(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function rootNodeWithOneVisibleChild_IsVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => TRUE)
		);

		$this->assertTrue(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function rootNodeWithOneVisibleGrandChild_IsVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('child' => array('grandChild' => TRUE))
		);

		$this->assertTrue(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function childOfRootNodeWithOneVisibleChild_IsVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('child' => array('grandChild' => TRUE))
		);

		$children = $this->fixture->getRootNode()->getChildren();

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
	public function makeNodesVisibleForEmptyArrayGiven_DoesNotMakeRootVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array()
		);
		$this->fixture->makeNodesVisible(array());

		$this->assertFalse(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	/**
	 * @test
	 */
	public function makeNodesVisibleForGivenNode_MakesThisNodeVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => FALSE)
		);
		$this->fixture->makeNodesVisible(array('testNode'));

		$this->fixture->getRootNode()->getChildren();

		$this->assertSame(
			array(),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}

	/**
	 * @test
	 */
	public function makeNodesVisibleForInexistentNodeGiven_DoesNotCrash() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => FALSE)
		);
		$this->fixture->makeNodesVisible(array('foo'));
	}

	/**
	 * @test
	 */
	public function makeNodesVisibleForInexistentNodeGiven_DoesNotMakeExistingNodeVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => FALSE)
		);
		$this->fixture->makeNodesVisible(array('foo'));

		$this->fixture->getRootNode()->getChildren();

		$this->assertSame(
			array('testNode'),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}


	/////////////////////////////////////////////
	// Tests concerning getKeysOfHiddenSubparts
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getKeysOfHiddenSubparts_ForTreeWithoutNodes_ReturnsEmptyArray() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array()
		);

		$this->assertSame(
			array(),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}

	/**
	 * @test
	 */
	public function getKeysOfHiddenSubparts_ForTreeWithOneHiddenNode_ReturnsArrayWithNodeName() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => FALSE)
		);

		$this->assertSame(
			array('testNode'),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}

	/**
	 * @test
	 */
	public function getKeysOfHiddenSubparts_ForTreeWithOneHiddenParentNodeAndOneHiddenChildNode_ReturnsArrayWithBothNodeNames() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('child' => array('parent' => FALSE))
		);

		$this->assertSame(
			array('parent', 'child'),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}

	/**
	 * @test
	 */
	public function getKeysOfHiddenSubparts_ForTreeWithVisibleParentNodeAndOneHiddenChildNode_ReturnsArrayWithChildNodeName() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('parent' => array('hidden' => FALSE, 'visible' => TRUE))
		);

		$this->assertSame(
			array('hidden'),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}
}
?>