<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for salutation switching in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('oelib').'tx_oelib_commonConstants.php');
require_once(t3lib_extMgm::extPath('oelib').'tests/fixtures/class.tx_oelib_salutationswitcherchild.php');

class tx_oelib_salutationswitcherchild_testcase extends tx_phpunit_testcase {
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_salutationswitcherchild(array());
	}

	public function tearDown() {
		unset($this->fixture);
	}


	////////////////////////////////////
	// Tests for setting the language.
	////////////////////////////////////

	public function testInitialLanguage() {
		$this->assertEquals(
			'default', $this->fixture->getLanguage()
		);
	}

	public function testSetLanguageDefault() {
		$this->fixture->setLanguage('default');
		$this->assertEquals(
			'default', $this->fixture->getLanguage()
		);
	}

	public function testSetLanguageDe() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'de', $this->fixture->getLanguage()
		);
	}

	public function testSetLanguageDefaultEmpty() {
		$this->fixture->setLanguage('');
		$this->assertEquals(
			'', $this->fixture->getLanguage()
		);
	}

	public function testInitialFallbackLanguage() {
		$this->assertEquals(
			'default', $this->fixture->getFallbackLanguage()
		);
	}

	public function testSetFallbackLanguageDefault() {
		$this->fixture->setFallbackLanguage('default');
		$this->assertEquals(
			'default', $this->fixture->getFallbackLanguage()
		);
	}

	public function testSetFallbackLanguageDe() {
		$this->fixture->setFallbackLanguage('de');
		$this->assertEquals(
			'de', $this->fixture->getFallbackLanguage()
		);
	}

	public function testSetFallbackLanguageEmpty() {
		$this->fixture->setFallbackLanguage('');
		$this->assertEquals(
			'', $this->fixture->getFallbackLanguage()
		);
	}


	///////////////////////////////////////////
	// Tests for setting the salutation modes.
	///////////////////////////////////////////

	public function testSetSalutationFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'formal', $this->fixture->getSalutationMode()
		);
	}

	public function testSetSalutationInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'informal', $this->fixture->getSalutationMode()
		);
	}


	//////////////////////////////////////
	// Tests for empty keys or languages.
	//////////////////////////////////////

	public function testEmptyKeyDefault() {
		$this->fixture->setLanguage('default');

		try {
			$this->fixture->translate('');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testEmptyKeyDe() {
		$this->fixture->setLanguage('de');

		try {
			$this->fixture->translate('');
		} catch (Exception $expected) {
			return;
		}

		// Fails the test if the expected exception was not raised above.
		$this->fail(EXCEPTION_EXPECTED);
	}

	public function testNoLanguageAtAllWithKnownKey() {
		$this->fixture->setLanguage('');
		$this->fixture->setFallbackLanguage('');
		$this->assertEquals(
			'in_both', $this->fixture->translate('in_both')
		);
	}

	public function testNoLanguageAtAllWithUnknownKey() {
		$this->fixture->setLanguage('');
		$this->fixture->setFallbackLanguage('');
		$this->assertEquals(
			'missing_key', $this->fixture->translate('missing_key')
		);
	}

	public function testNoFallbackLanguageWithGermanMissingLabel() {
		$this->fixture->setLanguage('de');
		$this->fixture->setFallbackLanguage('');
		$this->assertEquals(
			'only_in_default', $this->fixture->translate('only_in_default')
		);
	}


	///////////////////////////////////////////////////////////
	// Tests for translating without setting salutation modes.
	///////////////////////////////////////////////////////////

	public function testTranslateWithoutLanguageOnlyInDefault() {
		$this->assertEquals(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	public function testTranslateWithoutLanguageInBoth() {
		$this->assertEquals(
			'in both languages', $this->fixture->translate('in_both')
		);
	}

	public function testMissingKeyDefault() {
		$this->fixture->setLanguage('default');
		$this->assertEquals(
			'missing_key', $this->fixture->translate('missing_key')
		);
	}

	public function testMissingKeyDe() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'missing_key', $this->fixture->translate('missing_key')
		);
	}

	public function testOnlyInDefaultUsingDefault() {
		$this->fixture->setLanguage('default');
		$this->assertEquals(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	public function testOnlyInDefaultUsingNothing() {
		$this->assertEquals(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	public function testOnlyInDefaultUsingDe() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'only in default', $this->fixture->translate('only_in_default')
		);
	}

	public function testInBothUsingDefault() {
		$this->fixture->setLanguage('default');
		$this->assertEquals(
			'in both languages', $this->fixture->translate('in_both')
		);
	}

	public function testInBothUsingDe() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'in beiden Sprachen', $this->fixture->translate('in_both')
		);
	}

	public function testEmptyStringDefault() {
		$this->fixture->setLanguage('default');
		$this->assertEquals(
			'', $this->fixture->translate('empty_string_in_default')
		);
	}

	public function testEmptyStringDe() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'', $this->fixture->translate('empty_string_in_default')
		);
	}

	public function testFallbackToDefault() {
		$this->fixture->setLanguage('xy');
		$this->assertEquals(
			'default_not_fallback default',
			$this->fixture->translate('default_not_fallback')
		);
	}

	public function testFallbackToDe() {
		$this->fixture->setLanguage('xy');
		$this->fixture->setFallbackLanguage('de');
		$this->assertEquals(
			'default_not_fallback de',
			$this->fixture->translate('default_not_fallback')
		);
	}

	public function testFallbackToDefaultFromEmptyLanguage() {
		$this->fixture->setLanguage('');
		$this->fixture->setFallbackLanguage('default');
		$this->assertEquals(
			'default_not_fallback default',
			$this->fixture->translate('default_not_fallback')
		);
	}

	public function testFallbackToDeFromEmptyLanguage() {
		$this->fixture->setLanguage('');
		$this->fixture->setFallbackLanguage('de');
		$this->assertEquals(
			'default_not_fallback de',
			$this->fixture->translate('default_not_fallback')
		);
	}

	public function testFallbackToFrFromDe() {
		$this->fixture->setLanguage('de');
		$this->fixture->setFallbackLanguage('fr');
		$this->assertEquals(
			'only in french fr',
			$this->fixture->translate('only_in_french')
		);
	}

	public function testFallbackToDefaultFromDe() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'only in french',
			$this->fixture->translate('only_in_french')
		);
	}


	/////////////////////////////////////////////////////////////////////////
	// Tests for translating with salutation modes in the default language.
	/////////////////////////////////////////////////////////////////////////

	public function testFormalOnly() {
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'only formal',
			$this->fixture->translate('formal_string_only')
		);
	}

	public function testInformalOnly() {
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'only informal',
			$this->fixture->translate('informal_string_only')
		);
	}

	public function testFormalWithNormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingNothing() {
		$this->assertEquals(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInvalid() {
		$this->fixture->setSalutationMode('foobar');
		$this->assertEquals(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testInformalWithNormal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'informal with normal, informal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingNothing() {
		$this->assertEquals(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingInvalid() {
		$this->fixture->setSalutationMode('foobar');
		$this->assertEquals(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testBothWithoutNormalTryingFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'both without normal, informal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingNothing() {
		$this->assertEquals(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInvalid() {
		$this->fixture->setSalutationMode('foobar');
		$this->assertEquals(
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
		$this->assertEquals(
			'only formal',
			$this->fixture->translate('formal_string_only')
		);
	}

	public function testInformalOnlyNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'only informal',
			$this->fixture->translate('informal_string_only')
		);
	}

	public function testFormalWithNormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInformalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingNothingNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'formal with normal, formal',
			$this->fixture->translate('formal_string_with_normal')
		);
	}

	public function testInformalWithNormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'informal with normal, informal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingFormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingNothingNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'informal with normal, normal',
			$this->fixture->translate('informal_string_with_normal')
		);
	}

	public function testBothWithoutNormalTryingFormalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'both without normal, formal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInformalNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'both without normal, informal',
			$this->fixture->translate('both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingNothingNoGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
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
		$this->assertEquals(
			'de only formal',
			$this->fixture->translate('de_formal_string_only')
		);
	}

	public function testInformalOnlyWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'de only informal',
			$this->fixture->translate('de_informal_string_only')
		);
	}

	public function testFormalWithNormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInformalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingNothingWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	public function testFormalWithNormalTryingInvalidWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('foobar');
		$this->assertEquals(
			'de formal with normal, formal',
			$this->fixture->translate('de_formal_string_with_normal')
		);
	}

	public function testInformalWithNormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'de informal with normal, informal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingFormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'de informal with normal, normal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingNothingWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'de informal with normal, normal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	public function testInformalWithNormalTryingInvalidWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('foobar');
		$this->assertEquals(
			'de informal with normal, normal',
			$this->fixture->translate('de_informal_string_with_normal')
		);
	}

	public function testBothWithoutNormalTryingFormalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('formal');
		$this->assertEquals(
			'de both without normal, formal',
			$this->fixture->translate('de_both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInformalWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('informal');
		$this->assertEquals(
			'de both without normal, informal',
			$this->fixture->translate('de_both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingNothingWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'de both without normal, formal',
			$this->fixture->translate('de_both_without_normal')
		);
	}

	public function testBothWithoutNormalTryingInvalidWithGermanLabels() {
		$this->fixture->setLanguage('de');
		$this->fixture->setSalutationMode('foobar');
		$this->assertEquals(
			'de both without normal, formal',
			$this->fixture->translate('de_both_without_normal')
		);
	}


	/////////////////////////////////////////////
	// Tests for the useHtmlSpecialchars option.
	/////////////////////////////////////////////

	public function testHtmlSpecialCharsWithNoOption() {
		$this->assertEquals(
			'a&o',
			$this->fixture->translate('htmlspecialchars')
		);
	}

	public function testHtmlSpecialCharsWithFalse() {
		$this->assertEquals(
			'a&o',
			$this->fixture->translate('htmlspecialchars', false)
		);
	}

	public function testHtmlSpecialCharsWithTrue() {
		$this->assertEquals(
			'a&amp;o',
			$this->fixture->translate('htmlspecialchars', true)
		);
	}

	public function testPiGetLlHtmlSpecialCharsWithNoOption() {
		$this->assertEquals(
			'a&o',
			$this->fixture->pi_getLL('htmlspecialchars')
		);
	}

	public function testPiGetLlHtmlSpecialCharsWithFalse() {
		$this->assertEquals(
			'a&o',
			$this->fixture->pi_getLL('htmlspecialchars', '', false)
		);
	}

	public function testPiGetLlHtmlSpecialCharsWithTrue() {
		$this->assertEquals(
			'a&amp;o',
			$this->fixture->pi_getLL('htmlspecialchars', '', true)
		);
	}
}

?>
