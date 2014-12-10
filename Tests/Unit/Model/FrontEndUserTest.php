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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Model_FrontEndUserTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Model_FrontEndUser
	 */
	protected $subject = NULL;

	/**
	 * @var int a backup of $GLOBALS['EXEC_TIME']
	 */
	protected $globalExecTimeBackup = 0;

	protected function setUp() {
		$this->subject = new Tx_Oelib_Model_FrontEndUser();

		$this->globalExecTimeBackup = $GLOBALS['EXEC_TIME'];
	}

	protected function tearDown() {
		$GLOBALS['EXEC_TIME'] = $this->globalExecTimeBackup;

		unset($this->subject);
	}


	/*
	 * Tests concerning the user name
	 */

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

	/**
	 * @test
	 */
	public function setUserNameSetsUserName() {
		$this->subject->setUserName('foo_bar');

		$this->assertSame(
			'foo_bar',
			$this->subject->getUserName()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setUserNameWithEmptyUserNameThrowsException() {
		$this->subject->setUserName('');
	}


	/*
	 * Tests concerning the password
	 */

	/**
	 * @test
	 */
	public function getPasswordInitiallyReturnsEmptyString() {
		$this->subject->setData(array());

		$this->assertSame(
			'',
			$this->subject->getPassword()
		);
	}

	/**
	 * @test
	 */
	public function getPasswordReturnsPassword() {
		$this->subject->setData(array('password' => 'kasfdjklsdajk'));

		$this->assertSame(
			'kasfdjklsdajk',
			$this->subject->getPassword()
		);
	}

	/**
	 * @test
	 */
	public function setPasswordSetsPassword() {
		$this->subject->setPassword('kljvasgd24vsga354');

		$this->assertSame(
			'kljvasgd24vsga354',
			$this->subject->getPassword()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setPasswordWithEmptyPasswordThrowsException() {
		$this->subject->setPassword('');
	}


	/*
	 * Tests concerning the name
	 */

	/**
	 * @test
	 */
	public function hasNameForEmptyNameLastNameAndFirstNameReturnsFalse() {
		$this->subject->setData(array(
			'name' => '',
			'first_name' => '',
			'last_name' => '',
		));

		$this->assertFalse(
			$this->subject->hasName()
		);
	}

	/**
	 * @test
	 */
	public function hasNameForNonEmptyUserReturnsFalse() {
		$this->subject->setData(array(
			'username' => 'johndoe',
		));

		$this->assertFalse(
			$this->subject->hasName()
		);
	}

	/**
	 * @test
	 */
	public function hasNameForNonEmptyNameReturnsTrue() {
		$this->subject->setData(array(
			'name' => 'John Doe',
			'first_name' => '',
			'last_name' => '',
		));

		$this->assertTrue(
			$this->subject->hasName()
		);
	}

	/**
	 * @test
	 */
	public function hasNameForNonEmptyFirstNameReturnsTrue() {
		$this->subject->setData(array(
			'name' => '',
			'first_name' => 'John',
			'last_name' => '',
		));

		$this->assertTrue(
			$this->subject->hasName()
		);
	}

	/**
	 * @test
	 */
	public function hasNameForNonEmptyLastNameReturnsTrue() {
		$this->subject->setData(array(
			'name' => '',
			'first_name' => '',
			'last_name' => 'Doe',
		));

		$this->assertTrue(
			$this->subject->hasName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForNonEmptyNameReturnsName() {
		$this->subject->setData(array(
			'name' => 'John Doe',
		));

		$this->assertSame(
			'John Doe',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForNonEmptyNameFirstNameAndLastNameReturnsName() {
		$this->subject->setData(array(
			'name' => 'John Doe',
			'first_name' => 'Peter',
			'last_name' => 'Pan',
		));

		$this->assertSame(
			'John Doe',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyNameAndNonEmptyFirstAndLastNameReturnsFirstAndLastName() {
		$this->subject->setData(array(
			'name' => '',
			'first_name' => 'Peter',
			'last_name' => 'Pan',
		));

		$this->assertSame(
			'Peter Pan',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForNonEmptyFirstAndLastNameAndNonEmptyUserNameReturnsFirstAndLastName() {
		$this->subject->setData(array(
			'first_name' => 'Peter',
			'last_name' => 'Pan',
			'username' => 'johndoe',
		));

		$this->assertSame(
			'Peter Pan',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyFirstNameAndNonEmptyLastAndUserNameReturnsLastName() {
		$this->subject->setData(array(
			'first_name' => '',
			'last_name' => 'Pan',
			'username' => 'johndoe',
		));

		$this->assertSame(
			'Pan',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyLastNameAndNonEmptyFirstAndUserNameReturnsFirstName() {
		$this->subject->setData(array(
			'first_name' => 'Peter',
			'last_name' => '',
			'username' => 'johndoe',
		));

		$this->assertSame(
			'Peter',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameForEmptyFirstAndLastNameAndNonEmptyUserNameReturnsUserName() {
		$this->subject->setData(array(
			'first_name' => '',
			'last_name' => '',
			'username' => 'johndoe',
		));

		$this->assertSame(
			'johndoe',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function setNameSetsFullName() {
		$this->subject->setName('Alfred E. Neumann');

		$this->assertSame(
			'Alfred E. Neumann',
			$this->subject->getName()
		);
	}


	/*
	 * Tests concerning getting the company
	 */

	/**
	 * @test
	 */
	public function hasCompanyForEmptyCompanyReturnsFalse() {
		$this->subject->setData(array('company' => ''));

		$this->assertFalse(
			$this->subject->hasCompany()
		);
	}

	/**
	 * @test
	 */
	public function hasCompanyForNonEmptyCompanyReturnsTrue() {
		$this->subject->setData(array('company' => 'Test Inc.'));

		$this->assertTrue(
			$this->subject->hasCompany()
		);
	}

	/**
	 * @test
	 */
	public function getCompanyForEmptyCompanyReturnsEmptyString() {
		$this->subject->setData(array('company' => ''));

		$this->assertSame(
			'',
			$this->subject->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function getCompanyForNonEmptyCompanyReturnsCompany() {
		$this->subject->setData(array('company' => 'Test Inc.'));

		$this->assertSame(
			'Test Inc.',
			$this->subject->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function setCompanySetsCompany() {
		$this->subject->setCompany('Test Inc.');

		$this->assertSame(
			'Test Inc.',
			$this->subject->getCompany()
		);
	}


	/*
	 * Tests concerning getting the street
	 */

	/**
	 * @test
	 */
	public function hasStreetForEmptyAddressReturnsFalse() {
		$this->subject->setData(array('address' => ''));

		$this->assertFalse(
			$this->subject->hasStreet()
		);
	}

	/**
	 * @test
	 */
	public function hasStreetForNonEmptyAddressReturnsTrue() {
		$this->subject->setData(array('address' => 'Foo street 1'));

		$this->assertTrue(
			$this->subject->hasStreet()
		);
	}

	/**
	 * @test
	 */
	public function getStreetForEmptyAddressReturnsEmptyString() {
		$this->subject->setData(array('address' => ''));

		$this->assertSame(
			'',
			$this->subject->getStreet()
		);
	}

	/**
	 * @test
	 */
	public function getStreetForNonEmptyAddressReturnsAddress() {
		$this->subject->setData(array('address' => 'Foo street 1'));

		$this->assertSame(
			'Foo street 1',
			$this->subject->getStreet()
		);
	}

	/**
	 * @test
	 */
	public function getStreetForMultilineAddressReturnsAddress() {
		$this->subject->setData(array(
			'address' => 'Foo street 1' . LF . 'Floor 3'
		));

		$this->assertSame(
			'Foo street 1' . LF . 'Floor 3',
			$this->subject->getStreet()
		);
	}

	/**
	 * @test
	 */
	public function setStreetSetsStreet() {
		$street = 'Barber Street 42';
		$this->subject->setData(array());
		$this->subject->setStreet($street);

		$this->assertSame(
			$street,
			$this->subject->getStreet()
		);
	}


	/*
	 * Tests concerning the ZIP code
	 */

	/**
	 * @test
	 */
	public function hasZipForEmptyZipReturnsFalse() {
		$this->subject->setData(array('zip' => ''));

		$this->assertFalse(
			$this->subject->hasZip()
		);
	}

	/**
	 * @test
	 */
	public function hasZipForNonEmptyZipReturnsTrue() {
		$this->subject->setData(array('zip' => '12345'));

		$this->assertTrue(
			$this->subject->hasZip()
		);
	}

	/**
	 * @test
	 */
	public function getZipForEmptyZipReturnsEmptyString() {
		$this->subject->setData(array('zip' => ''));

		$this->assertSame(
			'',
			$this->subject->getZip()
		);
	}

	/**
	 * @test
	 */
	public function getZipForNonEmptyZipReturnsZip() {
		$this->subject->setData(array('zip' => '12345'));

		$this->assertSame(
			'12345',
			$this->subject->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setZipSetsZip() {
		$zip = '12356';
		$this->subject->setData(array());
		$this->subject->setZip($zip);

		$this->assertSame(
			$zip,
			$this->subject->getZip()
		);
	}


	/*
	 * Tests concerning the city
	 */

	/**
	 * @test
	 */
	public function hasCityForEmptyCityReturnsFalse() {
		$this->subject->setData(array('city' => ''));

		$this->assertFalse(
			$this->subject->hasCity()
		);
	}

	/**
	 * @test
	 */
	public function hasCityForNonEmptyCityReturnsTrue() {
		$this->subject->setData(array('city' => 'Test city'));

		$this->assertTrue(
			$this->subject->hasCity()
		);
	}

	/**
	 * @test
	 */
	public function getCityForEmptyCityReturnsEmptyString() {
		$this->subject->setData(array('city' => ''));

		$this->assertSame(
			'',
			$this->subject->getCity()
		);
	}

	/**
	 * @test
	 */
	public function getCityForNonEmptyCityReturnsCity() {
		$this->subject->setData(array('city' => 'Test city'));

		$this->assertSame(
			'Test city',
			$this->subject->getCity()
		);
	}

	/**
	 * @test
	 */
	public function setCitySetsCity() {
		$city = 'Köln';
		$this->subject->setData(array());
		$this->subject->setCity($city);

		$this->assertSame(
			$city,
			$this->subject->getCity()
		);
	}

	/**
	 * @test
	 */
	public function getZipAndCityForNonEmptyZipAndCityReturnsZipAndCity() {
		$this->subject->setData(array(
			'zip' => '12345',
			'city' => 'Test city',
		));

		$this->assertSame(
			'12345 Test city',
			$this->subject->getZipAndCity()
		);
	}

	/**
	 * @test
	 */
	public function getZipAndCityForEmptyZipAndNonEmptyCityReturnsCity() {
		$this->subject->setData(array(
			'zip' => '',
			'city' => 'Test city',
		));

		$this->assertSame(
			'Test city',
			$this->subject->getZipAndCity()
		);
	}

	/**
	 * @test
	 */
	public function getZipAndGetCityForNonEmptyZipAndEmptyCityReturnsEmptyString() {
		$this->subject->setData(array(
			'zip' => '12345',
			'city' => '',
		));

		$this->assertSame(
			'',
			$this->subject->getZipAndCity()
		);
	}

	/**
	 * @test
	 */
	public function getZipAndGetCityForEmptyZipAndEmptyCityReturnsEmptyString() {
		$this->subject->setData(array(
			'zip' => '',
			'city' => '',
		));

		$this->assertSame(
			'',
			$this->subject->getZipAndCity()
		);
	}


	/*
	 * Tests concerning the phone number
	 */

	/**
	 * @test
	 */
	public function hasPhoneNumberForEmptyPhoneReturnsFalse() {
		$this->subject->setData(array('telephone' => ''));

		$this->assertFalse(
			$this->subject->hasPhoneNumber()
		);
	}

	/**
	 * @test
	 */
	public function hasPhoneNumberForNonEmptyPhoneReturnsTrue() {
		$this->subject->setData(array('telephone' => '1234 5678'));

		$this->assertTrue(
			$this->subject->hasPhoneNumber()
		);
	}

	/**
	 * @test
	 */
	public function getPhoneNumberForEmptyPhoneReturnsEmptyString() {
		$this->subject->setData(array('telephone' => ''));

		$this->assertSame(
			'',
			$this->subject->getPhoneNumber()
		);
	}

	/**
	 * @test
	 */
	public function getPhoneNumberForNonEmptyPhoneReturnsPhone() {
		$this->subject->setData(array('telephone' => '1234 5678'));

		$this->assertSame(
			'1234 5678',
			$this->subject->getPhoneNumber()
		);
	}

	/**
	 * @test
	 */
	public function setPhoneNumberSetsPhoneNumber() {
		$phoneNumber = '+49 124 1234123';
		$this->subject->setData(array());
		$this->subject->setPhoneNumber($phoneNumber);

		$this->assertSame(
			$phoneNumber,
			$this->subject->getPhoneNumber()
		);
	}


	/*
	 * Tests concerning the e-mail address
	 */

	/**
	 * @test
	 */
	public function hasEmailAddressForEmptyEmailReturnsFalse() {
		$this->subject->setData(array('email' => ''));

		$this->assertFalse(
			$this->subject->hasEmailAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasEmailAddressForNonEmptyEmailReturnsTrue() {
		$this->subject->setData(array('email' => 'john@doe.com'));

		$this->assertTrue(
			$this->subject->hasEmailAddress()
		);
	}

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

	/**
	 * @test
	 */
	public function setEmailAddressSetsEmailAddress() {
		$this->subject->setEmailAddress('john@example.com');

		$this->assertSame(
			'john@example.com',
			$this->subject->getEmailAddress()
		);
	}


	/*
	 * Tests concerning getting the homepage
	 */

	/**
	 * @test
	 */
	public function hasHomepageForEmptyWwwReturnsFalse() {
		$this->subject->setData(array('www' => ''));

		$this->assertFalse(
			$this->subject->hasHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageForNonEmptyWwwReturnsTrue() {
		$this->subject->setData(array('www' => 'http://www.doe.com'));

		$this->assertTrue(
			$this->subject->hasHomepage()
		);
	}

	/**
	 * @test
	 */
	public function getHomepageForEmptyWwwReturnsEmptyString() {
		$this->subject->setData(array('www' => ''));

		$this->assertSame(
			'',
			$this->subject->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function getHomepageForNonEmptyWwwReturnsWww() {
		$this->subject->setData(array('www' => 'http://www.doe.com'));

		$this->assertSame(
			'http://www.doe.com',
			$this->subject->getHomepage()
		);
	}


	/*
	 * Tests concerning getting the picture
	 */

	/**
	 * @test
	 */
	public function hasImageForEmptyImageReturnsFalse() {
		$this->subject->setData(array('image' => ''));

		$this->assertFalse(
			$this->subject->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageForNonEmptyImageReturnsTrue() {
		$this->subject->setData(array('image' => 'thats-me.jpg'));

		$this->assertTrue(
			$this->subject->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageForEmptyImageReturnsEmptyString() {
		$this->subject->setData(array('image' => ''));

		$this->assertSame(
			'',
			$this->subject->getImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageForNonEmptyImageReturnsImage() {
		$this->subject->setData(array('image' => 'thats-me.jpg'));

		$this->assertSame(
			'thats-me.jpg',
			$this->subject->getImage()
		);
	}


	/*
	 * Tests concerning wantsHtmlEmail
	 */

	/**
	 * @test
	 */
	public function wantsHtmlEmailForMissingModuleSysDmailHtmlFieldReturnsFalse() {
		$this->subject->setData(array());

		$this->assertFalse(
			$this->subject->wantsHtmlEmail()
		);
	}

	/**
	 * @test
	 */
	public function wantsHtmlEmailForModuleSysDmailHtmlOneReturnsTrue() {
		$this->subject->setData(array('module_sys_dmail_html' => 1));

		$this->assertTrue(
			$this->subject->wantsHtmlEmail()
		);
	}

	/**
	 * @test
	 */
	public function wantsHtmlEmailForModuleSysDmailHtmlZeroReturnsFalse() {
		$this->subject->setData(array('module_sys_dmail_html' => 0));

		$this->assertFalse(
			$this->subject->wantsHtmlEmail()
		);
	}


	/*
	 * Tests concerning the user groups
	 */

	/**
	 * @test
	 */
	public function getUserGroupsForReturnsUserGroups() {
		$userGroups = new Tx_Oelib_List();

		$this->subject->setData(array('usergroup' => $userGroups));

		$this->assertSame(
			$userGroups,
			$this->subject->getUserGroups()
		);
	}

	/**
	 * @test
	 */
	public function setUserGroupsSetsUserGroups() {
		$userGroups = new Tx_Oelib_List();

		$this->subject->setUserGroups($userGroups);

		$this->assertSame(
			$userGroups,
			$this->subject->getUserGroups()
		);
	}

	/**
	 * @test
	 */
	public function addUserGroupAddsUserGroup() {
		$userGroups = new Tx_Oelib_List();
		$this->subject->setUserGroups($userGroups);

		$userGroup = new Tx_Oelib_Model_FrontEndUserGroup();
		$this->subject->addUserGroup($userGroup);

		$this->assertTrue(
			$this->subject->getUserGroups()->contains($userGroup)
		);
	}


	/*
	 * Test concerning hasGroupMembership
	 */

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function hasGroupMembershipWithEmptyUidListThrowsException() {
		$this->subject->hasGroupMembership('');
	}

	/**
	 * @test
	 */
	public function hasGroupMembershipForUserOnlyInProvidedGroupReturnsTrue() {
		$userGroup = Tx_Oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUserGroup')->getNewGhost();
		$list = new Tx_Oelib_List();
		$list->add($userGroup);

		$this->subject->setData(array('usergroup' => $list));

		$this->assertTrue(
			$this->subject->hasGroupMembership($userGroup->getUid())
		);
	}

	/**
	 * @test
	 */
	public function hasGroupMembershipForUserInProvidedGroupAndInAnotherReturnsTrue() {
		$groupMapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUserGroup');
		$userGroup = $groupMapper->getNewGhost();
		$list = new Tx_Oelib_List();
		$list->add($groupMapper->getNewGhost());
		$list->add($userGroup);

		$this->subject->setData(array('usergroup' => $list));

		$this->assertTrue(
			$this->subject->hasGroupMembership($userGroup->getUid())
		);
	}

	/**
	 * @test
	 */
	public function hasGroupMembershipForUserInOneOfTheProvidedGroupsReturnsTrue() {
		$groupMapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUserGroup');
		$userGroup = $groupMapper->getNewGhost();
		$list = new Tx_Oelib_List();
		$list->add($userGroup);

		$this->subject->setData(array('usergroup' => $list));

		$this->assertTrue(
			$this->subject->hasGroupMembership(
				$userGroup->getUid() . ',' . $groupMapper->getNewGhost()->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function hasGroupMembershipForUserNoneOfTheProvidedGroupsReturnsFalse() {
		$groupMapper = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUserGroup');
		$list = new Tx_Oelib_List();
		$list->add($groupMapper->getNewGhost());
		$list->add($groupMapper->getNewGhost());

		$this->subject->setData(array('usergroup' => $list));

		$this->assertFalse(
			$this->subject->hasGroupMembership(
				$groupMapper->getNewGhost()->getUid() . ',' . $groupMapper->getNewGhost()->getUid()
			)
		);
	}


	/*
	 * Tests concerning the gender
	 */

	/**
	 * @test
	 */
	public function getGenderForNotInstalledSrFeUserRegisterReturnsGenderUnknown() {
		if (Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if no FrontEndUser.gender field exists.');
		}

		$this->assertSame(
			Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$this->subject->getGender()
		);
	}

	/**
	 * @test
	 */
	public function getGenderForGenderValueZeroReturnsGenderMale() {
		if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if the FrontEndUser.gender field exists.');
		}
		$this->subject->setData(array('gender' => 0));

		$this->assertSame(
			Tx_Oelib_Model_FrontEndUser::GENDER_MALE,
			$this->subject->getGender()
		);
	}

	/**
	 * @test
	 */
	public function getGenderForGenderValueOneReturnsGenderFemale() {
		if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if the FrontEndUser.gender field exists.');
		}
		$this->subject->setData(array('gender' => 1));

		$this->assertSame(
			Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE,
			$this->subject->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setGenderCanSetGenderToMale() {
		if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if the FrontEndUser.gender field exists.');
		}
		$this->subject->setData(array());
		$this->subject->setGender(Tx_Oelib_Model_FrontEndUser::GENDER_MALE);

		$this->assertSame(
			Tx_Oelib_Model_FrontEndUser::GENDER_MALE,
			$this->subject->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setGenderCanSetGenderToFemale() {
		if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if the FrontEndUser.gender field exists.');
		}
		$this->subject->setData(array());
		$this->subject->setGender(Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE);

		$this->assertSame(
			Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE,
			$this->subject->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setGenderCanSetGenderToUnknown() {
		if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if the FrontEndUser.gender field exists.');
		}
		$this->subject->setData(array());
		$this->subject->setGender(Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN);

		$this->assertSame(
			Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$this->subject->getGender()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setGenderForInvalidGenderKeyThrowsException() {
		if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if the FrontEndUser.gender field exists.');
		}
		$this->subject->setData(array());
		$this->subject->setGender(4);
	}


	/*
	 * Tests concerning the first name
	 */

	/**
	 * @test
	 */
	public function hasFirstNameForNoFirstNameSetReturnsFalse() {
		$this->subject->setData(array());

		$this->assertFalse(
			$this->subject->hasFirstName()
		);
	}

	/**
	 * @test
	 */
	public function hasFirstNameForFirstNameSetReturnsTrue() {
		$this->subject->setData(array('first_name' => 'foo'));

		$this->assertTrue(
			$this->subject->hasFirstName()
		);
	}

	/**
	 * @test
	 */
	public function getFirstNameForNoFirstNameSetReturnsEmptyString() {
		$this->subject->setData(array());

		$this->assertSame(
			'',
			$this->subject->getFirstName()
		);
	}

	/**
	 * @test
	 */
	public function getFirstNameForFirstNameSetReturnsFirstName() {
		$this->subject->setData(array('first_name' => 'foo'));

		$this->assertSame(
			'foo',
			$this->subject->getFirstName()
		);
	}

	/**
	 * @test
	 */
	public function setFirstNameSetsFirstName() {
		$this->subject->setFirstName('John');

		$this->assertSame(
			'John',
			$this->subject->getFirstName()
		);
	}

	/**
	 * @test
	 */
	public function getFirstOrFullNameForUserWithFirstNameReturnsFirstName() {
		$this->subject->setData(
			array('first_name' => 'foo', 'name' => 'foo bar')
		);

		$this->assertSame(
			'foo',
			$this->subject->getFirstOrFullName()
		);
	}

	/**
	 * @test
	 */
	public function getFirstOrFullNameForUserWithoutFirstNameReturnsName() {
		$this->subject->setData(array('name' => 'foo bar'));

		$this->assertSame(
			'foo bar',
			$this->subject->getFirstOrFullName()
		);
	}


	/*
	 * Tests concerning the last name
	 */

	/**
	 * @test
	 */
	public function hasLastNameForNoLastNameSetReturnsFalse() {
		$this->subject->setData(array());

		$this->assertFalse(
			$this->subject->hasLastName()
		);
	}

	/**
	 * @test
	 */
	public function hasLastNameForLastNameSetReturnsTrue() {
		$this->subject->setData(array('last_name' => 'bar'));

		$this->assertTrue(
			$this->subject->hasLastName()
		);
	}

	/**
	 * @test
	 */
	public function getLastNameForNoLastNameSetReturnsEmptyString() {
		$this->subject->setData(array());

		$this->assertSame(
			'',
			$this->subject->getLastName()
		);
	}

	/**
	 * @test
	 */
	public function getLastNameForLastNameSetReturnsLastName() {
		$this->subject->setData(array('last_name' => 'bar'));

		$this->assertSame(
			'bar',
			$this->subject->getLastName()
		);
	}

	/**
	 * @test
	 */
	public function setLastNameSetsLastName() {
		$this->subject->setLastName('Jacuzzi');

		$this->assertSame(
			'Jacuzzi',
			$this->subject->getLastName()
		);
	}

	/**
	 * @test
	 */
	public function getLastOrFullNameForUserWithLastNameReturnsLastName() {
		$this->subject->setData(
			array('last_name' => 'bar', 'name' => 'foo bar')
		);

		$this->assertSame(
			'bar',
			$this->subject->getLastOrFullName()
		);
	}

	/**
	 * @test
	 */
	public function getLastOrFullNameForUserWithoutLastNameReturnsName() {
		$this->subject->setData(array('name' => 'foo bar'));

		$this->assertSame(
			'foo bar',
			$this->subject->getLastOrFullName()
		);
	}


	/*
	 * Tests concerning the date of birth
	 */

	/**
	 * @test
	 */
	public function getDateOfBirthReturnsZeroForNoDateSet() {
		$this->subject->setData(array());

		$this->assertSame(
			0,
			$this->subject->getDateOfBirth()
		);
	}

	/**
	 * @test
	 */
	public function getDateOfBirthReturnsDateFromDateOfBirthField() {
		// 1980-04-01
		$date = 323391600;
		$this->subject->setData(array('date_of_birth' => $date));

		$this->assertSame(
			$date,
			$this->subject->getDateOfBirth()
		);
	}

	/**
	 * @test
	 */
	public function hasDateOfBirthForNoDateOfBirthReturnsFalse() {
		$this->subject->setData(array());

		$this->assertFalse(
			$this->subject->hasDateOfBirth()
		);
	}

	/**
	 * @test
	 */
	public function hasDateOfBirthForNonZeroDateOfBirthReturnsTrue() {
		// 1980-04-01
		$date = 323391600;
		$this->subject->setData(array('date_of_birth' => $date));

		$this->assertTrue(
			$this->subject->hasDateOfBirth()
		);
	}


	/*
	 * Tests concerning getAge
	 */

	/**
	 * @test
	 */
	public function getAgeForNoDateOfBirthReturnsZero() {
		$this->subject->setData(array());

		$this->assertSame(
			0,
			$this->subject->getAge()
		);
	}

	/**
	 * @test
	 */
	public function getAgeForBornOneHourAgoReturnsZero() {
		$now = mktime(18, 0, 0, 9, 15, 2010);
		$GLOBALS['EXEC_TIME'] = $now;

		$this->subject->setData(
			array('date_of_birth' => $now - 60 * 60)
		);

		$this->assertSame(
			0,
			$this->subject->getAge()
		);
	}

	/**
	 * @test
	 */
	public function getAgeForAnAgeOfTenYearsAndSomeMonthsReturnsTen() {
		$GLOBALS['EXEC_TIME'] = mktime(18, 0, 0, 9, 15, 2010);

		$this->subject->setData(
			array('date_of_birth' => mktime(18, 0, 0, 1, 15, 2000))
		);

		$this->assertSame(
			10,
			$this->subject->getAge()
		);
	}

	/**
	 * @test
	 */
	public function getAgeForAnAgeOfTenYearsMinusSomeMonthsReturnsNine() {
		$GLOBALS['EXEC_TIME'] = mktime(18, 0, 0, 9, 15, 2010);

		$this->subject->setData(
			array('date_of_birth' => mktime(18, 0, 0, 11, 15, 2000))
		);

		$this->assertSame(
			9,
			$this->subject->getAge()
		);
	}

	/**
	 * @test
	 */
	public function getAgeForAnAgeOfTenYearsMinusSomeDaysReturnsNine() {
		$GLOBALS['EXEC_TIME'] = mktime(18, 0, 0, 9, 15, 2010);

		$this->subject->setData(
			array('date_of_birth' => mktime(18, 0, 0, 9, 21, 2000))
		);

		$this->assertSame(
			9,
			$this->subject->getAge()
		);
	}


	/*
	 * Tests concerning the date of the last login
	 */

	/**
	 * @test
	 */
	public function getLastLoginAsUnixTimestampReturnsZeroForNoDateSet() {
		$this->subject->setData(array());

		$this->assertSame(
			0,
			$this->subject->getLastLoginAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getLastLoginAsUnixTimestampReturnsDateFromLastLoginField() {
		// 1980-04-01
		$date = 323391600;
		$this->subject->setData(array('lastlogin' => $date));

		$this->assertSame(
			$date,
			$this->subject->getLastLoginAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function hasLastLoginForNoLastLoginReturnsFalse() {
		$this->subject->setData(array());

		$this->assertFalse(
			$this->subject->hasLastLogin()
		);
	}

	/**
	 * @test
	 */
	public function hasLastLoginForNonZeroLastLoginReturnsTrue() {
		// 1980-04-01
		$date = 323391600;
		$this->subject->setData(array('lastlogin' => $date));

		$this->assertTrue(
			$this->subject->hasLastLogin()
		);
	}


	/*
	 * Tests regarding the country
	 */

	/**
	 * @test
	 */
	public function getCountryWithoutCountryReturnsNull() {
		$this->subject->setData(array());

		$this->assertNull(
			$this->subject->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithInvalidCountryCodeReturnsNull() {
		$this->subject->setData(array('static_info_country' => 'xyz'));

		$this->assertNull(
			$this->subject->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithCountryReturnsCountryAsModel() {
		$country = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')
			->find(54);
		$this->subject->setData(
			array('static_info_country' => $country->getIsoAlpha3Code())
		);

		$this->assertSame(
			$country,
			$this->subject->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setCountrySetsCountry() {
		$country = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')
			->find(54);
		$this->subject->setCountry($country);

		$this->assertSame(
			$country,
			$this->subject->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function countryCanBeSetToNull() {
		$this->subject->setCountry(NULL);

		$this->assertNull(
			$this->subject->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithoutCountryReturnsFalse() {
		$this->subject->setData(array());

		$this->assertFalse(
			$this->subject->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithInvalidCountryReturnsFalse() {
		$this->subject->setData(array('static_info_country' => 'xyz'));

		$this->assertFalse(
			$this->subject->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithCountryReturnsTrue() {
		$country = Tx_Oelib_MapperRegistry::get('tx_oelib_Mapper_Country')
			->find(54);
		$this->subject->setCountry($country);

		$this->assertTrue(
			$this->subject->hasCountry()
		);
	}


	/*
	 * Tests concerning the job title
	 */

	/**
	 * @test
	 */
	public function hasJobTitleForEmptyJobTitleReturnsFalse() {
		$this->subject->setData(array('title' => ''));

		$this->assertFalse(
			$this->subject->hasJobTitle()
		);
	}

	/**
	 * @test
	 */
	public function hasJobTitleForNonEmptyJobTitleReturnsTrue() {
		$this->subject->setData(array('title' => 'facility manager'));

		$this->assertTrue(
			$this->subject->hasJobTitle()
		);
	}

	/**
	 * @test
	 */
	public function getJobTitleForEmptyJobTitleReturnsEmptyString() {
		$this->subject->setData(array('title' => ''));

		$this->assertSame(
			'',
			$this->subject->getJobTitle()
		);
	}

	/**
	 * @test
	 */
	public function getJobTitleForNonEmptyJobTitleReturnsJobTitle() {
		$this->subject->setData(array('title' => 'facility manager'));

		$this->assertSame(
			'facility manager',
			$this->subject->getJobTitle()
		);
	}

	/**
	 * @test
	 */
	public function setJobTitleSetsJobTitle() {
		$this->subject->setJobTitle('foo bar');

		$this->assertSame(
			'foo bar',
			$this->subject->getJobTitle()
		);
	}
}