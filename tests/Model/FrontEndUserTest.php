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
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Model_FrontEndUserTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Model_FrontEndUser
	 */
	private $fixture;

	/**
	 * @var integer a backup of $GLOBALS['EXEC_TIME']
	 */
	private $globalExecTimeBackup;

	public function setUp() {
		$this->fixture = new tx_oelib_Model_FrontEndUser();

		$this->globalExecTimeBackup = $GLOBALS['EXEC_TIME'];
	}

	public function tearDown() {
		$GLOBALS['EXEC_TIME'] = $this->globalExecTimeBackup;

		$this->fixture->__destruct();
		unset($this->fixture);
	}


	///////////////////////////////////
	// Tests concerning the user name
	///////////////////////////////////

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

	/**
	 * @test
	 */
	public function setUserNameSetsUserName() {
		$this->fixture->setUserName('foo_bar');

		$this->assertSame(
			'foo_bar',
			$this->fixture->getUserName()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setUserNameWithEmptyUserNameThrowsException() {
		$this->fixture->setUserName('');
	}


	//////////////////////////////////
	// Tests concerning the password
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getPasswordInitiallyReturnsEmptyString() {
		$this->fixture->setData(array());

		$this->assertSame(
			'',
			$this->fixture->getPassword()
		);
	}

	/**
	 * @test
	 */
	public function getPasswordReturnsPassword() {
		$this->fixture->setData(array('password' => 'kasfdjklsdajk'));

		$this->assertSame(
			'kasfdjklsdajk',
			$this->fixture->getPassword()
		);
	}

	/**
	 * @test
	 */
	public function setPasswordSetsPassword() {
		$this->fixture->setPassword('kljvasgd24vsga354');

		$this->assertSame(
			'kljvasgd24vsga354',
			$this->fixture->getPassword()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setPasswordWithEmptyPasswordThrowsException() {
		$this->fixture->setPassword('');
	}


	//////////////////////////////
	// Tests concerning the name
	//////////////////////////////

	/**
	 * @test
	 */
	public function hasNameForEmptyNameLastNameAndFirstNameReturnsFalse() {
		$this->fixture->setData(array(
			'name' => '',
			'first_name' => '',
			'last_name' => '',
		));

		$this->assertFalse(
			$this->fixture->hasName()
		);
	}

	/**
	 * @test
	 */
	public function hasNameForNonEmptyUserReturnsFalse() {
		$this->fixture->setData(array(
			'username' => 'johndoe',
		));

		$this->assertFalse(
			$this->fixture->hasName()
		);
	}

	/**
	 * @test
	 */
	public function hasNameForNonEmptyNameReturnsTrue() {
		$this->fixture->setData(array(
			'name' => 'John Doe',
			'first_name' => '',
			'last_name' => '',
		));

		$this->assertTrue(
			$this->fixture->hasName()
		);
	}

	/**
	 * @test
	 */
	public function hasNameForNonEmptyFirstNameReturnsTrue() {
		$this->fixture->setData(array(
			'name' => '',
			'first_name' => 'John',
			'last_name' => '',
		));

		$this->assertTrue(
			$this->fixture->hasName()
		);
	}

	/**
	 * @test
	 */
	public function hasNameForNonEmptyLastNameReturnsTrue() {
		$this->fixture->setData(array(
			'name' => '',
			'first_name' => '',
			'last_name' => 'Doe',
		));

		$this->assertTrue(
			$this->fixture->hasName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForNonEmptyNameReturnsName() {
		$this->fixture->setData(array(
			'name' => 'John Doe',
		));

		$this->assertSame(
			'John Doe',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForNonEmptyNameFirstNameAndLastNameReturnsName() {
		$this->fixture->setData(array(
			'name' => 'John Doe',
			'first_name' => 'Peter',
			'last_name' => 'Pan',
		));

		$this->assertSame(
			'John Doe',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyNameAndNonEmptyFirstAndLastNameReturnsFirstAndLastName() {
		$this->fixture->setData(array(
			'name' => '',
			'first_name' => 'Peter',
			'last_name' => 'Pan',
		));

		$this->assertSame(
			'Peter Pan',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForNonEmptyFirstAndLastNameAndNonEmptyUserNameReturnsFirstAndLastName() {
		$this->fixture->setData(array(
			'first_name' => 'Peter',
			'last_name' => 'Pan',
			'username' => 'johndoe',
		));

		$this->assertSame(
			'Peter Pan',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyFirstNameAndNonEmptyLastAndUserNameReturnsLastName() {
		$this->fixture->setData(array(
			'first_name' => '',
			'last_name' => 'Pan',
			'username' => 'johndoe',
		));

		$this->assertSame(
			'Pan',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyLastNameAndNonEmptyFirstAndUserNameReturnsFirstName() {
		$this->fixture->setData(array(
			'first_name' => 'Peter',
			'last_name' => '',
			'username' => 'johndoe',
		));

		$this->assertSame(
			'Peter',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyFirstAndLastNameAndNonEmptyUserNameReturnsUserName() {
		$this->fixture->setData(array(
			'first_name' => '',
			'last_name' => '',
			'username' => 'johndoe',
		));

		$this->assertSame(
			'johndoe',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function setNameSetsFullName() {
		$this->fixture->setName('Alfred E. Neumann');

		$this->assertSame(
			'Alfred E. Neumann',
			$this->fixture->getName()
		);
	}


	/////////////////////////////////////////
	// Tests concerning getting the company
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasCompanyForEmptyCompanyReturnsFalse() {
		$this->fixture->setData(array('company' => ''));

		$this->assertFalse(
			$this->fixture->hasCompany()
		);
	}

	/**
	 * @test
	 */
	public function hasCompanyForNonEmptyCompanyReturnsTrue() {
		$this->fixture->setData(array('company' => 'Test Inc.'));

		$this->assertTrue(
			$this->fixture->hasCompany()
		);
	}

	/**
	 * @test
	 */
	public function getCompanyForEmptyCompanyReturnsEmptyString() {
		$this->fixture->setData(array('company' => ''));

		$this->assertSame(
			'',
			$this->fixture->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function getCompanyForNonEmptyCompanyReturnsCompany() {
		$this->fixture->setData(array('company' => 'Test Inc.'));

		$this->assertSame(
			'Test Inc.',
			$this->fixture->getCompany()
		);
	}


	////////////////////////////////////////
	// Tests concerning getting the street
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasStreetForEmptyAddressReturnsFalse() {
		$this->fixture->setData(array('address' => ''));

		$this->assertFalse(
			$this->fixture->hasStreet()
		);
	}

	/**
	 * @test
	 */
	public function hasStreetForNonEmptyAddressReturnsTrue() {
		$this->fixture->setData(array('address' => 'Foo street 1'));

		$this->assertTrue(
			$this->fixture->hasStreet()
		);
	}

	/**
	 * @test
	 */
	public function getStreetForEmptyAddressReturnsEmptyString() {
		$this->fixture->setData(array('address' => ''));

		$this->assertSame(
			'',
			$this->fixture->getStreet()
		);
	}

	/**
	 * @test
	 */
	public function getStreetForNonEmptyAddressReturnsAddress() {
		$this->fixture->setData(array('address' => 'Foo street 1'));

		$this->assertSame(
			'Foo street 1',
			$this->fixture->getStreet()
		);
	}

	/**
	 * @test
	 */
	public function getStreetForMultilineAddressReturnsAddress() {
		$this->fixture->setData(array(
			'address' => 'Foo street 1' . LF . 'Floor 3'
		));

		$this->assertSame(
			'Foo street 1' . LF . 'Floor 3',
			$this->fixture->getStreet()
		);
	}


	//////////////////////////////////////////
	// Tests concerning getting the ZIP code
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasZipForEmptyZipReturnsFalse() {
		$this->fixture->setData(array('zip' => ''));

		$this->assertFalse(
			$this->fixture->hasZip()
		);
	}

	/**
	 * @test
	 */
	public function hasZipForNonEmptyZipReturnsTrue() {
		$this->fixture->setData(array('zip' => '12345'));

		$this->assertTrue(
			$this->fixture->hasZip()
		);
	}

	/**
	 * @test
	 */
	public function getZipForEmptyZipReturnsEmptyString() {
		$this->fixture->setData(array('zip' => ''));

		$this->assertSame(
			'',
			$this->fixture->getZip()
		);
	}

	/**
	 * @test
	 */
	public function getZipForNonEmptyZipReturnsZip() {
		$this->fixture->setData(array('zip' => '12345'));

		$this->assertSame(
			'12345',
			$this->fixture->getZip()
		);
	}


	//////////////////////////////////////
	// Tests concerning getting the city
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function hasCityForEmptyCityReturnsFalse() {
		$this->fixture->setData(array('city' => ''));

		$this->assertFalse(
			$this->fixture->hasCity()
		);
	}

	/**
	 * @test
	 */
	public function hasCityForNonEmptyCityReturnsTrue() {
		$this->fixture->setData(array('city' => 'Test city'));

		$this->assertTrue(
			$this->fixture->hasCity()
		);
	}

	/**
	 * @test
	 */
	public function getCityForEmptyCityReturnsEmptyString() {
		$this->fixture->setData(array('city' => ''));

		$this->assertSame(
			'',
			$this->fixture->getCity()
		);
	}

	/**
	 * @test
	 */
	public function getCityForNonEmptyCityReturnsCity() {
		$this->fixture->setData(array('city' => 'Test city'));

		$this->assertSame(
			'Test city',
			$this->fixture->getCity()
		);
	}

	/**
	 * @test
	 */
	public function getZipAndCityForNonEmptyZipAndCityReturnsZipAndCity() {
		$this->fixture->setData(array(
			'zip' => '12345',
			'city' => 'Test city',
		));

		$this->assertSame(
			'12345 Test city',
			$this->fixture->getZipAndCity()
		);
	}

	/**
	 * @test
	 */
	public function getZipAndCityForEmptyZipAndNonEmptyCityReturnsCity() {
		$this->fixture->setData(array(
			'zip' => '',
			'city' => 'Test city',
		));

		$this->assertSame(
			'Test city',
			$this->fixture->getZipAndCity()
		);
	}

	/**
	 * @test
	 */
	public function getZipAndGetCityForNonEmptyZipAndEmptyCityReturnsEmptyString() {
		$this->fixture->setData(array(
			'zip' => '12345',
			'city' => '',
		));

		$this->assertSame(
			'',
			$this->fixture->getZipAndCity()
		);
	}

	/**
	 * @test
	 */
	public function getZipAndGetCityForEmptyZipAndEmptyCityReturnsEmptyString() {
		$this->fixture->setData(array(
			'zip' => '',
			'city' => '',
		));

		$this->assertSame(
			'',
			$this->fixture->getZipAndCity()
		);
	}


	//////////////////////////////////////
	// Tests concerning getting the phone
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function hasPhoneNumberForEmptyPhoneReturnsFalse() {
		$this->fixture->setData(array('telephone' => ''));

		$this->assertFalse(
			$this->fixture->hasPhoneNumber()
		);
	}

	/**
	 * @test
	 */
	public function hasPhoneNumberForNonEmptyPhoneReturnsTrue() {
		$this->fixture->setData(array('telephone' => '1234 5678'));

		$this->assertTrue(
			$this->fixture->hasPhoneNumber()
		);
	}

	/**
	 * @test
	 */
	public function getPhoneNumberForEmptyPhoneReturnsEmptyString() {
		$this->fixture->setData(array('telephone' => ''));

		$this->assertSame(
			'',
			$this->fixture->getPhoneNumber()
		);
	}

	/**
	 * @test
	 */
	public function getPhoneNumberForNonEmptyPhoneReturnsPhone() {
		$this->fixture->setData(array('telephone' => '1234 5678'));

		$this->assertSame(
			'1234 5678',
			$this->fixture->getPhoneNumber()
		);
	}


	////////////////////////////////////////
	// Tests concerning the e-mail address
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasEmailAddressForEmptyEmailReturnsFalse() {
		$this->fixture->setData(array('email' => ''));

		$this->assertFalse(
			$this->fixture->hasEmailAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasEmailAddressForNonEmptyEmailReturnsTrue() {
		$this->fixture->setData(array('email' => 'john@doe.com'));

		$this->assertTrue(
			$this->fixture->hasEmailAddress()
		);
	}

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

	/**
	 * @test
	 */
	public function setEmailAddressSetsEmailAddress() {
		$this->fixture->setEmailAddress('john@example.com');

		$this->assertSame(
			'john@example.com',
			$this->fixture->getEmailAddress()
		);
	}


	//////////////////////////////////////////
	// Tests concerning getting the homepage
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasHomepageForEmptyWwwReturnsFalse() {
		$this->fixture->setData(array('www' => ''));

		$this->assertFalse(
			$this->fixture->hasHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageForNonEmptyWwwReturnsTrue() {
		$this->fixture->setData(array('www' => 'http://www.doe.com'));

		$this->assertTrue(
			$this->fixture->hasHomepage()
		);
	}

	/**
	 * @test
	 */
	public function getHomepageForEmptyWwwReturnsEmptyString() {
		$this->fixture->setData(array('www' => ''));

		$this->assertSame(
			'',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function getHomepageForNonEmptyWwwReturnsWww() {
		$this->fixture->setData(array('www' => 'http://www.doe.com'));

		$this->assertSame(
			'http://www.doe.com',
			$this->fixture->getHomepage()
		);
	}


	/////////////////////////////////////////
	// Tests concerning getting the picture
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasImageForEmptyImageReturnsFalse() {
		$this->fixture->setData(array('image' => ''));

		$this->assertFalse(
			$this->fixture->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageForNonEmptyImageReturnsTrue() {
		$this->fixture->setData(array('image' => 'thats-me.jpg'));

		$this->assertTrue(
			$this->fixture->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageForEmptyImageReturnsEmptyString() {
		$this->fixture->setData(array('image' => ''));

		$this->assertSame(
			'',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageForNonEmptyImageReturnsImage() {
		$this->fixture->setData(array('image' => 'thats-me.jpg'));

		$this->assertSame(
			'thats-me.jpg',
			$this->fixture->getImage()
		);
	}


	////////////////////////////////////
	// Tests concerning wantsHtmlEmail
	////////////////////////////////////

	/**
	 * @test
	 */
	public function wantsHtmlEmailForMissingModuleSysDmailHtmlFieldReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->wantsHtmlEmail()
		);
	}

	/**
	 * @test
	 */
	public function wantsHtmlEmailForModuleSysDmailHtmlOneReturnsTrue() {
		$this->fixture->setData(array('module_sys_dmail_html' => 1));

		$this->assertTrue(
			$this->fixture->wantsHtmlEmail()
		);
	}

	/**
	 * @test
	 */
	public function wantsHtmlEmailForModuleSysDmailHtmlZeroReturnsFalse() {
		$this->fixture->setData(array('module_sys_dmail_html' => 0));

		$this->assertFalse(
			$this->fixture->wantsHtmlEmail()
		);
	}


	///////////////////////////////////////
	// Test concerning hasGroupMembership
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function hasGroupMembershipWithEmptyUidListThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$uidList must not be empty.'
		);

		$this->fixture->hasGroupMembership('');
	}

	/**
	 * @test
	 */
	public function hasGroupMembershipForUserOnlyInProvidedGroupReturnsTrue() {
		$userGroup = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUserGroup')->getNewGhost();
		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertTrue(
			$this->fixture->hasGroupMembership($userGroup->getUid())
		);
	}

	/**
	 * @test
	 */
	public function hasGroupMembershipForUserInProvidedGroupAndInAnotherReturnsTrue() {
		$groupMapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUserGroup');
		$userGroup = $groupMapper->getNewGhost();
		$list = new tx_oelib_List();
		$list->add($groupMapper->getNewGhost());
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertTrue(
			$this->fixture->hasGroupMembership($userGroup->getUid())
		);
	}

	/**
	 * @test
	 */
	public function hasGroupMembershipForUserInOneOfTheProvidedGroupsReturnsTrue() {
		$groupMapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUserGroup');
		$userGroup = $groupMapper->getNewGhost();
		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertTrue(
			$this->fixture->hasGroupMembership(
				$userGroup->getUid() . ',' . $groupMapper->getNewGhost()->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function hasGroupMembershipForUserNoneOfTheProvidedGroupsReturnsFalse() {
		$groupMapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUserGroup');
		$list = new tx_oelib_List();
		$list->add($groupMapper->getNewGhost());
		$list->add($groupMapper->getNewGhost());

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertFalse(
			$this->fixture->hasGroupMembership(
				$groupMapper->getNewGhost()->getUid() . ',' . $groupMapper->getNewGhost()->getUid()
			)
		);
	}


	///////////////////////////////
	// Tests concerning getGender
	///////////////////////////////

	/**
	 * @test
	 */
	public function getGenderForNotInstalledSrFeUserRegisterReturnsGenderUnknown() {
		if (t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
					'This test is only applicable if sr_feuser_register is ' .
						'not loaded.'
			);
		}

		$this->assertSame(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function getGenderForGenderValueZeroReturnsGenderMale() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
					'This test is only applicable if sr_feuser_register is ' .
						'loaded.'
			);
		}
		$this->fixture->setData(array('gender' => 0));

		$this->assertSame(
			tx_oelib_Model_FrontEndUser::GENDER_MALE,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function getGenderForGenderValueOneReturnsGenderFemale() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
					'This test is only applicable if sr_feuser_register is ' .
						'loaded.'
			);
		}
		$this->fixture->setData(array('gender' => 1));

		$this->assertSame(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE,
			$this->fixture->getGender()
		);
	}


	////////////////////////////////////
	// Tests concerning the first name
	////////////////////////////////////

	/**
	 * @test
	 */
	public function hasFirstNameForNoFirstNameSetReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasFirstName()
		);
	}

	/**
	 * @test
	 */
	public function hasFirstNameForFirstNameSetReturnsTrue() {
		$this->fixture->setData(array('first_name' => 'foo'));

		$this->assertTrue(
			$this->fixture->hasFirstName()
		);
	}

	/**
	 * @test
	 */
	public function getFirstNameForNoFirstNameSetReturnsEmptyString() {
		$this->fixture->setData(array());

		$this->assertSame(
			'',
			$this->fixture->getFirstName()
		);
	}

	/**
	 * @test
	 */
	public function getFirstNameForFirstNameSetReturnsFirstName() {
		$this->fixture->setData(array('first_name' => 'foo'));

		$this->assertSame(
			'foo',
			$this->fixture->getFirstName()
		);
	}

	/**
	 * @test
	 */
	public function setFirstNameSetsFirstName() {
		$this->fixture->setFirstName('John');

		$this->assertSame(
			'John',
			$this->fixture->getFirstName()
		);
	}

	/**
	 * @test
	 */
	public function getFirstOrFullNameForUserWithFirstNameReturnsFirstName() {
		$this->fixture->setData(
			array('first_name' => 'foo', 'name' => 'foo bar')
		);

		$this->assertSame(
			'foo',
			$this->fixture->getFirstOrFullName()
		);
	}

	/**
	 * @test
	 */
	public function getFirstOrFullNameForUserWithoutFirstNameReturnsName() {
		$this->fixture->setData(array('name' => 'foo bar'));

		$this->assertSame(
			'foo bar',
			$this->fixture->getFirstOrFullName()
		);
	}


	///////////////////////////////////
	// Tests concerning the last name
	///////////////////////////////////

	/**
	 * @test
	 */
	public function hasLastNameForNoLastNameSetReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasLastName()
		);
	}

	/**
	 * @test
	 */
	public function hasLastNameForLastNameSetReturnsTrue() {
		$this->fixture->setData(array('last_name' => 'bar'));

		$this->assertTrue(
			$this->fixture->hasLastName()
		);
	}

	/**
	 * @test
	 */
	public function getLastNameForNoLastNameSetReturnsEmptyString() {
		$this->fixture->setData(array());

		$this->assertSame(
			'',
			$this->fixture->getLastName()
		);
	}

	/**
	 * @test
	 */
	public function getLastNameForLastNameSetReturnsLastName() {
		$this->fixture->setData(array('last_name' => 'bar'));

		$this->assertSame(
			'bar',
			$this->fixture->getLastName()
		);
	}

	/**
	 * @test
	 */
	public function setLastNameSetsLastName() {
		$this->fixture->setLastName('Jacuzzi');

		$this->assertSame(
			'Jacuzzi',
			$this->fixture->getLastName()
		);
	}

	/**
	 * @test
	 */
	public function getLastOrFullNameForUserWithLastNameReturnsLastName() {
		$this->fixture->setData(
			array('last_name' => 'bar', 'name' => 'foo bar')
		);

		$this->assertSame(
			'bar',
			$this->fixture->getLastOrFullName()
		);
	}

	/**
	 * @test
	 */
	public function getLastOrFullNameForUserWithoutLastNameReturnsName() {
		$this->fixture->setData(array('name' => 'foo bar'));

		$this->assertSame(
			'foo bar',
			$this->fixture->getLastOrFullName()
		);
	}


	///////////////////////////////////////
	// Tests concerning the date of birth
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getDateOfBirthReturnsZeroForNoDateSet() {
		$this->fixture->setData(array());

		$this->assertSame(
			0,
			$this->fixture->getDateOfBirth()
		);
	}

	/**
	 * @test
	 */
	public function getDateOfBirthReturnsDateFromDateOfBirthField() {
		// 1980-04-01
		$date = 323391600;
		$this->fixture->setData(array('date_of_birth' => $date));

		$this->assertSame(
			$date,
			$this->fixture->getDateOfBirth()
		);
	}

	/**
	 * @test
	 */
	public function hasDateOfBirthForNoDateOfBirthReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasDateOfBirth()
		);
	}

	/**
	 * @test
	 */
	public function hasDateOfBirthForNonZeroDateOfBirthReturnsTrue() {
		// 1980-04-01
		$date = 323391600;
		$this->fixture->setData(array('date_of_birth' => $date));

		$this->assertTrue(
			$this->fixture->hasDateOfBirth()
		);
	}


	////////////////////////////
	// Tests concerning getAge
	////////////////////////////

	/**
	 * @test
	 */
	public function getAgeForNoDateOfBirthReturnsZero() {
		$this->fixture->setData(array());

		$this->assertSame(
			0,
			$this->fixture->getAge()
		);
	}

	/**
	 * @test
	 */
	public function getAgeForBornOneHourAgoReturnsZero() {
		$now = mktime(18, 0, 0, 9, 15, 2010);
		$GLOBALS['EXEC_TIME'] = $now;

		$this->fixture->setData(
			array('date_of_birth' => $now - 60 * 60)
		);

		$this->assertSame(
			0,
			$this->fixture->getAge()
		);
	}

	/**
	 * @test
	 */
	public function getAgeForAnAgeOfTenYearsAndSomeMonthsReturnsTen() {
		$GLOBALS['EXEC_TIME'] = mktime(18, 0, 0, 9, 15, 2010);

		$this->fixture->setData(
			array('date_of_birth' => mktime(18, 0, 0, 1, 15, 2000))
		);

		$this->assertSame(
			10,
			$this->fixture->getAge()
		);
	}

	/**
	 * @test
	 */
	public function getAgeForAnAgeOfTenYearsMinusSomeMonthsReturnsNine() {
		$GLOBALS['EXEC_TIME'] = mktime(18, 0, 0, 9, 15, 2010);

		$this->fixture->setData(
			array('date_of_birth' => mktime(18, 0, 0, 11, 15, 2000))
		);

		$this->assertSame(
			9,
			$this->fixture->getAge()
		);
	}

	/**
	 * @test
	 */
	public function getAgeForAnAgeOfTenYearsMinusSomeDaysReturnsNine() {
		$GLOBALS['EXEC_TIME'] = mktime(18, 0, 0, 9, 15, 2010);

		$this->fixture->setData(
			array('date_of_birth' => mktime(18, 0, 0, 9, 21, 2000))
		);

		$this->assertSame(
			9,
			$this->fixture->getAge()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning the date of the last login
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getLastLoginAsUnixTimestampReturnsZeroForNoDateSet() {
		$this->fixture->setData(array());

		$this->assertSame(
			0,
			$this->fixture->getLastLoginAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getLastLoginAsUnixTimestampReturnsDateFromLastLoginField() {
		// 1980-04-01
		$date = 323391600;
		$this->fixture->setData(array('lastlogin' => $date));

		$this->assertSame(
			$date,
			$this->fixture->getLastLoginAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function hasLastLoginForNoLastLoginReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasLastLogin()
		);
	}

	/**
	 * @test
	 */
	public function hasLastLoginForNonZeroLastLoginReturnsTrue() {
		// 1980-04-01
		$date = 323391600;
		$this->fixture->setData(array('lastlogin' => $date));

		$this->assertTrue(
			$this->fixture->hasLastLogin()
		);
	}


	////////////////////////////////
	// Tests regarding the country
	////////////////////////////////

	/**
	 * @test
	 */
	public function getCountryWithoutCountryReturnsNull() {
		$this->fixture->setData(array());

		$this->assertNull(
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithInvalidCountryCodeReturnsNull() {
		$this->fixture->setData(array('static_info_country' => 'xyz'));

		$this->assertNull(
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithCountryReturnsCountryAsModel() {
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')
			->find(54);
		$this->fixture->setData(
			array('static_info_country' => $country->getIsoAlpha3Code())
		);

		$this->assertSame(
			$country,
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setCountrySetsCountry() {
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')
			->find(54);
		$this->fixture->setCountry($country);

		$this->assertSame(
			$country,
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function countryCanBeSetToNull() {
		$this->fixture->setCountry(NULL);

		$this->assertNull(
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithoutCountryReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithInvalidCountryReturnsFalse() {
		$this->fixture->setData(array('static_info_country' => 'xyz'));

		$this->assertFalse(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithCountryReturnsTrue() {
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')
			->find(54);
		$this->fixture->setCountry($country);

		$this->assertTrue(
			$this->fixture->hasCountry()
		);
	}


	///////////////////////////////////
	// Tests concerning the job title
	///////////////////////////////////

	/**
	 * @test
	 */
	public function hasJobTitleForEmptyJobTitleReturnsFalse() {
		$this->fixture->setData(array('title' => ''));

		$this->assertFalse(
			$this->fixture->hasJobTitle()
		);
	}

	/**
	 * @test
	 */
	public function hasJobTitleForNonEmptyJobTitleReturnsTrue() {
		$this->fixture->setData(array('title' => 'facility manager'));

		$this->assertTrue(
			$this->fixture->hasJobTitle()
		);
	}

	/**
	 * @test
	 */
	public function getJobTitleForEmptyJobTitleReturnsEmptyString() {
		$this->fixture->setData(array('title' => ''));

		$this->assertSame(
			'',
			$this->fixture->getJobTitle()
		);
	}

	/**
	 * @test
	 */
	public function getJobTitleForNonEmptyJobTitleReturnsJobTitle() {
		$this->fixture->setData(array('title' => 'facility manager'));

		$this->assertSame(
			'facility manager',
			$this->fixture->getJobTitle()
		);
	}

	/**
	 * @test
	 */
	public function setJobTitleSetsJobTitle() {
		$this->fixture->setJobTitle('foo bar');

		$this->assertSame(
			'foo bar',
			$this->fixture->getJobTitle()
		);
	}


	/////////////////////////////////////
	// Tests concerning the user groups
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getUserGroupsForReturnsUserGroups() {
		$userGroups = new tx_oelib_List();

		$this->fixture->setData(array('usergroup' => $userGroups));

		$this->assertSame(
			$userGroups,
			$this->fixture->getUserGroups()
		);
	}

	/**
	 * @test
	 */
	public function setUserGroupsSetsUserGroups() {
		$userGroups = new tx_oelib_List();

		$this->fixture->setUserGroups($userGroups);

		$this->assertSame(
			$userGroups,
			$this->fixture->getUserGroups()
		);
	}
}
?>