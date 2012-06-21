<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2012 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_oelib_Translator class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_TranslatorTest extends tx_phpunit_testcase {
	/////////////////////////////////
	// Tests regarding translate().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function translateForInexistentLabelReturnsLabelKey() {
		$fixture = new tx_oelib_Translator('default', '', array());

		$this->assertSame(
			'label_test',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
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
		$fixture = new tx_oelib_Translator('default', '', $localizedLabels);

		$this->assertSame(
			'English',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
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
		$fixture = new tx_oelib_Translator('de', '', $localizedLabels);

		$this->assertSame(
			'Deutsch',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
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
		$fixture = new tx_oelib_Translator('de', '', $localizedLabels);

		$this->assertSame(
			'English',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
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
		$fixture = new tx_oelib_Translator('default', 'de', $localizedLabels);

		$this->assertSame(
			'Deutsch',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
	}
}
?>