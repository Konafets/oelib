<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_ModelTest extends tx_phpunit_testcase {
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

	/**
	 * Loading function stub.
	 *
	 * @param tx_oelib_Model $model
	 *
	 * @return void
	 */
	public function load(tx_oelib_Model $model) {
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

	/**
	 * @test
	 */
	public function getWithNoDataThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Please call setData() directly after instantiation first.'
		);

		$this->fixture->getTitle();
	}

	/**
	 * @test
	 */
	public function setDataWithEmptyArrayIsAllowed() {
		$this->fixture->setData(array());
	}

	/**
	 * @test
	 */
	public function getAfterSetReturnsTheSetValue() {
		$this->fixture->setTitle('bar');

		$this->assertSame(
			'bar',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getAfterSetDataReturnsTheSetValue() {
		$this->fixture->setData(
			array('title' => 'bar')
		);

		$this->assertSame(
			'bar',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function setDataCalledTwoTimesThrowsAnException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'setData must only be called once per model instance.'
		);

		$this->fixture->setData(
			array('title' => 'bar')
		);
		$this->fixture->setData(
			array('title' => 'bar')
		);
	}

	/**
	 * @test
	 */
	public function isHiddenForLoadedHiddenObjectReturnsTrue() {
		$this->fixture->setData(
			array('hidden' => 1)
		);

		$this->assertTrue(
			$this->fixture->isHidden()
		);
	}

	/**
	 * @test
	 */
	public function isHiddenForLoadedNonHiddenObjectReturnsFalse() {
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

	/**
	 * @test
	 */
	public function existsKeyForInexistentKeyReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->existsKey('foo')
		);
	}

	/**
	 * @test
	 */
	public function existsKeyForExistingKeyWithNonEmptyDataReturnsTrue() {
		$this->fixture->setData(
			array('foo' => 'bar')
		);

		$this->assertTrue(
			$this->fixture->existsKey('foo')
		);
	}

	/**
	 * @test
	 */
	public function existsKeyForExistingKeyWithEmptyStringDataReturnsTrue() {
		$this->fixture->setData(
			array('foo' => '')
		);

		$this->assertTrue(
			$this->fixture->existsKey('foo')
		);
	}

	/**
	 * @test
	 */
	public function existsKeyForExistingKeyWithZeroDataReturnsTrue() {
		$this->fixture->setData(
			array('foo' => 0)
		);

		$this->assertTrue(
			$this->fixture->existsKey('foo')
		);
	}

	/**
	 * @test
	 */
	public function existsKeyForExistingKeyWithNullDataReturnsTrue() {
		$this->fixture->setData(
			array('foo' => NULL)
		);

		$this->assertTrue(
			$this->fixture->existsKey('foo')
		);
	}


	////////////////////////////////
	// Tests concerning getAsModel
	////////////////////////////////

	/**
	 * @test
	 */
	public function getAsModelWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->getAsModel('');
	}

	/**
	 * @test
	 */
	public function getAsModelWithInexistentKeyReturnsNull() {
		$this->fixture->setData(array());

		$this->assertNull(
			$this->fixture->getAsModel('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsModelWithKeyForStringDataThrowsException() {
		$this->setExpectedException(
			'UnexpectedValueException',
			'The data item for the key "foo" is no model instance.'
		);

		$this->fixture->setData(array('foo' => 'bar'));

		$this->fixture->getAsModel('foo');
	}

	/**
	 * @test
	 */
	public function getAsModelReturnsNullSetViaSetData() {
		$this->fixture->setData(
			array('foo' => NULL)
		);

		$this->assertNull(
			$this->fixture->getAsModel('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsModelReturnsModelSetViaSetData() {
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

	/**
	 * @test
	 */
	public function getAsListWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be empty.'
		);

		$this->fixture->getAsList('');
	}

	/**
	 * @test
	 */
	public function getAsListWithInexistentKeyThrowsException() {
		$this->setExpectedException(
			'UnexpectedValueException',
			'The data item for the key "foo" is no list instance.'
		);

		$this->fixture->setData(array());

		$this->assertNull(
			$this->fixture->getAsList('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsListWithKeyForStringDataThrowsException() {
		$this->setExpectedException(
			'UnexpectedValueException',
			'The data item for the key "foo" is no list instance.'
		);

		$this->fixture->setData(array('foo' => 'bar'));

		$this->fixture->getAsList('foo');
	}

	/**
	 * @test
	 */
	public function getAsListReturnsListSetViaSetData() {
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

	/**
	 * @test
	 */
	public function getUidForNoUidReturnsZero() {
		$this->fixture->setData(array());

		$this->assertSame(
			0,
			$this->fixture->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getUidForSetUidReturnsTheSetUid() {
		$this->fixture->setUid(42);

		$this->assertSame(
			42,
			$this->fixture->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getUidForSetUidViaSetDataReturnsTheSetUid() {
		$this->fixture->setData(array('uid' => 42));

		$this->assertSame(
			42,
			$this->fixture->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getUidForSetStringUidViaSetDataReturnsTheSetIntegerUid() {
		$this->fixture->setData(array('uid' => '42'));

		$this->assertSame(
			42,
			$this->fixture->getUid()
		);
	}

	/**
	 * @test
	 */
	public function hasUidForNoUidReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasUid()
		);
	}

	/**
	 * @test
	 */
	public function hasUidForPositiveUidReturnsTrue() {
		$this->fixture->setUid(42);

		$this->assertTrue(
			$this->fixture->hasUid()
		);
	}

	/**
	 * @test
	 */
	public function setUidTwoTimesThrowsAnException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The UID of a model cannot be set a second time.'
		);
		$this->fixture->setUid(42);
		$this->fixture->setUid(42);
	}

	/**
	 * @test
	 */
	public function setUidForAModelWithAUidSetViaSetDataThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The UID of a model cannot be set a second time.'
		);

		$this->fixture->setData(array('uid' => 1));
		$this->fixture->setUid(42);
	}

	/**
	 * @test
	 */
	public function setUidForAModelWithoutUidDoesNotFail() {
		$this->fixture->setData(array());
		$this->fixture->setUid(42);
	}


	//////////////////////////////////////
	// Tests concerning the model states
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function initiallyHasVirginState() {
		$this->assertTrue(
			$this->fixture->isVirgin()
		);
	}

	/**
	 * @test
	 */
	public function afterSettingDataWithoutUidHasLoadedState() {
		$this->fixture->setData(array());

		$this->assertTrue(
			$this->fixture->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function afterSettingDataWithUidHasLoadedState() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertTrue(
			$this->fixture->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function afterSettingDataWithUidNotHasDeadState() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertFalse(
			$this->fixture->isDead()
		);
	}

	/**
	 * @test
	 */
	public function afterSettingUidWithoutDataHasGhostState() {
		$this->fixture->setUid(1);

		$this->assertTrue(
			$this->fixture->isGhost()
		);
	}

	/**
	 * @test
	 */
	public function afterMarkAsDeadHasDeadState() {
		$this->fixture->markAsDead();

		$this->assertTrue(
			$this->fixture->isDead()
		);
	}

	/**
	 * @test
	 */
	public function getOnAModelWithoutLoadCallbackThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Ghosts need a load callback function before their data can be accessed.'
		);

		$this->fixture->setUid(1);
		$this->fixture->getTitle();
	}

	/**
	 * @test
	 */
	public function setOnAModelInStatusGhostWithoutLoadCallbackThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Ghosts need a load callback function before their data can be accessed.'
		);

		$this->fixture->setUid(1);
		$this->fixture->setTitle('foo');
	}

	/**
	 * @test
	 */
	public function getOnDeadModelThrowsException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'The tx_oelib_tests_fixtures_TestingModel with the UID 0' .
				' either has been deleted (or has never existed), but still is accessed.'
		);

		$this->fixture->markAsDead();
		$this->fixture->getTitle();
	}

	/**
	 * @test
	 */
	public function getUidOnDeadModelDoesNotFail() {
		$this->fixture->markAsDead();
		$this->fixture->getUid();
	}

	/**
	 * @test
	 */
	public function isHiddenOnDeadModelThrowsException() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'The tx_oelib_tests_fixtures_TestingModel with the UID 0' .
				' either has been deleted (or has never existed), but still is accessed.'
		);

		$this->fixture->markAsDead();
		$this->fixture->isHidden();
	}


	//////////////////////
	// Tests for isEmpty
	//////////////////////

	/**
	 * @test
	 */
	public function isEmptyForLoadedEmptyObjectReturnsTrue() {
		$this->fixture->setData(array());

		$this->assertTrue(
			$this->fixture->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function isEmptyForLoadedNotEmptyObjectReturnsFalse() {
		$this->fixture->setData(
			array('foo' => 'bar')
		);

		$this->assertFalse(
			$this->fixture->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function isEmptyForGhostLoadsModel() {
		$this->fixture->setData(array());
		$this->fixture->setUid(1);
		$this->fixture->setLoadCallback(array($this, 'load'));
		$this->fixture->isEmpty();

		$this->assertTrue(
			$this->fixture->isLoaded()
		);
	}

	/**
	 * @test
	 */
	public function isEmptyForGhostWithLoadedDataReturnsFalse() {
		$this->fixture->setData(
			array('foo' => 'bar')
		);
		$this->fixture->setUid(1);
		$this->fixture->setLoadCallback(array($this, 'load'));

		$this->assertFalse(
			$this->fixture->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function isEmptyForGhostWithoutLoadedDataReturnsTrue() {
		$this->fixture->setUid(1);
		$this->fixture->setLoadCallback(array($this, 'load'));

		$this->assertTrue(
			$this->fixture->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function isEmptyForVirginStateReturnsTrue() {
		$this->assertTrue(
			$this->fixture->isEmpty()
		);
	}


	//////////////////////
	// Tests for isDirty
	//////////////////////

	/**
	 * @test
	 */
	public function isDirtyAfterMarkAsDirtyReturnsTrue() {
		$this->fixture->markAsDirty();

		$this->assertTrue(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyAfterMarkAsCleanReturnsFalse() {
		$this->fixture->markAsClean();

		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyAfterSetReturnsTrue() {
		$this->fixture->setTitle('foo');

		$this->assertTrue(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyAfterSetDataWithUidAndOtherDataReturnsFalse() {
		$this->fixture->setData(array('uid' => 42, 'title' => 'foo'));

		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyAfterSetDataOnlyWithUidReturnsFalse() {
		$this->fixture->setData(array('uid' => 42, 'title' => 'foo'));

		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyAfterSetDataForAModelAlreadyHavingAUidReturnsFalse() {
		$this->fixture->setUid(42);
		$this->fixture->setData(array('title' => 'foo'));

		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyAfterSetDataWithoutUidReturnsTrue() {
		$this->fixture->setData(array('title' => 'foo'));

		$this->assertTrue(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyOnModelInVirginStateReturnsFalse() {
		$this->assertTrue(
			$this->fixture->isVirgin()
		);
		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyOnModelInGhostStateReturnsFalse() {
		$this->fixture->setUid(1);

		$this->assertTrue(
			$this->fixture->isGhost()
		);
		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyOnInitiallyDeadModelReturnsFalse() {
		$this->fixture->markAsDead();

		$this->assertFalse(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function isDirtyOnModelWhichTurnedIntoDeadStateReturnsFalse() {
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

	/**
	 * @test
	 */
	public function setToDeletedOnVirginModelMarksModelAsDead() {
		$this->assertTrue(
			$this->fixture->isVirgin()
		);

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDead()
		);
	}

	/**
	 * @test
	 */
	public function setToDeletedOnGhostModelMarksModelAsDead() {
		$this->fixture->setUid(1);

		$this->assertTrue(
			$this->fixture->isGhost()
		);

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDead()
		);
	}

	/**
	 * @test
	 */
	public function setToDeletedOnLoadedModelMarksModelAsDirty() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertTrue(
			$this->fixture->isLoaded()
		);

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDirty()
		);
	}

	/**
	 * @test
	 */
	public function setToDeletedOnLoadedModelMarksModelAsDeleted() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertTrue(
			$this->fixture->isLoaded()
		);

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDeleted()
		);
	}

	/**
	 * @test
	 */
	public function settingDeletedByUsingSetThrowsAnException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$key must not be "deleted". Please use setToDeleted() instead.'
		);

		$this->fixture->setDeletedPropertyUsingSet();
	}

	/**
	 * @test
	 */
	public function isDeletedForModelSetToDeletedReturnsTrue() {
		$this->fixture->setData(array('uid' => 1));

		$this->fixture->setToDeleted();

		$this->assertTrue(
			$this->fixture->isDeleted()
		);
	}

	/**
	 * @test
	 */
	public function isDeletedForNonDeletedModelReturnsFalse() {
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
			'BadMethodCallException',
			'set() must not be called on a read-only model.'
		);

		$model = new tx_oelib_tests_fixtures_ReadOnlyModel();
		$model->setTitle('foo');
	}


	/////////////////////////////
	// Tests concerning getData
	/////////////////////////////

	/**
	 * @test
	 */
	public function getDataForNoDataSetReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getData()
		);
	}

	/**
	 * @test
	 */
	public function getDataReturnsArrayWithTheSetData() {
		$data = array('foo' => 'bar');
		$this->fixture->setData($data);

		$this->assertSame(
			$data,
			$this->fixture->getData()
		);
	}

	/**
	 * @test
	 */
	public function getDataReturnsArrayWithoutKeyUid() {
		$this->fixture->setData(array('uid' => 1));

		$this->assertSame(
			array(),
			$this->fixture->getData()
		);
	}


	/////////////////////////////////////////////////////
	// Test concerning setTimestamp and setCreationDate
	/////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setTimestampForLoadedModelSetsTheTimestamp() {
		$this->fixture->setData(array());
		$this->fixture->setTimestamp();

		$this->assertSame(
			$GLOBALS['SIM_EXEC_TIME'],
			$this->fixture->getAsInteger('tstamp')
		);
	}

	/**
	 * @test
	 */
	public function setCreationDateForLoadedModelWithUidThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'Only new objects (without UID) may receive "crdate".'
		);

		$this->fixture->setData(array('uid' => 1));
		$this->fixture->setCreationDate();
	}

	/**
	 * @test
	 */
	public function setCreationDateForLoadedModelWithoutUidSetsCrdate() {
		$this->fixture->setData(array());
		$this->fixture->setCreationDate();

		$this->assertSame(
			$GLOBALS['SIM_EXEC_TIME'],
			$this->fixture->getAsInteger('crdate')
		);
	}


	////////////////////////////////
	// Tests concerning getPageUid
	////////////////////////////////

	/**
	 * @test
	 */
	public function getPageUidForNoPageUidSetReturnsZero() {
		$this->fixture->setData(array());

		$this->assertSame(
			0,
			$this->fixture->getPageUid()
		);
	}

	/**
	 * @test
	 */
	public function getPageUidReturnsPageUid() {
		$this->fixture->setData(array('pid' => 42));

		$this->assertSame(
			42,
			$this->fixture->getPageUid()
		);
	}

	/**
	 * @test
	 */
	public function setPageUidSetsPageUid() {
		$this->fixture->setPageUid(84);

		$this->assertSame(
			84,
			$this->fixture->getPageUid()
		);
	}

	/**
	 * @test
	 */
	public function setPageUidWithZeroPageUidNotThrowsException() {
		$this->fixture->setPageUid(0);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setPageUidWithNegativePageUidThrowsException() {
		$this->fixture->setPageUid(-1);
	}


	//////////////////////////////////////////////////////////
	// Tests concerning the setting of the "hidden" property
	//////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function markAsHiddenMarksVisibleModelAsHidden() {
		$this->fixture->setData(array('hidden' => FALSE));

		$this->fixture->markAsHidden();

		$this->assertTrue(
			$this->fixture->isHidden()
		);
	}

	/**
	 * @test
	 */
	public function markAsVisibleMarksHiddenModelAsNotHidden() {
		$this->fixture->setData(array('hidden' => TRUE));

		$this->fixture->markAsVisible();

		$this->assertFalse(
			$this->fixture->isHidden()
		);
	}
}
?>