<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Saskia Metzler <saskia@merlin.owl.de>
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
 * Testcase for the tx_oelib_Model_BackEndUser class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Model_BackEndUserTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Model_BackEndUser
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Model_BackEndUser();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	///////////////////////////////////////////
	// Tests concerning getting the user name
	///////////////////////////////////////////

	public function testGetUserNameForEmptyUserNameReturnsEmptyString() {
		$this->fixture->setData(array('username' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getUserName()
		);
	}

	public function testGetUserNameForNonEmptyUserNameReturnsUserName() {
		$this->fixture->setData(array('username' => 'johndoe'));

		$this->assertEquals(
			'johndoe',
			$this->fixture->getUserName()
		);
	}


	//////////////////////////////////////
	// Tests concerning getting the name
	//////////////////////////////////////

	public function testGetNameForNonEmptyNameReturnsName() {
		$this->fixture->setData(array('realName' => 'John Doe'));

		$this->assertEquals(
			'John Doe',
			$this->fixture->getName()
		);
	}

	public function testGetNameForEmptyNameReturnsEmptyString() {
		$this->fixture->setData(array('realName' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getName()
		);
	}


	//////////////////////////////////////////////////////
	// Tests concerning setting and getting the language
	//////////////////////////////////////////////////////

	public function testGetLanguageForNonEmptyLanguageReturnsLanguageKey() {
		$this->fixture->setData(array('lang' => 'de'));

		$this->assertEquals(
			'de',
			$this->fixture->getLanguage()
		);
	}

	public function testGetLanguageForEmptyLanguageKeyReturnsDefault() {
		$this->fixture->setData(array('lang' => ''));

		$this->assertEquals(
			'default',
			$this->fixture->getLanguage()
		);
	}

	public function testGetLanguageForLanguageSetInUserConfigurationReturnsThisLanguage() {
		$this->fixture->setData(array('uc' => serialize(array('lang' => 'de'))));

		$this->assertEquals(
			'de',
			$this->fixture->getLanguage()
		);
	}

	public function testGetLanguageForSetDefaultLanguageAndLanguageSetInUserConfigurationReturnsLanguageFromConfiguration() {
		$this->fixture->setData(array('uc' => serialize(array('lang' => 'fr'))));
		$this->fixture->setDefaultLanguage('de');

		$this->assertEquals(
			'fr',
			$this->fixture->getLanguage()
		);
	}

	public function testSetDefaultLanguageSetsLanguage() {
		$this->fixture->setDefaultLanguage('de');

		$this->assertEquals(
			'de',
			$this->fixture->getLanguage()
		);
	}

	public function testSetDefaultLanguageWithDefaultSetsLanguage() {
		$this->fixture->setDefaultLanguage('default');

		$this->assertEquals(
			'default',
			$this->fixture->getLanguage()
		);
	}

	public function testSetDefaultLanguageWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'Exception', '$language must not be empty.'
		);

		$this->fixture->setDefaultLanguage('');
	}

	public function testHasLanguageWithoutLanguageReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	public function testHasLanguageWithDefaultLanguageSetReturnsFalse() {
		$this->fixture->setData(array());
		$this->fixture->setDefaultLanguage('default');

		$this->assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	public function testHasLanguageWithNonEmptyLanguageReturnsTrue() {
		$this->fixture->setData(array('lang' => 'de'));

		$this->assertTrue(
			$this->fixture->hasLanguage()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning getting the e-mail address
	////////////////////////////////////////////////

	public function testGetEmailAddressForEmptyEMailReturnsEmptyString() {
		$this->fixture->setData(array('email' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getEmailAddress()
		);
	}

	public function testGetEmailAddressForNonEmptyEMailReturnsEMail() {
		$this->fixture->setData(array('email' => 'john@doe.com'));

		$this->assertEquals(
			'john@doe.com',
			$this->fixture->getEmailAddress()
		);
	}


	///////////////////////////////
	// Tests concerning getGroups
	///////////////////////////////

	/**
	 * @test
	 */
	public function getGroupsReturnsListFromUserGroupField() {
		$groups = new tx_oelib_List();

		$this->fixture->setData(array('usergroup' => $groups));

		$this->assertSame(
			$groups,
			$this->fixture->getGroups()
		);
	}


	//////////////////////////////////
	// Tests concerning getAllGroups
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getAllGroupsForNoGroupsReturnsList() {
		$this->fixture->setData(array('usergroup' => new tx_oelib_List()));

		$this->assertTrue(
			$this->fixture->getAllGroups() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForNoGroupsReturnsEmptyList() {
		$this->fixture->setData(array('usergroup' => new tx_oelib_List()));

		$this->assertTrue(
			$this->fixture->getAllGroups()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForOneGroupReturnsListWithThatGroup() {
		$group = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$groups = new tx_oelib_List();
		$groups->add($group);
		$this->fixture->setData(array('usergroup' => $groups));

		$this->assertSame(
			$group,
			$this->fixture->getAllGroups()->first()
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForTwoGroupsReturnsBothGroups() {
		$group1 = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$group2 = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$groups = new tx_oelib_List();
		$groups->add($group1);
		$groups->add($group2);
		$this->fixture->setData(array('usergroup' => $groups));

		$this->assertTrue(
			$this->fixture->getAllGroups()->hasUid($group1->getUid())
		);
		$this->assertTrue(
			$this->fixture->getAllGroups()->hasUid($group2->getUid())
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForGroupWithSubgroupReturnsBothGroups() {
		$subgroup = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$group = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(
				array('subgroup' => $subgroup->getUid())
			);
		$groups = new tx_oelib_List();
		$groups->add($group);
		$this->fixture->setData(array('usergroup' => $groups));

		$this->assertTrue(
			$this->fixture->getAllGroups()->hasUid($group->getUid())
		);
		$this->assertTrue(
			$this->fixture->getAllGroups()->hasUid($subgroup->getUid())
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForGroupWithSubsubgroupContainsSubsubgroup() {
		$subsubgroup = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$subgroup = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(
				array('subgroup' => $subsubgroup->getUid())
			);
		$group = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(
				array('subgroup' => $subgroup->getUid())
			);
		$groups = new tx_oelib_List();
		$groups->add($group);
		$this->fixture->setData(array('usergroup' => $groups));

		$this->assertTrue(
			$this->fixture->getAllGroups()->hasUid($subsubgroup->getUid())
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForGroupWithSubgroupSelfReferenceReturnsOnlyOneGroup() {
		$group = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getNewGhost();
		$subgroups = new tx_oelib_List();
		$subgroups->add($group);
		$group->setData(array('subgroup' => $subgroups));

		$groups = new tx_oelib_List();
		$groups->add($group);
		$this->fixture->setData(array('usergroup' => $groups));

		$this->assertEquals(
			1,
			$this->fixture->getAllGroups()->count()
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForGroupWithSubgroupCycleReturnsBothGroups() {
		$group1 = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getNewGhost();
		$group2 = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getNewGhost();

		$subgroups1 = new tx_oelib_List();
		$subgroups1->add($group2);
		$group1->setData(array('subgroup' => $subgroups1));

		$subgroups2 = new tx_oelib_List();
		$subgroups2->add($group1);
		$group2->setData(array('subgroup' => $subgroups2));

		$groups = new tx_oelib_List();
		$groups->add($group1);
		$this->fixture->setData(array('usergroup' => $groups));

		$this->assertEquals(
			2,
			$this->fixture->getAllGroups()->count()
		);
	}
}
?>