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
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Exception_DatabaseTest extends Tx_Phpunit_TestCase {
	/**
	 * @var bool the saved content of $GLOBALS['TYPO3_DB']->debugOutput
	 */
	private $savedDebugOutput;

	/**
	 * @var bool the saved content of
	 *              $GLOBALS['TYPO3_DB']->store_lastBuiltQuery
	 */
	private $savedStoreLastBuildQuery;

	protected function setUp() {
		$this->savedDebugOutput = $GLOBALS['TYPO3_DB']->debugOutput;
		$this->savedStoreLastBuildQuery = $GLOBALS['TYPO3_DB']->store_lastBuiltQuery;

		$GLOBALS['TYPO3_DB']->debugOutput = FALSE;
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = TRUE;
	}

	protected function tearDown() {
		$GLOBALS['TYPO3_DB']->debugOutput = $this->savedDebugOutput;
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = $this->savedStoreLastBuildQuery;
	}

	/**
	 * @test
	 */
	public function messageForInvalidQueryContainsErrorMessageFromDatabase() {
		/** @var t3lib_DB $databaseAdapter */
		$databaseAdapter =$GLOBALS['TYPO3_DB'];
		$databaseAdapter->exec_SELECTquery('asdf', 'tx_oelib_test', '');
		$subject = new tx_oelib_Exception_Database();

		$this->assertContains(
			'asdf',
			$subject->getMessage()
		);
	}

	/**
	 * @test
	 */
	public function messageForInvalidQueryWithLastQueryEnabledContainsLastQuery() {
		/** @var t3lib_DB $databaseAdapter */
		$databaseAdapter =$GLOBALS['TYPO3_DB'];
		$databaseAdapter->exec_SELECTquery('asdf', 'tx_oelib_test', '');
		$subject = new tx_oelib_Exception_Database();

		$this->assertContains(
			'SELECT',
			$subject->getMessage()
		);
	}
}