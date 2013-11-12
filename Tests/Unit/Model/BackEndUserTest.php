<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Saskia Metzler <saskia@merlin.owl.de>
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
 * @subpackage oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Model_BackEndUserTest extends Tx_Phpunit_TestCase {
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

	/**
	 * @test
	 */
	public function getUserNameForEmptyUserNameReturnsEmptyString() {
		$this->fixture->setData(array('username' => ''));

		$this->assertSame(
			'',
			$this->fixture->getUserName()
		);
	}

	/**
	 * @test
	 */
	public function getUserNameForNonEmptyUserNameReturnsUserName() {
		$this->fixture->setData(array('username' => 'johndoe'));

		$this->assertSame(
			'johndoe',
			$this->fixture->getUserName()
		);
	}


	//////////////////////////////////////
	// Tests concerning getting the name
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getNameForNonEmptyNameReturnsName() {
		$this->fixture->setData(array('realName' => 'John Doe'));

		$this->assertSame(
			'John Doe',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyNameReturnsEmptyString() {
		$this->fixture->setData(array('realName' => ''));

		$this->assertSame(
			'',
			$this->fixture->getName()
		);
	}


	//////////////////////////////////////////////////////
	// Tests concerning setting and getting the language
	//////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getLanguageForNonEmptyLanguageReturnsLanguageKey() {
		$this->fixture->setData(array('lang' => 'de'));

		$this->assertSame(
			'de',
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageForEmptyLanguageKeyReturnsDefault() {
		$this->fixture->setData(array('lang' => ''));

		$this->assertSame(
			'default',
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageForLanguageSetInUserConfigurationReturnsThisLanguage() {
		$this->fixture->setData(array('uc' => serialize(array('lang' => 'de'))));

		$this->assertSame(
			'de',
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageForSetDefaultLanguageAndLanguageSetInUserConfigurationReturnsLanguageFromConfiguration() {
		$this->fixture->setData(array('uc' => serialize(array('lang' => 'fr'))));
		$this->fixture->setDefaultLanguage('de');

		$this->assertSame(
			'fr',
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultLanguageSetsLanguage() {
		$this->fixture->setDefaultLanguage('de');

		$this->assertSame(
			'de',
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setDefaultLanguageWithDefaultSetsLanguage() {
		$this->fixture->setDefaultLanguage('default');

		$this->assertSame(
			'default',
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setDefaultLanguageWithEmptyKeyThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$language must not be empty.'
		);

		$this->fixture->setDefaultLanguage('');
	}

	/**
	 * @test
	 */
	public function hasLanguageWithoutLanguageReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithDefaultLanguageSetReturnsFalse() {
		$this->fixture->setData(array());
		$this->fixture->setDefaultLanguage('default');

		$this->assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithNonEmptyLanguageReturnsTrue() {
		$this->fixture->setData(array('lang' => 'de'));

		$this->assertTrue(
			$this->fixture->hasLanguage()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning getting the e-mail address
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getEmailAddressForEmptyEmailReturnsEmptyString() {
		$this->fixture->setData(array('email' => ''));

		$this->assertSame(
			'',
			$this->fixture->getEmailAddress()
		);
	}

	/**
	 * @test
	 */
	public function getEmailAddressForNonEmptyEmailReturnsEmail() {
		$this->fixture->setData(array('email' => 'john@doe.com'));

		$this->assertSame(
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

		$this->assertSame(
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

		$this->assertSame(
			2,
			$this->fixture->getAllGroups()->count()
		);
	}
}
?>