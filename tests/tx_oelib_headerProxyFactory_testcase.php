<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Saskia Metzler <saskia@merlin.owl.de>
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
 * Testcase for the header proxy factory class and the header collector class
 * in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Saskia Metzler <saskia@merlin.owl.de>
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('oelib') . 'tx_oelib_commonConstants.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_headerProxyFactory.php');

class tx_oelib_headerProxyFactory_testcase extends tx_phpunit_testcase {
	/** instance of the object to test */
	private $fixture;

	protected function setUp() {
		// Only the instance with an enabled test mode can be tested as in the
		// non-test mode added headers are not accessible.
		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
		$this->fixture = tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy();
	}

	protected function tearDown() {
		tx_oelib_headerProxyFactory::getInstance()->discardInstance();
		unset($this->fixture);
	}

	public function testGetHeaderProxyInTestMode() {
		$this->assertEquals(
			'tx_oelib_headerCollector',
			get_class($this->fixture)
		);
	}

	public function testGetHeaderProxyInNonTestMode() {
		// new instances always have a disabled test mode
		tx_oelib_headerProxyFactory::getInstance()->discardInstance();

		$this->assertEquals(
			'tx_oelib_realHeaderProxy',
			get_class(tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy())
		);
	}

	public function testGetHeaderProxyReturnsTheSameObjectWhenCalledInTheSameClassInTheSameMode() {
		$this->assertSame(
			$this->fixture,
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
		);
	}

	public function testGetHeaderProxyNotReturnsTheSameObjectWhenCalledInTheSameClassInAnotherMode() {
		// new instances always have a disabled test mode
		tx_oelib_headerProxyFactory::getInstance()->discardInstance();

		$this->assertNotSame(
			$this->fixture,
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
		);
	}

	public function testAddHeaderAndGetIt() {
		$this->fixture->addHeader('123: foo.');

		$this->assertEquals(
			'123: foo.',
			$this->fixture->getLastAddedHeader()
		);
	}

	public function testAddTwoHeadersAndGetTheLast() {
		$this->fixture->addHeader('123: foo.');
		$this->fixture->addHeader('123: bar.');

		$this->assertEquals(
			'123: bar.',
			$this->fixture->getLastAddedHeader()
		);
	}

	public function testAddTwoHeadersAndGetBoth() {
		$this->fixture->addHeader('123: foo.');
		$this->fixture->addHeader('123: bar.');

		$this->assertEquals(
			array('123: foo.', '123: bar.'),
			$this->fixture->getAllAddedHeaders()
		);
	}

	public function testHeaderProxyInNonTestModeReallySendsCatchableHeader() {
		$this->setExpectedException(
			'Exception',
			'Cannot modify header information - headers already sent by'
		);
		tx_oelib_headerProxyFactory::getInstance()->discardInstance();
		tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader('123: foo.');
	}
}
?>