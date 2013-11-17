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
	private $subject;

	public function setUp() {
		$this->subject = new tx_oelib_Model_BackEndUser();
	}

	public function tearDown() {
		$this->subject->__destruct();
		unset($this->subject);
	}


	///////////////////////////////////////////
	// Tests concerning getting the user name
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getUserNameForEmptyUserNameReturnsEmptyString() {
		$this->subject->setData(array('username' => ''));

		$this->assertSame(
			'',
			$this->subject->getUserName()
		);
	}

	/**
	 * @test
	 */
	public function getUserNameForNonEmptyUserNameReturnsUserName() {
		$this->subject->setData(array('username' => 'johndoe'));

		$this->assertSame(
			'johndoe',
			$this->subject->getUserName()
		);
	}


	//////////////////////////////////////
	// Tests concerning getting the name
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getNameForNonEmptyNameReturnsName() {
		$this->subject->setData(array('realName' => 'John Doe'));

		$this->assertSame(
			'John Doe',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyNameReturnsEmptyString() {
		$this->subject->setData(array('realName' => ''));

		$this->assertSame(
			'',
			$this->subject->getName()
		);
	}


	//////////////////////////////////////////////////////
	// Tests concerning setting and getting the language
	//////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getLanguageForNonEmptyLanguageReturnsLanguageKey() {
		$this->subject->setData(array('lang' => 'de'));

		$this->assertSame(
			'de',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageForEmptyLanguageKeyReturnsDefault() {
		$this->subject->setData(array('lang' => ''));

		$this->assertSame(
			'default',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageForLanguageSetInUserConfigurationReturnsThisLanguage() {
		$this->subject->setData(array('uc' => serialize(array('lang' => 'de'))));

		$this->assertSame(
			'de',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageForSetDefaultLanguageAndLanguageSetInUserConfigurationReturnsLanguageFromConfiguration() {
		$this->subject->setData(array('uc' => serialize(array('lang' => 'fr'))));
		$this->subject->setDefaultLanguage('de');

		$this->assertSame(
			'fr',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultLanguageSetsLanguage() {
		$this->subject->setDefaultLanguage('de');

		$this->assertSame(
			'de',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setDefaultLanguageWithDefaultSetsLanguage() {
		$this->subject->setDefaultLanguage('default');

		$this->assertSame(
			'default',
			$this->subject->getLanguage()
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

		$this->subject->setDefaultLanguage('');
	}

	/**
	 * @test
	 */
	public function hasLanguageWithoutLanguageReturnsFalse() {
		$this->subject->setData(array());

		$this->assertFalse(
			$this->subject->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithDefaultLanguageSetReturnsFalse() {
		$this->subject->setData(array());
		$this->subject->setDefaultLanguage('default');

		$this->assertFalse(
			$this->subject->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithNonEmptyLanguageReturnsTrue() {
		$this->subject->setData(array('lang' => 'de'));

		$this->assertTrue(
			$this->subject->hasLanguage()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning getting the e-mail address
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getEmailAddressForEmptyEmailReturnsEmptyString() {
		$this->subject->setData(array('email' => ''));

		$this->assertSame(
			'',
			$this->subject->getEmailAddress()
		);
	}

	/**
	 * @test
	 */
	public function getEmailAddressForNonEmptyEmailReturnsEmail() {
		$this->subject->setData(array('email' => 'john@doe.com'));

		$this->assertSame(
			'john@doe.com',
			$this->subject->getEmailAddress()
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

		$this->subject->setData(array('usergroup' => $groups));

		$this->assertSame(
			$groups,
			$this->subject->getGroups()
		);
	}


	//////////////////////////////////
	// Tests concerning getAllGroups
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getAllGroupsForNoGroupsReturnsList() {
		$this->subject->setData(array('usergroup' => new tx_oelib_List()));

		$this->assertTrue(
			$this->subject->getAllGroups() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForNoGroupsReturnsEmptyList() {
		$this->subject->setData(array('usergroup' => new tx_oelib_List()));

		$this->assertTrue(
			$this->subject->getAllGroups()->isEmpty()
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
		$this->subject->setData(array('usergroup' => $groups));

		$this->assertSame(
			$group,
			$this->subject->getAllGroups()->first()
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
		$this->subject->setData(array('usergroup' => $groups));

		$this->assertTrue(
			$this->subject->getAllGroups()->hasUid($group1->getUid())
		);
		$this->assertTrue(
			$this->subject->getAllGroups()->hasUid($group2->getUid())
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
		$this->subject->setData(array('usergroup' => $groups));

		$this->assertTrue(
			$this->subject->getAllGroups()->hasUid($group->getUid())
		);
		$this->assertTrue(
			$this->subject->getAllGroups()->hasUid($subgroup->getUid())
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
		$this->subject->setData(array('usergroup' => $groups));

		$this->assertTrue(
			$this->subject->getAllGroups()->hasUid($subsubgroup->getUid())
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
		$this->subject->setData(array('usergroup' => $groups));

		$this->assertSame(
			1,
			$this->subject->getAllGroups()->count()
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
		$this->subject->setData(array('usergroup' => $groups));

		$this->assertSame(
			2,
			$this->subject->getAllGroups()->count()
		);
	}
}
?>