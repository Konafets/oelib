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
 * Class 'tx_oelib_Visibility_Tree' for the 'oelib' extension.
 *
 * This class represents a visbility tree.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_oelib_Visibility_Tree {
	/**
	 * @var array all nodes within the tree referenced by their keys
	 */
	private $nodes = array();

	/**
	 * @var tx_oelib_Visibility_Node the root node of the tree
	 */
	private $rootNode = NULL;

	/**
	 * Initializes the tree structure.
	 *
	 * Example for a tree array:
	 *  array(ParentNode => array(
	 *   ChildNode1 => TRUE,
	 *   ChildNode2 => array(
	 *     GrandChildNode1 => TRUE,
	 *     GrandChildNode2 => FALSE
	 *   ),
	 *  ));
	 * If an array element has the value TRUE it will be marked as visible, if
	 * it has the value FALSE it will be invisible.
	 * These elements represent leaves in the visibility tree.
	 *
	 * @param array $treeStructure the tree structure in a nested array, may be empty
	 */
	public function __construct(array $treeStructure) {
		$this->rootNode = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Node'
		);

		$this->buildTreeFromArray($treeStructure, $this->rootNode);
	}

	/**
	 * Destructs the tree structure.
	 */
	public function __destruct() {
		foreach (array_keys($this->nodes) as $nodeKey) {
			unset($this->nodes[$nodeKey]);
		}

		if ($this->rootNode) {
			$this->rootNode->__destruct();
			unset($this->rootNode);
		}
	}

	/**
	 * Builds the node tree from the given structure.
	 *
	 * @param array $treeStructure
	 *        the tree structure as array, may be empty
	 * @param tx_oelib_Visibility_Node $parentNode
	 *        the parent node for the current key
	 *
	 * @return void
	 */
	private function buildTreeFromArray(
		array $treeStructure, tx_oelib_Visibility_Node $parentNode
	) {
		foreach ($treeStructure as $nodeKey => $nodeContents) {
			$childNode = tx_oelib_ObjectFactory::make(
				'tx_oelib_Visibility_Node'
			);
			$parentNode->addChild($childNode);

			if (is_array($nodeContents)) {
				$this->buildTreeFromArray(
					$nodeContents, $childNode
				);
			} elseif ($nodeContents === TRUE) {
				$childNode->markAsVisible();
			}

			$this->nodes[$nodeKey] = $childNode;
		}
	}

	/**
	 * Creates a numeric array of all subparts that still are hidden.
	 *
	 * The output of this function can be used for
	 * tx_oelib_Template::hideSubpartsArray.
	 *
	 * @return array the subparts which are hidden, will be empty if no elements
	 *               are hidden
	 */
	public function getKeysOfHiddenSubparts() {
		$keysToHide = array();

		foreach ($this->nodes as $key => $node) {
			if (!$node->isVisible()) {
				$keysToHide[] = $key;
			}
		}

		return $keysToHide;
	}

	/**
	 * Returns the root node.
	 *
	 * @return tx_oelib_Visibility_Node the root node
	 */
	public function getRootNode() {
		return $this->rootNode;
	}

	/**
	 * Makes nodes in the tree visible.
	 *
	 * @param array $nodeKeys
	 *        the keys of the visible nodes, may be empty
	 *
	 * @return void
	 */
	public function makeNodesVisible(array $nodeKeys) {
		foreach ($nodeKeys as $nodeKey) {
			if (isset($this->nodes[$nodeKey])) {
				$this->nodes[$nodeKey]->markAsVisible();
			}
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Visibility/class.tx_oelib_Visibility_Tree.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Visibility/class.tx_oelib_Visibility_Tree.php']);
}
?>