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
 * Testcase for the tx_oelib_Model_FrontEndUser class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Model_FrontEndUser_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Model_FrontEndUser
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_Model_FrontEndUser();
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
}
?>