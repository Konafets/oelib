<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Testcase for the Tx_Oelib_Domain_Repository_FederalStateRepository class.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Domain_Repository_FederalStateRepositoryTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Oelib_Domain_Repository_FederalStateRepository
	 */
	private $subject = NULL;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Domain_Repository_FederalStateRepository(
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
			new Tx_Oelib_Domain_Repository_FederalStateRepository(
				$this->getMock('Tx_Extbase_Object_ObjectManagerInterface')
			)
		);
	}

	/**
	 * @test
	 */
	public function initializeObjectSetsRespectStoragePidToFalse() {
		$objectManager = $this->getMock('Tx_Extbase_Object_ObjectManagerInterface');
		$subject = new Tx_Oelib_Domain_Repository_FederalStateRepository($objectManager);

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
			'Tx_Oelib_Domain_Repository_FederalStateRepository',
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
		$this->subject->add(new Tx_Oelib_Domain_Model_FederalState());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function removeThrowsException() {
		$this->subject->remove(new Tx_Oelib_Domain_Model_FederalState());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function replaceThrowsException() {
		$this->subject->replace(new Tx_Oelib_Domain_Model_FederalState(), new Tx_Oelib_Domain_Model_FederalState());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function updateThrowsException() {
		$this->subject->update(new Tx_Oelib_Domain_Model_FederalState());
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
?>