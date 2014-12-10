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
 * Testcase for the Tx_Oelib_Domain_Repository_CountryRepository class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Domain_Repository_CountryRepositoryTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Oelib_Domain_Repository_CountryRepository
	 */
	private $subject = NULL;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Domain_Repository_CountryRepository(
			$this->getMock('Tx_Extbase_Object_ObjectManagerInterface')
		);
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function classCanBeInstantiated() {
		$this->assertNotNull(
			new Tx_Oelib_Domain_Repository_CountryRepository(
				$this->getMock('Tx_Extbase_Object_ObjectManagerInterface')
			)
		);
	}

	/**
	 * @test
	 */
	public function initializeObjectSetsRespectStoragePidToFalse() {
		$objectManager = $this->getMock('Tx_Extbase_Object_ObjectManagerInterface');
		$subject = new Tx_Oelib_Domain_Repository_CountryRepository($objectManager);

		$querySettings = $this->getMock('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->expects($this->once())->method('setRespectStoragePage')->with(FALSE);
		$objectManager->expects($this->once())->method('create')
			->with('Tx_Extbase_Persistence_Typo3QuerySettings')->will($this->returnValue($querySettings));

		$subject->initializeObject();
	}

	/**
	 * @test
	 */
	public function initializeObjectSetsDefaultQuerySettings() {
		$objectManager = $this->getMock('Tx_Extbase_Object_ObjectManagerInterface');
		$subject = $this->getMock(
			'Tx_Oelib_Domain_Repository_CountryRepository',
			array('setDefaultQuerySettings'), array($objectManager)
		);

		$querySettings = $this->getMock('Tx_Extbase_Persistence_Typo3QuerySettings');
		$objectManager->expects($this->once())->method('create')
			->with('Tx_Extbase_Persistence_Typo3QuerySettings')->will($this->returnValue($querySettings));

		$subject->expects($this->once())->method('setDefaultQuerySettings')->with($querySettings);

		$subject->initializeObject();
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function addThrowsException() {
		$this->subject->add(new Tx_Oelib_Domain_Model_Country());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function removeThrowsException() {
		$this->subject->remove(new Tx_Oelib_Domain_Model_Country());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function replaceThrowsException() {
		$this->subject->replace(new Tx_Oelib_Domain_Model_Country(), new Tx_Oelib_Domain_Model_Country());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function updateThrowsException() {
		$this->subject->update(new Tx_Oelib_Domain_Model_Country());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function removeAllThrowsException() {
		$this->subject->removeAll();
	}
}