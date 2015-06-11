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
 * @subpackage oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Model_BackEndUserTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Model_BackEndUser
	 */
	private $subject;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Model_BackEndUser();
	}

	///////////////////////////////////////////
	// Tests concerning getting the user name
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getUserNameForEmptyUserNameReturnsEmptyString() {
		$this->subject->setData(array('username' => ''));

		self::assertSame(
			'',
			$this->subject->getUserName()
		);
	}

	/**
	 * @test
	 */
	public function getUserNameForNonEmptyUserNameReturnsUserName() {
		$this->subject->setData(array('username' => 'johndoe'));

		self::assertSame(
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

		self::assertSame(
			'John Doe',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyNameReturnsEmptyString() {
		$this->subject->setData(array('realName' => ''));

		self::assertSame(
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

		self::assertSame(
			'de',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageForEmptyLanguageKeyReturnsDefault() {
		$this->subject->setData(array('lang' => ''));

		self::assertSame(
			'default',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageForLanguageSetInUserConfigurationReturnsThisLanguage() {
		$this->subject->setData(array('uc' => serialize(array('lang' => 'de'))));

		self::assertSame(
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

		self::assertSame(
			'fr',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultLanguageSetsLanguage() {
		$this->subject->setDefaultLanguage('de');

		self::assertSame(
			'de',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setDefaultLanguageWithDefaultSetsLanguage() {
		$this->subject->setDefaultLanguage('default');

		self::assertSame(
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

		self::assertFalse(
			$this->subject->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithDefaultLanguageSetReturnsFalse() {
		$this->subject->setData(array());
		$this->subject->setDefaultLanguage('default');

		self::assertFalse(
			$this->subject->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithNonEmptyLanguageReturnsTrue() {
		$this->subject->setData(array('lang' => 'de'));

		self::assertTrue(
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

		self::assertSame(
			'',
			$this->subject->getEmailAddress()
		);
	}

	/**
	 * @test
	 */
	public function getEmailAddressForNonEmptyEmailReturnsEmail() {
		$this->subject->setData(array('email' => 'john@doe.com'));

		self::assertSame(
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
		$groups = new Tx_Oelib_List();

		$this->subject->setData(array('usergroup' => $groups));

		self::assertSame(
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
		$this->subject->setData(array('usergroup' => new Tx_Oelib_List()));

		self::assertTrue(
			$this->subject->getAllGroups() instanceof Tx_Oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForNoGroupsReturnsEmptyList() {
		$this->subject->setData(array('usergroup' => new Tx_Oelib_List()));

		self::assertTrue(
			$this->subject->getAllGroups()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForOneGroupReturnsListWithThatGroup() {
		$group = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$groups = new Tx_Oelib_List();
		$groups->add($group);
		$this->subject->setData(array('usergroup' => $groups));

		self::assertSame(
			$group,
			$this->subject->getAllGroups()->first()
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForTwoGroupsReturnsBothGroups() {
		$group1 = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$group2 = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$groups = new Tx_Oelib_List();
		$groups->add($group1);
		$groups->add($group2);
		$this->subject->setData(array('usergroup' => $groups));

		self::assertTrue(
			$this->subject->getAllGroups()->hasUid($group1->getUid())
		);
		self::assertTrue(
			$this->subject->getAllGroups()->hasUid($group2->getUid())
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForGroupWithSubgroupReturnsBothGroups() {
		$subgroup = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$group = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(
				array('subgroup' => $subgroup->getUid())
			);
		$groups = new Tx_Oelib_List();
		$groups->add($group);
		$this->subject->setData(array('usergroup' => $groups));

		self::assertTrue(
			$this->subject->getAllGroups()->hasUid($group->getUid())
		);
		self::assertTrue(
			$this->subject->getAllGroups()->hasUid($subgroup->getUid())
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForGroupWithSubsubgroupContainsSubsubgroup() {
		$subsubgroup = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(array());
		$subgroup = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(
				array('subgroup' => $subsubgroup->getUid())
			);
		$group = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getLoadedTestingModel(
				array('subgroup' => $subgroup->getUid())
			);
		$groups = new Tx_Oelib_List();
		$groups->add($group);
		$this->subject->setData(array('usergroup' => $groups));

		self::assertTrue(
			$this->subject->getAllGroups()->hasUid($subsubgroup->getUid())
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForGroupWithSubgroupSelfReferenceReturnsOnlyOneGroup() {
		$group = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getNewGhost();
		$subgroups = new Tx_Oelib_List();
		$subgroups->add($group);
		$group->setData(array('subgroup' => $subgroups));

		$groups = new Tx_Oelib_List();
		$groups->add($group);
		$this->subject->setData(array('usergroup' => $groups));

		self::assertSame(
			1,
			$this->subject->getAllGroups()->count()
		);
	}

	/**
	 * @test
	 */
	public function getAllGroupsForGroupWithSubgroupCycleReturnsBothGroups() {
		$group1 = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getNewGhost();
		$group2 = Tx_Oelib_MapperRegistry::
			get('tx_oelib_Mapper_BackEndUserGroup')->getNewGhost();

		$subgroups1 = new Tx_Oelib_List();
		$subgroups1->add($group2);
		$group1->setData(array('subgroup' => $subgroups1));

		$subgroups2 = new Tx_Oelib_List();
		$subgroups2->add($group1);
		$group2->setData(array('subgroup' => $subgroups2));

		$groups = new Tx_Oelib_List();
		$groups->add($group1);
		$this->subject->setData(array('usergroup' => $groups));

		self::assertSame(
			2,
			$this->subject->getAllGroups()->count()
		);
	}
}