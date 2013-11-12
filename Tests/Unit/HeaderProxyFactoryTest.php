<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Saskia Metzler <saskia@merlin.owl.de>
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
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_HeaderProxyFactoryTest extends Tx_Phpunit_TestCase {
	/** instance of the object to test */
	private $fixture;

	protected function setUp() {
		// Only the instance with an enabled test mode can be tested as in the
		// non-test mode added headers are not accessible.
		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
		$this->fixture = tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy();
	}

	protected function tearDown() {
		tx_oelib_headerProxyFactory::purgeInstance();
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getHeaderProxyInTestMode() {
		$this->assertSame(
			'tx_oelib_headerCollector',
			get_class($this->fixture)
		);
	}

	/**
	 * @test
	 */
	public function getHeaderProxyInNonTestMode() {
		// new instances always have a disabled test mode
		tx_oelib_headerProxyFactory::purgeInstance();

		$this->assertSame(
			'tx_oelib_realHeaderProxy',
			get_class(tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy())
		);
	}

	/**
	 * @test
	 */
	public function getHeaderProxyInSameModeAfterPurgeInstanceReturnsNewInstance() {
		tx_oelib_headerProxyFactory::purgeInstance();
		$instance = tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy();
		tx_oelib_headerProxyFactory::purgeInstance();

		$this->assertNotSame(
			$instance,
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
		);
	}

	/**
	 * @test
	 */
	public function getHeaderProxyReturnsTheSameObjectWhenCalledInTheSameClassInTheSameMode() {
		$this->assertSame(
			$this->fixture,
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
		);
	}

	/**
	 * @test
	 */
	public function getHeaderProxyNotReturnsTheSameObjectWhenCalledInTheSameClassInAnotherMode() {
		// new instances always have a disabled test mode
		tx_oelib_headerProxyFactory::purgeInstance();

		$this->assertNotSame(
			$this->fixture,
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
		);
	}

	/**
	 * @test
	 */
	public function addHeaderAndGetIt() {
		$this->fixture->addHeader('123: foo.');

		$this->assertSame(
			'123: foo.',
			$this->fixture->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function addTwoHeadersAndGetTheLast() {
		$this->fixture->addHeader('123: foo.');
		$this->fixture->addHeader('123: bar.');

		$this->assertSame(
			'123: bar.',
			$this->fixture->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function addTwoHeadersAndGetBoth() {
		$this->fixture->addHeader('123: foo.');
		$this->fixture->addHeader('123: bar.');

		$this->assertSame(
			array('123: foo.', '123: bar.'),
			$this->fixture->getAllAddedHeaders()
		);
	}
}
?>