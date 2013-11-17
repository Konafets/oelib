<?php
/***************************************************************
* Copyright notice
*
* (c) 2012 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the Tx_Oelib_ViewHelpers_ImageSourceViewHelper class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ViewHelpers_ImageSourceViewHelperTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @test
	 */
	public function renderForEmptyReturnsEmptyString() {
		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue(''));

		$this->assertSame(
			'',
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForNonEmptyNonImageContentReturnsEmptyString() {
		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('foo bar'));

		$this->assertSame(
			'',
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForOtherTagReturnsEmptyString() {
		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('<p>foo<br />bar</p>'));

		$this->assertSame(
			'',
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForImageWithMissingSourceReturnsEmptyString() {
		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('<img />'));

		$this->assertSame(
			'',
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForImageWithEmptySourceReturnsEmptyString() {
		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('<img src="" />'));

		$this->assertSame(
			'',
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForOneImageWithNonEmptySourceReturnsImageSource() {
		$imageSource = 'fileadmin/foo.jpg';

		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('<img src="' . $imageSource . '" />'));

		$this->assertSame(
			$imageSource,
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForOneImageWithAltTextAndWithNonEmptySourceReturnsImageSource() {
		$imageSource = 'fileadmin/foo.jpg';

		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('<img alt="Foo" src="' . $imageSource . '" />'));

		$this->assertSame(
			$imageSource,
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForOneImageWithNonEmptySourceWithSpacesBeforeSrcReturnsImageSource() {
		$imageSource = 'fileadmin/foo.jpg';

		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('<img   src="' . $imageSource . '" />'));

		$this->assertSame(
			$imageSource,
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForOneImageWithNonEmptySourceWithSpacesAroundEqualsSignReturnsImageSource() {
		$imageSource = 'fileadmin/foo.jpg';

		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('<img src  =  "' . $imageSource . '" />'));

		$this->assertSame(
			$imageSource,
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForOneImageWithNonEmptySourceUsingSingleQuotesReturnsImageSource() {
		$imageSource = 'fileadmin/foo.jpg';

		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('<img src=\'' . $imageSource . '\' />'));

		$this->assertSame(
			$imageSource,
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForTwoImagesWithNonEmptySourceReturnsFirstImageSource() {
		$imageSource = 'fileadmin/foo.jpg';

		$subject = $this->getMock('Tx_Oelib_ViewHelpers_ImageSourceViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')
			->will($this->returnValue('<img src="' . $imageSource . '" /> <img src="anotherImage.png" />'));

		$this->assertSame(
			$imageSource,
			$subject->render()
		);
	}
}
?>