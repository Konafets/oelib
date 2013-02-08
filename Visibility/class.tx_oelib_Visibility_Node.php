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
 * Class 'tx_oelib_Visibility_Node' for the 'oelib' extension.
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
	 * @var array numeric array with all direct children of this node
	 */
	private $children = array();

	/**
	 * @var tx_oelib_Visibility_Node the parent node of this node
	 */
	private $parentNode = NULL;

	/**
	 * @var boolean whether this node is visible
	 */
	private $isVisible;

	/**
	 * Constructor of this class.
	 *
	 * @param boolean $isVisible whether this node should be initially visible
	 */
	public function __construct($isVisible = FALSE) {
		$this->isVisible = $isVisible;
	}

	/**
	 * Destructor of this class. Tries to free as much memory as possible.
	 */
	public function __destruct() {
		if ($this->parentNode) {
			unset($this->parentNode);
		}

		foreach ($this->children as $key => $child) {
			$child->__destruct();
			unset($this->children[$key]);
		}
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
	 * @return boolean TRUE if this node is visible, FALSE otherwise
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
	 * @return array this node's children, will be empty if no children are set
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

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Visibility/class.tx_oelib_Visibility_Node.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Visibility/class.tx_oelib_Visibility_Node.php']);
}
?>