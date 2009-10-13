<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the tx_oelib_Model class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Model_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_tests_fixtures_TestingModel the model to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_tests_fixtures_TestingModel();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	//////////////////////////////////////
	// Tests for the basic functionality
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function destructDoesNotCrashForRelationToSelf() {
		$fixture = new tx_oelib_tests_fixtures_TestingModel();
		$fixture->setData(
			array('foo' => $fixture)
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function destructDoesNotCrashForTwoModelsInACircle() {
		$fixture1 = new tx_oelib_tests_fixtures_TestingModel();
		$fixture2 = new tx_oelib_tests_fixtures_TestingModel();

		$fixture1->setData(
			array('foo' => $fixture2)
		);
		$fixture2->setData(
			array('foo' => $fixture1)
		);

		$fixture1->__destruct();
		$fixture2->__destruct();
	}

	public function testGetWithNoDataThrowsException() {
		$this->setExpectedException(
			'Exception',
			'Please call setData() directly after instantiation first.'
		);

		$this->fixture->getTitle();
	}

	public function testSetDataWithEmptyArrayIsAllowed() {
		$this->fixture->setData(array());
	}

	public function testGetAfterSetReturnsTheSetValue() {
		$this->fixture->setTitle('bar');

		$this->assertEquals(
			'bar',
			$this->fixture->getTitle()
		);
	}

	public function testGetAfterSetDataReturnsTheSetValue() {
		$this->fixture->setData(
			array('title' => 'bar')
		);

		$this->assertEquals(
			'bar',
			$this->fixture->getTitle()
		);
	}

	public function testSetDataCalledTwoTimesThrowsAnException() {
		$this->setExpectedException(
			'Exception', 'setData must only be called once per model instance.'
		);

		$this->fixture->setData(
			array('title' => 'bar')
		);
		$this->fixture->setData(
			array('title' => 'bar')
		);
	}

	public function testIsHiddenForLoadedHiddenObjectReturnsTrue() {
		$this->fixture->setData(
			array('hidden' => 1)
		);

		$this->assertTrue(
			$this->fixture->isHidden()
		);
	}

	public function testIsHiddenForLoadedNonHiddenObjectReturnsFalse() {
		$this->fixture->setData(
			array('hidden' => 0)
		);

		$this->assertFalse(
			$this->fixture->isHidden()
		);
	}


	///////////////////////////////
	// Tests concerning existsKey
	///////////////////////////////

	public function testExistsKeyForInexistentKeyReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->existsKey('foo')
		);
	}

	public function testExistsKeyForExistingKeyWithNonEmptyDataReturnsTrue() {
		$this->fixture->setData(
			array('foo' => 'bar')
		);

		$this->assertTrue(
			$this->fixture->existsKey('foo')
		);
	}

	public function testExistsKeyForExistingKeyWithEmptyDataReturnsTrue() {
		$this->fixture->setData(
			array('foo' => '')
		);

		$this->assertTrue(
			$this->fixture->existsKey('foo')
		);
	}


	////////////////////////////////
	// Tests concerning getAsModel
	////////////////////////////////

	public function testGetAsModelWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '$key must not be empty.'
		);

		$this->fixture->getAsModel('');
	}

	public function testGetAsModelWithInexistentKeyReturnsNull() {
		$this->fixture->setData(array());

		$this->assertNull(
			$this->fixture->getAsModel('foo')
		);
	}

	public function testGetAsModelWithKeyForStringDataThrowsException() {
		$this->setExpectedException(
			'Exception', 'The data item for the key "foo" is no model instance.'
		);

		$this->fixture->setData(array('foo' => 'bar'));

		$this->fixture->getAsModel('foo');
	}

	public function testGetAsModelReturnsNullSetViaSetData() {
		$this->fixture->setData(
			array('foo' => null)
		);

		$this->assertNull(
			$this->fixture->getAsModel('foo')
		);
	}

	public function testGetAsModelReturnsModelSetViaSetData() {
		$otherModel = new tx_oelib_tests_fixtures_TestingModel();
		$this->fixture->setData(
			array('foo' => $otherModel)
		);

		$this->assertSame(
			$otherModel,
			$this->fixture->getAsModel('foo')
		);

		$otherModel->__destruct();
	}

	/**
	 * @test
	 */
	public function getAsModelForSelfReturnsSelf() {
		$this->fixture->setData(
			array('foo' => $this->fixture)
		);

		$this->assertSame(
			$this->fixture,
			$this->fixture->getAsModel('foo')
		);
	}


	////////////////////////////////
	// Tests concerning getAsList
	////////////////////////////////

	public function testGetAsListWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '$key must not be empty.'
		);

		$this->fixture->getAsList('');
	}

	public function testGetAsListWithInexistentKeyThrowsException() {
		$this->setExpectedException(
			'Exception', 'The data item for the key "foo" is no list instance.'
		);

		$this->fixture->setData(array());

		$this->assertNull(
			$this->fixture->getAsList('foo')
		);
	}

	public function testGetAsListWithKeyForStringDataThrowsException() {
		$this->setExpectedException(
			'Exception', 'The data item for the key "foo" is no list instance.'
		);

		$this->fixture->setData(array('foo' => 'bar'));

		$this->fixture->getAsList('foo');
	}

	public function testGetAsListReturnsListSetViaSetData() {
		$list = new tx_oelib_List();
		$this->fixture->setData(
			array('foo' => $list)
		);

		$this->assertSame(
			$list,
			$this->fixture->getAsList('foo')
		);

		$list->__destruct();
	}


	/////////////////////////////
	// Tests concerning the UID
	/////////////////////////////

	public function testGetUidForNoUidReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getUid()
		);
	}

	public function testGetUidForSetUidReturnsTheSetUid() {
		$this->fixture->setUid(42);

		$this->assertEquals(
			42,
			$this->fixture->getUid()
		);
	}

	public function testGetUidForSetUidViaSetDataReturnsTheSetUid() {
		$this->fixture->setData(array('uid' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getUid()
		);
	}

	public function testHasUidForNoUidReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasUid()
		);
	}

	public function testHasUidForPositiveUidReturnsTrue() {
		$this->fixture->setUid(42);

		$this->assertTrue(
			$this->fixture->hasUid()
		);
	}

	public function testSetUidTwoTimesThrowsAnException() {
		$this->setExpectedException(
			'Exception', 'The UID of a model cannot be set a second time.'
		);
		$this->fixture->setUid(42);
		$this->fixture->setUid(42);
	}

	public function testSetUidForAModelWithAUidSetViaSetDataThrowsException() {
		$this->setExpectedException(
			'Exception', 'The UID of a model cannot be set a second time.'
		);

		$this->fixture->setData(array('uid' => 1));
		$this->fixture->setUid(42);
	}

	public function testSetUidForAModelWithoutUidDoesNotFail() {
		$this->fixture->setData(array());
		$this->fixture->setUid(42);
	}


	//////////////////////////////////////
	// Tests concerning the model states
	//////////////////////////////////////

	public function testInitiallyHasVirginState() {
		$this->assertTrue(
			$this->fixture->isVirgin()
		);
	}

	public function testAfterSettingDataWithoutUidHasLoadedState() {
		$this->fixture->setData(array());

		$this->assertTrue(
			$this->fixture->isLoaded()
		);
	}

	public function testAfterSettingDataWithUidHasLoadedState() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertTrue(
			$this->fixture->isLoaded()
		);
	}

	public function testAfterSettingDataWithUidNotHasDeadState() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertFalse(
			$this->fixture->isDead()
		);
	}

	public function testAfterSettingUidWithoutDataHasGhostState() {
		$this->fixture->setUid(1);

		$this->assertTrue(
			$this->fixture->isGhost()
		);
	}

	public function testAfterMarkAsDeadHasDeadState() {
		$this->fixture->markAsDead();

		$this->assertTrue(
			$this->fixture->isDead()
		);
	}

	public function testGetOnAModelWithoutLoadCallbackThrowsException() {
		$this->setExpectedException(
			'Exception',
			'Ghosts need a load callback function before their data can be ' .
				'accessed.'
		);

		$this->fixture->setUid(1);
		$this->fixture->getTitle();
	}

	public function testSetOnAModelInStatusGhostWithoutLoadCallbackThrowsException() {
		$this->setExpectedException(
			'Exception',
			'Ghosts need a load callback function before their data can be ' .
				'accessed.'
		);

		$this->fixture->setUid(1);
		$this->fixture->setTitle('foo');
	}

	public function testGetOnDeadModelThrowsException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'This tx_oelib_tests_fixtures_TestingModel with the UID 0 is ' .
				'dead and cannot have any data.'
		);

		$this->fixture->markAsDead();
		$this->fixture->getTitle();
	}

	public function testGetUidOnDeadModelDoesNotFail() {
		$this->fixture->markAsDead();
		$this->fixture->getUid();
	}

	public function testIsHiddenOnDeadModelThrowsException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'This tx_oelib_tests_fixtures_TestingModel with the UID 0 is ' .
				'dead and cannot have any data.'
		);

		$this->fixture->markAsDead();
		$this->fixture->isHidden();
	}


	//////////////////////
	// Tests for isDirty
	//////////////////////

	public function testIsDirtyAfterMarkAsDirtyReturnsTrue() {
		$this->fixture->markAsDirty();

		$this->assertTrue(
			$this->fixture->isDirty()
		);
	}

	public function testIsDirtyAfterMarkAsCleanReturnsFalse() {
		$this->fixture->markAsClean();

		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	public function testIsDirtyAfterSetReturnsTrue() {
		$this->fixture->setTitle('foo');

		$this->assertTrue(
			$this->fixture->isDirty()
		);
	}

	public function testIsDirtyAfterSetDataReturnsFalse() {
		$this->fixture->setData(array('title' => 'foo'));

		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	public function testIsDirtyOnModelInVirginStateReturnsFalse() {
		$this->assertTrue(
			$this->fixture->isVirgin()
		);
		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	public function testIsDirtyOnModelInGhostStateReturnsFalse() {
		$this->fixture->setUid(1);

		$this->assertTrue(
			$this->fixture->isGhost()
		);
		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	public function testIsDirtyOnInitiallyDeadModelReturnsFalse() {
		$this->fixture->markAsDead();

		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	public function testIsDirtyOnModelWhichTurnedIntoDeadStateReturnsFalse() {
		$this->fixture->setTitle('foo');

		$this->assertTrue(
			$this->fixture->isDirty()
		);

		$this->fixture->markAsDead();
		$this->assertTrue(
			$this->fixture->isDead()
		);
		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}


	//////////////////////////////////////////
	// Tests concerning the deleted property
	//////////////////////////////////////////

	public function testSetToDeletedOnVirginModelMarksModelAsDead() {
		$this->assertTrue(
			$this->fixture->isVirgin()
		);

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDead()
		);
	}

	public function testSetToDeletedOnGhostModelMarksModelAsDead() {
		$this->fixture->setUid(1);

		$this->assertTrue(
			$this->fixture->isGhost()
		);

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDead()
		);
	}

	public function testSetToDeletedOnLoadedModelMarksModelAsDirty() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertTrue(
			$this->fixture->isLoaded()
		);

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDirty()
		);
	}

	public function testSetToDeletedOnLoadedModelMarksModelAsDeleted() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertTrue(
			$this->fixture->isLoaded()
		);

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDeleted()
		);
	}

	public function testSettingDeletedByUsingSetThrowsAnException() {
		$this->setExpectedException(
			Exception,
			'$key must not be "deleted". Please use setToDeleted() instead.'
		);

		$this->fixture->setDeletedPropertyUsingSet();
	}

	public function testIsDeletedForModelSetToDeletedReturnsTrue() {
		$this->fixture->setData(array('uid' => 1));

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDeleted()
		);
	}

	public function testIsDeletedForNonDeletedModelReturnsFalse() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertFalse(
			$this->fixture->isDeleted()
		);
	}


	//////////////////////////////////////
	// Tests concerning read-only models
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function isReadOnlyOnReadWriteModelReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isReadOnly()
		);
	}

	/**
	 * @test
	 */
	public function isReadOnlyOnReadOnlyModelReturnsTrue() {
		$model = new tx_oelib_tests_fixtures_ReadOnlyModel();

		$this->assertTrue(
			$model->isReadOnly()
		);

		$model->__destruct();
	}

	/**
	 * @test
	 */
	public function setDataOnReadOnlyModelDoesNotFail() {
		$model = new tx_oelib_tests_fixtures_ReadOnlyModel();
		$model->setData(array());

		$model->__destruct();
	}

	/**
	 * @test
	 */
	public function setOnReadOnlyModelThrowsException() {
		$this->setExpectedException(
			'Exception', 'set() must not be called on a read-only model.'
		);

		$model = new tx_oelib_tests_fixtures_ReadOnlyModel();
		$model->setTitle('foo');
	}


	/////////////////////////////
	// Tests concerning getData
	/////////////////////////////

	public function testGetDataForNoDataSetReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->getData()
		);
	}

	public function testGetDataReturnsArrayWithTheSetData() {
		$data = array('foo' => 'bar');
		$this->fixture->setData($data);

		$this->assertEquals(
			$data,
			$this->fixture->getData()
		);
	}

	public function testGetDataReturnsArrayWithoutKeyUid() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertEquals(
			array(),
			$this->fixture->getData()
		);
	}


	/////////////////////////////////////////////////////
	// Test concerning setTimestamp and setCreationDate
	/////////////////////////////////////////////////////

	public function testSetTimestampForLoadedModelSetsTheTimestamp() {
		$this->fixture->setData(array());
		$this->fixture->setTimestamp();

		$this->assertEquals(
			$GLOBALS['SIM_EXEC_TIME'],
			$this->fixture->getAsInteger('tstamp')
		);
	}

	public function testsetCreationDateForLoadedModelWithUidThrowsException() {
		$this->setExpectedException(
			'Exception',
			'Only new objects (without UID) may receive "crdate".'
		);

		$this->fixture->setData(array('uid' => 1));
		$this->fixture->setCreationDate();
	}

	public function testsetCreationDateForLoadedModelWithoutUidSetsCrdate() {
		$this->fixture->setData(array());
		$this->fixture->setCreationDate();

		$this->assertEquals(
			$GLOBALS['SIM_EXEC_TIME'],
			$this->fixture->getAsInteger('crdate')
		);
	}

}
?>