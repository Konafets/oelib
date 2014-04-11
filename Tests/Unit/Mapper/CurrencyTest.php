<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon (mail@niels-pardon.de)
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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Mapper_CurrencyTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Mapper_Currency
	 */
	private $subject;

	public function setUp() {
		$this->subject = new tx_oelib_Mapper_Currency();
	}

	public function tearDown() {
		unset($this->subject);
	}


	///////////////////////////
	// Tests concerning find.
	///////////////////////////

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsCurrencyInstance() {
		$this->assertTrue(
			$this->subject->find(49) instanceof Tx_Oelib_Model_Currency
		);
	}


	/////////////////////////////////////////
	// Tests regarding findByIsoAlpha3Code.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsCurrencyInstance() {
		$this->assertTrue(
			$this->subject->findByIsoAlpha3Code('EUR')
				instanceof Tx_Oelib_Model_Currency
		);
	}

	/**
	 * @test
	 */
	public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsRecordAsModel() {
		$this->assertSame(
			49,
			$this->subject->findByIsoAlpha3Code('EUR')->getUid()
		);
	}
}