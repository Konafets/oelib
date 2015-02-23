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
 * Testcase for the Tx_Oelib_ViewHelpers_UppercaseViewHelper class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_ViewHelpers_UppercaseViewHelperTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @test
	 */
	public function renderConvertsToUppercase() {
		$subject = $this->getMock('Tx_Oelib_ViewHelpers_UppercaseViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('foo bar'));

		/** @var Tx_Oelib_ViewHelpers_UppercaseViewHelper $subject */
		$this->assertSame(
			'FOO BAR',
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderCanConvertUmlautsToUppercase() {
		$subject = $this->getMock('Tx_Oelib_ViewHelpers_UppercaseViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('äöü'));

		/** @var Tx_Oelib_ViewHelpers_UppercaseViewHelper $subject */
		$this->assertSame(
			'ÄÖÜ',
			$subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderCanConvertAccentedCharactersToUppercase() {
		$subject = $this->getMock('Tx_Oelib_ViewHelpers_UppercaseViewHelper', array('renderChildren'));
		$subject->expects($this->once())->method('renderChildren')->will($this->returnValue('áàéè'));

		/** @var Tx_Oelib_ViewHelpers_UppercaseViewHelper $subject */
		$this->assertSame(
			'ÁÀÉÈ',
			$subject->render()
		);
	}
}