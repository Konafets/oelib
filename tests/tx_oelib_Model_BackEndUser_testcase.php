<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Saskia Metzler <saskia@merlin.owl.de>
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
 * Testcase for the tx_oelib_Model_BackEndUser class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_oelib_Model_BackEndUser_testcase extends tx_phpunit_testcase {
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
}
?>