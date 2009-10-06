<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Bernd Schönbach <bernd@oliverklee.de>
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the visibility tree class of the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_oelib_Visibility_Tree_testcase extends tx_phpunit_testcase {
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

	public function test_ConstructWithEmptyArray_CreatesRootNodeWithoutChildren() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array()
		);

		$this->assertEquals(
			array(),
			$this->fixture->getRootNode()->getChildren()
		);
	}

	public function test_ConstructWithOneElementInArray_AddsOneChildToRootNode() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array('testNode' => false)
		);

		$children = $this->fixture->getRootNode()->getChildren();

		$this->assertTrue(
			$children[0] instanceof tx_oelib_Visibility_Node
		);
	}

	public function test_ConstructWithTwoElementsInFirstArrayLevel_AddsTwoChildsToRootNode() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => false, 'testNode2' => false)
		);

		$this->assertEquals(
			2,
			count($this->fixture->getRootNode()->getChildren())
		);
	}

	public function test_ConstructWithTwoElementsInArrayOneFirstOneSecondLevel_AddsGrandChildToRootNode() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('child' => array('grandChild' => false))
		);

		$children = $this->fixture->getRootNode()->getChildren();
		$this->assertEquals(
			1,
			count($children[0]->getChildren())
		);
	}

	public function test_ConstructForOneVisibleElement_StoresVisibilityStatus() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('visibleNode' => true)
		);

		$children = $this->fixture->getRootNode()->getChildren();

		$this->assertTrue(
			$children[0]->isVisible()
		);
	}

	public function test_ConstructForOneInvisibleElement_StoresVisibilityStatus() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('hiddenNode' => false)
		);

		$children = $this->fixture->getRootNode()->getChildren();

		$this->assertFalse(
			$children[0]->isVisible()
		);
	}

	public function test_RootNodeWithoutChild_IsInvisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array()
		);

		$this->assertFalse(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	public function test_RootNodeWithOneInvisibleChild_IsInvisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => false)
		);

		$this->assertFalse(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	public function test_RootNodeWithOneVisibleChild_IsVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => true)
		);

		$this->assertTrue(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	public function test_RootNodeWithOneVisibleGrandChild_IsVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('child' => array('grandChild' => true))
		);

		$this->assertTrue(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	public function test_ChildOfRootNodeWithOneVisibleChild_IsVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('child' => array('grandChild' => true))
		);

		$children = $this->fixture->getRootNode()->getChildren();

		$this->assertTrue(
			$children[0]->isVisible()
		);
	}


	//////////////////////////////////////
	// Tests concerning makeNodesVisible
	//////////////////////////////////////

	public function test_makeNodesVisibleForEmptyArrayGiven_DoesNotMakeRootVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array()
		);
		$this->fixture->makeNodesVisible(array());

		$this->assertFalse(
			$this->fixture->getRootNode()->isVisible()
		);
	}

	public function test_makeNodesVisibleForGivenNode_MakesThisNodeVisible() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => false)
		);
		$this->fixture->makeNodesVisible(array('testNode'));

		$this->fixture->getRootNode()->getChildren();

		$this->assertEquals(
			array(),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}


	/////////////////////////////////////////////
	// Tests concerning getKeysOfHiddenSubparts
	/////////////////////////////////////////////

	public function test_getKeysOfHiddenSubparts_ForTreeWithoutNodes_ReturnsEmptyArray() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', array()
		);

		$this->assertEquals(
			array(),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}

	public function test_getKeysOfHiddenSubparts_ForTreeWithOneHiddenNode_ReturnsArrayWithNodeName() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('testNode' => false)
		);

		$this->assertEquals(
			array('testNode'),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}

	public function test_getKeysOfHiddenSubparts_ForTreeWithOneHiddenParentNodeAndOneHiddenChildNode_ReturnsArrayWithBothNodeNames() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('child' => array('parent' => false))
		);

		$this->assertEquals(
			array('parent', 'child'),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}

	public function test_getKeysOfHiddenSubparts_ForTreeWithVisibleParentNodeAndOneHiddenChildNode_ReturnsArrayWithChildNodeName() {
		$this->fixture = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree',
			array('parent' => array('hidden' => false, 'visible' => true))
		);

		$this->assertEquals(
			array('hidden'),
			$this->fixture->getKeysOfHiddenSubparts()
		);
	}
}
?>