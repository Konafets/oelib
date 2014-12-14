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
 * This class represents a node for a visibility tree.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_oelib_Visibility_Node {
	/**
	 * @var tx_oelib_Visibility_Node[] numeric array with all direct children of this node
	 */
	private $children = array();

	/**
	 * @var tx_oelib_Visibility_Node the parent node of this node
	 */
	private $parentNode = NULL;

	/**
	 * @var bool whether this node is visible
	 */
	private $isVisible;

	/**
	 * Constructor of this class.
	 *
	 * @param bool $isVisible whether this node should be initially visible
	 */
	public function __construct($isVisible = FALSE) {
		$this->isVisible = $isVisible;
	}

	/**
	 * Destructor of this class. Tries to free as much memory as possible.
	 */
	public function __destruct() {
		unset($this->parentNode, $this->children);
	}

	/**
	 * Adds a child to this node.
	 *
	 * @param tx_oelib_Visibility_Node $child the child to add to this node
	 *
	 * @return void
	 */
	public function addChild(tx_oelib_Visibility_Node $child) {
		$this->children[] = $child;
		$child->setParent($this);
	}

	/**
	 * Sets the parent node of this node.
	 *
	 * The parent can only be set once.
	 *
	 * @param tx_oelib_Visibility_Node $parentNode the parent node to add
	 *
	 * @return void
	 */
	public function setParent(tx_oelib_Visibility_Node $parentNode) {
		if ($this->parentNode instanceof tx_oelib_Visibility_Node) {
			throw new InvalidArgumentException('This node already has a parent node.', 1331488668);
		}

		$this->parentNode = $parentNode;
	}

	/**
	 * Returns the visibility status of this node.
	 *
	 * @return bool TRUE if this node is visible, FALSE otherwise
	 */
	public function isVisible() {
		return $this->isVisible;
	}

	/**
	 * Marks this node as visible and propagates the visibility recursively to
	 * the parent up to the root.
	 *
	 * @return void
	 */
	public function markAsVisible() {
		$this->isVisible = TRUE;
		if ($this->parentNode) {
			$this->parentNode->markAsVisible();
		}
	}

	/**
	 * Returns the children set for the current node.
	 *
	 * @return tx_oelib_Visibility_Node[] this node's children, will be empty if no children are set
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Returns the parent node set for this node.
	 *
	 * @return tx_oelib_Visibility_Node the parent node of this node, will be
	 *                                  empty if no parent was set
	 */
	public function getParent() {
		return $this->parentNode;
	}
}