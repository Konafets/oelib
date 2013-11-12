<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Niels Pardon (mail@niels-pardon.de)
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
 */
class tx_oelib_TemplateTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Template
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Template();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	////////////////////////////////////////////
	// Tests for reading the HTML from a file.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function processTemplateFromFileProcessesTemplateFromFile() {
		$this->fixture->processTemplateFromFile(
			'EXT:oelib/tests/fixtures/oelib.html'
		);

		$this->assertSame(
			'Hello world!' . LF, $this->fixture->getSubpart()
		);
	}

	///////////////////////////////
	// Tests for getting subparts.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getSubpartWithNoSubpartNameInitiallyReturnsAnEmptyString() {
		$this->assertSame(
			'', $this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function getSubpartWithNotExistingSubpartNameThrowsException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'$key contained the subpart name "FOOBAR", but only the following subparts are available: ()'
		);

		$this->assertSame(
			'', $this->fixture->getSubpart('FOOBAR')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartWithNotExistingSubpartNameThrowsExceptionWithSubpartNames() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'$key contained the subpart name "COFFEE", but only the following subparts are available: (FOO, BAR)'
		);

		$this->fixture->processTemplate(
			'<!-- ###FOO### -->' .
				'<!-- ###FOO### -->' .
				'<!-- ###BAR### -->' .
				'<!-- ###BAR### -->'
		);

		$this->assertSame(
			'', $this->fixture->getSubpart('COFFEE')
		);
	}

	/**
	 * @test
	 */
	public function getCompleteTemplateReturnsCompleteTemplateContent() {
		$templateCode = 'This is a test including'.LF.'a linefeed.'.LF;
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$templateCode, $this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function getCompleteTemplateCanContainUtf8Umlauts() {
		$this->fixture->processTemplate('äöüßÄÖÜßéèáàóò');

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function getCompleteTemplateCanContainIso88591Umlauts() {
		// 228 = ä, 223 = ß (in ISO8859-1)
		$this->fixture->processTemplate(chr(228) . chr(223));

		$this->assertSame(
			chr(228) . chr(223),
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function getCompleteTemplateWithComment() {
		$templateCode = 'This is a test including a comment. '
			.'<!-- This is a comment. -->'
			.'And some more text.';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$templateCode, $this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function getSimpleSubpart() {
		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart'
			.'<!-- ###MY_SUBPART### -->'
			.$subpartContent
			.'<!-- ###MY_SUBPART### -->'
			.'Text after the subpart.';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFindsSubpartWithTextBeforeClosingSubpartStartComment() {
		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart' .
			'<!-- ###MY_SUBPART### start -->' .
			$subpartContent .
			'<!-- ###MY_SUBPART### -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFindsSubpartWithTextBeforeClosingSubpartEndComment() {
		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart' .
			'<!-- ###MY_SUBPART### -->' .
			$subpartContent .
			'<!-- ###MY_SUBPART### end -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFindsSubpartWithTextBeforeOpeningAndClosingSubpartEndComment() {
		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart' .
			'<!-- ###MY_SUBPART### start -->' .
			$subpartContent .
			'<!-- ###MY_SUBPART### end -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFindsSubpartWithTextWithLinefeedsBeforeOpeningAndClosingSubpartEndComment() {
		$subpartContent = 'Subpart content';
		$templateCode = '<!-- ###MY_SUBPART### start '. LF . ' start -->' .
			$subpartContent .
			'<!-- ###MY_SUBPART### end ' . LF . ' end -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFindsSubpartWithTextWithDashesBeforeOpeningAndClosingSubpartEndComment() {
		$subpartContent = 'Subpart content';
		$templateCode = '<!-- ###MY_SUBPART### start - hey hey -->' .
			$subpartContent .
			'<!-- ###MY_SUBPART### end - really the end -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFindsSubpartWithTextWithHtmlBeforeOpeningAndClosingSubpartEndComment() {
		$subpartContent = 'Subpart content';
		$templateCode = '<!-- ###MY_SUBPART### <em>start</em> -->' .
			$subpartContent .
			'<!-- ###MY_SUBPART### <em>end</em> -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFindsSubpartWithHtmlCommentInIt() {
		$subpartContent = 'Subpart <!-- this is hidden --> content';
		$templateCode = '<!-- ###MY_SUBPART### -->' .
			$subpartContent .
			'<!-- ###MY_SUBPART### -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartWithTextBeforeClosingSubpartStartCommentReplacesNestedSubpart() {
		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart' .
			'<!-- ###MY_SUBPART### start -->' .
			'<!-- ###MY_INNER_SUBPART### start -->' .
			$subpartContent .
			'<!-- ###MY_INNER_SUBPART### -->' .
			'<!-- ###MY_SUBPART### -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartWithTextBeforeClosingSubpartEndCommentReplacesNestedSubpart() {
		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart' .
			'<!-- ###MY_SUBPART### -->' .
			'<!-- ###MY_INNER_SUBPART### -->' .
			$subpartContent .
			'<!-- ###MY_INNER_SUBPART### end -->' .
			'<!-- ###MY_SUBPART### end -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartWithTextBeforeOpeningAndClosingSubpartEndCommentReplacesNestedSubpart() {
		$subpartContent = 'Subpart content';
		$templateCode = 'Text before the subpart' .
			'<!-- ###MY_SUBPART### -->' .
			'<!-- ###MY_INNER_SUBPART### start -->' .
			$subpartContent .
			'<!-- ###MY_INNER_SUBPART### end -->' .
			'<!-- ###MY_SUBPART### end -->';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFromTemplateCanContainUtf8Umlauts() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
			'äöüßÄÖÜßéèáàóò' .
			'<!-- ###MY_SUBPART### -->'
		);

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFromTemplateCanContainIso88591Umlauts() {
		// 228 = ä, 223 = ß (in ISO8859-1)
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
			chr(228) . chr(223) .
			'<!-- ###MY_SUBPART### -->'
		);

		$this->assertSame(
			chr(228) . chr(223),
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getOneOfTwoSimpleSubparts() {
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
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSimpleSubpartWithLinefeed() {
		$subpartContent = LF.'Subpart content'.LF;
		$templateCode = 'Text before the subpart'.LF
			.'<!-- ###MY_SUBPART### -->'
			.$subpartContent
			.'<!-- ###MY_SUBPART### -->'.LF
			.'Text after the subpart.'.LF;
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getDoubleOccurringSubpart() {
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
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartWithNestedInnerSubparts() {
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
		$this->assertSame(
			'outer start, inner start, '.$subpartContent.'inner end, outer end ',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getEmptyExistingSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getHiddenSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubparts('MY_SUBPART');

		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayAndGetHiddenSubpartReturnsEmptySubpartContent() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'Some text. ' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));

		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}



	//////////////////////////////////
	// Tests for filling in markers.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getInexistentMarkerWillReturnAnEmptyString() {
		$this->fixture->processTemplate(
			'foo'
		);
		$this->assertSame(
			'', $this->fixture->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setAndGetInexistentMarkerSucceeds() {
		$this->fixture->processTemplate(
			'foo'
		);

		$this->fixture->setMarker('bar', 'test');
		$this->assertSame(
			'test', $this->fixture->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setAndGetExistingMarkerSucceeds() {
		$this->fixture->processTemplate(
			'###BAR###'
		);

		$this->fixture->setMarker('bar', 'test');
		$this->assertSame(
			'test', $this->fixture->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setMarkerAndGetMarkerCanHaveUtf8UmlautsInMarkerContent() {
		$this->fixture->processTemplate(
			'###BAR###'
		);
		$this->fixture->setMarker('bar', 'äöüßÄÖÜßéèáàóò');

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->fixture->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setMarkerAndGetMarkerCanHaveIso88591UmlautsInMarkerContent() {
		$this->fixture->processTemplate(
			'###BAR###'
		);
		// 228 = ä, 223 = ß (in ISO8859-1)
		$this->fixture->setMarker('bar', chr(228) . chr(223));

		$this->assertSame(
			chr(228) . chr(223),
			$this->fixture->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setLowercaseMarkerInCompleteTemplate() {
		$this->fixture->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setUppercaseMarkerInCompleteTemplate() {
		$this->fixture->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);
		$this->fixture->setMarker('MARKER', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setLowercaseMarkerInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setUppercaseMarkerInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->setMarker('MARKER', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setDoubleMarkerInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'###MARKER### This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertSame(
			'foo This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInCompleteTemplateTwoTimes() {
		$this->fixture->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);

		$this->fixture->setMarker('marker', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart()
		);

		$this->fixture->setMarker('marker', 'bar');
		$this->assertSame(
			'This is some template code. bar More text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInSubpartTwoTimes() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);

		$this->fixture->setMarker('marker', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);

		$this->fixture->setMarker('marker', 'bar');
		$this->assertSame(
			'This is some template code. bar More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function markerNamesArePrefixesBothUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->fixture->setMarker('my_marker_too', 'bar');
		$this->assertSame(
			'foo bar',
			$this->fixture->getSubpart('')
		);
	}

	/**
	 * @test
	 */
	public function markerNamesAreSuffixesBothUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->fixture->setMarker('also_my_marker', 'bar');
		$this->assertSame(
			'foo bar',
			$this->fixture->getSubpart('')
		);
	}

	/**
	 * @test
	 */
	public function markerNamesArePrefixesFirstUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->assertSame(
			'foo ###MY_MARKER_TOO###',
			$this->fixture->getSubpart('')
		);
	}

	/**
	 * @test
	 */
	public function markerNamesAreSuffixesFirstUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->assertSame(
			'foo ###ALSO_MY_MARKER###',
			$this->fixture->getSubpart('')
		);
	}

	/**
	 * @test
	 */
	public function markerNamesArePrefixesSecondUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->fixture->setMarker('my_marker_too', 'bar');
		$this->assertSame(
			'###MY_MARKER### bar',
			$this->fixture->getSubpart('')
		);
	}

	/**
	 * @test
	 */
	public function markerNamesAreSuffixesSecondUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->fixture->setMarker('also_my_marker', 'bar');
		$this->assertSame(
			'###MY_MARKER### bar',
			$this->fixture->getSubpart('')
		);
	}

	/**
	 * @test
	 */
	public function markerNamesArePrefixesBothUsedWithSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
			.'###MY_MARKER### ###MY_MARKER_TOO###'
			.'<!-- ###MY_SUBPART### -->'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->fixture->setMarker('my_marker_too', 'bar');
		$this->assertSame(
			'foo bar',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function markerNamesAreSuffixesBothUsedWithSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
			.'###MY_MARKER### ###ALSO_MY_MARKER###'
			.'<!-- ###MY_SUBPART### -->'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->fixture->setMarker('also_my_marker', 'bar');
		$this->assertSame(
			'foo bar',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}


	///////////////////////////////////////////////////////////////
	// Tests for replacing subparts with their content on output.
	///////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getUnchangedSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'This is some template code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'This is some subpart code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'More text.'
		);
		$this->assertSame(
			'This is some template code.'
				.'This is some subpart code.'
				.'More text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function getUnchangedDoubleSubpartInCompleteTemplate() {
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
		$this->assertSame(
			'This is some template code.'
				.'This is some subpart code.'
				.'More text.'
				.'This is some subpart code.'
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function getUnchangedSubpartInRequestedSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'This is some subpart code.'
				.'<!-- ###INNER_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertSame(
			'This is some template code.'
				.'This is some subpart code.'
				.'More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getUnchangedDoubleSubpartInRequestedSubpart() {
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
		$this->assertSame(
			'This is some template code.'
				.'This is some subpart code.'
				.'More text.'
				.'This is some subpart code.'
				.'Even more text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}


	///////////////////////////////////////////////////////////////
	// Tests for retrieving subparts with names that are prefixes
	// or suffixes of other subpart names.
	///////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function subpartNamesArePrefixesGetCompleteTemplate() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###MY_SUBPART_TOO### -->'
				.'bar'
				.'<!-- ###MY_SUBPART_TOO### -->'
		);
		$this->assertSame(
			'foo Some more text. bar',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesAreSuffixesGetCompleteTemplate() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###ALSO_MY_SUBPART### -->'
				.'bar'
				.'<!-- ###ALSO_MY_SUBPART### -->'
		);
		$this->assertSame(
			'foo Some more text. bar',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesArePrefixesGetFirstSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###MY_SUBPART_TOO### -->'
				.'bar'
				.'<!-- ###MY_SUBPART_TOO### -->'
		);
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesAreSuffixesGetFirstSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###ALSO_MY_SUBPART### -->'
				.'bar'
				.'<!-- ###ALSO_MY_SUBPART### -->'
		);
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesArePrefixesGetSecondSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###MY_SUBPART_TOO### -->'
				.'bar'
				.'<!-- ###MY_SUBPART_TOO### -->'
		);
		$this->assertSame(
			'bar',
			$this->fixture->getSubpart('MY_SUBPART_TOO')
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesAreSuffixesGetSecondSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'foo'
				.'<!-- ###MY_SUBPART### -->'
				.' Some more text. '
				.'<!-- ###ALSO_MY_SUBPART### -->'
				.'bar'
				.'<!-- ###ALSO_MY_SUBPART### -->'
		);
		$this->assertSame(
			'bar',
			$this->fixture->getSubpart('ALSO_MY_SUBPART')
		);
	}


	////////////////////////////////////////////
	// Tests for hiding and unhiding subparts.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hideSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideOverwrittenSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'More text. ');
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->unhideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartInCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartInSubpart() {
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function twoSubpartInNestedSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###SINGLE_VIEW###  -->'
				.'<!-- ###FIELD_WRAPPER_TITLE### -->'
				.'<h3 class="seminars-item-title">Title'
				.'<!-- ###FIELD_WRAPPER_SUBTITLE### -->'
				.'<span class="seminars-item-subtitle"> - ###SUBTITLE###</span>'
				.'<!-- ###FIELD_WRAPPER_SUBTITLE### -->'
				.'</h3>'
						.'<!-- ###FIELD_WRAPPER_TITLE### -->'
						.'<!-- ###SINGLE_VIEW###  -->'
		);
		$this->fixture->hideSubparts('FIELD_WRAPPER_SUBTITLE');
		$this->assertSame(
			'<h3 class="seminars-item-title">Title'
				.'</h3>',
			$this->fixture->getSubpart('SINGLE_VIEW')
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartInSubpart() {
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
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartInSubpart() {
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
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsSeparately() {
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsWithoutSpaceAfterComma() {
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsInReverseOrder() {
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsWithSpaceAfterComma() {
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideTwoSubpartsSeparately() {
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
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideTwoSubpartsInSameOrder() {
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
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideTwoSubpartsInReverseOrder() {
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
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsUnhideFirst() {
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
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsUnhideSecond() {
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
		$this->assertSame(
			'Some text. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function unhidePermanentlyHiddenSubpart() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('MY_SUBPART', 'MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function unhideOneOfTwoPermanentlyHiddenSubparts() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('MY_SUBPART', 'MY_SUBPART,MY_OTHER_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartAndPermanentlyHideAnother() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('MY_SUBPART', 'MY_OTHER_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function unhidePermanentlyHiddenSubpartWithPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###SUBPART### -->'
				.'Some text. '
				.'<!-- ###SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('SUBPART', 'SUBPART', 'MY');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function unhideOneOfTwoPermanentlyHiddenSubpartsWithPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###SUBPART### -->'
				.'Some text. '
				.'<!-- ###SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('SUBPART', 'SUBPART,OTHER_SUBPART', 'MY');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartAndPermanentlyHideAnotherWithPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###SUBPART### -->'
				.'Some text. '
				.'<!-- ###SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('SUBPART', 'OTHER_SUBPART', 'MY');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function subpartIsInvisibleIfTheSubpartNameIsEmpty() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertFalse(
			$this->fixture->isSubpartVisible('')
		);
	}

	/**
	 * @test
	 */
	public function noExistentSubpartIsInvisible() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FOO')
		);
	}

	/**
	 * @test
	 */
	public function subpartIsVisibleByDefault() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertTrue(
			$this->fixture->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function subpartIsNotVisibleAfterHiding() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->assertFalse(
			$this->fixture->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function subpartIsVisibleAfterHidingAndUnhiding() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->unhideSubparts('MY_SUBPART');
		$this->assertTrue(
			$this->fixture->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartReturnsContentOfVisibleSubpartThatWasFilledWhenHidden() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->fixture->setSubpart('MY_SUBPART', 'foo');
		$this->fixture->unhideSubparts('MY_SUBPART');
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayWithCompleteTemplateHidesSubpart() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
			'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayWithCompleteTemplateHidesOverwrittenSubpart() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'More text. ');
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartsArrayWithCompleteTemplateUnhidesSubpart() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'More text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayWithCompleteTemplateHidesAndUnhidesSubpart() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'More text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesSubpartInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->' .
				'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.' .
				'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesSubpartInNestedSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###SINGLE_VIEW###  -->' .
				'<!-- ###FIELD_WRAPPER_TITLE### -->' .
				'<h3 class="seminars-item-title">Title' .
				'<!-- ###FIELD_WRAPPER_SUBTITLE### -->' .
				'<span class="seminars-item-subtitle"> - ###SUBTITLE###</span>' .
				'<!-- ###FIELD_WRAPPER_SUBTITLE### -->' .
				'</h3>' .
				'<!-- ###FIELD_WRAPPER_TITLE### -->' .
				'<!-- ###SINGLE_VIEW###  -->'
		);
		$this->fixture->hideSubpartsArray(array('FIELD_WRAPPER_SUBTITLE'));
		$this->assertSame(
			'<h3 class="seminars-item-title">Title' .
				'</h3>',
			$this->fixture->getSubpart('SINGLE_VIEW')
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartsArrayUnhidesSubpartInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->' .
				'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.' .
				'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'More text. ' .
				'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesSubpartInSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###OUTER_SUBPART### -->' .
				'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.' .
				'<!-- ###OUTER_SUBPART### -->'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'More text. ' .
				'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesTwoSubpartsSeparately() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_1'));
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesTwoSubparts() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesTwoSubpartsInReverseOrder() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_2', 'MY_SUBPART_1'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsSeparately() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_1'));
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_2'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART_1'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'More text here.' .
				'More text there. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsInSameOrder() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'More text here.' .
				'More text there. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsInReverseOrder() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART_2', 'MY_SUBPART_1'));
		$this->assertSame(
			'Some text. ' .
				'More text here.' .
				'More text there. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesTwoSubpartsAndUnhidesTheFirst() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART_1'));
		$this->assertSame(
			'Some text. ' .
				'More text here.' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesTwoSubpartsAndUnhidesTheSecond() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'More text there. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesPermanentlyHiddenSubpart() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(
			array('MY_SUBPART'), array('MY_SUBPART')
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesOneOfTwoPermanentlyHiddenSubparts() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(
			array('MY_SUBPART'), array('MY_SUBPART', 'MY_OTHER_SUBPART')
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayUnhidesSubpartAndPermanentlyHidesAnother() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(
			array('MY_SUBPART'), array('MY_OTHER_SUBPART')
		);
		$this->assertSame(
			'Some text. ' .
				'More text here. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesPermanentlyHiddenSubpartWithPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###SUBPART### -->' .
				'Some text. ' .
				'<!-- ###SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(
			array('SUBPART'), array('SUBPART'), 'MY'
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesOneOfTwoPermanentlyHiddenSubpartsWithPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###SUBPART### -->' .
				'Some text. ' .
				'<!-- ###SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(
			array('SUBPART'), array('SUBPART', 'OTHER_SUBPART'), 'MY'
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayUnhidesSubpartAndPermanentlyHidesAnotherWithPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###SUBPART### -->' .
				'Some text. ' .
				'<!-- ###SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(
			array('SUBPART'), array('OTHER_SUBPART'), 'MY'
		);
		$this->assertSame(
			'Some text. ' .
				'More text here. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayResultsInNotVisibleSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->assertFalse(
			$this->fixture->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayResultsInVisibleSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertTrue(
			$this->fixture->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayWithFilledSubpartWhenHiddenReturnsContentOfUnhiddenSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->fixture->setSubpart('MY_SUBPART', 'foo');
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}


	////////////////////////////////
	// Tests for setting subparts.
	////////////////////////////////

	/**
	 * @test
	 */
	public function setSubpartNotEmptyGetCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'foo');
		$this->assertSame(
			'Some text. '
				.'foo'
				.' Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartNotEmptyGetSubpart() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'foo');
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setNewSubpartNotEmptyGetSubpart() {
		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'foo');
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameWithSpaceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $subpartName is not valid.'
		);

		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('MY SUBPART', 'foo');
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameWithUtf8UmlautThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $subpartName is not valid.'
		);

		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('MY_SÜBPART', 'foo');
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameWithUnderscoreSuffixThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $subpartName is not valid.'
		);

		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('MY_SUBPART_', 'foo');
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameStartingWithUnderscoreThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $subpartName is not valid.'
		);

		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('_MY_SUBPART', 'foo');
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameStartingWithNumberThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $subpartName is not valid.'
		);

		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('1_MY_SUBPART', 'foo');
	}

	/**
	 * @test
	 */
	public function setSubpartNotEmptyGetOuterSubpart() {
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
		$this->assertSame(
			'Some text. foo Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setSubpartToEmptyGetCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', '');
		$this->assertSame(
			'Some text. '
				.' Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartToEmptyGetSubpart() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', '');
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setSubpartToEmptyGetOuterSubpart() {
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
		$this->assertSame(
			'Some text.  Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setSubpartAndGetSubpartCanHaveUtf8UmlautsInSubpartContent() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
			'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'äöüßÄÖÜßéèáàóò');

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setSubpartAndGetSubpartCanHaveIso88591UmlautsInSubpartContent() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
			'<!-- ###MY_SUBPART### -->'
		);
		// 228 = ä, 223 = ß (in ISO8859-1)
		$this->fixture->setSubpart('MY_SUBPART', chr(228) . chr(223));

		$this->assertSame(
			chr(228) . chr(223),
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}


	//////////////////////////////////////////////////////
	// Tests for setting markers within nested subparts.
	//////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setMarkerInSubpartWithinCompleteTemplate() {
		$this->fixture->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('')
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInSubpartWithinOtherSubpart() {
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
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInOverwrittenSubpartWithinCompleteTemplate() {
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
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('')
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInOverwrittenSubpartWithinOtherSubpart() {
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
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setMarkerWithinNestedInnerSubpart() {
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
		$this->fixture->setMarker('marker', 'foo ');

		$this->assertSame(
			'outer start, inner start, foo inner end, outer end ',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}


	////////////////////////////////////////////////////////////
	// Tests for using the prefix to marker and subpart names.
	////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setMarkerWithPrefix() {
		$this->fixture->processTemplate(
			'This is some template code. '
				.'###FIRST_MARKER### ###MARKER### More text.'
		);
		$this->fixture->setMarker('marker', 'foo', 'first');
		$this->assertSame(
			'This is some template code. foo ###MARKER### More text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartWithPrefix() {
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
		$this->fixture->setSubpart('MY_SUBPART', 'foo', 'FIRST');
		$this->assertSame(
			'Some text. '
				.'foo'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartWithPrefix() {
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
		$this->assertSame(
			'Some text. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartWithPrefix() {
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
		$this->fixture->unhideSubparts('MY_SUBPART', '', 'FIRST');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsWithPrefix() {
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideTwoSubpartsWithPrefix() {
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
		$this->fixture->unhideSubparts('1,2', '', 'FIRST_MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesSubpartWithPrefix() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###FIRST_MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###FIRST_MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'), 'FIRST');
		$this->assertSame(
			'Some text. ' .
				'More text there. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesTwoSubpartsWithPrefix() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###FIRST_MY_SUBPART_1### -->' .
				'More text here. ' .
				'<!-- ###FIRST_MY_SUBPART_1### -->' .
				'<!-- ###FIRST_MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###FIRST_MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(
			array('1', '2'), 'FIRST_MY_SUBPART'
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesSubpartWithPrefix() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###FIRST_MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###FIRST_MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('FIRST_MY_SUBPART'));
		$this->fixture->unhideSubpartsArray(array('MY_SUBPART'), array(''), 'FIRST');
		$this->assertSame(
			'Some text. ' .
				'More text here. ' .
				'More text there. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsWithPrefix() {
		$this->fixture->processTemplate(
			'Some text. ' .
				'<!-- ###FIRST_MY_SUBPART_1### -->' .
				'More text here. ' .
				'<!-- ###FIRST_MY_SUBPART_1### -->' .
				'<!-- ###FIRST_MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###FIRST_MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->fixture->hideSubpartsArray(array('FIRST_MY_SUBPART_1'));
		$this->fixture->hideSubpartsArray(array('FIRST_MY_SUBPART_2'));
		$this->fixture->unhideSubpartsArray(
			array('1', '2'), array(''), 'FIRST_MY_SUBPART'
		);
		$this->assertSame(
			'Some text. ' .
				'More text here. ' .
				'More text there. ' .
				'Even more text.',
			$this->fixture->getSubpart()
		);
	}


	/////////////////////////////////////////////////////////////////////
	// Test for conditional filling and hiding of markers and subparts.
	/////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithPositiveInteger() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotZero('marker', 42)
		);
		$this->assertSame(
			'42',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithNegativeInteger() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotZero('marker', -42)
		);
		$this->assertSame(
			'-42',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithZero() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertFalse(
			$this->fixture->setMarkerIfNotZero('marker', 0)
		);
		$this->assertSame(
			'###MARKER###',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithPositiveIntegerWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotZero('marker', 42, 'MY')
		);
		$this->assertSame(
			'42',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithNegativeIntegerWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotZero('marker', -42, 'MY')
		);
		$this->assertSame(
			'-42',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithZeroWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertFalse(
			$this->fixture->setMarkerIfNotZero('marker', 0, 'MY')
		);
		$this->assertSame(
			'###MY_MARKER###',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotEmptyWithNotEmpty() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotEmpty('marker', 'foo')
		);
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotEmptyWithEmpty() {
		$this->fixture->processTemplate(
			'###MARKER###'
		);

		$this->assertFalse(
			$this->fixture->setMarkerIfNotEmpty('marker', '')
		);
		$this->assertSame(
			'###MARKER###',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotEmptyWithNotEmptyWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertTrue(
			$this->fixture->setMarkerIfNotEmpty('marker', 'foo', 'MY')
		);
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotEmptyWithEmptyWithPrefix() {
		$this->fixture->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertFalse(
			$this->fixture->setMarkerIfNotEmpty('marker', '', 'MY')
		);
		$this->assertSame(
			'###MY_MARKER###',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerWithTrue() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarker(
				'marker', TRUE, 'foo', '', 'WRAPPER'
			)
		);
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerWithFalse() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->fixture->setOrDeleteMarker(
				'marker', FALSE, 'foo', '', 'WRAPPER'
			)
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerWithTrueWithMarkerPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->fixture->setOrDeleteMarker(
				'marker', TRUE, 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerWithFalseWithMarkerPrefix() {
		$this->fixture->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->fixture->setOrDeleteMarker(
				'marker', FALSE, 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithZero() {
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
		$this->assertSame(
			'',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithPositiveIntegers() {
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
		$this->assertSame(
			'42',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithNegativeIntegers() {
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
		$this->assertSame(
			'-42',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithZeroWithMarkerPrefix() {
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
		$this->assertSame(
			'',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithPositiveIntegerWithMarkerPrefix() {
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
		$this->assertSame(
			'42',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithNegativeIntegerWithMarkerPrefix() {
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
		$this->assertSame(
			'-42',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotEmptyWithEmpty() {
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
		$this->assertSame(
			'',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotEmptyWithNotEmpty() {
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
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotEmptyWithEmptyWithMarkerPrefix() {
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
		$this->assertSame(
			'',
			$this->fixture->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotEmptyWithNotEmptyWithMarkerPrefix() {
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
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart()
		);
	}


	///////////////////////////////////////////////////
	// Test concerning unclosed markers and subparts.
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function unclosedMarkersAreIgnored() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'###MY_MARKER_1### '
				.'###MY_MARKER_2 '
				.'###MY_MARKER_3# '
				.'###MY_MARKER_4## '
				.'###MY_MARKER_5###'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->setMarker('my_marker_1', 'test 1');
		$this->fixture->setMarker('my_marker_2', 'test 2');
		$this->fixture->setMarker('my_marker_3', 'test 3');
		$this->fixture->setMarker('my_marker_4', 'test 4');
		$this->fixture->setMarker('my_marker_5', 'test 5');

		$this->assertSame(
			'test 1 '
				.'###MY_MARKER_2 '
				.'###MY_MARKER_3# '
				.'###MY_MARKER_4## '
				.'test 5',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
			'test 1 '
				.'###MY_MARKER_2 '
				.'###MY_MARKER_3# '
				.'###MY_MARKER_4## '
				.'test 5',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function unclosedSubpartsAreIgnored() {
		$this->fixture->processTemplate(
			'Text before. '
				.'<!-- ###UNCLOSED_SUBPART_1### -->'
				.'<!-- ###OUTER_SUBPART### -->'
				.'<!-- ###UNCLOSED_SUBPART_2### -->'
				.'<!-- ###INNER_SUBPART### -->'
				.'<!-- ###UNCLOSED_SUBPART_3### -->'
				.'Inner text. '
				.'<!-- ###UNCLOSED_SUBPART_4### -->'
				.'<!-- ###INNER_SUBPART### -->'
				.'<!-- ###UNCLOSED_SUBPART_5### -->'
				.'<!-- ###OUTER_SUBPART### -->'
				.'<!-- ###UNCLOSED_SUBPART_6### -->'
				.'Text after.'
		);

		$this->assertSame(
			'Text before. '
				.'<!-- ###UNCLOSED_SUBPART_1### -->'
				.'<!-- ###UNCLOSED_SUBPART_2### -->'
				.'<!-- ###UNCLOSED_SUBPART_3### -->'
				.'Inner text. '
				.'<!-- ###UNCLOSED_SUBPART_4### -->'
				.'<!-- ###UNCLOSED_SUBPART_5### -->'
				.'<!-- ###UNCLOSED_SUBPART_6### -->'
				.'Text after.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
			'<!-- ###UNCLOSED_SUBPART_2### -->'
				.'<!-- ###UNCLOSED_SUBPART_3### -->'
				.'Inner text. '
				.'<!-- ###UNCLOSED_SUBPART_4### -->'
				.'<!-- ###UNCLOSED_SUBPART_5### -->',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function unclosedSubpartMarkersAreIgnored() {
		$this->fixture->processTemplate(
			'Text before. '
				.'<!-- ###UNCLOSED_SUBPART_1###'
				.'<!-- ###OUTER_SUBPART### -->'
				.'<!-- ###UNCLOSED_SUBPART_2 -->'
				.'<!-- ###INNER_SUBPART### -->'
				.'<!-- ###UNCLOSED_SUBPART_3### --'
				.'Inner text. '
				.'<!-- UNCLOSED_SUBPART_4### -->'
				.'<!-- ###INNER_SUBPART### -->'
				.' ###UNCLOSED_SUBPART_5### -->'
				.'<!-- ###OUTER_SUBPART### -->'
				.'<!-- ###UNCLOSED_SUBPART_6### -->'
				.'Text after.'
		);

		$this->assertSame(
			'Text before. '
				.'<!-- ###UNCLOSED_SUBPART_1###'
				.'<!-- ###UNCLOSED_SUBPART_2 -->'
				.'<!-- ###UNCLOSED_SUBPART_3### --'
				.'Inner text. '
				.'<!-- UNCLOSED_SUBPART_4### -->'
				.' ###UNCLOSED_SUBPART_5### -->'
				.'<!-- ###UNCLOSED_SUBPART_6### -->'
				.'Text after.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
			'<!-- ###UNCLOSED_SUBPART_2 -->'
				.'<!-- ###UNCLOSED_SUBPART_3### --'
				.'Inner text. '
				.'<!-- UNCLOSED_SUBPART_4### -->'
				.' ###UNCLOSED_SUBPART_5### -->',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function invalidMarkerNamesAreIgnored() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'###MARKER 1### '
				.'###MARKER-2### '
				.'###marker_3### '
				.'###MÄRKER_4### '
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->setMarker('marker 1', 'foo');
		$this->fixture->setMarker('marker-2', 'foo');
		$this->fixture->setMarker('marker_3', 'foo');
		$this->fixture->setMarker('märker_4', 'foo');

		$this->assertSame(
			'###MARKER 1### '
				.'###MARKER-2### '
				.'###marker_3### '
				.'###MÄRKER_4### ',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
			'###MARKER 1### '
				.'###MARKER-2### '
				.'###marker_3### '
				.'###MÄRKER_4### ',
			$this->fixture->getSubpart('MY_SUBPART')
		);
	}


	///////////////////////////////////////////////////
	// Tests for getting subparts with invalid names.
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSubpartWithNameWithSpaceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $key is not valid.'
		);

		$this->fixture->processTemplate(
			'<!-- ###MY SUBPART### -->'
				.'Some text.'
				.'<!-- ###MY SUBPART### -->'
		);

		$this->fixture->getSubpart('MY SUBPART');
	}

	/**
	 * @test
	 */
	public function getSubpartWithNameWithUtf8UmlautThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $key is not valid.'
		);

		$this->fixture->processTemplate(
			'<!-- ###MY_SÜBPART### -->'
				.'Some text.'
				.'<!-- ###MY_SÜBPART### -->'
		);

		$this->fixture->getSubpart('MY_SÜBPART');
	}

	/**
	 * @test
	 */
	public function getSubpartWithNameWithUnderscoreSuffixThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $key is not valid.'
		);

		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART_### -->'
				.'Some text.'
				.'<!-- ###MY_SUBPART_### -->'
		);

		$this->fixture->getSubpart('MY_SUBPART_');
	}

	/**
	 * @test
	 */
	public function getSubpartWithNameStartingWithUnderscoreThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $key is not valid.'
		);

		$this->fixture->processTemplate(
			'<!-- ###_MY_SUBPART### -->'
				.'Some text.'
				.'<!-- ###_MY_SUBPART### -->'
		);

		$this->fixture->getSubpart('_MY_SUBPART');
	}

	/**
	 * @test
	 */
	public function getSubpartWithNameStartingWithNumberThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The value of the parameter $key is not valid.'
		);

		$this->fixture->processTemplate(
			'<!-- ###1_MY_SUBPART### -->'
				.'Some text.'
				.'<!-- ###1_MY_SUBPART### -->'
		);

		$this->fixture->getSubpart('1_MY_SUBPART');
	}

	/**
	 * @test
	 */
	public function getSubpartWithLowercaseNameWithUsingLowercaseThrowsException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'$key contained the subpart name "my_subpart", but only the following subparts are available: ()'

		);

		$this->fixture->processTemplate(
			'<!-- ###my_subpart### -->' .
				'Some text.' .
				'<!-- ###my_subpart### -->'
		);

		$this->fixture->getSubpart('my_subpart');
	}

	/**
	 * @test
	 */
	public function getSubpartWithLowercaseNameWithUsingUppercaseThrowsException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'$key contained the subpart name "MY_SUBPART", but only the following subparts are available: ()'
		);

		$this->fixture->processTemplate(
			'<!-- ###my_subpart### -->' .
				'Some text.' .
				'<!-- ###my_subpart### -->'
		);

		$this->fixture->getSubpart('MY_SUBPART');
	}


	////////////////////////////////////////
	// Tests concerning getPrefixedMarkers
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPrefixedMarkersForNoMatchesReturnsEmptyArray() {
		$this->fixture->processTemplate('');

		$this->assertSame(
			array(),
			$this->fixture->getPrefixedMarkers('foo')
		);
	}

	/**
	 * @test
	 */
	public function getPrefixedMarkersForOneMatchReturnsArrayWithCompleteMarkerName() {
		$this->fixture->processTemplate('###FOO_BAR###');

		$this->assertSame(
			array('FOO_BAR'),
			$this->fixture->getPrefixedMarkers('foo')
		);
	}

	/**
	 * @test
	 */
	public function getPrefixedMarkersForTwoIdenticalMatchesReturnsArrayWithCompleteMarkerNameOnce() {
		$this->fixture->processTemplate('###FOO_BAR### ###FOO_BAR###');

		$this->assertSame(
			array('FOO_BAR'),
			$this->fixture->getPrefixedMarkers('foo')
		);
	}

	/**
	 * @test
	 */
	public function getPrefixedMarkersForTwoMatchesReturnsArrayWithCompleteMarkerNames() {
		$this->fixture->processTemplate('###FOO_BAR### ###FOO_BAZ###');

		$this->assertSame(
			array('FOO_BAR', 'FOO_BAZ'),
			$this->fixture->getPrefixedMarkers('foo')
		);
	}
}
?>