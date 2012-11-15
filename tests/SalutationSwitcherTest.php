<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2012 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_salutationswitcher class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Benjamin Schulte <benj@minschulte.de>
 */
class tx_oelib_SalutationSwitcherTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_salutationswitcherchild
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');
		$this->testingFramework->createFakeFrontEnd();

		$this->fixture = new tx_oelib_salutationswitcherchild(array());
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	////////////////////////////////////
	// Tests for setting the language.
	////////////////////////////////////

	public function testInitialLanguage() {
		$this->assertSame(
			'default', $this->fixture->getLanguage()
		);
	}

	public function testSetLanguageDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'default', $this->fixture->getLanguage()
		);
	}

	public function testSetLanguageDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'de', $this->fixture->getLanguage()
		);
	}

	public function testSetLanguageDefaultEmpty() {
		$this->fixture->setLanguage('');
		$this->assertSame(
			'', $this->fixture->getLanguage()
		);
	}

	public function testInitialFallbackLanguage() {
		$this->assertSame(
			'default', $this->fixture->getFallbackLanguage()
		);
	}

	public function testSetFallbackLanguageDefault() {
		$this->fixture->setFallbackLanguage('default');
		$this->assertSame(
			'default', $this->fixture->getFallbackLanguage()
		);
	}

	public function testSetFallbackLanguageDe() {
		$this->fixture->setFallbackLanguage('de');
		$this->assertSame(
			'de', $this->fixture->getFallbackLanguage()
		);
	}

	public function testSetFallbackLanguageEmpty() {
		$this->fixture->setFallbackLanguage('');
		$this->assertSame(
			'', $this->fixture->getFallbackLanguage()
		);
	}


	///////////////////////////////////////////
	// Tests for setting the salutation modes.
	///////////////////////////////////////////

	public function testSetSalutationFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'formal', $this->fixture->getSalutationMode()
		);
	}

	public function testSetSalutationInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'informal', $this->fixture->getSalutationMode()
		);
	}


	//////////////////////////////////////
	// Tests for empty keys or languages.
	//////////////////////////////////////

	public function testEmptyKeyDefault() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setLanguage('default');
		$this->fixture->translate('');
	}

	public function testEmptyKeyDe() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setLanguage('de');
		$this->fixture->translate('');
	}

	public function testNoLanguageAtAllWithKnownKey() {
		$this->fixture->setLanguage('');
		$this->fixture->setFallbackLanguage('');
		$this->assertSame(
			'in_both', $this->fixture->translate('in_both')
		);
	}

	public function testNoLanguageAtAllWithUnknownKey() {
		$this->fixture->setLanguage('');
		$this->fixture->setFallbackLanguage('');
		$this->assertSame(
			'missing_key', $this->fixture->translate('missing_key')
		);
	}

	/**
	 * @test
	 */
	public function translateForMissingLabelAndEmptyDefaultLanguageKeyReturnsLabelKey() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			$this->markTestSkipped('This test is skipped because the old behaviour is only part of TYPO3 < 4.6.');
		}

		$this->fixture->setLanguage('de');
		$this->fixture->setFallbackLanguage('');

		$this->assertSame(
			'only_in_default',
			$this->fixture->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function translateForMissingLabelAndEmptyDefaultLanguageKeyStillUsesDefaultAsLanguageKey() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 4006000) {
			$this->markTestSkipped('This test is skipped because the new behaviour is only part of TYPO3 >= 4.6.');
		}

		$this->fixture->setLanguage('de');
		$this->fixture->setFallbackLanguage('');

		$this->assertSame(
			'only in default',
			$this->fixture->translate('only_in_default')
		);
	}


	///////////////////////////////////////////////////////////
	// Tests for translating without setting salutation modes.
	///////////////////////////////////////////////////////////

	public function testTranslateWithoutLanguageOnlyInDefault() {
		$this->assertSame(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	public function testTranslateWithoutLanguageInBoth() {
		$this->assertSame(
			'in both languages', $this->fixture->translate('in_both')
		);
	}

	public function testMissingKeyDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'missing_key', $this->fixture->translate('missing_key')
		);
	}

	public function testMissingKeyDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'missing_key', $this->fixture->translate('missing_key')
		);
	}

	public function testOnlyInDefaultUsingDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	public function testOnlyInDefaultUsingNothing() {
		$this->assertSame(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	public function testOnlyInDefaultUsingDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	public function testInBothUsingDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'in both languages', $this->fixture->translate('in_both')
		);
	}

	public function testInBothUsingDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'in beiden Sprachen', $this->fixture->translate('in_both')
		);
	}

	public function testEmptyStringDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'', $this->fixture->translate('empty_string_in_default')
		);
	}

	public function testEmptyStringDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'', $this->fixture->translate('empty_string_in_default')
		);
	}

	public function testFallbackToDefault() {
		$this->fixture->setLanguage('xy');
		$this->assertSame(
			'default_not_fallback default',
			$this->fixture->translate('default_not_fallback')
		);
	}

	/**
	 * @test
	 */
	public function translateForMissingLanguageAndGermanFallbackLanguageReturnsGermanTranslation() {
		$this->fixture->setLanguage('xy');
		$this->fixture->setFallbackLanguage('de');

		$this->assertSame(
			'default_not_fallback de',
			$this->fixture->translate('default_not_fallback')
		);
	}

	public function testFallbackToDefaultFromEmptyLanguage() {
		$this->fixture->setLanguage('');
		$this->fixture->setFallbackLanguage('default');
		$this->assertSame(
			'default_not_fallback default',
			$this->fixture->translate('default_not_fallback')
		);
	}

	/**
	 * @test
	 */
	public function translateForEmptyLanguageAndGermanFallbackLanguageReturnsGermanTranslation() {
		$this->fixture->setLanguage('');
		$this->fixture->setFallbackLanguage('de');

		$this->assertSame(
			'default_not_fallback de',
			$this->fixture->translate('default_not_fallback')
		);
	}

	/**
	 * @test
	 */
	public function translateForGermanLanguageAndFrenchFallbackLanguageReturnsFrenchTranslation() {
		$this->fixture->setLanguage('de');
		$this->fixture->setFallbackLanguage('fr');

		$this->assertSame(
			'only in french fr',
			$this->fixture->translate('only_in_french')
		);
	}

	public function testFallbackToDefaultFromDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'only in french',
			$this->fixture->translate('only_in_french')
		);
	}


	/////////////////////////////////////////////////////////////////////////
	// Tests for translating with salutation modes in the default language.
	/////////////////////////////////////////////////////////////////////////

	public function testFormalOnly() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'only formal',
			$this->fixture->translate('formal_string_only')
		);
	}

	public function testInformalOnly() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'only informal',
			$this->fixture->translate('informal_string_only')
		);
	}

	public function testFormalWithNormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingNothing() {
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInvalid() {
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testInformalWithNormal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'informal with normal, informal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingNothing() {
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingInvalid() {
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testBothWithoutNormalTryingFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'both without normal, informal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingNothing() {
		$this->assertSame(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInvalid() {
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}


	//////////////////////////////////////////////////////////////////////
	// Tests for translating with salutation modes in the German, always
	// falling back to the default language as the corresponding German
	// labels are missing.
	//////////////////////////////////////////////////////////////////////

	public function testFormalOnlyNoGermanLabel() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'only formal',
			$this->fixture->translate('formal_string_only')
		);
	}

	public function testInformalOnlyNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'only informal',
			$this->fixture->translate('informal_string_only')
		);
	}

	public function testFormalWithNormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInformalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingNothingNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testInformalWithNormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'informal with normal, informal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingFormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingNothingNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testBothWithoutNormalTryingFormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInformalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'both without normal, informal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingNothingNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}


	//////////////////////////////////////////////////////////////////
	// Tests for translating with salutation modes in the German for
	// existing labels.
	//////////////////////////////////////////////////////////////////

	public function testFormalOnlyWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'de only formal',
			$this->fixture->translate('de_formal_string_only')
		);
	}

	public function testInformalOnlyWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'de only informal',
			$this->fixture->translate('de_informal_string_only')
		);
	}

	public function testFormalWithNormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInformalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingNothingWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInvalidWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	public function testInformalWithNormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'de informal with normal, informal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingFormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'de informal with normal, normal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingNothingWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'de informal with normal, normal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingInvalidWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'de informal with normal, normal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	public function testBothWithoutNormalTryingFormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'de both without normal, formal',
			$this->fixture->translate('de_both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInformalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'de both without normal, informal',
			$this->fixture->translate('de_both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingNothingWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'de both without normal, formal',
			$this->fixture->translate('de_both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInvalidWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'de both without normal, formal',
			$this->fixture->translate('de_both_without_normal')
		);
	}


	/////////////////////////////////////////////
	// Tests for the useHtmlSpecialchars option.
	/////////////////////////////////////////////

	public function testHtmlSpecialCharsWithNoOption() {
		$this->assertSame(
			'a&o',
			$this->fixture->translate('htmlspecialchars')
		);
	}

	public function testHtmlSpecialCharsWithFalse() {
		$this->assertSame(
			'a&o',
			$this->fixture->translate('htmlspecialchars', FALSE)
		);
	}

	public function testHtmlSpecialCharsWithTrue() {
		$this->assertSame(
			'a&amp;o',
			$this->fixture->translate('htmlspecialchars', TRUE)
		);
	}

	public function testPiGetLlHtmlSpecialCharsWithNoOption() {
		$this->assertSame(
			'a&o',
			$this->fixture->pi_getLL('htmlspecialchars')
		);
	}

	public function testPiGetLlHtmlSpecialCharsWithFalse() {
		$this->assertSame(
			'a&o',
			$this->fixture->pi_getLL('htmlspecialchars', '', FALSE)
		);
	}

	public function testPiGetLlHtmlSpecialCharsWithTrue() {
		$this->assertSame(
			'a&amp;o',
			$this->fixture->pi_getLL('htmlspecialchars', '', TRUE)
		);
	}
}
?>