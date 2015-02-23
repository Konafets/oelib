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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_Unit_AttachmentTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Attachment
	 */
	private $subject;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Attachment();
	}


	///////////////////////////////////////////////////////
	// Tests regarding setting and getting the file name.
	///////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getFileNameInitiallyReturnsAnEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getFileName()
		);
	}

	/**
	 * @test
	 */
	public function getFileNameWithFileNameSetReturnsFileName() {
		$this->subject->setFileName('test.txt');

		$this->assertSame(
			'test.txt',
			$this->subject->getFileName()
		);
	}

	/**
	 * @test
	 */
	public function setFileNameWithEmptyFileNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$fileName must not be empty.'
		);

		$this->subject->setFileName('');
	}


	//////////////////////////////////////////////////////////
	// Tests regarding setting and getting the content type.
	//////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getContentTypeInitiallyReturnsAnEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getContentType()
		);
	}

	/**
	 * @test
	 */
	public function getContentTypeWithContentTypeSetReturnsContentType() {
		$this->subject->setContentType('text/plain');

		$this->assertSame(
			'text/plain',
			$this->subject->getContentType()
		);
	}

	/**
	 * @test
	 */
	public function setContentTypeWithEmptyContentTypeThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$contentType must not be empty.'
		);

		$this->subject->setContentType('');
	}


	/////////////////////////////////////////////////////
	// Tests regarding setting and getting the content.
	/////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getContentInitiallyReturnsAnEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getContentWithContentSetReturnsContent() {
		$this->subject->setContent('test content');

		$this->assertSame(
			'test content',
			$this->subject->getContent()
		);
	}
}