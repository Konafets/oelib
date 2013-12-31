<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "oelib".
 *
 * Auto generated 31-12-2013 22:46
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'One is Enough Library',
	'description' => 'This extension provides useful stuff for extension development: helper functions for unit testing, templating, automatic configuration checks and performance benchmarking.',
	'category' => 'services',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'shy' => 0,
	'dependencies' => 'static_info_tables',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'be_users,be_groups,fe_groups,fe_users,pages,sys_template,tt_content',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'author_company' => '',
	'version' => '0.7.9',
	'_md5_values_when_last_written' => 'a:158:{s:13:"changelog.txt";s:4:"9092";s:38:"class.tx_oelib_abstractHeaderProxy.php";s:4:"70a3";s:33:"class.tx_oelib_abstractMailer.php";s:4:"b080";s:29:"class.tx_oelib_Attachment.php";s:4:"cb74";s:29:"class.tx_oelib_Autoloader.php";s:4:"761b";s:38:"class.tx_oelib_BackEndLoginManager.php";s:4:"5278";s:30:"class.tx_oelib_configcheck.php";s:4:"d82e";s:32:"class.tx_oelib_Configuration.php";s:4:"4679";s:37:"class.tx_oelib_configurationProxy.php";s:4:"8125";s:40:"class.tx_oelib_ConfigurationRegistry.php";s:4:"ac76";s:29:"class.tx_oelib_DataMapper.php";s:4:"3364";s:21:"class.tx_oelib_db.php";s:4:"5136";s:35:"class.tx_oelib_Double3Validator.php";s:4:"2826";s:33:"class.tx_oelib_emailCollector.php";s:4:"7de0";s:30:"class.tx_oelib_FakeSession.php";s:4:"1030";s:32:"class.tx_oelib_FileFunctions.php";s:4:"e1ee";s:39:"class.tx_oelib_FrontEndLoginManager.php";s:4:"aa7d";s:34:"class.tx_oelib_headerCollector.php";s:4:"51c9";s:37:"class.tx_oelib_headerProxyFactory.php";s:4:"d4f1";s:30:"class.tx_oelib_IdentityMap.php";s:4:"886c";s:23:"class.tx_oelib_List.php";s:4:"26e2";s:23:"class.tx_oelib_Mail.php";s:4:"d550";s:32:"class.tx_oelib_mailerFactory.php";s:4:"7f15";s:33:"class.tx_oelib_MapperRegistry.php";s:4:"1aae";s:24:"class.tx_oelib_Model.php";s:4:"7810";s:25:"class.tx_oelib_Object.php";s:4:"86a6";s:32:"class.tx_oelib_ObjectFactory.php";s:4:"96a8";s:29:"class.tx_oelib_PageFinder.php";s:4:"2dee";s:31:"class.tx_oelib_PublicObject.php";s:4:"a9d1";s:34:"class.tx_oelib_realHeaderProxy.php";s:4:"633a";s:29:"class.tx_oelib_realMailer.php";s:4:"a6c8";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"a526";s:26:"class.tx_oelib_Session.php";s:4:"0f31";s:27:"class.tx_oelib_Template.php";s:4:"700f";s:33:"class.tx_oelib_templatehelper.php";s:4:"a98c";s:35:"class.tx_oelib_TemplateRegistry.php";s:4:"e256";s:35:"class.tx_oelib_testingFramework.php";s:4:"2221";s:24:"class.tx_oelib_Timer.php";s:4:"1397";s:29:"class.tx_oelib_Translator.php";s:4:"ec7a";s:37:"class.tx_oelib_TranslatorRegistry.php";s:4:"cb23";s:16:"ext_autoload.php";s:4:"46d7";s:12:"ext_icon.gif";s:4:"b4bf";s:17:"ext_localconf.php";s:4:"1793";s:14:"ext_tables.php";s:4:"4384";s:14:"ext_tables.sql";s:4:"34e8";s:22:"icon_tx_oelib_test.gif";s:4:"bd58";s:16:"locallang_db.xml";s:4:"a70b";s:7:"tca.php";s:4:"9877";s:8:"todo.txt";s:4:"d400";s:28:"tx_oelib_commonConstants.php";s:4:"0182";s:51:"Exception/class.tx_oelib_Exception_AccessDenied.php";s:4:"369f";s:47:"Exception/class.tx_oelib_Exception_Database.php";s:4:"cf4d";s:55:"Exception/class.tx_oelib_Exception_EmptyQueryResult.php";s:4:"3ecd";s:47:"Exception/class.tx_oelib_Exception_NotFound.php";s:4:"693e";s:46:"Interface/class.tx_oelib_Interface_Address.php";s:4:"6250";s:51:"Interface/class.tx_oelib_Interface_LoginManager.php";s:4:"ab27";s:47:"Interface/class.tx_oelib_Interface_MailRole.php";s:4:"d968";s:44:"Mapper/class.tx_oelib_Mapper_BackEndUser.php";s:4:"8213";s:49:"Mapper/class.tx_oelib_Mapper_BackEndUserGroup.php";s:4:"779e";s:40:"Mapper/class.tx_oelib_Mapper_Country.php";s:4:"09b2";s:41:"Mapper/class.tx_oelib_Mapper_Currency.php";s:4:"d001";s:45:"Mapper/class.tx_oelib_Mapper_FrontEndUser.php";s:4:"bcd1";s:50:"Mapper/class.tx_oelib_Mapper_FrontEndUserGroup.php";s:4:"c900";s:41:"Mapper/class.tx_oelib_Mapper_Language.php";s:4:"15c4";s:42:"Model/class.tx_oelib_Model_BackEndUser.php";s:4:"0cde";s:47:"Model/class.tx_oelib_Model_BackEndUserGroup.php";s:4:"067f";s:38:"Model/class.tx_oelib_Model_Country.php";s:4:"c12e";s:39:"Model/class.tx_oelib_Model_Currency.php";s:4:"066b";s:43:"Model/class.tx_oelib_Model_FrontEndUser.php";s:4:"5298";s:48:"Model/class.tx_oelib_Model_FrontEndUserGroup.php";s:4:"eacc";s:39:"Model/class.tx_oelib_Model_Language.php";s:4:"17dc";s:40:"Resources/Private/Language/locallang.xml";s:4:"0494";s:46:"ViewHelper/class.tx_oelib_ViewHelper_Price.php";s:4:"b417";s:45:"Visibility/class.tx_oelib_Visibility_Node.php";s:4:"1932";s:45:"Visibility/class.tx_oelib_Visibility_Tree.php";s:4:"cdba";s:21:"contrib/PEAR/PEAR.php";s:4:"9e9a";s:22:"contrib/PEAR/PEAR5.php";s:4:"2107";s:26:"contrib/PEAR/Mail/mime.php";s:4:"94a3";s:30:"contrib/PEAR/Mail/mimePart.php";s:4:"c575";s:33:"contrib/emogrifier/emogrifier.php";s:4:"f03e";s:30:"contrib/emogrifier/LICENSE.TXT";s:4:"8403";s:14:"doc/manual.sxw";s:4:"e2d1";s:38:"tests/tx_oelib_Attachment_testcase.php";s:4:"6a3e";s:38:"tests/tx_oelib_Autoloader_testcase.php";s:4:"27b3";s:47:"tests/tx_oelib_BackEndLoginManager_testcase.php";s:4:"a721";s:41:"tests/tx_oelib_Configuration_testcase.php";s:4:"4ea4";s:46:"tests/tx_oelib_configurationCheck_testcase.php";s:4:"6a51";s:46:"tests/tx_oelib_configurationProxy_testcase.php";s:4:"434e";s:49:"tests/tx_oelib_ConfigurationRegistry_testcase.php";s:4:"7a85";s:38:"tests/tx_oelib_DataMapper_testcase.php";s:4:"6007";s:30:"tests/tx_oelib_db_testcase.php";s:4:"c896";s:44:"tests/tx_oelib_Double3Validator_testcase.php";s:4:"c20e";s:46:"tests/tx_oelib_Exception_Database_testcase.php";s:4:"1e4e";s:54:"tests/tx_oelib_Exception_EmptyQueryResult_testcase.php";s:4:"3d9f";s:39:"tests/tx_oelib_FakeSession_testcase.php";s:4:"4582";s:48:"tests/tx_oelib_FrontEndLoginManager_testcase.php";s:4:"5ae1";s:46:"tests/tx_oelib_headerProxyFactory_testcase.php";s:4:"d534";s:39:"tests/tx_oelib_IdentityMap_testcase.php";s:4:"3225";s:32:"tests/tx_oelib_List_testcase.php";s:4:"a7ac";s:32:"tests/tx_oelib_Mail_testcase.php";s:4:"f9b0";s:41:"tests/tx_oelib_mailerFactory_testcase.php";s:4:"731e";s:46:"tests/tx_oelib_Mapper_BackEndUser_testcase.php";s:4:"c689";s:51:"tests/tx_oelib_Mapper_BackEndUserGroup_testcase.php";s:4:"379b";s:42:"tests/tx_oelib_Mapper_Country_testcase.php";s:4:"a9d3";s:43:"tests/tx_oelib_Mapper_Currency_testcase.php";s:4:"dfd0";s:47:"tests/tx_oelib_Mapper_FrontEndUser_testcase.php";s:4:"b52e";s:52:"tests/tx_oelib_Mapper_FrontEndUserGroup_testcase.php";s:4:"b7c0";s:43:"tests/tx_oelib_Mapper_Language_testcase.php";s:4:"23d4";s:42:"tests/tx_oelib_MapperRegistry_testcase.php";s:4:"6c3c";s:45:"tests/tx_oelib_Model_BackEndUser_testcase.php";s:4:"b646";s:50:"tests/tx_oelib_Model_BackEndUserGroup_testcase.php";s:4:"c3fb";s:41:"tests/tx_oelib_Model_Country_testcase.php";s:4:"151d";s:42:"tests/tx_oelib_Model_Currency_testcase.php";s:4:"daf8";s:46:"tests/tx_oelib_Model_FrontEndUser_testcase.php";s:4:"7091";s:51:"tests/tx_oelib_Model_FrontEndUserGroup_testcase.php";s:4:"dd1e";s:42:"tests/tx_oelib_Model_Language_testcase.php";s:4:"b5da";s:33:"tests/tx_oelib_Model_testcase.php";s:4:"7fa2";s:34:"tests/tx_oelib_Object_testcase.php";s:4:"6d98";s:41:"tests/tx_oelib_ObjectFactory_testcase.php";s:4:"650b";s:38:"tests/tx_oelib_PageFinder_testcase.php";s:4:"8962";s:38:"tests/tx_oelib_phpmyadmin_testcase.php";s:4:"947c";s:51:"tests/tx_oelib_salutationswitcherchild_testcase.php";s:4:"5960";s:35:"tests/tx_oelib_Session_testcase.php";s:4:"bbf2";s:36:"tests/tx_oelib_Template_testcase.php";s:4:"ca36";s:47:"tests/tx_oelib_templatehelperchild_testcase.php";s:4:"56d6";s:44:"tests/tx_oelib_TemplateRegistry_testcase.php";s:4:"28ad";s:44:"tests/tx_oelib_testingFramework_testcase.php";s:4:"9561";s:33:"tests/tx_oelib_Timer_testcase.php";s:4:"fc27";s:38:"tests/tx_oelib_Translator_testcase.php";s:4:"1c3b";s:46:"tests/tx_oelib_TranslatorRegistry_testcase.php";s:4:"17e4";s:44:"tests/tx_oelib_ViewHelper_Price_testcase.php";s:4:"f10e";s:43:"tests/tx_oelib_Visibility_Node_testcase.php";s:4:"ec38";s:43:"tests/tx_oelib_Visibility_Tree_testcase.php";s:4:"8049";s:52:"tests/fixtures/class.tx_oelib_dummyObjectToCheck.php";s:4:"5f39";s:57:"tests/fixtures/class.tx_oelib_salutationswitcherchild.php";s:4:"08af";s:53:"tests/fixtures/class.tx_oelib_templatehelperchild.php";s:4:"d62f";s:47:"tests/fixtures/class.tx_oelib_testingObject.php";s:4:"f89e";s:72:"tests/fixtures/class.tx_oelib_tests_fixtures_ColumnLessTestingMapper.php";s:4:"9ef3";s:54:"tests/fixtures/class.tx_oelib_tests_fixtures_Empty.php";s:4:"c6b4";s:71:"tests/fixtures/class.tx_oelib_tests_fixtures_ModelLessTestingMapper.php";s:4:"7b42";s:60:"tests/fixtures/class.tx_oelib_tests_fixtures_NotIncluded.php";s:4:"b8c6";s:66:"tests/fixtures/class.tx_oelib_tests_fixtures_NotIncludedEither.php";s:4:"48d7";s:62:"tests/fixtures/class.tx_oelib_tests_fixtures_ReadOnlyModel.php";s:4:"a0e5";s:71:"tests/fixtures/class.tx_oelib_tests_fixtures_TableLessTestingMapper.php";s:4:"7763";s:67:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingChildMapper.php";s:4:"52ac";s:66:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingChildModel.php";s:4:"bdd7";s:64:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingMailRole.php";s:4:"e2c0";s:62:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingMapper.php";s:4:"ff2c";s:61:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingModel.php";s:4:"7687";s:28:"tests/fixtures/locallang.xml";s:4:"c52b";s:25:"tests/fixtures/oelib.html";s:4:"59ca";s:23:"tests/fixtures/test.css";s:4:"0acf";s:23:"tests/fixtures/test.png";s:4:"c7b6";s:25:"tests/fixtures/test_2.css";s:4:"4a4a";s:33:"tests/fixtures/user_oelibtest.t3x";s:4:"322c";s:34:"tests/fixtures/user_oelibtest2.t3x";s:4:"56c7";s:69:"tests/fixtures/pi1/class.tx_oelib_tests_fixtures_pi1_NotIncluded1.php";s:4:"34d7";s:64:"tests/fixtures/xclass/class.ux_tx_oelib_tests_fixtures_Empty.php";s:4:"530f";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.2.0-4.7.99',
			'static_info_tables' => '2.0.8-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>