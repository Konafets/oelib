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
 * This class represents a fake session that doesn't use any real sessions,
 * thus not sending any HTTP headers.
 *
 * It is intended for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_FakeSession extends Tx_Oelib_Session {
	/**
	 * @var array the data for this session
	 */
	private $sessionData = array();

	/**
	 * The constructor.
	 *
	 * This constructor is public to allow direct instantiation of this class
	 * for the unit tests, also bypassing the check for a front end.
	 */
	public function __construct() {
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->sessionData);
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param string $key the key of the data item to get, must not be empty
	 *
	 * @return mixed the data for the key $key, will be an empty string
	 *               if the key has not been set yet
	 */
	protected function get($key) {
		if (!isset($this->sessionData[$key])) {
			return '';
		}

		return $this->sessionData[$key];
	}

	/**
	 * Sets the value of the data item for the key $key.
	 *
	 * @param string $key the key of the data item to get, must not be empty
	 * @param mixed $value the data for the key $key
	 *
	 * @return void
	 */
	protected function set($key, $value) {
		$this->sessionData[$key] = $value;
	}
}