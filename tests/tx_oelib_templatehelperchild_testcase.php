<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the template helper class in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('oelib')
	.'tests/fixtures/class.tx_oelib_templatehelperchild.php');

class tx_oelib_templatehelperchild_testcase extends tx_phpunit_testcase {
	private $fixture;

	private $originalGlobalsTt;
	private $originalGlobalsTsfeSysPage;
	private $originalCObj;

	protected function setUp() {
		$this->fixture = new tx_oelib_templatehelperchild(array());

		$this->originalGlobalsTt = $GLOBALS['TT'];
		$this->originalGlobalsTsfeSysPage = $GLOBALS['TSFE']->sys_page;
		$this->originalCObj = $this->fixture->cObj;
	}

	protected function tearDown() {
		unset($this->fixture);

		$GLOBALS['TT'] = $this->originalGlobalsTt;
		$GLOBALS['TSFE']->sys_page = $this->originalGlobalsTsfeSysPage;
		$this->fixture->cObj = $this->originalCObj;
	}


	////////////////////////////////////////////////////////////////
	// Tests concerning the creation of the template helper object.
	////////////////////////////////////////////////////////////////

	public function testConfigurationCheckCreation() {
		$this->assertNotNull(
			$this->fixture->getConfigurationCheck()
		);
	}

	public function testFakeFrontendInitializesGlobalsTt() {
		unset($GLOBALS['TT']);
		$this->fixture->fakeFrontend();

		$this->assertTrue(is_object($GLOBALS['TT']));
	}

	public function testFakeFrontendInitializesGlobalsTsfeSysPage() {
		unset($GLOBALS['TSFE']->sys_page);
		$this->fixture->fakeFrontend();

		$this->assertTrue(is_object($GLOBALS['TSFE']->sys_page));
	}

	public function testFakeFrontendInitializesCobj() {
		unset($this->fixture->cObj);
		$this->fixture->fakeFrontend();

		$this->assertTrue(is_object($this->fixture->cObj));
	}


	///////////////////////////////////////////////////////
	// Tests for setting and reading configuration values.
	///////////////////////////////////////////////////////

	public function testSetConfigurationValueStringNotEmpty() {
		$this->fixture->setConfigurationValue('test', 'This is a test.');
		$this->assertEquals(
			'This is a test.', $this->fixture->getConfValueString('test')
		);
	}


	///////////////////////////////
	// Tests for getting subparts.
	///////////////////////////////

	public function testNoSubpartsAndEmptySubpartName() {
		$this->assertEquals(
			'', $this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testNotExistingSubpartName() {
		$this->assertEquals(
			'', $this->fixture->getSubpart('FOOBAR')
		);
		$this->assertContains(
			'The subpart',
			$this->fixture->getWrappedConfigCheckMessage()
		);
		$this->assertContains(
			'is missing',
			$this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetCompleteTemplate() {
		$templateCode = 'This is a test including'.LF.'a linefeed.'.LF;
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertEquals(
			$templateCode, $this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetCompleteTemplateWithComment() {
		$templateCode = 'This is a test including a comment. '
			.'<!-- This is a comment. -->'
			.'And some more text.';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertEquals(
			$templateCode, $this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetSimpleSubpart() {
 		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart'
			.'<!-- ###MY_SUBPART### -->'
			.$subpartContent
			.'<!-- ###MY_SUBPART### -->'
			.'Text after the subpart.';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertEquals(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetOneOfTwoSimpleSubparts() {
 		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart'
			.'<!-- ###MY_SUBPART### -->'
			.$subpartContent
			.'<!-- ###MY_SUBPART### -->'
			.'Text inbetween.'
			.'<!-- ###ANOTHER_SUBPART### -->'
			.'More text.'
			.'<!-- ###ANOTHER_SUBPART### -->'
			.'Text after the subpart.';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertEquals(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetSimpleSubpartWithLinefeed() {
 		$subpartContent = LF.'Subpart content'.LF;
		$templateCode = 'Text before the subpart'.LF
			.'<!-- ###MY_SUBPART### -->'
			.$subpartContent
			.'<!-- ###MY_SUBPART### -->'.LF
			.'Text after the subpart.'.LF;
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertEquals(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetDoubleOccuringSubpart() {
 		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart'
			.'<!-- ###MY_SUBPART### -->'
			.$subpartContent
			.'<!-- ###MY_SUBPART### -->'
			.'Text inbetween.'
			.'<!-- ###MY_SUBPART### -->'
			.'More text.'
			.'<!-- ###MY_SUBPART### -->'
			.'Text after the subpart.';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertEquals(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetSubpartWithNestedInnerSubparts() {
 		$subpartContent = 'Subpart content ';
		$templateCode = 'Text before the subpart'
			.'<!-- ###MY_SUBPART### -->'
			.'outer start, '
			.'<!-- ###OUTER_SUBPART### -->'
			.'inner start, '
			.'<!-- ###INNER_SUBPART### -->'
			.$subpartContent
			.'<!-- ###INNER_SUBPART### -->'
			.'inner end, '
			.'<!-- ###OUTER_SUBPART### -->'
			.'outer end '
			.'<!-- ###MY_SUBPART### -->'
			.'Text after the subpart.';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertEquals(
			'outer start, inner start, '.$subpartContent.'inner end, outer end ',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetEmptyExistingSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertEquals(
			'',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	//////////////////////////////////
	// Tests for filling in markers.
	//////////////////////////////////

	public function testSetLowercaseMarkerInCompleteTemplate() {
		$this->fixture->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertEquals(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetUppercaseMarkerInCompleteTemplate() {
		$this->fixture->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);
		$this->fixture->setMarker('MARKER', 'foo');
		$this->assertEquals(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetLowercaseMarkerInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertEquals(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetUppercaseMarkerInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->setMarker('MARKER', 'foo');
		$this->assertEquals(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetDoubleMarkerInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'###MARKER### This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertEquals(
			'foo This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetMarkerInCompleteTemplateTwoTimes() {
		$this->fixture->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);

		$this->fixture->setMarker('marker', 'foo');
		$this->assertEquals(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart()
		);

		$this->fixture->setMarker('marker', 'bar');
		$this->assertEquals(
			'This is some template code. bar More text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetMarkerInSubpartTwoTimes() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);

		$this->fixture->setMarker('marker', 'foo');
		$this->assertEquals(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);

		$this->fixture->setMarker('marker', 'bar');
		$this->assertEquals(
			'This is some template code. bar More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesArePrefixesBothUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->fixture->setMarker('my_marker_too', 'bar');
		$this->assertEquals(
			'foo bar',
			$this->fixture->getSubpart('')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesAreSuffixesBothUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->fixture->setMarkerContent('my_marker', 'foo');
		$this->fixture->setMarkerContent('also_my_marker', 'bar');
		$this->assertEquals(
			'foo bar',
			$this->fixture->getSubpart('')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesArePrefixesFirstUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->assertEquals(
			'foo ###MY_MARKER_TOO###',
			$this->fixture->getSubpart('')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesAreSuffixesFirstUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->fixture->setMarkerContent('my_marker', 'foo');
		$this->assertEquals(
			'foo ###ALSO_MY_MARKER###',
			$this->fixture->getSubpart('')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesArePrefixesSecondUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->fixture->setMarker('my_marker_too', 'bar');
		$this->assertEquals(
			'###MY_MARKER### bar',
			$this->fixture->getSubpart('')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesAreSuffixesSecondUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->fixture->setMarkerContent('also_my_marker', 'bar');
		$this->assertEquals(
			'###MY_MARKER### bar',
			$this->fixture->getSubpart('')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesArePrefixesBothUsedWithSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
			.'###MY_MARKER### ###MY_MARKER_TOO###'
			.'<!-- ###MY_SUBPART### -->'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->fixture->setMarker('my_marker_too', 'bar');
		$this->assertEquals(
			'foo bar',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesAreSuffixesBothUsedWithSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
			.'###MY_MARKER### ###ALSO_MY_MARKER###'
			.'<!-- ###MY_SUBPART### -->'
		);

		$this->fixture->setMarkerContent('my_marker', 'foo');
		$this->fixture->setMarkerContent('also_my_marker', 'bar');
		$this->assertEquals(
			'foo bar',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	///////////////////////////////////////////////////////////////
	// Tests for replacing subparts with their content on output.
	///////////////////////////////////////////////////////////////

	function testGetUnchangedSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'This is some template code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'This is some subpart code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'More text.'
		);
		$this->assertEquals(
			'This is some template code.'
				.'This is some subpart code.'
				.'More text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	function testGetUnchangedDoubleSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'This is some template code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'This is some subpart code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'More text.'
				.'<!-- ###INNER_SUBPART### -->'
				.'This is other subpart code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'Even more text.'
		);
		$this->assertEquals(
			'This is some template code.'
				.'This is some subpart code.'
				.'More text.'
				.'This is some subpart code.'
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	function testGetUnchangedSubpartInRequestedSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'This is some subpart code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertEquals(
			'This is some template code.'
				.'This is some subpart code.'
				.'More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	function testGetUnchangedDoubleSubpartInRequestedSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'This is some subpart code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'More text.'
				.'<!-- ###INNER_SUBPART### -->'
				.'This is other subpart code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'Even more text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertEquals(
			'This is some template code.'
				.'This is some subpart code.'
				.'More text.'
				.'This is some subpart code.'
				.'Even more text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	///////////////////////////////////////////////////////////////
	// Tests for retrieving subparts with names that are prefixes
	// or suffixes of other subpart names.
	///////////////////////////////////////////////////////////////

	public function testSubpartNamesArePrefixesGetCompleteTemplate() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###MY_SUBPART_TOO### -->'
				.'bar'
				.'<!-- ###MY_SUBPART_TOO### -->'
		);
		$this->assertEquals(
			'foo Some more text. bar',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartNamesAreSuffixesGetCompleteTemplate() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###ALSO_MY_SUBPART### -->'
				.'bar'
				.'<!-- ###ALSO_MY_SUBPART### -->'
		);
		$this->assertEquals(
			'foo Some more text. bar',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartNamesArePrefixesGetFirstSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###MY_SUBPART_TOO### -->'
				.'bar'
				.'<!-- ###MY_SUBPART_TOO### -->'
		);
		$this->assertEquals(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartNamesAreSuffixesGetFirstSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###ALSO_MY_SUBPART### -->'
				.'bar'
				.'<!-- ###ALSO_MY_SUBPART### -->'
		);
		$this->assertEquals(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartNamesArePrefixesGetSecondSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###MY_SUBPART_TOO### -->'
				.'bar'
				.'<!-- ###MY_SUBPART_TOO### -->'
		);
		$this->assertEquals(
			'bar',
			$this->fixture->getSubpart('MY_SUBPART_TOO')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartNamesAreSuffixesGetSecondSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###ALSO_MY_SUBPART### -->'
				.'bar'
				.'<!-- ###ALSO_MY_SUBPART### -->'
		);
		$this->assertEquals(
			'bar',
			$this->fixture->getSubpart('ALSO_MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	////////////////////////////////////////////
	// Tests for hiding and unhiding subparts.
	////////////////////////////////////////////

	public function testHideSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->assertEquals(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideOverwrittenSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'More text. ');
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->assertEquals(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhideSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->unhideSubparts('MY_SUBPART');
		$this->assertEquals(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('MY_SUBPART');
		$this->assertEquals(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->assertEquals(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhideSubpartInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->unhideSubparts('MY_SUBPART');
		$this->assertEquals(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('MY_SUBPART');
		$this->assertEquals(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideTwoSubpartsSeparately() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART_1');
		$this->fixture->hideSubparts('MY_SUBPART_2');
		$this->assertEquals(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideTwoSubpartsWithoutSpaceAfterComma() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->assertEquals(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideTwoSubpartsInReverseOrder() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART_2,MY_SUBPART_1');
		$this->assertEquals(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideTwoSubpartsWithSpaceAfterComma() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART_1, MY_SUBPART_2');
		$this->assertEquals(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideTwoSubpartsSeparately() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART_1');
		$this->fixture->hideSubparts('MY_SUBPART_2');
		$this->fixture->unhideSubparts('MY_SUBPART_1');
		$this->fixture->unhideSubparts('MY_SUBPART_2');
		$this->assertEquals(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideTwoSubpartsInSameOrder() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->fixture->unhideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->assertEquals(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideTwoSubpartsInReverseOrder() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->fixture->unhideSubparts('MY_SUBPART_2,MY_SUBPART_1');
		$this->assertEquals(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideTwoSubpartsUnhideFirst() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->fixture->unhideSubparts('MY_SUBPART_1');
		$this->assertEquals(
			'Some text. '
				.'More text here.'
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideTwoSubpartsUnhideSecond() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->fixture->unhideSubparts('MY_SUBPART_2');
		$this->assertEquals(
			'Some text. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	////////////////////////////////
	// Tests for setting subparts.
	////////////////////////////////

	public function testSetSubpartNotEmptyGetCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'foo');
		$this->assertEquals(
			'Some text. '
				.'foo'
				.' Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetSubpartNotEmptyGetSubpart() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'foo');
		$this->assertEquals(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetSubpartNotEmptyGetOuterSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'foo');
		$this->assertEquals(
			'Some text. foo Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetSubpartToEmptyGetCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', '');
		$this->assertEquals(
			'Some text. '
				.' Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetSubpartToEmptyGetSubpart() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', '');
		$this->assertEquals(
			'',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetSubpartToEmptyGetOuterSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->setSubpart('MY_SUBPART', '');
		$this->assertEquals(
			'Some text.  Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	//////////////////////////////////////////////////////
	// Tests for setting markers within nested subparts.
	//////////////////////////////////////////////////////

	public function testSetMarkerInSubpartWithinCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertEquals(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetMarkerInSubpartWithinOtherSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertEquals(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetMarkerInOverwrittenSubpartWithinCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart(
			'MY_SUBPART',
			'This is some template code. ###MARKER### More text.'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertEquals(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetMarkerInOverwrittenSubpartWithinOtherSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->setSubpart(
			'MY_SUBPART',
			'This is some template code. ###MARKER### More text.'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertEquals(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetMarkerWithinNestedInnerSubpart() {
		$templateCode = 'Text before the subpart'
			.'<!-- ###MY_SUBPART### -->'
			.'outer start, '
			.'<!-- ###OUTER_SUBPART### -->'
			.'inner start, '
			.'<!-- ###INNER_SUBPART### -->'
			.'###MARKER###'
			.'<!-- ###INNER_SUBPART### -->'
			.'inner end, '
			.'<!-- ###OUTER_SUBPART### -->'
			.'outer end '
			.'<!-- ###MY_SUBPART### -->'
			.'Text after the subpart.';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->fixture->setMarkerContent('marker', 'foo ');

		$this->assertEquals(
			'outer start, inner start, foo inner end, outer end ',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	////////////////////////////////////////////////////////////
	// Tests for using the prefix to marker and subpart names.
	////////////////////////////////////////////////////////////

	public function testSetMarkerWithPrefix() {
		$this->fixture->processTemplate(
			'This is some template code. '
				.'###FIRST_MARKER### ###MARKER### More text.'
		);
		$this->fixture->setMarker('marker', 'foo', 'first');
		$this->assertEquals(
			'This is some template code. foo ###MARKER### More text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetSubpartWithPrefix() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'foo', 'FIRST');
		$this->assertEquals(
			'Some text. '
				.'foo'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartWithPrefix() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART', 'FIRST');
		$this->assertEquals(
			'Some text. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartWithPrefix() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('FIRST_MY_SUBPART');
		$this->fixture->unhideSubparts('MY_SUBPART', 'FIRST');
		$this->assertEquals(
			'Some text. '
				.'More text here. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideTwoSubpartsWithPrefix() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART_1### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART_1### -->'
				.'<!-- ###FIRST_MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###FIRST_MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('1,2', 'FIRST_MY_SUBPART');
		$this->assertEquals(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideTwoSubpartsWithPrefix() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART_1### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART_1### -->'
				.'<!-- ###FIRST_MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###FIRST_MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('FIRST_MY_SUBPART_1');
		$this->fixture->hideSubparts('FIRST_MY_SUBPART_2');
		$this->fixture->unhideSubparts('1,2', 'FIRST_MY_SUBPART');
		$this->assertEquals(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertEquals(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	////////////////////////////////////////////
	// Tests for automatically setting labels.
	////////////////////////////////////////////

	public function testSetLabels() {
		$this->fixture->processTemplate(
			'a ###LABEL_FOO### b'
		);
		$this->fixture->setLabels();
		$this->assertEquals(
			'a foo b',
			$this->fixture->getSubpart()
		);
	}

	public function testSetLabelsNoSalutation() {
		$this->fixture->processTemplate(
			'a ###LABEL_BAR### b'
		);
		$this->fixture->setLabels();
		$this->assertEquals(
			'a bar (formal) b',
			$this->fixture->getSubpart()
		);
	}

	public function testSetLabelsFormal() {
		$this->fixture->setSalutationMode('formal');
		$this->fixture->processTemplate(
			'a ###LABEL_BAR### b'
		);
		$this->fixture->setLabels();
		$this->assertEquals(
			'a bar (formal) b',
			$this->fixture->getSubpart()
		);
	}

	public function testSetLabelsInformal() {
		$this->fixture->setSalutationMode('informal');
		$this->fixture->processTemplate(
			'a ###LABEL_BAR### b'
		);
		$this->fixture->setLabels();
		$this->assertEquals(
			'a bar (informal) b',
			$this->fixture->getSubpart()
		);
	}

	public function testSetLabelsWithOneBeingThePrefixOfAnother() {
		$this->fixture->processTemplate(
			'###LABEL_FOO###, ###LABEL_FOO2###'
		);
		$this->fixture->setLabels();
		$this->assertEquals(
			'foo, foo two',
			$this->fixture->getSubpart()
		);
	}


	/////////////////////////////////////////////////////////////////////
	// Test for conditional filling and hiding of markers and subparts.
	/////////////////////////////////////////////////////////////////////

	public function testSetMarkerIfNotZeroWithPositiveInteger() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotZero('marker', 42)
		);
		$this->assertEquals(
			'42',
			$this->fixture->getSubpart()
		);
	}

	public function testSetMarkerIfNotZeroWithNegativeInteger() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotZero('marker', -42)
		);
		$this->assertEquals(
			'-42',
			$this->fixture->getSubpart()
		);
	}

	public function testSetMarkerIfNotZeroWithZero() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertFalse(
			$this->fixture->setMarkerIfNotZero('marker', 0)
		);
		$this->assertEquals(
			'###MARKER###',
			$this->fixture->getSubpart()
		);
	}

	public function testSetMarkerIfNotZeroWithPositiveIntegerWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotZero('marker', 42, 'MY')
		);
		$this->assertEquals(
			'42',
			$this->fixture->getSubpart()
		);
	}

	public function testSetMarkerIfNotZeroWithNegativeIntegerWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotZero('marker', -42, 'MY')
		);
		$this->assertEquals(
			'-42',
			$this->fixture->getSubpart()
		);
	}

	public function testSetMarkerIfNotZeroWithZeroWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertFalse(
			$this->fixture->setMarkerIfNotZero('marker', 0, 'MY')
		);
		$this->assertEquals(
			'###MY_MARKER###',
			$this->fixture->getSubpart()
		);
	}


	public function testSetMarkerIfNotEmptyWithNotEmpty() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotEmpty('marker', 'foo')
		);
		$this->assertEquals(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	public function testSetMarkerIfNotEmptyWithEmpty() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertFalse(
			$this->fixture->setMarkerIfNotEmpty('marker', '')
		);
		$this->assertEquals(
			'###MARKER###',
			$this->fixture->getSubpart()
		);
	}

	public function testSetMarkerIfNotEmptyWithNotEmptyWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotEmpty('marker', 'foo', 'MY')
		);
		$this->assertEquals(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	public function testSetMarkerIfNotEmptyWithEmptyWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertFalse(
			$this->fixture->setMarkerIfNotEmpty('marker', '', 'MY')
		);
		$this->assertEquals(
			'###MY_MARKER###',
			$this->fixture->getSubpart()
		);
	}


	public function testSetOrDeleteMarkerWithTrue() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarker(
				'marker', true, 'foo', '', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerWithFalse() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->fixture->setOrDeleteMarker(
				'marker', false, 'foo', '', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerWithTrueWithMarkerPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarker(
				'marker', true, 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerWithFalseWithMarkerPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->fixture->setOrDeleteMarker(
				'marker', false, 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotZeroWithZero() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->fixture->setOrDeleteMarkerIfNotZero(
				'marker', 0, '', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotZeroWithPositiveIntegers() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarkerIfNotZero(
				'marker', 42, '', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'42',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotZeroWithNegativeIntegers() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarkerIfNotZero(
				'marker', -42, '', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'-42',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotZeroWithZeroWithMarkerPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->fixture->setOrDeleteMarkerIfNotZero(
				'marker', 0, 'MY', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotZeroWithPositiveIntegerWithMarkerPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarkerIfNotZero(
				'marker', 42, 'MY', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'42',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotZeroWithNegativeIntegerWithMarkerPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarkerIfNotZero(
				'marker', -42, 'MY', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'-42',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotEmptyWithEmpty() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->fixture->setOrDeleteMarkerIfNotEmpty(
				'marker', '', '', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotEmptyWithNotEmpty() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarkerIfNotEmpty(
				'marker', 'foo', '', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotEmptyWithEmptyWithMarkerPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->fixture->setOrDeleteMarkerIfNotEmpty(
				'marker', '', 'MY', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'',
			$this->fixture->getSubpart()
		);
	}

	public function testSetOrDeleteMarkerIfNotEmptyWithNotEmptyWithMarkerPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarkerIfNotEmpty(
				'marker', 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertEquals(
			'foo',
			$this->fixture->getSubpart()
		);
	}
}

?>
