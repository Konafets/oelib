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
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_TemplateHelperTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingTemplateHelper
	 */
	protected $subject = NULL;
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var bool
	 */
	protected $deprecationLogEnabledBackup = FALSE;

	protected function setUp() {
		$this->deprecationLogEnabledBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];

		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');
		$pageUid = $this->testingFramework->createFrontEndPage(0, array('storage_pid' => 0));
		$this->testingFramework->createFakeFrontEnd($pageUid);
		Tx_Oelib_ConfigurationProxy::getInstance('oelib')->setAsBoolean('enableConfigCheck', TRUE);

		$this->subject = new Tx_Oelib_TestingTemplateHelper(array());
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = $this->deprecationLogEnabledBackup;
	}


	/////////////////////////////////////////////////////////////////
	// Tests concerning the creation of the template helper object.
	/////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function configurationCheckCreationForEnabledConfigurationCheck() {
		// This test relies on the config check to be enabled during setUp().
		$this->assertNotNull(
			$this->subject->getConfigurationCheck()
		);
	}

	/**
	 * @test
	 */
	public function configurationCheckCreationForDisabledConfigurationCeck() {
		Tx_Oelib_ConfigurationProxy::getInstance('oelib')
			->setAsBoolean('enableConfigCheck', FALSE);
		$subject = new Tx_Oelib_TestingTemplateHelper();
		$result = $subject->getConfigurationCheck();

		$this->assertNull(
			$result
		);
	}


	/////////////////////////////////////////////////////////////
	// Tests concerning using the template without an HTML file
	/////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function processTemplateWithoutTemplateFileDoesNotThrowException() {
		$this->subject->processTemplate('foo');
	}

	/**
	 * @test
	 */
	public function processTemplateTwoTimesWillUseTheLastSetTemplate() {
		$this->subject->processTemplate('foo');
		$this->subject->processTemplate('bar');

		$this->assertSame(
			'bar',
			$this->subject->getSubpart()
		);
	}


	///////////////////////////////////////////////////////////////////////
	// Tests for the behavior of the template helper without a front end.
	///////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function initMarksObjectAsInitialized() {
		$this->subject->init();

		$this->assertTrue(
			$this->subject->isInitialized()
		);
	}

	/**
	 * @test
	 */
	public function initInitializesContentObject() {
		$this->subject->init();

		$contentObjectClassName = (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6000000)
			? 'tslib_cObj' : 'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer';
		$this->assertInstanceOf(
			$contentObjectClassName,
			$this->subject->cObj
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

		Tx_Oelib_TemplateHelper::setCachedConfigurationValue('foo', 'bar');

		$subject = new Tx_Oelib_TemplateHelper();
		$subject->init();

		$this->assertSame(
			'bar',
			$subject->getConfValueString('foo')
		);
	}

	/**
	 * @test
	 */
	public function purgeCachedConfigurationsDropsCachedConfiguration() {
		$this->testingFramework->discardFakeFrontEnd();

		Tx_Oelib_TemplateHelper::setCachedConfigurationValue('foo', 'bar');
		Tx_Oelib_TemplateHelper::purgeCachedConfigurations();

		$subject = new Tx_Oelib_TemplateHelper();
		$subject->init();

		$this->assertSame(
			'',
			$subject->getConfValueString('foo')
		);
	}

	/**
	 * @test
	 */
	public function configurationInitiallyIsAnEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function setConfigurationValueFailsWithAnEmptyKey() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty'
		);

		$this->subject->setConfigurationValue('', 'test');
	}

	/**
	 * @test
	 */
	public function setConfigurationValueWithNonEmptyStringChangesTheConfiguration() {
		$this->subject->setConfigurationValue('test', 'This is a test.');
		$this->assertSame(
			array('test' => 'This is a test.'),
			$this->subject->getConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function setConfigurationValueWithEmptyStringChangesTheConfiguration() {
		$this->subject->setConfigurationValue('test', '');
		$this->assertSame(
			array('test' => ''),
			$this->subject->getConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function setConfigurationValueStringNotEmpty() {
		$this->subject->setConfigurationValue('test', 'This is a test.');
		$this->assertSame(
			'This is a test.', $this->subject->getConfValueString('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueStringReturnsAString() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => 'This is a test.')
		);

		$this->assertSame(
			'This is a test.',
			$this->subject->getListViewConfValueString('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueStringReturnsATrimmedString() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => ' string ')
		);

		$this->assertSame(
			'string',
			$this->subject->getListViewConfValueString('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueStringReturnsEmptyStringWhichWasSet() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => '')
		);

		$this->assertSame(
			'',
			$this->subject->getListViewConfValueString('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueStringReturnsEmptyStringIfNoValueSet() {
		$this->subject->setConfigurationValue(
			'listView.', array()
		);

		$this->assertSame(
			'',
			$this->subject->getListViewConfValueString('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueIntegerReturnsNumber() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => '123')
		);

		$this->assertSame(
			123,
			$this->subject->getListViewConfValueInteger('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueIntegerReturnsZeroIfTheValueWasEmpty() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => '')
		);

		$this->assertSame(
			0,
			$this->subject->getListViewConfValueInteger('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueIntegerReturnsZeroIfTheValueWasNoInteger() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => 'string')
		);

		$this->assertSame(
			0,
			$this->subject->getListViewConfValueInteger('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueIntegerReturnsZeroIfNoValueWasSet() {
		$this->subject->setConfigurationValue(
			'listView.', array()
		);

		$this->assertSame(
			0,
			$this->subject->getListViewConfValueInteger('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueBooleanReturnsTrue() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => '1')
		);

		$this->assertSame(
			TRUE,
			$this->subject->getListViewConfValueBoolean('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueBooleanReturnsTrueIfTheValueWasAPositiveInteger() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => '123')
		);

		$this->assertSame(
			TRUE,
			$this->subject->getListViewConfValueBoolean('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueBooleanReturnsFalseIfTheValueWasZero() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => '0')
		);

		$this->assertSame(
			FALSE,
			$this->subject->getListViewConfValueBoolean('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueBooleanReturnsFalseIfTheValueWasAnEmptyString() {
		$this->subject->setConfigurationValue(
			'listView.', array('test' => '')
		);

		$this->assertSame(
			FALSE,
			$this->subject->getListViewConfValueBoolean('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueBooleanReturnsFalseIfTheValueWasNotSet() {
		$this->subject->setConfigurationValue(
			'listView.', array()
		);

		$this->assertSame(
			FALSE,
			$this->subject->getListViewConfValueBoolean('test')
		);
	}

	/**
	 * @test
	 */
	public function getListViewConfigurationValueThrowsAnExeptionIfNoFieldNameWasProvided() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$fieldName must not be empty.'
		);

		$this->subject->getListViewConfValueBoolean('');
	}


	////////////////////////////////////////////
	// Tests for reading the HTML from a file.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getCompleteTemplateFromFile() {
		$this->subject->setConfigurationValue('templateFile', 'EXT:oelib/Tests/Unit/Fixtures/oelib.html');
		$this->subject->getTemplateCode(TRUE);

		$this->assertSame(
			'Hello world!'.LF, $this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}


	///////////////////////////////
	// Tests for getting subparts.
	///////////////////////////////

	/**
	 * @test
	 */
	public function noSubpartsAndEmptySubpartName() {
		$this->assertSame(
			'', $this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function notExistingSubpartName() {
		$this->assertSame(
			'', $this->subject->getSubpart('FOOBAR')
		);
		$this->assertContains(
			'The subpart',
			$this->subject->getWrappedConfigCheckMessage()
		);
		$this->assertContains(
			'is missing',
			$this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function getCompleteTemplateReturnsCompleteTemplateContent() {
		$templateCode = 'This is a test including'.LF.'a linefeed.'.LF;
		$this->subject->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$templateCode, $this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function getCompleteTemplateCanContainUtf8Umlauts() {
		$this->subject->processTemplate('äöüßÄÖÜßéèáàóò');

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function getCompleteTemplateCanContainIso88591Umlauts() {
		// 228 = ä, 223 = ß (in ISO8859-1)
		$this->subject->processTemplate(chr(228) . chr(223));

		$this->assertSame(
			chr(228) . chr(223),
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function getCompleteTemplateWithComment() {
		$templateCode = 'This is a test including a comment. '
			.'<!-- This is a comment. -->'
			.'And some more text.';
		$this->subject->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$templateCode, $this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
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
		$this->subject->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFromTemplateCanContainUtf8Umlauts() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
			'äöüßÄÖÜßéèáàóò' .
			'<!-- ###MY_SUBPART### -->'
		);

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->subject->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartFromTemplateCanContainIso88591Umlauts() {
		// 228 = ä, 223 = ß (in ISO8859-1)
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
			chr(228) . chr(223) .
			'<!-- ###MY_SUBPART### -->'
		);

		$this->assertSame(
			chr(228) . chr(223),
			$this->subject->getSubpart('MY_SUBPART')
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
		$this->subject->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
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
		$this->subject->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
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
		$this->subject->processTemplate(
			$templateCode
		);
		$this->assertSame(
			$subpartContent, $this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
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
		$this->subject->processTemplate(
			$templateCode
		);
		$this->assertSame(
			'outer start, inner start, '.$subpartContent.'inner end, outer end ',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function getEmptyExistingSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function getHiddenSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->hideSubparts('MY_SUBPART');

		$this->assertSame(
			'',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayAndGetHiddenSubpartReturnsEmptySubpartContent() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'Some text. ' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));

		$this->assertSame(
			'',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}



	//////////////////////////////////
	// Tests for filling in markers.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getInexistentMarkerWillReturnAnEmptyString() {
		$this->subject->processTemplate(
			'foo'
		);
		$this->assertSame(
			'', $this->subject->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setAndGetInexistentMarkerSucceeds() {
		$this->subject->processTemplate(
			'foo'
		);

		$this->subject->setMarker('bar', 'test');
		$this->assertSame(
			'test', $this->subject->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setAndGetExistingMarkerSucceeds() {
		$this->subject->processTemplate(
			'###BAR###'
		);

		$this->subject->setMarker('bar', 'test');
		$this->assertSame(
			'test', $this->subject->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setMarkerAndGetMarkerCanHaveUtf8UmlautsInMarkerContent() {
		$this->subject->processTemplate(
			'###BAR###'
		);
		$this->subject->setMarker('bar', 'äöüßÄÖÜßéèáàóò');

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->subject->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setMarkerAndGetMarkerCanHaveIso88591UmlautsInMarkerContent() {
		$this->subject->processTemplate(
			'###BAR###'
		);
		// 228 = ä, 223 = ß (in ISO8859-1)
		$this->subject->setMarker('bar', chr(228) . chr(223));

		$this->assertSame(
			chr(228) . chr(223),
			$this->subject->getMarker('bar')
		);
	}

	/**
	 * @test
	 */
	public function setLowercaseMarkerInCompleteTemplate() {
		$this->subject->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);
		$this->subject->setMarker('marker', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setUppercaseMarkerInCompleteTemplate() {
		$this->subject->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);
		$this->subject->setMarker('MARKER', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setLowercaseMarkerInSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->setMarker('marker', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setUppercaseMarkerInSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->setMarker('MARKER', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setDoubleMarkerInSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'###MARKER### This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->setMarker('marker', 'foo');
		$this->assertSame(
			'foo This is some template code. foo More text.',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInCompleteTemplateTwoTimes() {
		$this->subject->processTemplate(
			'This is some template code. ###MARKER### More text.'
		);

		$this->subject->setMarker('marker', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->subject->getSubpart()
		);

		$this->subject->setMarker('marker', 'bar');
		$this->assertSame(
			'This is some template code. bar More text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInSubpartTwoTimes() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
		);

		$this->subject->setMarker('marker', 'foo');
		$this->assertSame(
			'This is some template code. foo More text.',
			$this->subject->getSubpart('MY_SUBPART')
		);

		$this->subject->setMarker('marker', 'bar');
		$this->assertSame(
			'This is some template code. bar More text.',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function markerNamesArePrefixesBothUsed() {
		$this->subject->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->subject->setMarker('my_marker', 'foo');
		$this->subject->setMarker('my_marker_too', 'bar');
		$this->assertSame(
			'foo bar',
			$this->subject->getSubpart('')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function markerNamesAreSuffixesBothUsed() {
		$this->subject->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->subject->setMarker('my_marker', 'foo');
		$this->subject->setMarker('also_my_marker', 'bar');
		$this->assertSame(
			'foo bar',
			$this->subject->getSubpart('')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function markerNamesArePrefixesFirstUsed() {
		$this->subject->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->subject->setMarker('my_marker', 'foo');
		$this->assertSame(
			'foo ###MY_MARKER_TOO###',
			$this->subject->getSubpart('')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function markerNamesAreSuffixesFirstUsed() {
		$this->subject->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->subject->setMarker('my_marker', 'foo');
		$this->assertSame(
			'foo ###ALSO_MY_MARKER###',
			$this->subject->getSubpart('')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function markerNamesArePrefixesSecondUsed() {
		$this->subject->processTemplate(
			'###MY_MARKER### ###MY_MARKER_TOO###'
		);

		$this->subject->setMarker('my_marker_too', 'bar');
		$this->assertSame(
			'###MY_MARKER### bar',
			$this->subject->getSubpart('')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function markerNamesAreSuffixesSecondUsed() {
		$this->subject->processTemplate(
			'###MY_MARKER### ###ALSO_MY_MARKER###'
		);

		$this->subject->setMarker('also_my_marker', 'bar');
		$this->assertSame(
			'###MY_MARKER### bar',
			$this->subject->getSubpart('')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function markerNamesArePrefixesBothUsedWithSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
			.'###MY_MARKER### ###MY_MARKER_TOO###'
			.'<!-- ###MY_SUBPART### -->'
		);

		$this->subject->setMarker('my_marker', 'foo');
		$this->subject->setMarker('my_marker_too', 'bar');
		$this->assertSame(
			'foo bar',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function markerNamesAreSuffixesBothUsedWithSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
			.'###MY_MARKER### ###ALSO_MY_MARKER###'
			.'<!-- ###MY_SUBPART### -->'
		);

		$this->subject->setMarker('my_marker', 'foo');
		$this->subject->setMarker('also_my_marker', 'bar');
		$this->assertSame(
			'foo bar',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}


	///////////////////////////////////////////////////////////////
	// Tests for replacing subparts with their content on output.
	///////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getUnchangedSubpartInCompleteTemplate() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function getUnchangedDoubleSubpartInCompleteTemplate() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function getUnchangedSubpartInRequestedSubpart() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function getUnchangedDoubleSubpartInRequestedSubpart() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
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
		$this->subject->processTemplate(
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
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesAreSuffixesGetCompleteTemplate() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesArePrefixesGetFirstSubpart() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesAreSuffixesGetFirstSubpart() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesArePrefixesGetSecondSubpart() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart('MY_SUBPART_TOO')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartNamesAreSuffixesGetSecondSubpart() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart('ALSO_MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}


	////////////////////////////////////////////
	// Tests for hiding and unhiding subparts.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hideSubpartInCompleteTemplate() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideOverwrittenSubpartInCompleteTemplate() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->setSubpart('MY_SUBPART', 'More text. ');
		$this->subject->hideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartInCompleteTemplate() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->unhideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartInCompleteTemplate() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->unhideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartInSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function twoSubpartInNestedSubpart() {
		$this->subject->processTemplate(
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
		$this->subject->hideSubparts('FIELD_WRAPPER_SUBTITLE');
		$this->assertSame(
			'<h3 class="seminars-item-title">Title'
				.'</h3>',
			$this->subject->getSubpart('SINGLE_VIEW')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartInSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->unhideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartInSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->unhideSubparts('MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text. '
				.'Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsSeparately() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART_1');
		$this->subject->hideSubparts('MY_SUBPART_2');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsWithoutSpaceAfterComma() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsInReverseOrder() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART_2,MY_SUBPART_1');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsWithSpaceAfterComma() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART_1, MY_SUBPART_2');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideTwoSubpartsSeparately() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART_1');
		$this->subject->hideSubparts('MY_SUBPART_2');
		$this->subject->unhideSubparts('MY_SUBPART_1');
		$this->subject->unhideSubparts('MY_SUBPART_2');
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideTwoSubpartsInSameOrder() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->subject->unhideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideTwoSubpartsInReverseOrder() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->subject->unhideSubparts('MY_SUBPART_2,MY_SUBPART_1');
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'More text there. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsUnhideFirst() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->subject->unhideSubparts('MY_SUBPART_1');
		$this->assertSame(
			'Some text. '
				.'More text here.'
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsUnhideSecond() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART_1### -->'
				.'More text here.'
				.'<!-- ###MY_SUBPART_1### -->'
				.'<!-- ###MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART_1,MY_SUBPART_2');
		$this->subject->unhideSubparts('MY_SUBPART_2');
		$this->assertSame(
			'Some text. '
				.'More text there. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhidePermanentlyHiddenSubpart() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->unhideSubparts('MY_SUBPART', 'MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhideOneOfTwoPermanentlyHiddenSubparts() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->unhideSubparts('MY_SUBPART', 'MY_SUBPART,MY_OTHER_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartAndPermanentlyHideAnother() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->unhideSubparts('MY_SUBPART', 'MY_OTHER_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhidePermanentlyHiddenSubpartWithPrefix() {
		$this->subject->processTemplate(
			'<!-- ###SUBPART### -->'
				.'Some text. '
				.'<!-- ###SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->unhideSubparts('SUBPART', 'SUBPART', 'MY');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhideOneOfTwoPermanentlyHiddenSubpartsWithPrefix() {
		$this->subject->processTemplate(
			'<!-- ###SUBPART### -->'
				.'Some text. '
				.'<!-- ###SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->unhideSubparts('SUBPART', 'SUBPART,OTHER_SUBPART', 'MY');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartAndPermanentlyHideAnotherWithPrefix() {
		$this->subject->processTemplate(
			'<!-- ###SUBPART### -->'
				.'Some text. '
				.'<!-- ###SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->unhideSubparts('SUBPART', 'OTHER_SUBPART', 'MY');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartIsInvisibleIfTheSubpartNameIsEmpty() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertFalse(
			$this->subject->isSubpartVisible('')
		);
	}

	/**
	 * @test
	 */
	public function nonexistentSubpartIsInvisible() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertFalse(
			$this->subject->isSubpartVisible('FOO')
		);
	}

	/**
	 * @test
	 */
	public function subpartIsVisibleByDefault() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->assertTrue(
			$this->subject->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function subpartIsNotVisibleAfterHiding() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->assertFalse(
			$this->subject->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function subpartIsVisibleAfterHidingAndUnhiding() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->unhideSubparts('MY_SUBPART');
		$this->assertTrue(
			$this->subject->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function getSubpartReturnsContentOfVisibleSubpartThatWasFilledWhenHidden() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->hideSubparts('MY_SUBPART');
		$this->subject->setSubpart('MY_SUBPART', 'foo');
		$this->subject->unhideSubparts('MY_SUBPART');
		$this->assertSame(
			'foo',
			$this->subject->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayWithCompleteTemplateHidesSubpart() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
			'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayWithCompleteTemplateHidesOverwrittenSubpart() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->setSubpart('MY_SUBPART', 'More text. ');
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartsArrayWithCompleteTemplateUnhidesSubpart() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'More text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayWithCompleteTemplateHidesAndUnhidesSubpart() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'More text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesSubpartInSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->' .
				'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.' .
				'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesSubpartInNestedSubpart() {
		$this->subject->processTemplate(
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
		$this->subject->hideSubpartsArray(array('FIELD_WRAPPER_SUBTITLE'));
		$this->assertSame(
			'<h3 class="seminars-item-title">Title' .
				'</h3>',
			$this->subject->getSubpart('SINGLE_VIEW')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function unhideSubpartsArrayUnhidesSubpartInSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->' .
				'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.' .
				'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'More text. ' .
				'Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesSubpartInSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->' .
				'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.' .
				'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'Some text. ' .
				'More text. ' .
				'Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesTwoSubpartsSeparately() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART_1'));
		$this->subject->hideSubpartsArray(array('MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesTwoSubparts() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesTwoSubpartsInReverseOrder() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART_2', 'MY_SUBPART_1'));
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsSeparately() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART_1'));
		$this->subject->hideSubpartsArray(array('MY_SUBPART_2'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART_1'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'More text here.' .
				'More text there. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsInSameOrder() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'More text here.' .
				'More text there. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsInReverseOrder() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART_2', 'MY_SUBPART_1'));
		$this->assertSame(
			'Some text. ' .
				'More text here.' .
				'More text there. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesTwoSubpartsAndUnhidesTheFirst() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART_1'));
		$this->assertSame(
			'Some text. ' .
				'More text here.' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesTwoSubpartsAndUnhidesTheSecond() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART_1### -->' .
				'More text here.' .
				'<!-- ###MY_SUBPART_1### -->' .
				'<!-- ###MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART_1', 'MY_SUBPART_2'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART_2'));
		$this->assertSame(
			'Some text. ' .
				'More text there. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesPermanentlyHiddenSubpart() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->unhideSubpartsArray(
			array('MY_SUBPART'), array('MY_SUBPART')
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesOneOfTwoPermanentlyHiddenSubparts() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->unhideSubpartsArray(
			array('MY_SUBPART'), array('MY_SUBPART', 'MY_OTHER_SUBPART')
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayUnhidesSubpartAndPermanentlyHidesAnother() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->unhideSubpartsArray(
			array('MY_SUBPART'), array('MY_OTHER_SUBPART')
		);
		$this->assertSame(
			'Some text. ' .
				'More text here. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesPermanentlyHiddenSubpartWithPrefix() {
		$this->subject->processTemplate(
			'<!-- ###SUBPART### -->' .
				'Some text. ' .
				'<!-- ###SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->unhideSubpartsArray(
			array('SUBPART'), array('SUBPART'), 'MY'
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesOneOfTwoPermanentlyHiddenSubpartsWithPrefix() {
		$this->subject->processTemplate(
			'<!-- ###SUBPART### -->' .
				'Some text. ' .
				'<!-- ###SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->unhideSubpartsArray(
			array('SUBPART'), array('SUBPART', 'OTHER_SUBPART'), 'MY'
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayUnhidesSubpartAndPermanentlyHidesAnotherWithPrefix() {
		$this->subject->processTemplate(
			'<!-- ###SUBPART### -->' .
				'Some text. ' .
				'<!-- ###SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->unhideSubpartsArray(
			array('SUBPART'), array('OTHER_SUBPART'), 'MY'
		);
		$this->assertSame(
			'Some text. ' .
				'More text here. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayResultsInNotVisibleSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->assertFalse(
			$this->subject->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayResultsInVisibleSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertTrue(
			$this->subject->isSubpartVisible('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayWithFilledSubpartWhenHiddenReturnsContentOfUnhiddenSubpart() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'));
		$this->subject->setSubpart('MY_SUBPART', 'foo');
		$this->subject->unhideSubpartsArray(array('MY_SUBPART'));
		$this->assertSame(
			'foo',
			$this->subject->getSubpart('MY_SUBPART')
		);
	}


	////////////////////////////////
	// Tests for setting subparts.
	////////////////////////////////

	/**
	 * @test
	 */
	public function setSubpartNotEmptyGetCompleteTemplate() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->subject->setSubpart('MY_SUBPART', 'foo');
		$this->assertSame(
			'Some text. '
				.'foo'
				.' Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartNotEmptyGetSubpart() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->subject->setSubpart('MY_SUBPART', 'foo');
		$this->assertSame(
			'foo',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setNewSubpartNotEmptyGetSubpart() {
		$this->subject->processTemplate(
			'Some text.'
		);
		$this->subject->setSubpart('MY_SUBPART', 'foo');
		$this->assertSame(
			'foo',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameWithSpaceCreatesWarning() {
		$this->subject->processTemplate(
			'Some text.'
		);
		$this->subject->setSubpart('MY SUBPART', 'foo');
		$this->assertSame(
			'',
			$this->subject->getSubpart('MY SUBPART')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameWithUtf8UmlautCreatesWarning() {
		$this->subject->processTemplate(
			'Some text.'
		);
		$this->subject->setSubpart('MY_SÜBPART', 'foo');
		$this->assertSame(
			'',
			$this->subject->getSubpart('MY_SÜBPART')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameWithUnderscoreSuffixCreatesWarning() {
		$this->subject->processTemplate(
			'Some text.'
		);
		$this->subject->setSubpart('MY_SUBPART_', 'foo');
		$this->assertSame(
			'',
			$this->subject->getSubpart('MY_SUBPART_')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameStartingWithUnderscoreCreatesWarning() {
		$this->subject->processTemplate(
			'Some text.'
		);
		$this->subject->setSubpart('_MY_SUBPART', 'foo');
		$this->assertSame(
			'',
			$this->subject->getSubpart('_MY_SUBPART')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setNewSubpartWithNameStartingWithNumberCreatesWarning() {
		$this->subject->processTemplate(
			'Some text.'
		);
		$this->subject->setSubpart('1_MY_SUBPART', 'foo');
		$this->assertSame(
			'',
			$this->subject->getSubpart('1_MY_SUBPART')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartNotEmptyGetOuterSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->setSubpart('MY_SUBPART', 'foo');
		$this->assertSame(
			'Some text. foo Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartToEmptyGetCompleteTemplate() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->subject->setSubpart('MY_SUBPART', '');
		$this->assertSame(
			'Some text. '
				.' Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartToEmptyGetSubpart() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->subject->setSubpart('MY_SUBPART', '');
		$this->assertSame(
			'',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartToEmptyGetOuterSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->setSubpart('MY_SUBPART', '');
		$this->assertSame(
			'Some text.  Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartAndGetSubpartCanHaveUtf8UmlautsInSubpartContent() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
			'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->setSubpart('MY_SUBPART', 'äöüßÄÖÜßéèáàóò');

		$this->assertSame(
			'äöüßÄÖÜßéèáàóò',
			$this->subject->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function setSubpartAndGetSubpartCanHaveIso88591UmlautsInSubpartContent() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->' .
			'<!-- ###MY_SUBPART### -->'
		);
		// 228 = ä, 223 = ß (in ISO8859-1)
		$this->subject->setSubpart('MY_SUBPART', chr(228) . chr(223));

		$this->assertSame(
			chr(228) . chr(223),
			$this->subject->getSubpart('MY_SUBPART')
		);
	}


	//////////////////////////////////////////////////////
	// Tests for setting markers within nested subparts.
	//////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setMarkerInSubpartWithinCompleteTemplate() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->subject->setMarker('marker', 'foo');
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->subject->getSubpart('')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInSubpartWithinOtherSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'This is some template code. ###MARKER### More text.'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->setMarker('marker', 'foo');
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInOverwrittenSubpartWithinCompleteTemplate() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
		);
		$this->subject->setSubpart(
			'MY_SUBPART',
			'This is some template code. ###MARKER### More text.'
		);
		$this->subject->setMarker('marker', 'foo');
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->subject->getSubpart('')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerInOverwrittenSubpartWithinOtherSubpart() {
		$this->subject->processTemplate(
			'<!-- ###OUTER_SUBPART### -->'
				.'Some text. '
				.'<!-- ###MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.' Even more text.'
				.'<!-- ###OUTER_SUBPART### -->'
		);
		$this->subject->setSubpart(
			'MY_SUBPART',
			'This is some template code. ###MARKER### More text.'
		);
		$this->subject->setMarker('marker', 'foo');
		$this->assertSame(
			'Some text. '
				.'This is some template code. foo More text.'
				.' Even more text.',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
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
		$this->subject->processTemplate(
			$templateCode
		);
		$this->subject->setMarker('marker', 'foo ');

		$this->assertSame(
			'outer start, inner start, foo inner end, outer end ',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}


	////////////////////////////////////////////////////////////
	// Tests for using the prefix to marker and subpart names.
	////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setMarkerWithPrefix() {
		$this->subject->processTemplate(
			'This is some template code. '
				.'###FIRST_MARKER### ###MARKER### More text.'
		);
		$this->subject->setMarker('marker', 'foo', 'first');
		$this->assertSame(
			'This is some template code. foo ###MARKER### More text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function setSubpartWithPrefix() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->setSubpart('MY_SUBPART', 'foo', 'FIRST');
		$this->assertSame(
			'Some text. '
				.'foo'
				.'More text there. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartWithPrefix() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('MY_SUBPART', 'FIRST');
		$this->assertSame(
			'Some text. '
				.'More text there. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartWithPrefix() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART### -->'
				.'<!-- ###MY_SUBPART### -->'
				.'More text there. '
				.'<!-- ###MY_SUBPART### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('FIRST_MY_SUBPART');
		$this->subject->unhideSubparts('MY_SUBPART', '', 'FIRST');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'More text there. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideTwoSubpartsWithPrefix() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART_1### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART_1### -->'
				.'<!-- ###FIRST_MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###FIRST_MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('1,2', 'FIRST_MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideTwoSubpartsWithPrefix() {
		$this->subject->processTemplate(
			'Some text. '
				.'<!-- ###FIRST_MY_SUBPART_1### -->'
				.'More text here. '
				.'<!-- ###FIRST_MY_SUBPART_1### -->'
				.'<!-- ###FIRST_MY_SUBPART_2### -->'
				.'More text there. '
				.'<!-- ###FIRST_MY_SUBPART_2### -->'
				.'Even more text.'
		);
		$this->subject->hideSubparts('FIRST_MY_SUBPART_1');
		$this->subject->hideSubparts('FIRST_MY_SUBPART_2');
		$this->subject->unhideSubparts('1,2', '', 'FIRST_MY_SUBPART');
		$this->assertSame(
			'Some text. '
				.'More text here. '
				.'More text there. '
				.'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesSubpartWithPrefix() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###FIRST_MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###FIRST_MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('MY_SUBPART'), 'FIRST');
		$this->assertSame(
			'Some text. ' .
				'More text there. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideSubpartsArrayHidesTwoSubpartsWithPrefix() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###FIRST_MY_SUBPART_1### -->' .
				'More text here. ' .
				'<!-- ###FIRST_MY_SUBPART_1### -->' .
				'<!-- ###FIRST_MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###FIRST_MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(
			array('1', '2'), 'FIRST_MY_SUBPART'
		);
		$this->assertSame(
			'Some text. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesSubpartWithPrefix() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###FIRST_MY_SUBPART### -->' .
				'More text here. ' .
				'<!-- ###FIRST_MY_SUBPART### -->' .
				'<!-- ###MY_SUBPART### -->' .
				'More text there. ' .
				'<!-- ###MY_SUBPART### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('FIRST_MY_SUBPART'));
		$this->subject->unhideSubpartsArray(array('MY_SUBPART'), array(''), 'FIRST');
		$this->assertSame(
			'Some text. ' .
				'More text here. ' .
				'More text there. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function hideAndUnhideSubpartsArrayHidesAndUnhidesTwoSubpartsWithPrefix() {
		$this->subject->processTemplate(
			'Some text. ' .
				'<!-- ###FIRST_MY_SUBPART_1### -->' .
				'More text here. ' .
				'<!-- ###FIRST_MY_SUBPART_1### -->' .
				'<!-- ###FIRST_MY_SUBPART_2### -->' .
				'More text there. ' .
				'<!-- ###FIRST_MY_SUBPART_2### -->' .
				'Even more text.'
		);
		$this->subject->hideSubpartsArray(array('FIRST_MY_SUBPART_1'));
		$this->subject->hideSubpartsArray(array('FIRST_MY_SUBPART_2'));
		$this->subject->unhideSubpartsArray(
			array('1', '2'), array(''), 'FIRST_MY_SUBPART'
		);
		$this->assertSame(
			'Some text. ' .
				'More text here. ' .
				'More text there. ' .
				'Even more text.',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}


	////////////////////////////////////////////
	// Tests for automatically setting labels.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setLabels() {
		$this->subject->processTemplate(
			'a ###LABEL_FOO### b'
		);
		$this->subject->setLabels();
		$this->assertSame(
			'a foo b',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setLabelsNoSalutation() {
		$this->subject->processTemplate(
			'a ###LABEL_BAR### b'
		);
		$this->subject->setLabels();
		$this->assertSame(
			'a bar (formal) b',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setLabelsFormal() {
		$this->subject->setSalutationMode('formal');
		$this->subject->processTemplate(
			'a ###LABEL_BAR### b'
		);
		$this->subject->setLabels();
		$this->assertSame(
			'a bar (formal) b',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setLabelsInformal() {
		$this->subject->setSalutationMode('informal');
		$this->subject->processTemplate(
			'a ###LABEL_BAR### b'
		);
		$this->subject->setLabels();
		$this->assertSame(
			'a bar (informal) b',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setLabelsWithOneBeingThePrefixOfAnother() {
		$this->subject->processTemplate(
			'###LABEL_FOO###, ###LABEL_FOO2###'
		);
		$this->subject->setLabels();
		$this->assertSame(
			'foo, foo two',
			$this->subject->getSubpart()
		);
	}


	/////////////////////////////////////////////////////////////////////
	// Test for conditional filling and hiding of markers and subparts.
	/////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithPositiveInteger() {
		$this->subject->processTemplate(
			'###MARKER###'
		);

		$this->assertTrue(
			$this->subject->setMarkerIfNotZero('marker', 42)
		);
		$this->assertSame(
			'42',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithNegativeInteger() {
		$this->subject->processTemplate(
			'###MARKER###'
		);

		$this->assertTrue(
			$this->subject->setMarkerIfNotZero('marker', -42)
		);
		$this->assertSame(
			'-42',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithZero() {
		$this->subject->processTemplate(
			'###MARKER###'
		);

		$this->assertFalse(
			$this->subject->setMarkerIfNotZero('marker', 0)
		);
		$this->assertSame(
			'###MARKER###',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithPositiveIntegerWithPrefix() {
		$this->subject->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertTrue(
			$this->subject->setMarkerIfNotZero('marker', 42, 'MY')
		);
		$this->assertSame(
			'42',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithNegativeIntegerWithPrefix() {
		$this->subject->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertTrue(
			$this->subject->setMarkerIfNotZero('marker', -42, 'MY')
		);
		$this->assertSame(
			'-42',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotZeroWithZeroWithPrefix() {
		$this->subject->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertFalse(
			$this->subject->setMarkerIfNotZero('marker', 0, 'MY')
		);
		$this->assertSame(
			'###MY_MARKER###',
			$this->subject->getSubpart()
		);
	}


	/**
	 * @test
	 */
	public function setMarkerIfNotEmptyWithNotEmpty() {
		$this->subject->processTemplate(
			'###MARKER###'
		);

		$this->assertTrue(
			$this->subject->setMarkerIfNotEmpty('marker', 'foo')
		);
		$this->assertSame(
			'foo',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotEmptyWithEmpty() {
		$this->subject->processTemplate(
			'###MARKER###'
		);

		$this->assertFalse(
			$this->subject->setMarkerIfNotEmpty('marker', '')
		);
		$this->assertSame(
			'###MARKER###',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotEmptyWithNotEmptyWithPrefix() {
		$this->subject->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertTrue(
			$this->subject->setMarkerIfNotEmpty('marker', 'foo', 'MY')
		);
		$this->assertSame(
			'foo',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setMarkerIfNotEmptyWithEmptyWithPrefix() {
		$this->subject->processTemplate(
			'###MY_MARKER###'
		);

		$this->assertFalse(
			$this->subject->setMarkerIfNotEmpty('marker', '', 'MY')
		);
		$this->assertSame(
			'###MY_MARKER###',
			$this->subject->getSubpart()
		);
	}


	/**
	 * @test
	 */
	public function setOrDeleteMarkerWithTrue() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->subject->setOrDeleteMarker(
				'marker', TRUE, 'foo', '', 'WRAPPER'
			)
		);
		$this->assertSame(
			'foo',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerWithFalse() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->subject->setOrDeleteMarker(
				'marker', FALSE, 'foo', '', 'WRAPPER'
			)
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerWithTrueWithMarkerPrefix() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->subject->setOrDeleteMarker(
				'marker', TRUE, 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
			'foo',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerWithFalseWithMarkerPrefix() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->subject->setOrDeleteMarker(
				'marker', FALSE, 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithZero() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->subject->setOrDeleteMarkerIfNotZero(
				'marker', 0, '', 'WRAPPER'
			)
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithPositiveIntegers() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->subject->setOrDeleteMarkerIfNotZero(
				'marker', 42, '', 'WRAPPER'
			)
		);
		$this->assertSame(
			'42',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithNegativeIntegers() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->subject->setOrDeleteMarkerIfNotZero(
				'marker', -42, '', 'WRAPPER'
			)
		);
		$this->assertSame(
			'-42',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithZeroWithMarkerPrefix() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->subject->setOrDeleteMarkerIfNotZero(
				'marker', 0, 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithPositiveIntegerWithMarkerPrefix() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->subject->setOrDeleteMarkerIfNotZero(
				'marker', 42, 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
			'42',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotZeroWithNegativeIntegerWithMarkerPrefix() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->subject->setOrDeleteMarkerIfNotZero(
				'marker', -42, 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
			'-42',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotEmptyWithEmpty() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->subject->setOrDeleteMarkerIfNotEmpty(
				'marker', '', '', 'WRAPPER'
			)
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotEmptyWithNotEmpty() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->subject->setOrDeleteMarkerIfNotEmpty(
				'marker', 'foo', '', 'WRAPPER'
			)
		);
		$this->assertSame(
			'foo',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotEmptyWithEmptyWithMarkerPrefix() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertFalse(
			$this->subject->setOrDeleteMarkerIfNotEmpty(
				'marker', '', 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart()
		);
	}

	/**
	 * @test
	 */
	public function setOrDeleteMarkerIfNotEmptyWithNotEmptyWithMarkerPrefix() {
		$this->subject->processTemplate(
			'<!-- ###WRAPPER_MARKER### -->'
				.'###MY_MARKER###'
				.'<!-- ###WRAPPER_MARKER### -->'
		);

		$this->assertTrue(
			$this->subject->setOrDeleteMarkerIfNotEmpty(
				'marker', 'foo', 'MY', 'WRAPPER'
			)
		);
		$this->assertSame(
			'foo',
			$this->subject->getSubpart()
		);
	}


	///////////////////////////////////////////////////
	// Test concerning unclosed markers and subparts.
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function unclosedMarkersAreIgnored() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'###MY_MARKER_1### '
				.'###MY_MARKER_2 '
				.'###MY_MARKER_3# '
				.'###MY_MARKER_4## '
				.'###MY_MARKER_5###'
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->setMarker('my_marker_1', 'test 1');
		$this->subject->setMarker('my_marker_2', 'test 2');
		$this->subject->setMarker('my_marker_3', 'test 3');
		$this->subject->setMarker('my_marker_4', 'test 4');
		$this->subject->setMarker('my_marker_5', 'test 5');

		$this->assertSame(
			'test 1 '
				.'###MY_MARKER_2 '
				.'###MY_MARKER_3# '
				.'###MY_MARKER_4## '
				.'test 5',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'test 1 '
				.'###MY_MARKER_2 '
				.'###MY_MARKER_3# '
				.'###MY_MARKER_4## '
				.'test 5',
			$this->subject->getSubpart('MY_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function unclosedSubpartsAreIgnored() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'<!-- ###UNCLOSED_SUBPART_2### -->'
				.'<!-- ###UNCLOSED_SUBPART_3### -->'
				.'Inner text. '
				.'<!-- ###UNCLOSED_SUBPART_4### -->'
				.'<!-- ###UNCLOSED_SUBPART_5### -->',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function unclosedSubpartMarkersAreIgnored() {
		$this->subject->processTemplate(
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
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'<!-- ###UNCLOSED_SUBPART_2 -->'
				.'<!-- ###UNCLOSED_SUBPART_3### --'
				.'Inner text. '
				.'<!-- UNCLOSED_SUBPART_4### -->'
				.' ###UNCLOSED_SUBPART_5### -->',
			$this->subject->getSubpart('OUTER_SUBPART')
		);
	}

	/**
	 * @test
	 */
	public function invalidMarkerNamesAreIgnored() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART### -->'
				.'###MARKER 1### '
				.'###MARKER-2### '
				.'###marker_3### '
				.'###MÄRKER_4### '
				.'<!-- ###MY_SUBPART### -->'
		);
		$this->subject->setMarker('marker 1', 'foo');
		$this->subject->setMarker('marker-2', 'foo');
		$this->subject->setMarker('marker_3', 'foo');
		$this->subject->setMarker('märker_4', 'foo');

		$this->assertSame(
			'###MARKER 1### '
				.'###MARKER-2### '
				.'###marker_3### '
				.'###MÄRKER_4### ',
			$this->subject->getSubpart()
		);
		$this->assertSame(
			'###MARKER 1### '
				.'###MARKER-2### '
				.'###marker_3### '
				.'###MÄRKER_4### ',
			$this->subject->getSubpart('MY_SUBPART')
		);
	}


	///////////////////////////////////////////////////
	// Tests for getting subparts with invalid names.
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function subpartWithNameWithSpaceIsIgnored() {
		$this->subject->processTemplate(
			'<!-- ###MY SUBPART### -->'
				.'Some text.'
				.'<!-- ###MY SUBPART### -->'
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart('MY SUBPART')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartWithNameWithUtf8UmlautIsIgnored() {
		$this->subject->processTemplate(
			'<!-- ###MY_SÜBPART### -->'
				.'Some text.'
				.'<!-- ###MY_SÜBPART### -->'
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart('MY_SÜBPART')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartWithNameWithUnderscoreSuffixIsIgnored() {
		$this->subject->processTemplate(
			'<!-- ###MY_SUBPART_### -->'
				.'Some text.'
				.'<!-- ###MY_SUBPART_### -->'
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart('MY_SUBPART_')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartWithNameStartingWithUnderscoreIsIgnored() {
		$this->subject->processTemplate(
			'<!-- ###_MY_SUBPART### -->'
				.'Some text.'
				.'<!-- ###_MY_SUBPART### -->'
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart('_MY_SUBPART')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartWithNameStartingWithNumberIsIgnored() {
		$this->subject->processTemplate(
			'<!-- ###1_MY_SUBPART### -->'
				.'Some text.'
				.'<!-- ###1_MY_SUBPART### -->'
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart('1_MY_SUBPART')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartWithLowercaseNameIsIgnoredWithUsingLowercase() {
		$this->subject->processTemplate(
			'<!-- ###my_subpart### -->'
				.'Some text.'
				.'<!-- ###my_subpart### -->'
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart('my_subpart')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}

	/**
	 * @test
	 */
	public function subpartWithLowercaseNameIsIgnoredWithUsingUppercase() {
		$this->subject->processTemplate(
			'<!-- ###my_subpart### -->'
				.'Some text.'
				.'<!-- ###my_subpart### -->'
		);
		$this->assertSame(
			'',
			$this->subject->getSubpart('MY_SUBPART')
		);
		$this->assertNotSame(
			'', $this->subject->getWrappedConfigCheckMessage()
		);
	}


	///////////////////////////////////
	// Tests concerning TS templates.
	///////////////////////////////////

	/**
	 * @test
	 */
	public function pageSetupInitiallyIsEmpty() {
		$pageId = $this->testingFramework->createFrontEndPage();
		$this->assertSame(
			array(),
			$this->subject->retrievePageConfig($pageId)
		);
	}


	//////////////////////////////////////////
	// Tests concerning the image functions.
	//////////////////////////////////////////

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function createRestrictedImageForEmptyPathThrowsException() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->subject->createRestrictedImage('');
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function createRestrictedImageForNonZeroMaxAreaThrowsException() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->subject->createRestrictedImage('typo3conf/ext/oelib/Tests/Unit/Fixtures/test.png', '', 0, 0, 1);
	}

	/**
	 * @test
	 */
	public function createRestrictedImageForInexistentFileReturnsHtmlspecialchardAltText() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$this->assertContains(
			'foo &amp; bar',
			$this->subject->createRestrictedImage('typo3conf/ext/oelib/Tests/Unit/Fixtures/nothing.png', 'foo & bar')
		);
	}

	/**
	 * @test
	 */
	public function createRestrictedImageForSuccessReturnsGeneratedImageTag() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$imageTag = '<img src="typo3temp/foo.jpg" alt="foo" title="bar" id="id-42"/>';

		/** @var $content tslib_cObj|PHPUnit_Framework_MockObject_MockObject */
		$content = $this->getMock('tslib_cObj');
		$content->expects($this->once())->method('IMAGE')
			->with(
				array('file' => 'foo.jpg', 'file.' => array(), 'altText' => 'foo', 'titleText' => 'bar', 'params' =>'id="id-42"')
			)
			->will($this->returnValue($imageTag));
		$this->subject->cObj = $content;

		$this->assertSame(
			$imageTag,
			$this->subject->createRestrictedImage('foo.jpg', 'foo', 0, 0, 0, 'bar', 'id-42')
		);
	}

	/**
	 * @test
	 */
	public function createRestrictedImageForGeneratedImageTagWithEmptySrcReturnsHtmlSpecialcharedAltText() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$altText = 'foo & bar';

		/** @var $content tslib_cObj|PHPUnit_Framework_MockObject_MockObject */
		$content = $this->getMock('tslib_cObj');
		$content->expects($this->once())->method('IMAGE')
			->with(array('file' => 'foo.jpg', 'file.' => array(), 'altText' => $altText, 'titleText' => ''))
			->will($this->returnValue('<img src=""/>'));
		$this->subject->cObj = $content;

		$this->assertSame(
			'foo &amp; bar',
			$this->subject->createRestrictedImage('foo.jpg', $altText)
		);
	}

	/**
	 * @test
	 */
	public function createRestrictedImageForGeneratedNullImageTagReturnsHtmlSpecialcharedAltText() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$altText = 'foo & bar';

		/** @var $content tslib_cObj|PHPUnit_Framework_MockObject_MockObject */
		$content = $this->getMock('tslib_cObj');
		$content->expects($this->once())->method('IMAGE')
			->with(array('file' => 'foo.jpg', 'file.' => array(),  'altText' => $altText, 'titleText' => ''))
			->will($this->returnValue(NULL));
		$this->subject->cObj = $content;

		$this->assertSame(
			'foo &amp; bar',
			$this->subject->createRestrictedImage('foo.jpg', $altText)
		);
	}

	/**
	 * @test
	 */
	public function createRestrictedImageForExceptionReturnsHtmlSpecialcharedAltText() {
		if (!class_exists('TYPO3\\CMS\\Core\\Resource\\Exception\\FileDoesNotExistException', TRUE)) {
			$this->markTestSkipped('This tests needs the FileDoesNotExistException class introduced in TYPO3 6.0.');
		}
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = FALSE;

		$altText = 'foo & bar';

		/** @var $content tslib_cObj|PHPUnit_Framework_MockObject_MockObject */
		$content = $this->getMock('tslib_cObj');
		$content->expects($this->once())->method('IMAGE')
			->with(array('file' => 'foo.jpg', 'file.' => array(), 'altText' => $altText, 'titleText' => ''))
			->will($this->throwException(new \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException()));
		$this->subject->cObj = $content;

		$this->assertSame(
			'foo &amp; bar',
			$this->subject->createRestrictedImage('foo.jpg', $altText)
		);
	}


	/*
	 * Tests for ensureIntegerPiVars
	 */

	/**
	 * @test
	 */
	public function ensureIntegerPiVarsDefinesAPiVarsArrayWithShowUidPointerAndModeIfPiVarsWasUndefined() {
		unset($this->subject->piVars);
		$this->subject->ensureIntegerPiVars();

		$this->assertSame(
			array('showUid' => 0, 'pointer' => 0, 'mode' => 0),
			$this->subject->piVars
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerPiVarsDefinesProvidedAdditionalParameterIfPiVarsWasUndefined() {
		$this->subject->piVars = array();
		$this->subject->ensureIntegerPiVars(array('additionalParameter'));

		$this->assertSame(
			array('showUid' => 0, 'pointer' => 0, 'mode' => 0, 'additionalParameter' => 0),
			$this->subject->piVars
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerPiVarsIntvalsAnAlreadyDefinedAdditionalParameter() {
		$this->subject->piVars = array();
		$this->subject->piVars['additionalParameter'] = 1.1;
		$this->subject->ensureIntegerPiVars(array('additionalParameter'));

		$this->assertSame(
			array(
				'additionalParameter' => 1,
				'showUid' => 0,
				'pointer' => 0,
				'mode' => 0
			),
			$this->subject->piVars
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerPiVarsDoesNotIntvalsDefinedPiVarWhichIsNotInTheListOfPiVarsToSecure() {
		$this->subject->piVars = array();
		$this->subject->piVars['non-integer'] = 'foo';
		$this->subject->ensureIntegerPiVars();

		$this->assertSame(
			array('non-integer' => 'foo', 'showUid' => 0, 'pointer' => 0, 'mode' => 0),
			$this->subject->piVars
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerPiVarsIntvalsAlreadyDefinedShowUid() {
		$this->subject->piVars = array();
		$this->subject->piVars['showUid'] = 1.1;
		$this->subject->ensureIntegerPiVars();

		$this->assertSame(
			array('showUid' => 1, 'pointer' => 0, 'mode' => 0),
			$this->subject->piVars
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
		$this->subject->cObj = $contentObject;

		$this->subject->ensureContentObject();

		$this->assertSame(
			$contentObject,
			$this->subject->cObj
		);
	}

	/**
	 * @test
	 */
	public function ensureContentObjectForMissingContentObjectWithFrontEndUsesContentObjectFromFrontEnd() {
		$this->subject->cObj = NULL;

		$this->subject->ensureContentObject();

		/** @var tslib_fe $frontEndController */
		$frontEndController = $GLOBALS['TSFE'];
		$this->assertSame(
			$frontEndController->cObj,
			$this->subject->cObj
		);
	}


	/*
	 * Tests concerning getStoragePid
	 */

	/**
	 * @test
	 */
	public function getStoragePidForNoSetGrspReturnsZero() {
		$this->assertSame(
			0,
			$this->subject->getStoragePid()
		);
	}

	/**
	 * @test
	 */
	public function getStoragePidForGrspSetReturnsThisId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);

		$this->assertSame(
			42,
			$this->subject->getStoragePid()
		);
	}

	/**
	 * @test
	 */
	public function hasStoragePidForGrspSetReturnsTrue() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);

		$this->assertTrue(
			$this->subject->hasStoragePid()
		);
	}

	/**
	 * @test
	 */
	public function hasStoragePidForNoGrspSetReturnsFalse() {
		$this->assertFalse(
			$this->subject->hasStoragePid()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning ensureIntegerArrayValues
	//////////////////////////////////////////////

	/**
	 * @test
	 */
	public function ensureIntegerArrayValuesForEmptyArrayGivenDoesNotAddAnyPiVars() {
		$originalPiVars = $this->subject->piVars;
		$this->subject->ensureIntegerArrayValues(array());

		$this->assertSame(
			$originalPiVars,
			$this->subject->piVars
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerArrayValuesForNotSetPiVarGivenDoesNotAddThisPiVar() {
		$originalPiVars = $this->subject->piVars;
		$this->subject->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			$originalPiVars,
			$this->subject->piVars
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerArrayValuesForPiVarNotArrayDoesNotModifyThisPiVar() {
		$this->subject->piVars['foo'] = 'Hallo';
		$this->subject->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			'Hallo',
			$this->subject->piVars['foo']
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerArrayValuesForValidIntegerInArrayDoesNotModifyThisArrayElement() {
		$this->subject->piVars['foo'] = array(10);
		$this->subject->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			10,
			$this->subject->piVars['foo'][0]
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerArrayValuesForStringInArrayRemovesThisArrayElement() {
		$this->subject->piVars['foo'] = array('Hallo');
		$this->subject->ensureIntegerArrayValues(array('foo'));

		$this->assertTrue(
			empty($this->subject->piVars['foo'])
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerArrayValuesForIntegerFollowedByStringInArrayRemovesStringFromArrayElement() {
		$this->subject->piVars['foo'] = array('2;blubb');
		$this->subject->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			2,
			$this->subject->piVars['foo'][0]
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerArrayValuesForSingleInArrayRemovesNumbersAfterDecimalPoint() {
		$this->subject->piVars['foo'] = array(2.3);
		$this->subject->ensureIntegerArrayValues(array('foo'));

		$this->assertSame(
			2,
			$this->subject->piVars['foo'][0]
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerArrayValuesForZeroInArrayRemovesThisArrayElement() {
		$this->subject->piVars['foo'] = array(0);
		$this->subject->ensureIntegerArrayValues(array('foo'));

		$this->assertTrue(
			empty($this->subject->piVars['foo'])
		);
	}

	/**
	 * @test
	 */
	public function ensureIntegerArrayValuesMultiplePiKeysGivenValidatesElementsOfAllPiVars() {
		$this->subject->piVars['foo'] = array('2;blubb');
		$this->subject->piVars['bar'] = array('42');

		$this->subject->ensureIntegerArrayValues(array('foo', 'bar'));

		$this->assertSame(
			2,
			$this->subject->piVars['foo'][0]
		);
		$this->assertSame(
			42,
			$this->subject->piVars['bar'][0]
		);
	}
}
