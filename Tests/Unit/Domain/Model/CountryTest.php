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
 * Testcase for the Tx_Oelib_Domain_Model_Country class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Domain_Model_CountryTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Oelib_Domain_Model_Country
	 */
	private $subject = NULL;

	public function setUp() {
		$this->subject = new Tx_Oelib_Domain_Model_Country();
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getLocalShortNameInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getLocalShortName()
		);
	}

	/**
	 * @test
	 */
	public function setLocalShortNameSetsLocalShortName() {
		$this->subject->setLocalShortName('Deutschland');

		$this->assertSame(
			'Deutschland',
			$this->subject->getLocalShortName()
		);
	}

	/**
	 * @test
	 */
	public function getLocalOfficialNameInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getLocalOfficialName()
		);
	}

	/**
	 * @test
	 */
	public function setLocalOfficialNameSetsLocalOfficialName() {
		$this->subject->setLocalOfficialName('Bundesrepublik Deutschland');

		$this->assertSame(
			'Bundesrepublik Deutschland',
			$this->subject->getLocalOfficialName()
		);
	}

	/**
	 * @test
	 */
	public function getEnglishShortNameInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getEnglishShortName()
		);
	}

	/**
	 * @test
	 */
	public function setEnglishShortNameSetsEnglishShortName() {
		$this->subject->setEnglishShortName('Germany');

		$this->assertSame(
			'Germany',
			$this->subject->getEnglishShortName()
		);
	}

	/**
	 * @test
	 */
	public function getEnglishOfficialNameInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getEnglishOfficialName()
		);
	}

	/**
	 * @test
	 */
	public function setEnglishOfficialNameSetsEnglishOfficialName() {
		$this->subject->setEnglishOfficialName('Federal Republic of Germany');

		$this->assertSame(
			'Federal Republic of Germany',
			$this->subject->getEnglishOfficialName()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlphaTwoCodeInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getIsoAlphaTwoCode()
		);
	}

	/**
	 * @test
	 */
	public function setIsoAlphaTwoCodeSetsIsoAlphaTwoCode() {
		$this->subject->setIsoAlphaTwoCode('GB');

		$this->assertSame(
			'GB',
			$this->subject->getIsoAlphaTwoCode()
		);
	}

	/**
	 * @test
	 */
	public function getIsoAlphaThreeCodeInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getIsoAlphaThreeCode()
		);
	}

	/**
	 * @test
	 */
	public function setIsoAlphaThreeCodeSetsIsoAlphaThreeCode() {
		$this->subject->setIsoAlphaThreeCode('DEU');

		$this->assertSame(
			'DEU',
			$this->subject->getIsoAlphaThreeCode()
		);
	}
}