<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2012 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_oelib_Attachment class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_AttachmentTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Attachment
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Attachment();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
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
			$this->fixture->getFileName()
		);
	}

	/**
	 * @test
	 */
	public function getFileNameWithFileNameSetReturnsFileName() {
		$this->fixture->setFileName('test.txt');

		$this->assertSame(
			'test.txt',
			$this->fixture->getFileName()
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

		$this->fixture->setFileName('');
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
			$this->fixture->getContentType()
		);
	}

	/**
	 * @test
	 */
	public function getContentTypeWithContentTypeSetReturnsContentType() {
		$this->fixture->setContentType('text/plain');

		$this->assertSame(
			'text/plain',
			$this->fixture->getContentType()
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

		$this->fixture->setContentType('');
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
			$this->fixture->getContent()
		);
	}

	/**
	 * @test
	 */
	public function getContentWithContentSetReturnsContent() {
		$this->fixture->setContent('test content');

		$this->assertSame(
			'test content',
			$this->fixture->getContent()
		);
	}
}
?>