<?php
/***************************************************************
* Copyright notice
*
* (c) 2011-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the Tx_Oelib_ViewHelpers_GoogleMapsViewHelper class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ViewHelpers_GoogleMapsViewHelperTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_ViewHelpers_GoogleMapsViewHelper
	 */
	private $fixture = NULL;

	/**
	 * @var tx_oelib_Interface_MapPoint
	 */
	private $mapPointWithCoordinates = NULL;

	public function setUp() {
		$GLOBALS['TSFE'] = $this->getMock('tslib_fe', array('dummy'), array(), '', FALSE);
		$this->mapPointWithCoordinates = $this->getMock('tx_oelib_Interface_MapPoint');
		$this->mapPointWithCoordinates->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$this->mapPointWithCoordinates->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));

		$this->fixture = new Tx_Oelib_ViewHelpers_GoogleMapsViewHelper();
	}

	public function tearDown() {
		unset($this->fixture, $GLOBALS['TSFE'], $this->mapPointWithCoordinates);
	}

	/**
	 * @test
	 */
	public function twoMapsAfterRenderingHaveDifferentMapIds() {
		$map1 = new Tx_Oelib_ViewHelpers_GoogleMapsViewHelper();
		$map1->render(array($this->mapPointWithCoordinates));
		$map2 = new Tx_Oelib_ViewHelpers_GoogleMapsViewHelper();
		$map2->render(array($this->mapPointWithCoordinates));

		$this->assertNotSame(
			$map1->getMapId(),
			$map2->getMapId()
		);
	}

	/**
	 * @test
	 */
	public function renderForEmptyMapPointsReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->render(array())
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithoutCoordinatesReturnsEmptyString() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(FALSE));

		$this->assertSame(
			'',
			$this->fixture->render(array($mapPoint))
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithoutCoordinatesNotSetsAdditionalHeaderData() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(FALSE));

		$this->fixture->render(array($mapPoint));

		$this->assertSame(
			array(),
			$GLOBALS['TSFE']->additionalHeaderData
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsDivWithIdWithGeneralMapId() {
		$this->assertContains(
			'<div id="' . Tx_Oelib_ViewHelpers_GoogleMapsViewHelper::MAP_HTML_ID_PREFIX,
			$this->fixture->render(array($this->mapPointWithCoordinates))
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsDivWithIdWithSpecificMapId() {
		$result = $this->fixture->render(array($this->mapPointWithCoordinates));

		$this->assertContains(
			'<div id="' . $this->fixture->getMapId(),
			$result
		);
	}

	/**
	 * @test
	 */
	public function renderWithoutWidthAndWithoutHeightReturnsStyleWithDefaultWidth() {
		$this->assertContains(
			'width: 600px;',
			$this->fixture->render(array($this->mapPointWithCoordinates))
		);
	}

	/**
	 * @test
	 */
	public function renderWithoutWidthAndWithoutHeightReturnsStyleWithDefaultHeight() {
		$this->assertContains(
			'height: 400px;',
			$this->fixture->render(array($this->mapPointWithCoordinates))
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function renderWithEmptyWidthThrowsException() {
		$this->fixture->render(array($this->mapPointWithCoordinates), '', '42px');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function renderWithInvalidWidthThrowsException() {
		$this->fixture->render(array($this->mapPointWithCoordinates), 'foo', '42px');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function renderWithEmptyHeightThrowsException() {
		$this->fixture->render(array($this->mapPointWithCoordinates), '42px', '');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function renderWithInvalidHeightThrowsException() {
		$this->fixture->render(array($this->mapPointWithCoordinates), '42px', 'foo');
	}

	/**
	 * @test
	 */
	public function renderWithWithAndHeightInPixelsNotThrowsException() {
		$this->fixture->render(array($this->mapPointWithCoordinates), '42px', '91px');
	}

	/**
	 * @test
	 */
	public function renderWithWithAndHeightInPercentNotThrowsException() {
		$this->fixture->render(array($this->mapPointWithCoordinates), '42%', '91%');
	}

	/**
	 * @test
	 */
	public function renderReturnsStyleWithGivenWidth() {
		$this->assertContains(
			'width: 142px;',
			$this->fixture->render(array($this->mapPointWithCoordinates), '142px')
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsStyleWithGivenHeight() {
		$this->assertContains(
			'height: 99px;',
			$this->fixture->render(array($this->mapPointWithCoordinates), '142px', '99px')
		);
	}

	/**
	 * @test
	 */
	public function renderIncludesGoogleMapsLibraryInHeader() {
		$this->fixture->render(array($this->mapPointWithCoordinates));

		$this->assertContains(
			'<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>',
			$GLOBALS['TSFE']->additionalHeaderData[Tx_Oelib_ViewHelpers_GoogleMapsViewHelper::LIBRARY_JAVASCRIPT_HEADER_KEY]
		);
	}

	/**
	 * @test
	 */
	public function renderIncludesJavaScriptInHeader() {
		$this->fixture->render(array($this->mapPointWithCoordinates));

		$this->assertTrue(
			isset($GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()])
		);
	}

	/**
	 * @test
	 */
	public function renderIncludesJavaScriptWithGoogleMapInitializationInHeader() {
		$this->fixture->render(array($this->mapPointWithCoordinates));

		$this->assertContains(
			'new google.maps.Map(',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsInitializationCallWithMapNumber() {
		$this->assertRegExp(
			'/initializeGoogleMap_\d+/',
			$this->fixture->render(array($this->mapPointWithCoordinates))
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function renderForMapPointsOfNonMapPointClassThrowsException() {
		$element = new stdClass();

		$this->fixture->render(array($element));
	}

	/**
	 * @test
	 */
	public function renderForElementWithCoordinatesCreatesMapMarker() {
		$this->fixture->render(array($this->mapPointWithCoordinates));

		$this->assertContains(
			'new google.maps.Marker(',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithCoordinatesCreatesMapPointCoordinates() {
		$this->fixture->render(array($this->mapPointWithCoordinates));

		$this->assertContains(
			'new google.maps.LatLng(1.200000, 3.400000)',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithCoordinatesWithoutIdentityNotCreatesUidProperty() {
		$this->fixture->render(array($this->mapPointWithCoordinates));

		$this->assertNotContains(
			'uid:',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithCoordinatesWithIdentityWithoutUidNotCreatesUidProperty() {
		$mapPoint = new tx_oelib_tests_fixtures_TestingMapPoint();
		$mapPoint->setUid(0);
		$this->fixture->render(array($mapPoint));

		$this->assertNotContains(
			'uid:',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithCoordinatesWithIdentityWithUidCreatesUidPropertyWithUid() {
		$uid = 42;
		$mapPoint = new tx_oelib_tests_fixtures_TestingMapPoint();
		$mapPoint->setUid($uid);
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'uid: ' . $uid,
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithCoordinatesWithoutIdentityNotCreatesEntryInMapMarkersByUid() {
		$this->fixture->render(array($this->mapPointWithCoordinates));

		$this->assertNotContains(
			'mapMarkersByUid.' . $this->fixture->getMapId() . '[',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithCoordinatesWithIdentityWithoutUidNotCreatesEntryInMapMarkersByUid() {
		$mapPoint = new tx_oelib_tests_fixtures_TestingMapPoint();
		$mapPoint->setUid(0);
		$this->fixture->render(array($mapPoint));

		$this->assertNotContains(
			'mapMarkersByUid.' . $this->fixture->getMapId() . '[',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithCoordinatesWithIdentityWithUidCreatesEntryInMapMarkersByUid() {
		$uid = 42;
		$mapPoint = new tx_oelib_tests_fixtures_TestingMapPoint();
		$mapPoint->setUid($uid);
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'mapMarkersByUid.' . $this->fixture->getMapId() . '[' . $uid . '] = marker_',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForOneElementWithCoordinatesUsesMapPointCoordinatesAsCenter() {
		$this->fixture->render(array($this->mapPointWithCoordinates));

		$this->assertContains(
			'var center = new google.maps.LatLng(1.200000, 3.400000);',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForTwoElementWithCoordinatesUsesFirstMapPointCoordinatesAsCenter() {
		$mapPoint1 = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint1->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint1->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint2 = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint2->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint2->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 5.6, 'longitude' => 7.8)));

		$this->fixture->render(array($mapPoint1, $mapPoint2));

		$this->assertContains(
			'var center = new google.maps.LatLng(1.200000, 3.400000);',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForTwoElementsWithCoordinatesCreatesTwoMapMarkers() {
		$mapPoint1 = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint1->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint1->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint2 = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint2->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint2->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 5.6, 'longitude' => 7.8)));

		$this->fixture->render(array($mapPoint1, $mapPoint2));

		$this->assertSame(
			2,
			substr_count(
				$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()],
				'new google.maps.Marker('
			)
		);
	}

	/**
	 * @test
	 */
	public function renderForTwoElementsWithCoordinatesExtendsBoundsTwoTimes() {
		$mapPoint1 = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint1->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint1->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint2 = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint2->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint2->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 5.6, 'longitude' => 7.8)));
		$this->fixture->render(array($mapPoint1, $mapPoint2));

		$this->assertSame(
			2,
			substr_count(
				$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()],
				'bounds.extend('
			)
		);
	}

	/**
	 * @test
	 */
	public function renderForTwoElementsWithCoordinatesFitsMapToBounds() {
		$mapPoint1 = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint1->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint1->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint2 = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint2->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint2->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 5.6, 'longitude' => 7.8)));
		$this->fixture->render(array($mapPoint1, $mapPoint2));

		$this->assertContains(
			'map.fitBounds(',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithTitleCreatesTitle() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasTooltipTitle')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getTooltipTitle')->will($this->returnValue('Hello world!'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'title: "Hello world!"',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithTitleEscapesQuotesInTitle() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasTooltipTitle')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getTooltipTitle')->will($this->returnValue('The "B" side'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'title: "The \\"B\\" side"',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithTitleEscapesLinefeedsInTitle() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasTooltipTitle')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getTooltipTitle')->will($this->returnValue('Here' . LF . 'There'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'title: "Here\\nThere"',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithTitleEscapesCarriageReturnsInTitle() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasTooltipTitle')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getTooltipTitle')->will($this->returnValue('Here' . CR . 'There'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'title: "Here\\rThere"',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithTitleEscapesBackslashesInTitle() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasTooltipTitle')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getTooltipTitle')->will($this->returnValue('Here\\There'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'title: "Here\\\\There"',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithoutTitleNotCreatesTitle() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasTooltipTitle')->will($this->returnValue(FALSE));
		$this->fixture->render(array($mapPoint));

		$this->assertNotContains(
			'title: ',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithInfoWindowContentCreatesInfoWindow() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasInfoWindowContent')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getInfoWindowContent')->will($this->returnValue('Hello world!'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'new google.maps.InfoWindow',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithInfoWindowContentEscapesQuotesInInfoWindowContent() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasInfoWindowContent')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getInfoWindowContent')->will($this->returnValue('The "B" side'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'"The \\"B\\" side"',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithInfoWindowContentEscapesLinefeedsInInfoWindowContent() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasInfoWindowContent')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getInfoWindowContent')->will($this->returnValue('Here' . LF . 'There'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'"Here\\nThere"',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithInfoWindowContentEscapesCarriageReturnsInInfoWindowContent() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasInfoWindowContent')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getInfoWindowContent')->will($this->returnValue('Here' . CR . 'There'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'"Here\\rThere"',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithInfoWindowContentEscapesBackslashesInInfoWindowContent() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasInfoWindowContent')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getInfoWindowContent')->will($this->returnValue('Here\\There'));
		$this->fixture->render(array($mapPoint));

		$this->assertContains(
			'"Here\\\\There"',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}

	/**
	 * @test
	 */
	public function renderForElementWithoutInfoWindowContentNotCreatesInfoWindow() {
		$mapPoint = $this->getMock('tx_oelib_Interface_MapPoint');
		$mapPoint->expects($this->any())->method('hasGeoCoordinates')->will($this->returnValue(TRUE));
		$mapPoint->expects($this->any())->method('getGeoCoordinates')
			->will($this->returnValue(array('latitude' => 1.2, 'longitude' => 3.4)));
		$mapPoint->expects($this->any())->method('hasInfoWindowContent')->will($this->returnValue(FALSE));
		$this->fixture->render(array($mapPoint));

		$this->assertNotContains(
			'new google.maps.InfoWindow',
			$GLOBALS['TSFE']->additionalJavaScript[$this->fixture->getMapId()]
		);
	}
}
?>