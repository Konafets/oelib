<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_session.php');

/**
 * Class 'tx_oelib_fakeSession' for the 'oelib' extension.
 *
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
class tx_oelib_fakeSession extends tx_oelib_session {
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
		parent::__destruct();
	}

	/**
	 * Gets the value of the data item for the key $key.
	 *
	 * @param string the key of the data item to get, must not be empty
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
	 * @param string the key of the data item to get, must not be empty
	 * @param mixed the data for the key $key
	 */
	protected function set($key, $value) {
		$this->sessionData[$key] = $value;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_fakeSession.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_fakeSession.php']);
}
?>