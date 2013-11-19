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
	 * @var Tx_Oelib_SalutationSwitcherchild
	 */
	private $subject;
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');
		$this->testingFramework->createFakeFrontEnd();

		$this->subject = new Tx_Oelib_TestingSalutationSwitcher(array());
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->subject->__destruct();
		unset($this->subject, $this->testingFramework);
	}


	////////////////////////////////////
	// Tests for setting the language.
	////////////////////////////////////

	/**
	 * @test
	 */
	public function initialLanguage() {
		$this->assertSame(
			'default', $this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setLanguageDefault() {
		$this->subject->setLanguage('default');
		$this->assertSame(
			'default', $this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setLanguageDe() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'de', $this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setLanguageDefaultEmpty() {
		$this->subject->setLanguage('');
		$this->assertSame(
			'', $this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function initialFallbackLanguage() {
		$this->assertSame(
			'default', $this->subject->getFallbackLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setFallbackLanguageDefault() {
		$this->subject->setFallbackLanguage('default');
		$this->assertSame(
			'default', $this->subject->getFallbackLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setFallbackLanguageDe() {
		$this->subject->setFallbackLanguage('de');
		$this->assertSame(
			'de', $this->subject->getFallbackLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setFallbackLanguageEmpty() {
		$this->subject->setFallbackLanguage('');
		$this->assertSame(
			'', $this->subject->getFallbackLanguage()
		);
	}


	///////////////////////////////////////////
	// Tests for setting the salutation modes.
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function setSalutationFormal() {
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'formal', $this->subject->getSalutationMode()
		);
	}

	/**
	 * @test
	 */
	public function setSalutationInformal() {
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'informal', $this->subject->getSalutationMode()
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

		$this->subject->setLanguage('default');
		$this->subject->translate('');
	}

	/**
	 * @test
	 */
	public function emptyKeyDe() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->subject->setLanguage('de');
		$this->subject->translate('');
	}

	/**
	 * @test
	 */
	public function noLanguageAtAllWithKnownKey() {
		$this->subject->setLanguage('');
		$this->subject->setFallbackLanguage('');
		$this->assertSame(
			'in_both', $this->subject->translate('in_both')
		);
	}

	/**
	 * @test
	 */
	public function noLanguageAtAllWithUnknownKey() {
		$this->subject->setLanguage('');
		$this->subject->setFallbackLanguage('');
		$this->assertSame(
			'missing_key', $this->subject->translate('missing_key')
		);
	}

	/**
	 * @test
	 */
	public function translateForMissingLabelAndEmptyDefaultLanguageKeyReturnsLabelKey() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			$this->markTestSkipped('This test is skipped because the old behaviour is only part of TYPO3 < 4.6.');
		}

		$this->subject->setLanguage('de');
		$this->subject->setFallbackLanguage('');

		$this->assertSame(
			'only_in_default',
			$this->subject->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function translateForMissingLabelAndEmptyDefaultLanguageKeyStillUsesDefaultAsLanguageKey() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 4006000) {
			$this->markTestSkipped('This test is skipped because the new behaviour is only part of TYPO3 >= 4.6.');
		}

		$this->subject->setLanguage('de');
		$this->subject->setFallbackLanguage('');

		$this->assertSame(
			'only in default',
			$this->subject->translate('only_in_default')
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
			'only in default', $this->subject->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function translateWithoutLanguageInBoth() {
		$this->assertSame(
			'in both languages', $this->subject->translate('in_both')
		);
	}

	/**
	 * @test
	 */
	public function missingKeyDefault() {
		$this->subject->setLanguage('default');
		$this->assertSame(
			'missing_key', $this->subject->translate('missing_key')
		);
	}

	/**
	 * @test
	 */
	public function missingKeyDe() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'missing_key', $this->subject->translate('missing_key')
		);
	}

	/**
	 * @test
	 */
	public function onlyInDefaultUsingDefault() {
		$this->subject->setLanguage('default');
		$this->assertSame(
			'only in default', $this->subject->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function onlyInDefaultUsingNothing() {
		$this->assertSame(
			'only in default', $this->subject->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function onlyInDefaultUsingDe() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'only in default', $this->subject->translate('only_in_default')
		);
	}

	/**
	 * @test
	 */
	public function inBothUsingDefault() {
		$this->subject->setLanguage('default');
		$this->assertSame(
			'in both languages', $this->subject->translate('in_both')
		);
	}

	/**
	 * @test
	 */
	public function inBothUsingDe() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'in beiden Sprachen', $this->subject->translate('in_both')
		);
	}

	/**
	 * @test
	 */
	public function emptyStringDefault() {
		$this->subject->setLanguage('default');
		$this->assertSame(
			'', $this->subject->translate('empty_string_in_default')
		);
	}

	/**
	 * @test
	 */
	public function emptyStringDe() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'', $this->subject->translate('empty_string_in_default')
		);
	}

	/**
	 * @test
	 */
	public function fallbackToDefault() {
		$this->subject->setLanguage('xy');
		$this->assertSame(
			'default_not_fallback default',
			$this->subject->translate('default_not_fallback')
		);
	}

	/**
	 * @test
	 */
	public function translateForMissingLanguageAndGermanFallbackLanguageReturnsGermanTranslation() {
		$this->subject->setLanguage('xy');
		$this->subject->setFallbackLanguage('de');

		$this->assertSame(
			'default_not_fallback de',
			$this->subject->translate('default_not_fallback')
		);
	}

	/**
	 * @test
	 */
	public function fallbackToDefaultFromEmptyLanguage() {
		$this->subject->setLanguage('');
		$this->subject->setFallbackLanguage('default');
		$this->assertSame(
			'default_not_fallback default',
			$this->subject->translate('default_not_fallback')
		);
	}

	/**
	 * @test
	 */
	public function translateForEmptyLanguageAndGermanFallbackLanguageReturnsGermanTranslation() {
		$this->subject->setLanguage('');
		$this->subject->setFallbackLanguage('de');

		$this->assertSame(
			'default_not_fallback de',
			$this->subject->translate('default_not_fallback')
		);
	}

	/**
	 * @test
	 */
	public function translateForGermanLanguageAndFrenchFallbackLanguageReturnsFrenchTranslation() {
		$this->subject->setLanguage('de');
		$this->subject->setFallbackLanguage('fr');

		$this->assertSame(
			'only in french fr',
			$this->subject->translate('only_in_french')
		);
	}

	/**
	 * @test
	 */
	public function fallbackToDefaultFromDe() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'only in french',
			$this->subject->translate('only_in_french')
		);
	}


	/////////////////////////////////////////////////////////////////////////
	// Tests for translating with salutation modes in the default language.
	/////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function formalOnly() {
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'only formal',
			$this->subject->translate('formal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function informalOnly() {
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'only informal',
			$this->subject->translate('informal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormal() {
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'formal with normal, formal',
			$this->subject->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInformal() {
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'formal with normal, formal',
			$this->subject->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingNothing() {
		$this->assertSame(
			'formal with normal, formal',
			$this->subject->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInvalid() {
		$this->subject->setSalutationMode('foobar');
		$this->assertSame(
			'formal with normal, formal',
			$this->subject->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormal() {
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'informal with normal, informal',
			$this->subject->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingFormal() {
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'informal with normal, normal',
			$this->subject->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingNothing() {
		$this->assertSame(
			'informal with normal, normal',
			$this->subject->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingInvalid() {
		$this->subject->setSalutationMode('foobar');
		$this->assertSame(
			'informal with normal, normal',
			$this->subject->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingFormal() {
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'both without normal, formal',
			$this->subject->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInformal() {
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'both without normal, informal',
			$this->subject->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingNothing() {
		$this->assertSame(
			'both without normal, formal',
			$this->subject->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInvalid() {
		$this->subject->setSalutationMode('foobar');
		$this->assertSame(
			'both without normal, formal',
			$this->subject->translate('both_without_normal')
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
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'only formal',
			$this->subject->translate('formal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function informalOnlyNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'only informal',
			$this->subject->translate('informal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'formal with normal, formal',
			$this->subject->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInformalNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'formal with normal, formal',
			$this->subject->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingNothingNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'formal with normal, formal',
			$this->subject->translate('formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'informal with normal, informal',
			$this->subject->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingFormalNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'informal with normal, normal',
			$this->subject->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingNothingNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'informal with normal, normal',
			$this->subject->translate('informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingFormalNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'both without normal, formal',
			$this->subject->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInformalNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'both without normal, informal',
			$this->subject->translate('both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingNothingNoGermanLabels() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'both without normal, formal',
			$this->subject->translate('both_without_normal')
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
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'de only formal',
			$this->subject->translate('de_formal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function informalOnlyWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'de only informal',
			$this->subject->translate('de_informal_string_only')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'de formal with normal, formal',
			$this->subject->translate('de_formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInformalWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'de formal with normal, formal',
			$this->subject->translate('de_formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingNothingWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'de formal with normal, formal',
			$this->subject->translate('de_formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function formalWithNormalTryingInvalidWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('foobar');
		$this->assertSame(
			'de formal with normal, formal',
			$this->subject->translate('de_formal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'de informal with normal, informal',
			$this->subject->translate('de_informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingFormalWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'de informal with normal, normal',
			$this->subject->translate('de_informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingNothingWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'de informal with normal, normal',
			$this->subject->translate('de_informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function informalWithNormalTryingInvalidWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('foobar');
		$this->assertSame(
			'de informal with normal, normal',
			$this->subject->translate('de_informal_string_with_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingFormalWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('formal');
		$this->assertSame(
			'de both without normal, formal',
			$this->subject->translate('de_both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInformalWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('informal');
		$this->assertSame(
			'de both without normal, informal',
			$this->subject->translate('de_both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingNothingWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->assertSame(
			'de both without normal, formal',
			$this->subject->translate('de_both_without_normal')
		);
	}

	/**
	 * @test
	 */
	public function bothWithoutNormalTryingInvalidWithGermanLabels() {
		$this->subject->setLanguage('de');
		$this->subject->setSalutationMode('foobar');
		$this->assertSame(
			'de both without normal, formal',
			$this->subject->translate('de_both_without_normal')
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
			$this->subject->translate('htmlspecialchars')
		);
	}

	/**
	 * @test
	 */
	public function htmlSpecialCharsWithFalse() {
		$this->assertSame(
			'a&o',
			$this->subject->translate('htmlspecialchars', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function htmlSpecialCharsWithTrue() {
		$this->assertSame(
			'a&amp;o',
			$this->subject->translate('htmlspecialchars', TRUE)
		);
	}

	/**
	 * @test
	 */
	public function piGetLlHtmlSpecialCharsWithNoOption() {
		$this->assertSame(
			'a&o',
			$this->subject->pi_getLL('htmlspecialchars')
		);
	}

	/**
	 * @test
	 */
	public function piGetLlHtmlSpecialCharsWithFalse() {
		$this->assertSame(
			'a&o',
			$this->subject->pi_getLL('htmlspecialchars', '', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function piGetLlHtmlSpecialCharsWithTrue() {
		$this->assertSame(
			'a&amp;o',
			$this->subject->pi_getLL('htmlspecialchars', '', TRUE)
		);
	}
}
?>