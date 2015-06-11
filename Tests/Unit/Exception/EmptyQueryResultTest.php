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
class Tx_Oelib_Tests_Unit_Exception_EmptyQueryResultTest extends Tx_Phpunit_TestCase {
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
		$databaseConnection = Tx_Oelib_Db::getDatabaseConnection();
		$this->savedDebugOutput = $databaseConnection->debugOutput;
		$this->savedStoreLastBuildQuery = $databaseConnection->store_lastBuiltQuery;

		$databaseConnection->debugOutput = FALSE;
		$databaseConnection->store_lastBuiltQuery = TRUE;
	}

	protected function tearDown() {
		$databaseConnection = Tx_Oelib_Db::getDatabaseConnection();
		$databaseConnection->debugOutput = $this->savedDebugOutput;
		$databaseConnection->store_lastBuiltQuery = $this->savedStoreLastBuildQuery;
	}

	/**
	 * @test
	 */
	public function messageAfterQueryWithLastQueryEnabledContainsLastQuery() {
		Tx_Oelib_Db::getDatabaseConnection()->exec_SELECTquery('title', 'tx_oelib_test', '');
		$subject = new tx_oelib_Exception_EmptyQueryResult();

		self::assertContains(
			'SELECT',
			$subject->getMessage()
		);
	}
}