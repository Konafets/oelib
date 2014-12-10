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
class Tx_Oelib_SessionTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework for creating a fake front end
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->testingFramework);
	}


	/////////////////////////////////////////////////////////
	// Tests for setting and getting the Singleton instance
	/////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceThrowsExceptionWithoutFrontEnd() {
		$this->setExpectedException(
			'BadMethodCallException',
			'This class must not be instantiated when there is no front end.'
		);

		$GLOBALS['TSFE'] = NULL;

		Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_USER);
	}

	/**
	 * @test
	 */
	public function getInstanceWithInvalidTypeThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.'
		);

		$this->testingFramework->createFakeFrontEnd();

		Tx_Oelib_Session::getInstance(42);
	}

	/**
	 * @test
	 */
	public function getInstanceWithUserTypeReturnsSessionInstance() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertTrue(
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_USER)
				instanceof Tx_Oelib_Session
		);
	}

	/**
	 * @test
	 */
	public function getInstanceWithTemporaryTypeReturnsSessionInstance() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertTrue(
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_TEMPORARY)
				instanceof Tx_Oelib_Session
		);
	}

	/**
	 * @test
	 */
	public function getInstanceWithSameTypeReturnsSameInstance() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertSame(
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_USER),
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_USER)
		);
	}

	/**
	 * @test
	 */
	public function getInstanceWithDifferentTypesReturnsDifferentInstance() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertNotSame(
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_USER),
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_TEMPORARY)
		);
	}

	/**
	 * @test
	 */
	public function getInstanceWithSameTypesAfterPurgeInstancesReturnsNewInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$firstInstance = Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_USER);
		Tx_Oelib_Session::purgeInstances();

		$this->assertNotSame(
			$firstInstance,
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_USER)
		);
	}

	/**
	 * @test
	 */
	public function setInstanceWithInvalidTypeThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.'
		);

		Tx_Oelib_Session::setInstance(42, new Tx_Oelib_FakeSession());
	}

	/**
	 * @test
	 */
	public function getInstanceWithUserTypeReturnsInstanceFromSetInstance() {
		$instance = new Tx_Oelib_FakeSession();
		Tx_Oelib_Session::setInstance(Tx_Oelib_Session::TYPE_USER, $instance);

		$this->assertSame(
			$instance,
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_USER)
		);
	}

	/**
	 * @test
	 */
	public function getInstanceWithTemporaryTypeReturnsInstanceFromSetInstance() {
		$instance = new Tx_Oelib_FakeSession();
		Tx_Oelib_Session::setInstance(
			Tx_Oelib_Session::TYPE_TEMPORARY, $instance
		);

		$this->assertSame(
			$instance,
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_TEMPORARY)
		);
	}

	/**
	 * @test
	 */
	public function getInstanceWithDifferentTypesReturnsDifferentInstancesSetViaSetInstance() {
		Tx_Oelib_Session::setInstance(
			Tx_Oelib_Session::TYPE_USER,
			new Tx_Oelib_FakeSession()
		);
		Tx_Oelib_Session::setInstance(
			Tx_Oelib_Session::TYPE_TEMPORARY,
			new Tx_Oelib_FakeSession()
		);

		$this->assertNotSame(
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_USER),
			Tx_Oelib_Session::getInstance(Tx_Oelib_Session::TYPE_TEMPORARY)
		);
	}
}