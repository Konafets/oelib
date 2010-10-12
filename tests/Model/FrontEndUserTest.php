<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the tx_oelib_Model_FrontEndUser class in the "oelib" extension.
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

	public function testHasNameForEmptyNameLastNameAndFirstNameReturnsFalse() {
		$this->fixture->setData(array(
			'name' => '',
			'first_name' => '',
			'last_name' => '',
		));

		$this->assertFalse(
			$this->fixture->hasName()
		);
	}

	public function testHasNameForNonEmptyUserReturnsFalse() {
		$this->fixture->setData(array(
			'username' => 'johndoe',
		));

		$this->assertFalse(
			$this->fixture->hasName()
		);
	}

	public function testHasNameForNonEmptyNameReturnsTrue() {
		$this->fixture->setData(array(
			'name' => 'John Doe',
			'first_name' => '',
			'last_name' => '',
		));

		$this->assertTrue(
			$this->fixture->hasName()
		);
	}

	public function testHasNameForNonEmptyFirstNameReturnsTrue() {
		$this->fixture->setData(array(
			'name' => '',
			'first_name' => 'John',
			'last_name' => '',
		));

		$this->assertTrue(
			$this->fixture->hasName()
		);
	}

	public function testHasNameForNonEmptyLastNameReturnsTrue() {
		$this->fixture->setData(array(
			'name' => '',
			'first_name' => '',
			'last_name' => 'Doe',
		));

		$this->assertTrue(
			$this->fixture->hasName()
		);
	}

	public function testGetNameForNonEmptyNameReturnsName() {
		$this->fixture->setData(array(
			'name' => 'John Doe',
		));

		$this->assertEquals(
			'John Doe',
			$this->fixture->getName()
		);
	}

	public function testGetNameForNonEmptyNameFirstNameAndLastNameReturnsName() {
		$this->fixture->setData(array(
			'name' => 'John Doe',
			'first_name' => 'Peter',
			'last_name' => 'Pan',
		));

		$this->assertEquals(
			'John Doe',
			$this->fixture->getName()
		);
	}

	public function testGetNameForEmptyNameAndNonEmptyFirstAndLastNameReturnsFirstAndLastName() {
		$this->fixture->setData(array(
			'name' => '',
			'first_name' => 'Peter',
			'last_name' => 'Pan',
		));

		$this->assertEquals(
			'Peter Pan',
			$this->fixture->getName()
		);
	}

	public function testGetNameForNonEmptyFirstAndLastNameAndNonEmptyUserNameReturnsFirstAndLastName() {
		$this->fixture->setData(array(
			'first_name' => 'Peter',
			'last_name' => 'Pan',
			'username' => 'johndoe',
		));

		$this->assertEquals(
			'Peter Pan',
			$this->fixture->getName()
		);
	}

	public function testGetNameForEmptyFirstNameAndNonEmptyLastAndUserNameReturnsLastName() {
		$this->fixture->setData(array(
			'first_name' => '',
			'last_name' => 'Pan',
			'username' => 'johndoe',
		));

		$this->assertEquals(
			'Pan',
			$this->fixture->getName()
		);
	}

	public function testGetNameForEmptyLastNameAndNonEmptyFirstAndUserNameReturnsFirstName() {
		$this->fixture->setData(array(
			'first_name' => 'Peter',
			'last_name' => '',
			'username' => 'johndoe',
		));

		$this->assertEquals(
			'Peter',
			$this->fixture->getName()
		);
	}

	public function testGetNameForEmptyFirstAndLastNameAndNonEmptyUserNameReturnsUserName() {
		$this->fixture->setData(array(
			'first_name' => '',
			'last_name' => '',
			'username' => 'johndoe',
		));

		$this->assertEquals(
			'johndoe',
			$this->fixture->getName()
		);
	}


	/////////////////////////////////////////
	// Tests concerning getting the company
	/////////////////////////////////////////

	public function testHasCompanyForEmptyCompanyReturnsFalse() {
		$this->fixture->setData(array('company' => ''));

		$this->assertFalse(
			$this->fixture->hasCompany()
		);
	}

	public function testHasCompanyForNonEmptyCompanyReturnsTrue() {
		$this->fixture->setData(array('company' => 'Test Inc.'));

		$this->assertTrue(
			$this->fixture->hasCompany()
		);
	}

	public function testGetCompanyForEmptyCompanyReturnsEmptyString() {
		$this->fixture->setData(array('company' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getCompany()
		);
	}

	public function testGetCompanyForNonEmptyCompanyReturnsCompany() {
		$this->fixture->setData(array('company' => 'Test Inc.'));

		$this->assertEquals(
			'Test Inc.',
			$this->fixture->getCompany()
		);
	}


	////////////////////////////////////////
	// Tests concerning getting the street
	////////////////////////////////////////

	public function testHasStreetForEmptyAddressReturnsFalse() {
		$this->fixture->setData(array('address' => ''));

		$this->assertFalse(
			$this->fixture->hasStreet()
		);
	}

	public function testHasStreetForNonEmptyAddressReturnsTrue() {
		$this->fixture->setData(array('address' => 'Foo street 1'));

		$this->assertTrue(
			$this->fixture->hasStreet()
		);
	}

	public function testGetStreetForEmptyAddressReturnsEmptyString() {
		$this->fixture->setData(array('address' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getStreet()
		);
	}

	public function testGetStreetForNonEmptyAddressReturnsAddress() {
		$this->fixture->setData(array('address' => 'Foo street 1'));

		$this->assertEquals(
			'Foo street 1',
			$this->fixture->getStreet()
		);
	}

	public function testGetStreetForMultilineAddressReturnsAddress() {
		$this->fixture->setData(array(
			'address' => 'Foo street 1' . LF . 'Floor 3'
		));

		$this->assertEquals(
			'Foo street 1' . LF . 'Floor 3',
			$this->fixture->getStreet()
		);
	}


	//////////////////////////////////////////
	// Tests concerning getting the ZIP code
	//////////////////////////////////////////

	public function testHasZipForEmptyZipReturnsFalse() {
		$this->fixture->setData(array('zip' => ''));

		$this->assertFalse(
			$this->fixture->hasZip()
		);
	}

	public function testHasZipForNonEmptyZipReturnsTrue() {
		$this->fixture->setData(array('zip' => '12345'));

		$this->assertTrue(
			$this->fixture->hasZip()
		);
	}

	public function testGetZipForEmptyZipReturnsEmptyString() {
		$this->fixture->setData(array('zip' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getZip()
		);
	}

	public function testGetZipForNonEmptyZipReturnsZip() {
		$this->fixture->setData(array('zip' => '12345'));

		$this->assertEquals(
			'12345',
			$this->fixture->getZip()
		);
	}


	//////////////////////////////////////
	// Tests concerning getting the city
	//////////////////////////////////////

	public function testHasCityForEmptyCityReturnsFalse() {
		$this->fixture->setData(array('city' => ''));

		$this->assertFalse(
			$this->fixture->hasCity()
		);
	}

	public function testHasCityForNonEmptyCityReturnsTrue() {
		$this->fixture->setData(array('city' => 'Test city'));

		$this->assertTrue(
			$this->fixture->hasCity()
		);
	}

	public function testGetCityForEmptyCityReturnsEmptyString() {
		$this->fixture->setData(array('city' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getCity()
		);
	}

	public function testGetCityForNonEmptyCityReturnsCity() {
		$this->fixture->setData(array('city' => 'Test city'));

		$this->assertEquals(
			'Test city',
			$this->fixture->getCity()
		);
	}

	public function testGetZipAndCityForNonEmptyZipAndCityReturnsZipAndCity() {
		$this->fixture->setData(array(
			'zip' => '12345',
			'city' => 'Test city',
		));

		$this->assertEquals(
			'12345 Test city',
			$this->fixture->getZipAndCity()
		);
	}

	public function testGetZipAndCityForEmptyZipAndNonEmptyCityReturnsCity() {
		$this->fixture->setData(array(
			'zip' => '',
			'city' => 'Test city',
		));

		$this->assertEquals(
			'Test city',
			$this->fixture->getZipAndCity()
		);
	}

	public function testZipAndGetCityForNonEmptyZipAndEmptyCityReturnsEmptyString() {
		$this->fixture->setData(array(
			'zip' => '12345',
			'city' => '',
		));

		$this->assertEquals(
			'',
			$this->fixture->getZipAndCity()
		);
	}

	public function testZipAndGetCityForEmptyZipAndCityReturnsEmptyString() {
		$this->fixture->setData(array(
			'zip' => '',
			'city' => '',
		));

		$this->assertEquals(
			'',
			$this->fixture->getZipAndCity()
		);
	}


	//////////////////////////////////////
	// Tests concerning getting the phone
	//////////////////////////////////////

	public function testHasPhoneNumberForEmptyPhoneReturnsFalse() {
		$this->fixture->setData(array('telephone' => ''));

		$this->assertFalse(
			$this->fixture->hasPhoneNumber()
		);
	}

	public function testHasPhoneNumberForNonEmptyPhoneReturnsTrue() {
		$this->fixture->setData(array('telephone' => '1234 5678'));

		$this->assertTrue(
			$this->fixture->hasPhoneNumber()
		);
	}

	public function testGetPhoneNumberForEmptyPhoneReturnsEmptyString() {
		$this->fixture->setData(array('telephone' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getPhoneNumber()
		);
	}

	public function testGetPhoneNumberForNonEmptyPhoneReturnsPhone() {
		$this->fixture->setData(array('telephone' => '1234 5678'));

		$this->assertEquals(
			'1234 5678',
			$this->fixture->getPhoneNumber()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning getting the e-mail address
	////////////////////////////////////////////////

	public function testHasEMailAddressForEmptyEMailReturnsFalse() {
		$this->fixture->setData(array('email' => ''));

		$this->assertFalse(
			$this->fixture->hasEMailAddress()
		);
	}

	public function testHasEMailAddressForNonEmptyEMailReturnsTrue() {
		$this->fixture->setData(array('email' => 'john@doe.com'));

		$this->assertTrue(
			$this->fixture->hasEMailAddress()
		);
	}

	public function testGetEMailAddressForEmptyEMailReturnsEmptyString() {
		$this->fixture->setData(array('email' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getEMailAddress()
		);
	}

	public function testGetEMailAddressForNonEmptyEMailReturnsEMail() {
		$this->fixture->setData(array('email' => 'john@doe.com'));

		$this->assertEquals(
			'john@doe.com',
			$this->fixture->getEMailAddress()
		);
	}


	//////////////////////////////////////////
	// Tests concerning getting the homepage
	//////////////////////////////////////////

	public function testHasHomepageForEmptyWwwReturnsFalse() {
		$this->fixture->setData(array('www' => ''));

		$this->assertFalse(
			$this->fixture->hasHomepage()
		);
	}

	public function testHasHomepageForNonEmptyWwwReturnsTrue() {
		$this->fixture->setData(array('www' => 'http://www.doe.com'));

		$this->assertTrue(
			$this->fixture->hasHomepage()
		);
	}

	public function testGetHomepageForEmptyWwwReturnsEmptyString() {
		$this->fixture->setData(array('www' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getHomepage()
		);
	}

	public function testGetHomepageForNonEmptyWwwReturnsWww() {
		$this->fixture->setData(array('www' => 'http://www.doe.com'));

		$this->assertEquals(
			'http://www.doe.com',
			$this->fixture->getHomepage()
		);
	}


	/////////////////////////////////////////
	// Tests concerning getting the picture
	/////////////////////////////////////////

	public function testHasImageForEmptyImageReturnsFalse() {
		$this->fixture->setData(array('image' => ''));

		$this->assertFalse(
			$this->fixture->hasImage()
		);
	}

	public function testHasImageForNonEmptyImageReturnsTrue() {
		$this->fixture->setData(array('image' => 'thats-me.jpg'));

		$this->assertTrue(
			$this->fixture->hasImage()
		);
	}

	public function testGetImageForEmptyImageReturnsEmptyString() {
		$this->fixture->setData(array('image' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getImage()
		);
	}

	public function testGetImageForNonEmptyImageReturnsImage() {
		$this->fixture->setData(array('image' => 'thats-me.jpg'));

		$this->assertEquals(
			'thats-me.jpg',
			$this->fixture->getImage()
		);
	}


	////////////////////////////////////
	// Tests concerning wantsHtmlEMail
	////////////////////////////////////

	public function test_WantsHtmlEMail_ForMissingModuleSysDmailHtmlField_ReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->wantsHtmlEMail()
		);
	}

	public function test_WantsHtmlEMail_ForModuleSysDmailHtmlOne_ReturnsTrue() {
		$this->fixture->setData(array('module_sys_dmail_html' => 1));

		$this->assertTrue(
			$this->fixture->wantsHtmlEMail()
		);
	}

	public function test_WantsHtmlEMail_ForModuleSysDmailHtmlZero_ReturnsFalse() {
		$this->fixture->setData(array('module_sys_dmail_html' => 0));

		$this->assertFalse(
			$this->fixture->wantsHtmlEMail()
		);
	}


	///////////////////////////////////////
	// Test concerning hasGroupMembership
	///////////////////////////////////////

	public function testHasGroupMembershipWithEmptyUidListThrowsException() {
		$this->setExpectedException(
			'Exception',
			'$uidList must not be empty.'
		);

		$this->fixture->hasGroupMembership('');
	}

	public function testHasGroupMembershipForUserOnlyInProvidedGroupReturnsTrue() {
		$userGroup = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_FrontEndUserGroup')->getNewGhost();
		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertTrue(
			$this->fixture->hasGroupMembership($userGroup->getUid())
		);
	}

	public function testHasGroupMembershipForUserInProvidedGroupAndInAnotherReturnsTrue() {
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

	public function testHasGroupMembershipForUserInOneOfTheProvidedGroupsReturnsTrue() {
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

	public function testHasGroupMembershipForUserNoneOfTheProvidedGroupsReturnsFalse() {
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

	public function test_getGender_ForNotInstalledSrFeUserRegister_ReturnsGenderUnknown() {
		if (t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
					'This test is only applicable if sr_feuser_register is ' .
						'not loaded.'
			);
		}

		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$this->fixture->getGender()
		);
	}

	public function test_getGender_ForGenderValueZero_ReturnsGenderMale() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
					'This test is only applicable if sr_feuser_register is ' .
						'loaded.'
			);
		}
		$this->fixture->setData(array('gender' => 0));

		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_MALE,
			$this->fixture->getGender()
		);
	}

	public function test_getGender_ForGenderValueOne_ReturnsGenderFemale() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
					'This test is only applicable if sr_feuser_register is ' .
						'loaded.'
			);
		}
		$this->fixture->setData(array('gender' => 1));

		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE,
			$this->fixture->getGender()
		);
	}


	////////////////////////////////////
	// Tests concerning the first name
	////////////////////////////////////

	public function test_hasFirstName_ForNoFirstNameSet_ReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasFirstName()
		);
	}

	public function test_hasFirstName_ForFirstNameSet_ReturnsTrue() {
		$this->fixture->setData(array('first_name' => 'foo'));

		$this->assertTrue(
			$this->fixture->hasFirstName()
		);
	}

	public function test_getFirstName_ForNoFirstNameSet_ReturnsEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getFirstName()
		);
	}

	public function test_getFirstName_ForFirstNameSet_ReturnsFirstName() {
		$this->fixture->setData(array('first_name' => 'foo'));

		$this->assertEquals(
			'foo',
			$this->fixture->getFirstName()
		);
	}

	public function test_getFirstOrFullName_ForUserWithFirstName_ReturnsFirstName() {
		$this->fixture->setData(
			array('first_name' => 'foo', 'name' => 'foo bar')
		);

		$this->assertEquals(
			'foo',
			$this->fixture->getFirstOrFullName()
		);
	}

	public function test_getFirstOrFullName_ForUserWithoutFirstName_ReturnsName() {
		$this->fixture->setData(array('name' => 'foo bar'));

		$this->assertEquals(
			'foo bar',
			$this->fixture->getFirstOrFullName()
		);
	}


	///////////////////////////////////
	// Tests concerning the last name
	///////////////////////////////////

	public function test_hasLastName_ForNoLastNameSet_ReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasLastName()
		);
	}

	public function test_hasLastName_ForLastNameSet_ReturnsTrue() {
		$this->fixture->setData(array('last_name' => 'bar'));

		$this->assertTrue(
			$this->fixture->hasLastName()
		);
	}

	public function test_getLastName_ForNoLastNameSet_ReturnsEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getLastName()
		);
	}

	public function test_getLastName_ForLastNameSet_ReturnsLastName() {
		$this->fixture->setData(array('last_name' => 'bar'));

		$this->assertEquals(
			'bar',
			$this->fixture->getLastName()
		);
	}

	public function test_getLastOrFullName_ForUserWithLastName_ReturnsLastName() {
		$this->fixture->setData(
			array('last_name' => 'bar', 'name' => 'foo bar')
		);

		$this->assertEquals(
			'bar',
			$this->fixture->getLastOrFullName()
		);
	}

	public function test_getLastOrFullName_ForUserWithoutLastName_ReturnsName() {
		$this->fixture->setData(array('name' => 'foo bar'));

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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
		$this->fixture->setCountry(null);

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

		$this->assertEquals(
			'',
			$this->fixture->getJobTitle()
		);
	}

	/**
	 * @test
	 */
	public function getJobTitleForNonEmptyJobTitleReturnsJobTitle() {
		$this->fixture->setData(array('title' => 'facility manager'));

		$this->assertEquals(
			'facility manager',
			$this->fixture->getJobTitle()
		);
	}
}
?>