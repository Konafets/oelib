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
 * This class represents a read-only model for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class Tx_Oelib_Tests_Unit_Fixtures_ReadOnlyModel extends Tx_Oelib_Model {
	/**
	 * @var boolean whether this model is read-only
	 */
	protected $readOnly = TRUE;

	/**
	 * Sets the "title" data item for this model.
	 *
	 * @param string $value
	 *        the value to set, may be empty
	 *
	 * @return void
	 */
	public function setTitle($value) {
		$this->setAsString('title', $value);
	}
}