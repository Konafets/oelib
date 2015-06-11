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
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_Unit_HeaderProxyFactoryTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_HeaderCollector
	 */
	private $subject;

	protected function setUp() {
		// Only the instance with an enabled test mode can be tested as in the
		// non-test mode added headers are not accessible.
		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
		$this->subject = tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy();
	}

	protected function tearDown() {
		tx_oelib_headerProxyFactory::purgeInstance();
	}

	/**
	 * @test
	 */
	public function getHeaderProxyInTestMode() {
		self::assertSame(
			'Tx_Oelib_HeaderCollector',
			get_class($this->subject)
		);
	}

	/**
	 * @test
	 */
	public function getHeaderProxyInNonTestMode() {
		// new instances always have a disabled test mode
		tx_oelib_headerProxyFactory::purgeInstance();

		self::assertSame(
			'Tx_Oelib_RealHeaderProxy',
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

		self::assertNotSame(
			$instance,
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
		);
	}

	/**
	 * @test
	 */
	public function getHeaderProxyReturnsTheSameObjectWhenCalledInTheSameClassInTheSameMode() {
		self::assertSame(
			$this->subject,
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
		);
	}

	/**
	 * @test
	 */
	public function getHeaderProxyNotReturnsTheSameObjectWhenCalledInTheSameClassInAnotherMode() {
		// new instances always have a disabled test mode
		tx_oelib_headerProxyFactory::purgeInstance();

		self::assertNotSame(
			$this->subject,
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
		);
	}

	/**
	 * @test
	 */
	public function addHeaderAndGetIt() {
		$this->subject->addHeader('123: foo.');

		self::assertSame(
			'123: foo.',
			$this->subject->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function addTwoHeadersAndGetTheLast() {
		$this->subject->addHeader('123: foo.');
		$this->subject->addHeader('123: bar.');

		self::assertSame(
			'123: bar.',
			$this->subject->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function addTwoHeadersAndGetBoth() {
		$this->subject->addHeader('123: foo.');
		$this->subject->addHeader('123: bar.');

		self::assertSame(
			array('123: foo.', '123: bar.'),
			$this->subject->getAllAddedHeaders()
		);
	}
}