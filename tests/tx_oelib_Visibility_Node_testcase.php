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
 * Testcase for the visibility node class of the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_oelib_Visibility_Node_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_visibilityNode
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

	public function test_IsVisible_IfSetToVisibleConstruction_ReturnsVisibilityFromConstruction() {
		$fixture = new tx_oelib_Visibility_Node(true);

		$this->assertTrue(
			$fixture->isVisible()
		);

		$fixture->__destruct();
	}

	public function test_IsVisible_IfSetToHiddenConstruction_ReturnsVisibilityFromConstruction() {
		$fixture = new tx_oelib_Visibility_Node(false);

		$this->assertFalse(
			$fixture->isVisible()
		);

		$fixture->__destruct();
	}


	//////////////////////////////
	// Tests concerning addChild
	//////////////////////////////

	public function test_getChildren_WithoutChildrenSet_ReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getChildren()
		);
	}

	public function test_addChild_WithOneGivenChildren_AddsOneChildToNode() {
		$childNode = new tx_oelib_Visibility_Node();
		$this->fixture->addChild($childNode);

		$this->assertEquals(
			array($childNode),
			$this->fixture->getChildren()
		);
	}

	public function test_addChild_ForNodeWithOneChildAndAnotherChildGiven_AddsAnotherChildToNode() {
		$this->fixture->addChild(new tx_oelib_Visibility_Node());
		$this->fixture->addChild(new tx_oelib_Visibility_Node());

		$this->assertEquals(
			2,
			count($this->fixture->getChildren())
		);
	}

	public function test_addChild_AddsParentToChild() {
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

	public function test_getParent_ForNodeWithoutParent_ReturnsNull() {
		$this->assertNull(
			$this->fixture->getParent()
		);
	}

	public function test_setParent_WithGivenParent_SetsThisNodeAsParent() {
		$childNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->fixture);

		$this->assertSame(
			$this->fixture,
			$childNode->getParent()
		);
	}

	public function test_setParent_ForNodeWithAlreadySetParentAndGivenParent_ThrowsException() {
		$this->setExpectedException(
			'Exception', 'This node already has a parent node.'
		);
		$childNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->fixture);

		$childNode->setParent($this->fixture);
	}


	///////////////////////////////////
	// Tests concerning markAsVisible
	///////////////////////////////////

	public function test_markAsVisible_ForInvisibleNode_SetsVisibilityTrue() {
		$this->fixture->markAsVisible();

		$this->assertTrue(
			$this->fixture->isVisible()
		);
	}

	public function test_markAsVisible_ForVisibleNode_SetsVisibilityTrue() {
		$visibleNode = new tx_oelib_Visibility_Node(true);
		$visibleNode->markAsVisible();

		$this->assertTrue(
			$visibleNode->isVisible()
		);

		$visibleNode->__destruct();
	}

	public function test_markAsVisible_ForNodeWithParent_MarksParentAsVisible() {
		$childNode = new tx_oelib_Visibility_Node();
		$childNode->setParent($this->fixture);
		$childNode->markAsVisible();

		$this->assertTrue(
			$this->fixture->isVisible()
		);
	}

	public function test_markAsVisible_ForNodeWithParentAndGrandparent_MarksGrandparentNodeAsVisible() {
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