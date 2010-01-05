<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the 'country mapper' class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_Mapper_Country_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Mapper_Country
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Mapper_Country();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	///////////////////////////
	// Tests concerning find.
	///////////////////////////

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsCountryInstance() {
		$this->assertTrue(
			$this->fixture->find(54) instanceof tx_oelib_Model_Country
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsRecordAsModel() {
		$this->assertEquals(
			'DE',
			$this->fixture->find(54)->getIsoAlpha2Code()
		);
	}


	/////////////////////////////////////////
	// Tests regarding findByIsoAlpha2Code.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsCountryInstance() {
		$this->assertTrue(
			$this->fixture->findByIsoAlpha2Code('DE')
				instanceof tx_oelib_Model_Country
		);
	}

	/**
	 * @test
	 */
	public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsRecordAsModel() {
		$this->assertEquals(
			'DE',
			$this->fixture->findByIsoAlpha2Code('DE')->getIsoAlpha2Code()
		);
	}


	/////////////////////////////////////////
	// Tests regarding findByIsoAlpha3Code.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsCountryInstance() {
		$this->assertTrue(
			$this->fixture->findByIsoAlpha3Code('DEU')
				instanceof tx_oelib_Model_Country
		);
	}

	/**
	 * @test
	 */
	public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsRecordAsModel() {
		$this->assertEquals(
			'DE',
			$this->fixture->findByIsoAlpha3Code('DEU')->getIsoAlpha2Code()
		);
	}
}
?>