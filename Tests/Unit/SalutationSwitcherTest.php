<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Benjamin Schulte <benj@minschulte.de>
 */
class Tx_Oelib_SalutationSwitcherTest extends Tx_Phpunit_TestCase {
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

		$this->fixture = new Tx_Oelib_TestingSalutationSwitcher(array());
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	////////////////////////////////////
	// Tests for setting the language.
	////////////////////////////////////

	/**
	 * @test
	 */
	public function initialLanguage() {
		$this->assertSame(
			'default', $this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setLanguageDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'default', $this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setLanguageDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'de', $this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setLanguageDefaultEmpty() {
		$this->fixture->setLanguage('');
		$this->assertSame(
			'', $this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function initialFallbackLanguage() {
		$this->assertSame(
			'default', $this->fixture->getFallbackLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setFallbackLanguageDefault() {
		$this->fixture->setFallbackLanguage('default');
		$this->assertSame(
			'default', $this->fixture->getFallbackLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setFallbackLanguageDe() {
		$this->fixture->setFallbackLanguage('de');
		$this->assertSame(
			'de', $this->fixture->getFallbackLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setFallbackLanguageEmpty() {
		$this->fixture->setFallbackLanguage('');
		$this->assertSame(
			'', $this->fixture->getFallbackLanguage()
		);
	}


	///////////////////////////////////////////
	// Tests for setting the salutation modes.
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function setSalutationFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'formal', $this->fixture->getSalutationMode()
		);
	}

	/**
	 * @test
	 */
	public function setSalutationInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'informal', $this->fixture->getSalutationMode()
		);
	}


	//////////////////////////////////////
	// Tests for empty keys or languages.
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function emptyKeyDefault() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setLanguage('default');
		$this->fixture->translate('');
	}

	/**
	 * @test
	 */
	public function emptyKeyDe() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->setLanguage('de');
		$this->fixture->translate('');
	}

	/**
	 * @test
	 */
	public function noLanguageAtAllWithKnownKey() {
		$this->fixture->setLanguage('');
		$this->fixture->setFallbackLanguage('');
		$this->assertSame(
			'in_both', $this->fixture->translate('in_both')
		);
	}

	/**
	 * @test
	 */
	public function noLanguageAtAllWithUnknownKey() {
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

	/**
	 * @test
	 */
	public function translateWithoutLanguageOnlyInDefault() {
		$this->assertSame(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function translateWithoutLanguageInBoth() {
		$this->assertSame(
			'in both languages', $this->fixture->translate('in_both')
		);
	}

	/**
	 * @test
	 */
	public function missingKeyDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'missing_key', $this->fixture->translate('missing_key')
		);
	}

	/**
	 * @test
	 */
	public function missingKeyDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'missing_key', $this->fixture->translate('missing_key')
		);
	}

	/**
	 * @test
	 */
	public function onlyInDefaultUsingDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function onlyInDefaultUsingNothing() {
		$this->assertSame(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function onlyInDefaultUsingDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function inBothUsingDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'in both languages', $this->fixture->translate('in_both')
		);
	}

	/**
	 * @test
	 */
	public function inBothUsingDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'in beiden Sprachen', $this->fixture->translate('in_both')
		);
	}

	/**
	 * @test
	 */
	public function emptyStringDefault() {
		$this->fixture->setLanguage('default');
		$this->assertSame(
			'', $this->fixture->translate('empty_string_in_default')
		);
	}

	/**
	 * @test
	 */
	public function emptyStringDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'', $this->fixture->translate('empty_string_in_default')
		);
	}

	/**
	 * @test
	 */
	public function fallbackToDefault() {
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

	/**
	 * @test
	 */
	public function fallbackToDefaultFromEmptyLanguage() {
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

	/**
	 * @test
	 */
	public function fallbackToDefaultFromDe() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'only in french',
			$this->fixture->translate('only_in_french')
		);
	}


	/////////////////////////////////////////////////////////////////////////
	// Tests for translating with salutation modes in the default language.
	/////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function formalOnly() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'only formal',
			$this->fixture->translate('formal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function informalOnly() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'only informal',
			$this->fixture->translate('informal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingNothing() {
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInvalid() {
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'informal with normal, informal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingNothing() {
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingInvalid() {
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'both without normal, informal',
			$this->fixture->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingNothing() {
		$this->assertSame(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInvalid() {
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

	/**
	 * @test
	 */
	public function formalOnlyNoGermanLabel() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'only formal',
			$this->fixture->translate('formal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function informalOnlyNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'only informal',
			$this->fixture->translate('informal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInformalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingNothingNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'informal with normal, informal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingFormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingNothingNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingFormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInformalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'both without normal, informal',
			$this->fixture->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingNothingNoGermanLabels() {
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

	/**
	 * @test
	 */
	public function formalOnlyWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'de only formal',
			$this->fixture->translate('de_formal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function informalOnlyWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'de only informal',
			$this->fixture->translate('de_informal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInformalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingNothingWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInvalidWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'de informal with normal, informal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingFormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'de informal with normal, normal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingNothingWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'de informal with normal, normal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingInvalidWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('foobar');
		$this->assertSame(
			'de informal with normal, normal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingFormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertSame(
			'de both without normal, formal',
			$this->fixture->translate('de_both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInformalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertSame(
			'de both without normal, informal',
			$this->fixture->translate('de_both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingNothingWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertSame(
			'de both without normal, formal',
			$this->fixture->translate('de_both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInvalidWithGermanLabels() {
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

	/**
	 * @test
	 */
	public function htmlSpecialCharsWithNoOption() {
		$this->assertSame(
			'a&o',
			$this->fixture->translate('htmlspecialchars')
		);
	}

	/**
	 * @test
	 */
	public function htmlSpecialCharsWithFalse() {
		$this->assertSame(
			'a&o',
			$this->fixture->translate('htmlspecialchars', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function htmlSpecialCharsWithTrue() {
		$this->assertSame(
			'a&amp;o',
			$this->fixture->translate('htmlspecialchars', TRUE)
		);
	}

	/**
	 * @test
	 */
	public function piGetLlHtmlSpecialCharsWithNoOption() {
		$this->assertSame(
			'a&o',
			$this->fixture->pi_getLL('htmlspecialchars')
		);
	}

	/**
	 * @test
	 */
	public function piGetLlHtmlSpecialCharsWithFalse() {
		$this->assertSame(
			'a&o',
			$this->fixture->pi_getLL('htmlspecialchars', '', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function piGetLlHtmlSpecialCharsWithTrue() {
		$this->assertSame(
			'a&amp;o',
			$this->fixture->pi_getLL('htmlspecialchars', '', TRUE)
		);
	}
}
?>