<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_Exception_Database class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Exception_Database_testcase extends tx_phpunit_testcase {
	public function testMessageContainsErrorMessage() {
		$fixture = new tx_oelib_Exception_Database();

		$this->assertContains(
			DATABASE_QUERY_ERROR,
			$fixture->getMessage()
		);
	}

	public function testMessageForInvalidQueryContainsErrorMessageFromDatabase() {
		// silences any error messages
		$GLOBALS['TYPO3_DB']->debugOutput = false;
		$GLOBALS['TYPO3_DB']->exec_SELECTquery('asdf', 'tx_oelib_test', '');

		$fixture = new tx_oelib_Exception_Database();
		$GLOBALS['TYPO3_DB']->debugOutput = true;

		$this->assertContains(
			'asdf',
			$fixture->getMessage()
		);
	}

	public function testMessageForInvalidQueryWithLastQueryEnabledContainsLastQuery() {
		// silences any error messages
		$GLOBALS['TYPO3_DB']->debugOutput = false;
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
		$GLOBALS['TYPO3_DB']->exec_SELECTquery('asdf', 'tx_oelib_test', '');

		$fixture = new tx_oelib_Exception_Database();
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = false;
		$GLOBALS['TYPO3_DB']->debugOutput = true;

		$this->assertContains(
			'SELECT',
			$fixture->getMessage()
		);

	}
}
?>