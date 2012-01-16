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
	private $fixture = NULL;

	protected function setUp() {
		$this->fixture = new Tx_Oelib_Domain_Repository_CountryRepository(
			$this->getMock('Tx_Extbase_Object_ObjectManagerInterface')
		);
	}

	protected function tearDown() {
		unset($this->fixture);
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
		$fixture = new Tx_Oelib_Domain_Repository_CountryRepository($objectManager);

		$querySettings = $this->getMock('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->expects($this->once())->method('setRespectStoragePage')->with(FALSE);
		$objectManager->expects($this->once())->method('create')
			->with('Tx_Extbase_Persistence_Typo3QuerySettings')->will($this->returnValue($querySettings));

		$fixture->initializeObject();
	}

	/**
	 * @test
	 */
	public function initializeObjectSetsDefaultQuerySettings() {
		$objectManager = $this->getMock('Tx_Extbase_Object_ObjectManagerInterface');
		$fixture = $this->getMock(
			'Tx_Oelib_Domain_Repository_CountryRepository',
			array('setDefaultQuerySettings'), array($objectManager)
		);

		$querySettings = $this->getMock('Tx_Extbase_Persistence_Typo3QuerySettings');
		$objectManager->expects($this->once())->method('create')
			->with('Tx_Extbase_Persistence_Typo3QuerySettings')->will($this->returnValue($querySettings));

		$fixture->expects($this->once())->method('setDefaultQuerySettings')->with($querySettings);

		$fixture->initializeObject();
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function addThrowsException() {
		$this->fixture->add(new Tx_Oelib_Domain_Model_Country());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function removeThrowsException() {
		$this->fixture->remove(new Tx_Oelib_Domain_Model_Country());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function replaceThrowsException() {
		$this->fixture->replace(new Tx_Oelib_Domain_Model_Country(), new Tx_Oelib_Domain_Model_Country());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function updateThrowsException() {
		$this->fixture->update(new Tx_Oelib_Domain_Model_Country());
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function removeAllThrowsException() {
		$this->fixture->removeAll();
	}
}
?>