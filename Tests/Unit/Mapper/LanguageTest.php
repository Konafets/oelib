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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Mapper_LanguageTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_oelib_Mapper_Language
	 */
	private $subject;

	protected function setUp() {
		$this->subject = new tx_oelib_Mapper_Language();
	}

	///////////////////////////
	// Tests concerning find.
	///////////////////////////

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsLanguageInstance() {
		$this->assertTrue(
			$this->subject->find(43) instanceof Tx_Oelib_Model_Language
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsRecordAsModel() {
		/** @var Tx_Oelib_Model_Language $model */
		$model = $this->subject->find(43);
		$this->assertSame(
			'DE',
			$model->getIsoAlpha2Code()
		);
	}


	/////////////////////////////////////////
	// Tests regarding findByIsoAlpha2Code.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsLanguageInstance() {
		$this->assertTrue(
			$this->subject->findByIsoAlpha2Code('DE')
				instanceof Tx_Oelib_Model_Language
		);
	}

	/**
	 * @test
	 */
	public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsRecordAsModel() {
		$this->assertSame(
			'DE',
			$this->subject->findByIsoAlpha2Code('DE')->getIsoAlpha2Code()
		);
	}
}