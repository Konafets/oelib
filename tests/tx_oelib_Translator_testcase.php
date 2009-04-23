<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_oelib_Translator class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage oelib
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_Translator_testcase extends tx_phpunit_testcase {
	public function setUp() {
	}

	public function tearDown() {
	}


	/////////////////////////////////
	// Tests regarding translate().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function translateForInexistentLabelReturnsLabelKey() {
		$fixture = new tx_oelib_Translator('default', '', array());

		$this->assertEquals(
			'label_test',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function translateWithLanguageEnglishReturnsEnglishLabel() {
		$localizedLabels = array(
			'default' => array('label_test' => 'English'),
			'de' => array('label_test' => 'Deutsch'),
		);
		$fixture = new tx_oelib_Translator('default', '', $localizedLabels);

		$this->assertEquals(
			'English',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function translateWithLanguageGermanReturnsGermanLabel() {
		$localizedLabels = array(
			'default' => array('label_test' => 'English'),
			'de' => array('label_test' => 'Deutsch'),
		);
		$fixture = new tx_oelib_Translator('de', '', $localizedLabels);

		$this->assertEquals(
			'Deutsch',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function translateForLabelInexistentInGermanWithEmptyAlternativeLanguageWithLanguageGermanReturnsEnglishLabel() {
		$localizedLabels = array('default' => array('label_test' => 'English'));
		$fixture = new tx_oelib_Translator('de', '', $localizedLabels);

		$this->assertEquals(
			'English',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function translateForLabelInexistentInEnglishAndAlternativeLanguageGermanReturnsGermanLabel() {
		$localizedLabels = array('de' => array('label_test' => 'Deutsch'));
		$fixture = new tx_oelib_Translator('default', 'de', $localizedLabels);

		$this->assertEquals(
			'Deutsch',
			$fixture->translate('label_test')
		);

		$fixture->__destruct();
	}
}
?>