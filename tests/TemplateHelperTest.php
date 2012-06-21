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
 * Testcase for the tx_oelib_templatehelper class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_TemplateHelperTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_templatehelperchild
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_oelib');
		$this->testingFramework->createFakeFrontEnd();
		tx_oelib_configurationProxy::getInstance('oelib')
			->setAsBoolean('enableConfigCheck', TRUE);

		$this->fixture = new tx_oelib_templatehelperchild(array());
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	/////////////////////////////////////////////////////////////////
	// Tests concerning the creation of the template helper object.
	/////////////////////////////////////////////////////////////////

	public function testConfigurationCheckCreationForEnabledConfigurationCheck() {
		// This test relies on the config check to be enabled during setUp().
		$this->assertNotNull(
			$this->fixture->getConfigurationCheck()
		);
	}

	public function testConfigurationCheckCreationForDisabledConfigurationCeck() {
		tx_oelib_configurationProxy::getInstance('oelib')
			->setAsBoolean('enableConfigCheck', FALSE);
		$fixture = new tx_oelib_templatehelperchild();
		$result = $fixture->getConfigurationCheck();

		$fixture->__destruct();
		unset($fixture);

		$this->assertNull(
			$result
		);
	}


	/////////////////////////////////////////////////////////////
	// Tests concerning using the template without an HTML file
	/////////////////////////////////////////////////////////////

	public function testProcessTemplateWithoutTemplateFileDoesNotThrowException() {
		$this->fixture->processTemplate('foo');
	}

	public function testProcessTemplateTwoTimesWillUseTheLastSetTemplate() {
		$this->fixture->processTemplate('foo');
		$this->fixture->processTemplate('bar');

		$this->assertSame(
			'bar',
			$this->fixture->getSubpart()
		);
	}


	///////////////////////////////////////////////////////////////////////
	// Tests for the behavior of the template helper without a front end.
	///////////////////////////////////////////////////////////////////////

	public function testInitMarksObjectAsInitialized() {
		$this->fixture->init();

		$this->assertTrue(
			$this->fixture->isInitialized()
		);
	}

	public function testInitInitializesContentObject() {
		$this->assertTrue(
			$this->fixture->cObj instanceof tslib_cObj
		);
	}


	////////////////////////////////////////////////////////
	// Tests for setting and reading configuration values.
	////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setCachedConfigurationValueCreatesConfigurationForNewInstance() {
		$this->testingFramework->discardFakeFrontEnd();

		tx_oelib_templatehelper::setCachedConfigurationValue('foo', 'bar');

		$fixture = new tx_oelib_templatehelper();
		$fixture->init();

		$this->assertSame(
			'bar',
			$fixture->getConfValueString('foo')
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function purgeCachedConfigurationsDropsCachedConfiguration() {
		$this->testingFramework->discardFakeFrontEnd();

		tx_oelib_templatehelper::setCachedConfigurationValue('foo', 'bar');
		tx_oelib_templatehelper::purgeCachedConfigurations();

		$fixture = new tx_oelib_templatehelper();
		$fixture->init();

		$this->assertSame(
			'',
			$fixture->getConfValueString('foo')
		);

		$fixture->__destruct();
	}

	public function testConfigurationInitiallyIsAnEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getConfiguration()
		);
	}

	public function testSetConfigurationValueFailsWithAnEmptyKey() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty'
		);

		$this->fixture->setConfigurationValue('', 'test');
	}

	public function testSetConfigurationValueWithNonEmptyStringChangesTheConfiguration() {
		$this->fixture->setConfigurationValue('test', 'This is a test.');
		$this->assertSame(
			array('test' => 'This is a test.'),
			$this->fixture->getConfiguration()
		);
	}

	public function testSetConfigurationValueWithEmptyStringChangesTheConfiguration() {
		$this->fixture->setConfigurationValue('test', '');
		$this->assertSame(
			array('test' => ''),
			$this->fixture->getConfiguration()
		);
	}

	public function testSetConfigurationValueStringNotEmpty() {
		$this->fixture->setConfigurationValue('test', 'This is a test.');
		$this->assertSame(
			'This is a test.', $this->fixture->getConfValueString('test')
		);
	}

	public function testGetListViewConfigurationValueStringReturnsAString() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => 'This is a test.')
		);

		$this->assertSame(
			'This is a test.',
			$this->fixture->getListViewConfValueString('test')
		);
	}

	public function testGetListViewConfigurationValueStringReturnsATrimmedString() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => ' string ')
		);

		$this->assertSame(
			'string',
			$this->fixture->getListViewConfValueString('test')
		);
	}

	public function testGetListViewConfigurationValueStringReturnsEmptyStringWhichWasSet() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => '')
		);

		$this->assertSame(
			'',
			$this->fixture->getListViewConfValueString('test')
		);
	}

	public function testGetListViewConfigurationValueStringReturnsEmptyStringIfNoValueSet() {
		$this->fixture->setConfigurationValue(
			'listView.', array()
		);

		$this->assertSame(
			'',
			$this->fixture->getListViewConfValueString('test')
		);
	}

	public function testGetListViewConfigurationValueIntegerReturnsNumber() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => '123')
		);

		$this->assertSame(
			123,
			$this->fixture->getListViewConfValueInteger('test')
		);
	}

	public function testGetListViewConfigurationValueIntegerReturnsZeroIfTheValueWasEmpty() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => '')
		);

		$this->assertSame(
			0,
			$this->fixture->getListViewConfValueInteger('test')
		);
	}

	public function testGetListViewConfigurationValueIntegerReturnsZeroIfTheValueWasNoInteger() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => 'string')
		);

		$this->assertSame(
			0,
			$this->fixture->getListViewConfValueInteger('test')
		);
	}

	public function testGetListViewConfigurationValueIntegerReturnsZeroIfNoValueWasSet() {
		$this->fixture->setConfigurationValue(
			'listView.', array()
		);

		$this->assertSame(
			0,
			$this->fixture->getListViewConfValueInteger('test')
		);
	}

	public function testGetListViewConfigurationValueBooleanReturnsTrue() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => '1')
		);

		$this->assertSame(
			TRUE,
			$this->fixture->getListViewConfValueBoolean('test')
		);
	}

	public function testGetListViewConfigurationValueBooleanReturnsTrueIfTheValueWasAPositiveInteger() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => '123')
		);

		$this->assertSame(
			TRUE,
			$this->fixture->getListViewConfValueBoolean('test')
		);
	}

	public function testGetListViewConfigurationValueBooleanReturnsFalseIfTheValueWasZero() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => '0')
		);

		$this->assertSame(
			FALSE,
			$this->fixture->getListViewConfValueBoolean('test')
		);
	}

	public function testGetListViewConfigurationValueBooleanReturnsFalseIfTheValueWasAnEmptyString() {
		$this->fixture->setConfigurationValue(
			'listView.', array('test' => '')
		);

		$this->assertSame(
			FALSE,
			$this->fixture->getListViewConfValueBoolean('test')
		);
	}

	public function testGetListViewConfigurationValueBooleanReturnsFalseIfTheValueWasNotSet() {
		$this->fixture->setConfigurationValue(
			'listView.', array()
		);

		$this->assertSame(
			FALSE,
			$this->fixture->getListViewConfValueBoolean('test')
		);
	}

	public function testGetListViewConfigurationValueThrowsAnExeptionIfNoFieldNameWasProvided() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$fieldName must not be empty.'
		);

		$this->fixture->getListViewConfValueBoolean('');
	}


	////////////////////////////////////////////
	// Tests for reading the HTML from a file.
	////////////////////////////////////////////

	public function testGetCompleteTemplateFromFile() {
		$this->fixture->setConfigurationValue(
			'templateFile', 'EXT:oelib/tests/fixtures/oelib.html'
		);
		$this->fixture->getTemplateCode(TRUE);

		$this->assertSame(
			'Hello world!'.LF, $this->fixture->getSubpart()
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	///////////////////////////////
	// Tests for getting subparts.
	///////////////////////////////

	public function testNoSubpartsAndEmptySubpartName() {
		$this->assertSame(
			'', $this->fixture->getSubpart()
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testNotExistingSubpartName() {
		$this->assertSame(
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

	public function testGetCompleteTemplateReturnsCompleteTemplateContent() {
		$templateCode = 'This is a test including'.LF.'a linefeed.'.LF;
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$templateCode, $this->fixture->getSubpart()
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetCompleteTemplateCanContainUtf8Umlauts() {
		$this->fixture->processTemplate('äöüßÄÖÜßéèáàóò');

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->fixture->getSubpart()
		);
	}

	public function testGetCompleteTemplateCanContainIso88591Umlauts() {
		// 228 = ä, 223 = ß (in ISO8859-1)
		$this->fixture->processTemplate(chr(228) . chr(223));

		$this->assertSame(
			chr(228) . chr(223),
			$this->fixture->getSubpart()
		);
	}

	public function testGetCompleteTemplateWithComment() {
		$templateCode = 'This is a test including a comment. '
			.'<!-- This is a comment. -->'
			.'And some more text.';
		$this->fixture->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$templateCode, $this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetSubpartFromTemplateCanContainUtf8Umlauts() {
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

	public function testGetSubpartFromTemplateCanContainIso88591Umlauts() {
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
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			$subpartContent, $this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'outer start, inner start, '.$subpartContent.'inner end, outer end ',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetEmptyExistingSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetHiddenSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayAndGetHiddenSubpartReturnsEmptySubpartContent() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}



	//////////////////////////////////
	// Tests for filling in markers.
	//////////////////////////////////

	public function testGetInexistentMarkerWillReturnAnEmptyString() {
		$this->fixture->processTemplate(
			'foo'
		);
		$this->assertSame(
			'', $this->fixture->getMarker('bar')
		);
	}

	public function testSetAndGetInexistentMarkerSucceeds() {
		$this->fixture->processTemplate(
			'foo'
		);

		$this->fixture->setMarker('bar', 'test');
		$this->assertSame(
			'test', $this->fixture->getMarker('bar')
		);
	}

	public function testSetAndGetExistingMarkerSucceeds() {
		$this->fixture->processTemplate(
			'###BAR###'
		);

		$this->fixture->setMarker('bar', 'test');
		$this->assertSame(
			'test', $this->fixture->getMarker('bar')
		);
	}

	public function testSetMarkerAndGetMarkerCanHaveUtf8UmlautsInMarkerContent() {
		$this->fixture->processTemplate(
			'###BAR###'
		);
		$this->fixture->setMarker('bar', 'äöüßÄÖÜßéèáàóò');

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->fixture->getMarker('bar')
		);
	}

	public function testSetMarkerAndGetMarkerCanHaveIso88591UmlautsInMarkerContent() {
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

	public function testSetLowercaseMarkerInCompleteTemplate() {
		$this->fixture->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);
		$this->fixture->setMarker('marker', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetUppercaseMarkerInCompleteTemplate() {
		$this->fixture->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);
		$this->fixture->setMarker('MARKER', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'foo This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetMarkerInCompleteTemplateTwoTimes() {
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
		$this->assertSame(
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
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);

		$this->fixture->setMarker('marker', 'bar');
		$this->assertSame(
			'This is some template code. bar More text.',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesArePrefixesBothUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->fixture->setMarker('my_marker_too', 'bar');
		$this->assertSame(
			'foo bar',
			$this->fixture->getSubpart('')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesAreSuffixesBothUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->fixture->setMarker('also_my_marker', 'bar');
		$this->assertSame(
			'foo bar',
			$this->fixture->getSubpart('')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesArePrefixesFirstUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->assertSame(
			'foo ###MY_MARKER_TOO###',
			$this->fixture->getSubpart('')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesAreSuffixesFirstUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->fixture->setMarker('my_marker', 'foo');
		$this->assertSame(
			'foo ###ALSO_MY_MARKER###',
			$this->fixture->getSubpart('')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesArePrefixesSecondUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->fixture->setMarker('my_marker_too', 'bar');
		$this->assertSame(
			'###MY_MARKER### bar',
			$this->fixture->getSubpart('')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesAreSuffixesSecondUsed() {
		$this->fixture->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->fixture->setMarker('also_my_marker', 'bar');
		$this->assertSame(
			'###MY_MARKER### bar',
			$this->fixture->getSubpart('')
		);
		$this->assertSame(
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
		$this->assertSame(
			'foo bar',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testMarkerNamesAreSuffixesBothUsedWithSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	///////////////////////////////////////////////////////////////
	// Tests for replacing subparts with their content on output.
	///////////////////////////////////////////////////////////////

	public function testGetUnchangedSubpartInCompleteTemplate() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetUnchangedDoubleSubpartInCompleteTemplate() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetUnchangedSubpartInRequestedSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testGetUnchangedDoubleSubpartInRequestedSubpart() {
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
		$this->assertSame(
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
		$this->assertSame(
			'foo Some more text. bar',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'foo Some more text. bar',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'bar',
			$this->fixture->getSubpart('MY_SUBPART_TOO')
		);
		$this->assertSame(
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
		$this->assertSame(
			'bar',
			$this->fixture->getSubpart('ALSO_MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testTwoSubpartInNestedSubpart() {
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
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhidePermanentlyHiddenSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhideOneOfTwoPermanentlyHiddenSubparts() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhideSubpartAndPermanentlyHideAnother() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhidePermanentlyHiddenSubpartWithPrefix() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhideOneOfTwoPermanentlyHiddenSubpartsWithPrefix() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhideSubpartAndPermanentlyHideAnotherWithPrefix() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartIsInvisibleIfTheSubpartNameIsEmpty() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertFalse(
			$this->fixture->isSubpartVisible('')
		);
	}

	public function testNoExistentSubpartIsInvisible() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FOO')
		);
	}

	public function testSubpartIsVisibleByDefault() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertTrue(
			$this->fixture->isSubpartVisible('MY_SUBPART')
		);
	}

	public function testSubpartIsNotVisibleAfterHiding() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubparts('MY_SUBPART');
		$this->assertFalse(
			$this->fixture->isSubpartVisible('MY_SUBPART')
		);
	}

	public function testSubpartIsVisibleAfterHidingAndUnhiding() {
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

	public function testGetSubpartReturnsContentOfVisibleSubpartThatWasFilledWhenHidden() {
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

	public function testHideSubpartsArrayWithCompleteTemplateHidesSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayWithCompleteTemplateHidesOverwrittenSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhideSubpartsArrayWithCompleteTemplateUnhidesSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayWithCompleteTemplateHidesAndUnhidesSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayHidesSubpartInSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayHidesSubpartInNestedSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testUnhideSubpartsArrayUnhidesSubpartInSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesAndUnhidesSubpartInSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayHidesTwoSubpartsSeparately() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayHidesTwoSubparts() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayHidesTwoSubpartsInReverseOrder() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsSeparately() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsInSameOrder() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsInReverseOrder() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesTwoSubpartsAndUnhidesTheFirst() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesTwoSubpartsAndUnhidesTheSecond() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesPermanentlyHiddenSubpart() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesOneOfTwoPermanentlyHiddenSubparts() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayUnhidesSubpartAndPermanentlyHidesAnother() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesPermanentlyHiddenSubpartWithPrefix() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesOneOfTwoPermanentlyHiddenSubpartsWithPrefix() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayUnhidesSubpartAndPermanentlyHidesAnotherWithPrefix() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayResultsInNotVisibleSubpart() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->fixture->hideSubpartsArray(array('MY_SUBPART'));
		$this->assertFalse(
			$this->fixture->isSubpartVisible('MY_SUBPART')
		);
	}

	public function testHideAndUnhideSubpartsArrayResultsInVisibleSubpart() {
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

	public function testHideAndUnhideSubpartsArrayWithFilledSubpartWhenHiddenReturnsContentOfUnhiddenSubpart() {
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

	public function testSetSubpartNotEmptyGetCompleteTemplate() {
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
		$this->assertSame(
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
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetNewSubpartNotEmptyGetSubpart() {
		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('MY_SUBPART', 'foo');
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetNewSubpartWithNameWithSpaceCreatesWarning() {
		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('MY SUBPART', 'foo');
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY SUBPART')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetNewSubpartWithNameWithUtf8UmlautCreatesWarning() {
		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('MY_SÜBPART', 'foo');
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SÜBPART')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetNewSubpartWithNameWithUnderscoreSuffixCreatesWarning() {
		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('MY_SUBPART_', 'foo');
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SUBPART_')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetNewSubpartWithNameStartingWithUnderscoreCreatesWarning() {
		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('_MY_SUBPART', 'foo');
		$this->assertSame(
			'',
			$this->fixture->getSubpart('_MY_SUBPART')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetNewSubpartWithNameStartingWithNumberCreatesWarning() {
		$this->fixture->processTemplate(
			'Some text.'
		);
		$this->fixture->setSubpart('1_MY_SUBPART', 'foo');
		$this->assertSame(
			'',
			$this->fixture->getSubpart('1_MY_SUBPART')
		);
		$this->assertNotSame(
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
		$this->assertSame(
			'Some text. foo Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.' Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text.  Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSetSubpartAndGetSubpartCanHaveUtf8UmlautsInSubpartContent() {
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

	public function testSetSubpartAndGetSubpartCanHaveIso88591UmlautsInSubpartContent() {
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

	public function testSetMarkerInSubpartWithinCompleteTemplate() {
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
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('')
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->fixture->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
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
		$this->fixture->setMarker('marker', 'foo ');

		$this->assertSame(
			'outer start, inner start, foo inner end, outer end ',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
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
		$this->assertSame(
			'This is some template code. foo ###MARKER### More text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->fixture->unhideSubparts('MY_SUBPART', '', 'FIRST');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
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
		$this->fixture->unhideSubparts('1,2', '', 'FIRST_MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'More text there. '
				.'Even more text.',
			$this->fixture->getSubpart()
		);
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayHidesSubpartWithPrefix() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideSubpartsArrayHidesTwoSubpartsWithPrefix() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesAndUnhidesSubpartWithPrefix() {
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
		$this->assertSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testHideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsWithPrefix() {
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
		$this->assertSame(
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
		$this->assertSame(
			'a foo b',
			$this->fixture->getSubpart()
		);
	}

	public function testSetLabelsNoSalutation() {
		$this->fixture->processTemplate(
			'a ###LABEL_BAR### b'
		);
		$this->fixture->setLabels();
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
			'a bar (informal) b',
			$this->fixture->getSubpart()
		);
	}

	public function testSetLabelsWithOneBeingThePrefixOfAnother() {
		$this->fixture->processTemplate(
			'###LABEL_FOO###, ###LABEL_FOO2###'
		);
		$this->fixture->setLabels();
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
				'marker', TRUE, 'foo', '', 'WRAPPER'
			)
		);
		$this->assertSame(
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
				'marker', FALSE, 'foo', '', 'WRAPPER'
			)
		);
		$this->assertSame(
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
				'marker', TRUE, 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
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
				'marker', FALSE, 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
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
		$this->assertSame(
			'foo',
			$this->fixture->getSubpart()
		);
	}


	///////////////////////////////////////////////////
	// Test concerning unclosed markers and subparts.
	///////////////////////////////////////////////////

	public function testUnclosedMarkersAreIgnored() {
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

	public function testUnclosedSubpartsAreIgnored() {
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

	public function testUnclosedSubpartMarkersAreIgnored() {
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

	public function testInvalidMarkerNamesAreIgnored() {
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

	public function testSubpartWithNameWithSpaceIsIgnored() {
		$this->fixture->processTemplate(
			'<!-- ###MY SUBPART### -->'
				.'Some text.'
				.'<!-- ###MY SUBPART### -->'
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY SUBPART')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartWithNameWithUtf8UmlautIsIgnored() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SÜBPART### -->'
				.'Some text.'
				.'<!-- ###MY_SÜBPART### -->'
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SÜBPART')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartWithNameWithUnderscoreSuffixIsIgnored() {
		$this->fixture->processTemplate(
			'<!-- ###MY_SUBPART_### -->'
				.'Some text.'
				.'<!-- ###MY_SUBPART_### -->'
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SUBPART_')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartWithNameStartingWithUnderscoreIsIgnored() {
		$this->fixture->processTemplate(
			'<!-- ###_MY_SUBPART### -->'
				.'Some text.'
				.'<!-- ###_MY_SUBPART### -->'
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart('_MY_SUBPART')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartWithNameStartingWithNumberIsIgnored() {
		$this->fixture->processTemplate(
			'<!-- ###1_MY_SUBPART### -->'
				.'Some text.'
				.'<!-- ###1_MY_SUBPART### -->'
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart('1_MY_SUBPART')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartWithLowercaseNameIsIgnoredWithUsingLowercase() {
		$this->fixture->processTemplate(
			'<!-- ###my_subpart### -->'
				.'Some text.'
				.'<!-- ###my_subpart### -->'
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart('my_subpart')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}

	public function testSubpartWithLowercaseNameIsIgnoredWithUsingUppercase() {
		$this->fixture->processTemplate(
			'<!-- ###my_subpart### -->'
				.'Some text.'
				.'<!-- ###my_subpart### -->'
		);
		$this->assertSame(
			'',
			$this->fixture->getSubpart('MY_SUBPART')
		);
		$this->assertNotSame(
			'', $this->fixture->getWrappedConfigCheckMessage()
		);
	}


	///////////////////////////////////
	// Tests concerning TS templates.
	///////////////////////////////////

	public function testPageSetupInitallyIsEmpty() {
		$pageId = $this->testingFramework->createFrontEndPage();
		$this->assertSame(
			array(),
			$this->fixture->retrievePageConfig($pageId)
		);
	}


	//////////////////////////////////////////
	// Tests concerning the image functions.
	//////////////////////////////////////////

	public function testCreateRestrictedImageThrowsExceptionForEmptyPath() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$path must not be empty.'
		);

		$this->fixture->createRestrictedImage('');
	}

	public function testCreateRestrictedImageThrowsExceptionForNonZeroMaxArea() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$maxArea is not used anymore and must be zero.'
		);

		$this->fixture->createRestrictedImage(
			'typo3conf/ext/oelib/tests/fixtures/test.png', '', 0, 0, 1
		);
	}

	/**
	 * @test
	 */
	public function createRestrictedImageForInexistentFileReturnsAltText() {
		$this->assertContains(
			'foo',
			$this->fixture->createRestrictedImage(
				'typo3conf/ext/oelib/tests/fixtures/nothing.png',
				'foo'
			)
		);
	}

	public function testCreateRestrictedImageReturnsImgTagForRelativePathToExistingFile() {
		$this->assertContains(
			'<img ',
			$this->fixture->createRestrictedImage(
				'typo3conf/ext/oelib/tests/fixtures/test.png'
			)
		);
	}

	public function testCreateRestrictedImageContainsEmptyAltTextByDefault() {
		$this->assertContains(
			' alt=""',
			$this->fixture->createRestrictedImage(
				'EXT:oelib/tests/fixtures/test.png'
			)
		);
	}

	public function testCreateRestrictedImageContainsProvidedNonEmptyAltText() {
		$this->assertContains(
			' alt="foo"',
			$this->fixture->createRestrictedImage(
				'EXT:oelib/tests/fixtures/test.png',
				'foo'
			)
		);
	}

	public function testCreateRestrictedImageContainsProvidedNonEmptyTitleText() {
		$this->assertContains(
			' title="foo"',
			$this->fixture->createRestrictedImage(
				'EXT:oelib/tests/fixtures/test.png',
				'', 0, 0, 0, 'foo'
			)
		);
	}

	public function testCreateRestrictedImageContainsProvidedNonEmptyAltAndTitleTexts() {
		$result = $this->fixture->createRestrictedImage(
			'EXT:oelib/tests/fixtures/test.png',
			'alt foo', 0, 0, 0, 'title bar'
		);

		$this->assertContains(
			' alt="alt foo"',
			$result
		);
		$this->assertContains(
			' title="title bar"',
			$result
		);
	}

	public function testCreateRestrictedImageContainsProvidedNonEmptyId() {
		$result = $this->fixture->createRestrictedImage(
			'EXT:oelib/tests/fixtures/test.png',
			'', 0, 0, 0, '', 'test-id'
		);

		$this->assertContains(
			' id="test-id"',
			$result
		);
	}

	public function testCreateRestrictedImageContainsNoIdTagForEmptyProvidedId() {
		$result = $this->fixture->createRestrictedImage(
			'EXT:oelib/tests/fixtures/test.png',
			'', 0, 0, 0, '', ''
		);

		$this->assertNotContains(
			' id=',
			$result
		);
	}

	///////////////////////////////////////////////////
	// Tests for securePiVars and ensureIntegerPiVars
	///////////////////////////////////////////////////

	public function testEnsureIntegerPiVarsDefinesAPiVarsArrayWithShowUidPointerAndModeIfPiVarsWasUndefined() {
		unset($this->fixture->piVars);
		$this->fixture->ensureIntegerPiVars();

		$this->assertSame(
			array('showUid' => 0, 'pointer' => 0, 'mode' => 0),
			$this->fixture->piVars
		);
	}

	public function testEnsureIntegerPiVarsDefinesProvidedAdditionalParameterIfPiVarsWasUndefined() {
		$this->fixture->piVars = array();
		$this->fixture->ensureIntegerPiVars(array('additionalParameter'));

		$this->assertSame(
			array('showUid' => 0, 'pointer' => 0, 'mode' => 0, 'additionalParameter' => 0),
			$this->fixture->piVars
		);
	}

	public function testEnsureIntegerPiVarsIntvalsAnAlreadyDefinedAdditionalParameter() {
		$this->fixture->piVars = array();
		$this->fixture->piVars['additionalParameter'] = 1.1;
		$this->fixture->ensureIntegerPiVars(array('additionalParameter'));

		$this->assertSame(
			array(
				'additionalParameter' => 1,
				'showUid' => 0,
				'pointer' => 0,
				'mode' => 0
			),
			$this->fixture->piVars
		);
	}

	public function testEnsureIntegerPiVarsDoesNotIntvalADefinedPiVarWhichIsNotInTheListOfPiVarsToSecure() {
		$this->fixture->piVars = array();
		$this->fixture->piVars['non-integer'] = 'foo';
		$this->fixture->ensureIntegerPiVars();

		$this->assertSame(
			array('non-integer' => 'foo', 'showUid' => 0, 'pointer' => 0, 'mode' => 0),
			$this->fixture->piVars
		);
	}

	public function testEnsureIntegerPiVarsIntvalsAnAlreadyDefinedShowUid() {
		$this->fixture->piVars = array();
		$this->fixture->piVars['showUid'] = 1.1;
		$this->fixture->ensureIntegerPiVars();

		$this->assertSame(
			array('showUid' => 1, 'pointer' => 0, 'mode' => 0),
			$this->fixture->piVars
		);
	}


	/////////////////////////////////////////
	// Tests concerning ensureContentObject
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function ensureContentObjectForExistingContentObjectLeavesItUntouched() {
		$contentObject = new tslib_cObj();
		$this->fixture->cObj = $contentObject;

		$this->fixture->ensureContentObject();

		$this->assertSame(
			$contentObject,
			$this->fixture->cObj
		);
	}

	/**
	 * @test
	 */
	public function ensureContentObjectForMissingContentObjectWithFrontEndUsesContentObjectFromFrontEnd() {
		$this->fixture->cObj = NULL;

		$this->fixture->ensureContentObject();

		$this->assertSame(
			$GLOBALS['TSFE']->cObj,
			$this->fixture->cObj
		);
	}


	///////////////////////////////////
	// Tests concerning getStoragePid
	///////////////////////////////////

	public function test_getStoragePidForNoSetGRSP_ReturnsZero() {
		$this->assertSame(
			0,
			$this->fixture->getStoragePid()
		);
	}

	public function test_getStoragePidForGRSPSet_ReturnsThisId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);

		$this->assertSame(
			42,
			$this->fixture->getStoragePid()
		);
	}

	public function test_hasStoragePidForGRSPSet_ReturnsTrue() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);

		$this->assertTrue(
			$this->fixture->hasStoragePid()
		);
	}

	public function test_hasStoragePidForNoGRSPSet_ReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasStoragePid()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning ensureIntegerArrayValues
	//////////////////////////////////////////////

	public function test_ensureIntegerArrayValuesForEmptyArrayGiven_DoesNotAddAnyPiVars() {
		$originalPiVars = $this->fixture->piVars;
		$this->fixture->ensureIntegerArrayValues(array());

		$this->assertSame(
			$originalPiVars,
			$this->fixture->piVars
		);
	}

	public function test_ensureIntegerArrayValuesForNotSetPiVarGiven_DoesNotAddThisPiVar() {
		$originalPiVars = $this->fixture->piVars;
		$this->fixture->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			$originalPiVars,
			$this->fixture->piVars
		);
	}

	public function test_ensureIntegerArrayValuesForPiVarNotArray_DoesNotModifyThisPiVar() {
		$this->fixture->piVars['foo'] = 'Hallo';
		$this->fixture->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			'Hallo',
			$this->fixture->piVars['foo']
		);
	}

	public function test_ensureIntegerArrayValuesForValidIntegerInArray_DoesNotModifyThisArrayElement() {
		$this->fixture->piVars['foo'] = array(10);
		$this->fixture->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			10,
			$this->fixture->piVars['foo'][0]
		);
	}

	public function test_ensureIntegerArrayValuesForStringInArray_RemovesThisArrayElement() {
		$this->fixture->piVars['foo'] = array('Hallo');
		$this->fixture->ensureIntegerArrayValues(array('foo'));

		$this->assertTrue(
			empty($this->fixture->piVars['foo'])
		);
	}

	public function test_ensureIntegerArrayValuesForIntegerFollowedByStringInArray_RemovesStringFromArrayElement() {
		$this->fixture->piVars['foo'] = array('2;blubb');
		$this->fixture->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			2,
			$this->fixture->piVars['foo'][0]
		);
	}

	public function test_ensureIntegerArrayValuesForSingleInArray_RemovesNumbersAfterDecimalPoint() {
		$this->fixture->piVars['foo'] = array(2.3);
		$this->fixture->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			2,
			$this->fixture->piVars['foo'][0]
		);
	}

	public function test_ensureIntegerArrayValuesForZeroInArray_RemovesThisArrayElement() {
		$this->fixture->piVars['foo'] = array(0);
		$this->fixture->ensureIntegerArrayValues(array('foo'));

		$this->assertTrue(
			empty($this->fixture->piVars['foo'])
		);
	}

	public function test_ensureIntegerArrayValuesMultiplePiKeysGiven_ValidatesElementsOfAllPiVars() {
		$this->fixture->piVars['foo'] = array('2;blubb');
		$this->fixture->piVars['bar'] = array('42');

		$this->fixture->ensureIntegerArrayValues(array('foo', 'bar'));

		$this->assertSame(
			2,
			$this->fixture->piVars['foo'][0]
		);
		$this->assertSame(
			42,
			$this->fixture->piVars['bar'][0]
		);
	}
}
?>