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
 */
class Tx_Oelib_FakeSessionTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_FakeSession the object to test
	 */
	private $subject;

	public function setUp() {
		$this->subject = new Tx_Oelib_FakeSession();
	}

	public function tearDown() {
		unset($this->subject);
	}


	/////////////////////////////////////////////////////////
	// Tests for the basic functions
	/////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function fakeSessionCanBeInstantiatedDirectly() {
		new Tx_Oelib_FakeSession();
	}


	////////////////////////////////////////
	// Tests that the setters/getters work
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAsStringWithInexistentKeyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsNonEmptyStringSetViaSetAsString() {
		$this->subject->setAsString('foo', 'bar');

		$this->assertSame(
			'bar',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringReturnsEmptyStringSetViaSetAsString() {
		$this->subject->setAsString('foo', '');

		$this->assertSame(
			'',
			$this->subject->getAsString('foo')
		);
	}
}