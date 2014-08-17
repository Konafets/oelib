<?php
/**
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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Model_LanguageTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Model_Language
	 */
	private $subject;

	public function setUp() {
		$this->subject = new Tx_Oelib_Model_Language();
	}

	public function tearDown() {
		unset($this->subject);
	}


	////////////////////////////////////////////
	// Tests regarding getting the local name.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getLocalNameReturnsLocalNameOfGerman() {
		$this->subject->setData(array('lg_name_local' => 'Deutsch'));

		$this->assertSame(
			'Deutsch',
			$this->subject->getLocalName()
		);
	}

	/**
	 * @test
	 */
	public function getLocalNameReturnsLocalNameOfEnglish() {
		$this->subject->setData(array('lg_name_local' => 'English'));

		$this->assertSame(
			'English',
			$this->subject->getLocalName()
		);
	}


	//////////////////////////////////////////////////
	// Tests regarding getting the ISO alpha-2 code.
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfGerman() {
		$this->subject->setData(array('lg_iso_2' => 'DE'));

		$this->assertSame(
			'DE',
			$this->subject->getIsoAlpha2Code()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfEnglish() {
		$this->subject->setData(array('lg_iso_2' => 'EN'));

		$this->assertSame(
			'EN',
			$this->subject->getIsoAlpha2Code()
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
			$this->subject->isReadOnly()
		);
	}
}