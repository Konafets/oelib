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
 * @subpackage oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_TranslatorTest extends Tx_Phpunit_TestCase {
	/////////////////////////////////
	// Tests regarding translate().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function translateForInexistentLabelReturnsLabelKey() {
		$subject = new Tx_Oelib_Translator('default', '', array());

		$this->assertSame(
			'label_test',
			$subject->translate('label_test')
		);
	}

	/**
	 * @test
	 */
	public function translateWithLanguageEnglishReturnsEnglishLabel() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			$localizedLabels = array(
				'default' => array('label_test' => array(0 => array('source' => 'English', 'target' => 'English'))),
				'de' => array('label_test' => array(0 => array('source' => 'English', 'target' => 'Deutsch'))),
			);
		} else {
			$localizedLabels = array(
				'default' => array('label_test' => 'English'),
				'de' => array('label_test' => 'Deutsch'),
			);
		}
		$subject = new Tx_Oelib_Translator('default', '', $localizedLabels);

		$this->assertSame(
			'English',
			$subject->translate('label_test')
		);
	}

	/**
	 * @test
	 */
	public function translateWithLanguageGermanReturnsGermanLabel() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			$localizedLabels = array(
				'default' => array('label_test' => array(0 => array('source' => 'English', 'target' => 'English'))),
				'de' => array('label_test' => array(0 => array('source' => 'English', 'target' => 'Deutsch'))),
			);
		} else {
			$localizedLabels = array(
				'default' => array('label_test' => 'English'),
				'de' => array('label_test' => 'Deutsch'),
			);
		}
		$subject = new Tx_Oelib_Translator('de', '', $localizedLabels);

		$this->assertSame(
			'Deutsch',
			$subject->translate('label_test')
		);
	}

	/**
	 * @test
	 */
	public function translateForLabelInexistentInGermanWithEmptyAlternativeLanguageWithLanguageGermanReturnsEnglishLabel() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			$localizedLabels = array(
				'default' => array('label_test' => array(0 => array('source' => 'English', 'target' => 'English'))),
			);
		} else {
			$localizedLabels = array(
				'default' => array('label_test' => 'English'),
			);
		}
		$subject = new Tx_Oelib_Translator('de', '', $localizedLabels);

		$this->assertSame(
			'English',
			$subject->translate('label_test')
		);
	}

	/**
	 * @test
	 */
	public function translateForLabelInexistentInEnglishAndAlternativeLanguageGermanReturnsGermanLabel() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			$localizedLabels = array(
				'de' => array('label_test' => array(0 => array('source' => 'English', 'target' => 'Deutsch'))),
			);
		} else {
			$localizedLabels = array(
				'de' => array('label_test' => 'Deutsch'),
			);
		}
		$subject = new Tx_Oelib_Translator('default', 'de', $localizedLabels);

		$this->assertSame(
			'Deutsch',
			$subject->translate('label_test')
		);
	}
}