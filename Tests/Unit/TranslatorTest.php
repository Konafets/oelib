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