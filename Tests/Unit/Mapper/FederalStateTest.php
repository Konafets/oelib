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
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Mapper_FederalStateTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Mapper_FederalState
	 */
	private $subject = NULL;

	protected function setUp() {
		$this->subject = new tx_oelib_Mapper_FederalState();
	}

	/*
	 * Tests concerning find
	 */

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsFederalStateInstance() {
		self::assertInstanceOf(
			'Tx_Oelib_Model_FederalState',
			$this->subject->find(88)
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsRecordAsModel() {
		/** @var Tx_Oelib_Model_FederalState $model */
		$model = $this->subject->find(88);
		self::assertSame(
			'NW',
			$model->getIsoAlpha2ZoneCode()
		);
	}


	/**
	 * Tests concerning findByIsoAlpha2Code
	 *

	/**
	 * @test
	 */
	public function findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCodeWithIsoAlpha2CodeOfExistingRecordReturnsFederalStateInstance() {
		self::assertInstanceOf(
			'Tx_Oelib_Model_FederalState',
			$this->subject->findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCode('DE', 'NW')
		);
	}

	/**
	 * @test
	 */
	public function findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCodeWithIsoAlpha2CodeOfExistingRecordReturnsRecordAsModel() {
		self::assertSame(
			'NW',
			$this->subject->findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCode('DE', 'NW')->getIsoAlpha2ZoneCode()
		);
	}
}