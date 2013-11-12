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
class Tx_Oelib_Model_LanguageTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Model_Language
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Model_Language();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	////////////////////////////////////////////
	// Tests regarding getting the local name.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getLocalNameReturnsLocalNameOfGerman() {
		$this->fixture->setData(array('lg_name_local' => 'Deutsch'));

		$this->assertSame(
			'Deutsch',
			$this->fixture->getLocalName()
		);
	}

	/**
	 * @test
	 */
	public function getLocalNameReturnsLocalNameOfEnglish() {
		$this->fixture->setData(array('lg_name_local' => 'English'));

		$this->assertSame(
			'English',
			$this->fixture->getLocalName()
		);
	}


	//////////////////////////////////////////////////
	// Tests regarding getting the ISO alpha-2 code.
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfGerman() {
		$this->fixture->setData(array('lg_iso_2' => 'DE'));

		$this->assertSame(
			'DE',
			$this->fixture->getIsoAlpha2Code()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfEnglish() {
		$this->fixture->setData(array('lg_iso_2' => 'EN'));

		$this->assertSame(
			'EN',
			$this->fixture->getIsoAlpha2Code()
		);
	}


	////////////////////////////////
	// Tests concerning isReadOnly
	////////////////////////////////

	/**
	 * @test
	 */
	public function isReadOnlyIsTrue() {
		$this->assertTrue(
			$this->fixture->isReadOnly()
		);
	}
}
?>