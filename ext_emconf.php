<?php

########################################################################
# Extension Manager/Repository config file for ext: "oelib"
#
# Auto generated 13-08-2009 17:14
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

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
	'modify_tables' => 'be_users,cache_pages,fe_groups,fe_users,pages,sys_template,tt_content,user_oelibtest_test,user_oelibtest_test_article_mm',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'author_company' => '',
	'version' => '0.6.60',
	'_md5_values_when_last_written' => 'a:141:{s:13:"changelog.txt";s:4:"4421";s:29:"class.tx_oelib_Attachment.php";s:4:"e330";s:29:"class.tx_oelib_Autoloader.php";s:4:"75b7";s:38:"class.tx_oelib_BackEndLoginManager.php";s:4:"345f";s:32:"class.tx_oelib_Configuration.php";s:4:"41c3";s:40:"class.tx_oelib_ConfigurationRegistry.php";s:4:"fab8";s:29:"class.tx_oelib_DataMapper.php";s:4:"80e7";s:30:"class.tx_oelib_FakeSession.php";s:4:"a19d";s:32:"class.tx_oelib_FileFunctions.php";s:4:"a194";s:39:"class.tx_oelib_FrontEndLoginManager.php";s:4:"bf8c";s:30:"class.tx_oelib_IdentityMap.php";s:4:"9df3";s:23:"class.tx_oelib_List.php";s:4:"e6bf";s:23:"class.tx_oelib_Mail.php";s:4:"a2bf";s:33:"class.tx_oelib_MapperRegistry.php";s:4:"0e28";s:24:"class.tx_oelib_Model.php";s:4:"8462";s:25:"class.tx_oelib_Object.php";s:4:"bb8f";s:32:"class.tx_oelib_ObjectFactory.php";s:4:"8851";s:29:"class.tx_oelib_PageFinder.php";s:4:"cb7d";s:31:"class.tx_oelib_PublicObject.php";s:4:"be1f";s:26:"class.tx_oelib_Session.php";s:4:"29fa";s:27:"class.tx_oelib_Template.php";s:4:"fe9c";s:35:"class.tx_oelib_TemplateRegistry.php";s:4:"875c";s:24:"class.tx_oelib_Timer.php";s:4:"370b";s:29:"class.tx_oelib_Translator.php";s:4:"0602";s:37:"class.tx_oelib_TranslatorRegistry.php";s:4:"93be";s:38:"class.tx_oelib_abstractHeaderProxy.php";s:4:"d904";s:33:"class.tx_oelib_abstractMailer.php";s:4:"3356";s:30:"class.tx_oelib_configcheck.php";s:4:"6b74";s:37:"class.tx_oelib_configurationProxy.php";s:4:"43b0";s:21:"class.tx_oelib_db.php";s:4:"82a6";s:33:"class.tx_oelib_emailCollector.php";s:4:"66a5";s:34:"class.tx_oelib_headerCollector.php";s:4:"7e6b";s:37:"class.tx_oelib_headerProxyFactory.php";s:4:"aa3f";s:32:"class.tx_oelib_mailerFactory.php";s:4:"3747";s:34:"class.tx_oelib_realHeaderProxy.php";s:4:"3984";s:29:"class.tx_oelib_realMailer.php";s:4:"488b";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"0adc";s:33:"class.tx_oelib_templatehelper.php";s:4:"9b5d";s:35:"class.tx_oelib_testingFramework.php";s:4:"4d57";s:12:"ext_icon.gif";s:4:"b4bf";s:14:"ext_tables.php";s:4:"2297";s:14:"ext_tables.sql";s:4:"0a06";s:22:"icon_tx_oelib_test.gif";s:4:"bd58";s:16:"locallang_db.xml";s:4:"a70b";s:7:"tca.php";s:4:"628c";s:8:"todo.txt";s:4:"d400";s:28:"tx_oelib_commonConstants.php";s:4:"daae";s:40:"Resources/Private/Language/locallang.xml";s:4:"0494";s:21:"contrib/PEAR/PEAR.php";s:4:"8092";s:26:"contrib/PEAR/Mail/mime.php";s:4:"46d7";s:30:"contrib/PEAR/Mail/mimePart.php";s:4:"197a";s:33:"contrib/emogrifier/emogrifier.php";s:4:"4442";s:30:"contrib/emogrifier/license.txt";s:4:"3555";s:44:"Mapper/class.tx_oelib_Mapper_BackEndUser.php";s:4:"d4d6";s:40:"Mapper/class.tx_oelib_Mapper_Country.php";s:4:"0ab3";s:45:"Mapper/class.tx_oelib_Mapper_FrontEndUser.php";s:4:"bba1";s:50:"Mapper/class.tx_oelib_Mapper_FrontEndUserGroup.php";s:4:"aa74";s:41:"Mapper/class.tx_oelib_Mapper_Language.php";s:4:"b9f9";s:46:"Interface/class.tx_oelib_Interface_Address.php";s:4:"7c18";s:51:"Interface/class.tx_oelib_Interface_LoginManager.php";s:4:"4d6a";s:47:"Interface/class.tx_oelib_Interface_MailRole.php";s:4:"5811";s:51:"Exception/class.tx_oelib_Exception_AccessDenied.php";s:4:"0f15";s:47:"Exception/class.tx_oelib_Exception_Database.php";s:4:"6923";s:55:"Exception/class.tx_oelib_Exception_EmptyQueryResult.php";s:4:"8546";s:47:"Exception/class.tx_oelib_Exception_NotFound.php";s:4:"ab50";s:38:"tests/tx_oelib_Attachment_testcase.php";s:4:"ea6e";s:38:"tests/tx_oelib_Autoloader_testcase.php";s:4:"3105";s:47:"tests/tx_oelib_BackEndLoginManager_testcase.php";s:4:"a8c0";s:49:"tests/tx_oelib_ConfigurationRegistry_testcase.php";s:4:"8cc8";s:41:"tests/tx_oelib_Configuration_testcase.php";s:4:"ac11";s:38:"tests/tx_oelib_DataMapper_testcase.php";s:4:"ea69";s:46:"tests/tx_oelib_Exception_Database_testcase.php";s:4:"fb51";s:54:"tests/tx_oelib_Exception_EmptyQueryResult_testcase.php";s:4:"c1a3";s:39:"tests/tx_oelib_FakeSession_testcase.php";s:4:"130b";s:48:"tests/tx_oelib_FrontEndLoginManager_testcase.php";s:4:"9ec7";s:39:"tests/tx_oelib_IdentityMap_testcase.php";s:4:"c955";s:32:"tests/tx_oelib_List_testcase.php";s:4:"d215";s:32:"tests/tx_oelib_Mail_testcase.php";s:4:"1c16";s:42:"tests/tx_oelib_MapperRegistry_testcase.php";s:4:"a620";s:46:"tests/tx_oelib_Mapper_BackEndUser_testcase.php";s:4:"6138";s:42:"tests/tx_oelib_Mapper_Country_testcase.php";s:4:"ebc6";s:52:"tests/tx_oelib_Mapper_FrontEndUserGroup_testcase.php";s:4:"3b70";s:47:"tests/tx_oelib_Mapper_FrontEndUser_testcase.php";s:4:"a0cc";s:43:"tests/tx_oelib_Mapper_Language_testcase.php";s:4:"5635";s:45:"tests/tx_oelib_Model_BackEndUser_testcase.php";s:4:"4559";s:41:"tests/tx_oelib_Model_Country_testcase.php";s:4:"896c";s:51:"tests/tx_oelib_Model_FrontEndUserGroup_testcase.php";s:4:"0f4a";s:46:"tests/tx_oelib_Model_FrontEndUser_testcase.php";s:4:"b25c";s:42:"tests/tx_oelib_Model_Language_testcase.php";s:4:"51b0";s:33:"tests/tx_oelib_Model_testcase.php";s:4:"c28e";s:41:"tests/tx_oelib_ObjectFactory_testcase.php";s:4:"306c";s:34:"tests/tx_oelib_Object_testcase.php";s:4:"df46";s:38:"tests/tx_oelib_PageFinder_testcase.php";s:4:"8a39";s:35:"tests/tx_oelib_Session_testcase.php";s:4:"6dc0";s:44:"tests/tx_oelib_TemplateRegistry_testcase.php";s:4:"c691";s:36:"tests/tx_oelib_Template_testcase.php";s:4:"2ded";s:33:"tests/tx_oelib_Timer_testcase.php";s:4:"80aa";s:46:"tests/tx_oelib_TranslatorRegistry_testcase.php";s:4:"61df";s:38:"tests/tx_oelib_Translator_testcase.php";s:4:"c632";s:43:"tests/tx_oelib_Visibility_Node_testcase.php";s:4:"d6f5";s:43:"tests/tx_oelib_Visibility_Tree_testcase.php";s:4:"6154";s:46:"tests/tx_oelib_configurationCheck_testcase.php";s:4:"8ac2";s:46:"tests/tx_oelib_configurationProxy_testcase.php";s:4:"c10c";s:30:"tests/tx_oelib_db_testcase.php";s:4:"e666";s:46:"tests/tx_oelib_headerProxyFactory_testcase.php";s:4:"54b7";s:41:"tests/tx_oelib_mailerFactory_testcase.php";s:4:"ee87";s:38:"tests/tx_oelib_phpmyadmin_testcase.php";s:4:"949e";s:51:"tests/tx_oelib_salutationswitcherchild_testcase.php";s:4:"6a38";s:47:"tests/tx_oelib_templatehelperchild_testcase.php";s:4:"1d56";s:44:"tests/tx_oelib_testingFramework_testcase.php";s:4:"0f11";s:52:"tests/fixtures/class.tx_oelib_dummyObjectToCheck.php";s:4:"430d";s:57:"tests/fixtures/class.tx_oelib_salutationswitcherchild.php";s:4:"4a7b";s:53:"tests/fixtures/class.tx_oelib_templatehelperchild.php";s:4:"ae17";s:47:"tests/fixtures/class.tx_oelib_testingObject.php";s:4:"b9c1";s:72:"tests/fixtures/class.tx_oelib_tests_fixtures_ColumnLessTestingMapper.php";s:4:"4546";s:71:"tests/fixtures/class.tx_oelib_tests_fixtures_ModelLessTestingMapper.php";s:4:"06f6";s:60:"tests/fixtures/class.tx_oelib_tests_fixtures_NotIncluded.php";s:4:"8162";s:66:"tests/fixtures/class.tx_oelib_tests_fixtures_NotIncludedEither.php";s:4:"ea31";s:62:"tests/fixtures/class.tx_oelib_tests_fixtures_ReadOnlyModel.php";s:4:"38df";s:71:"tests/fixtures/class.tx_oelib_tests_fixtures_TableLessTestingMapper.php";s:4:"a262";s:67:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingChildMapper.php";s:4:"27cd";s:66:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingChildModel.php";s:4:"4fa0";s:64:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingMailRole.php";s:4:"9e65";s:62:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingMapper.php";s:4:"cab8";s:61:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingModel.php";s:4:"c4b5";s:28:"tests/fixtures/locallang.xml";s:4:"c52b";s:25:"tests/fixtures/oelib.html";s:4:"59ca";s:23:"tests/fixtures/test.css";s:4:"0acf";s:23:"tests/fixtures/test.png";s:4:"c7b6";s:25:"tests/fixtures/test_2.css";s:4:"4a4a";s:33:"tests/fixtures/user_oelibtest.t3x";s:4:"322c";s:34:"tests/fixtures/user_oelibtest2.t3x";s:4:"56c7";s:69:"tests/fixtures/pi1/class.tx_oelib_tests_fixtures_pi1_NotIncluded1.php";s:4:"4c4f";s:45:"Visibility/class.tx_oelib_Visibility_Node.php";s:4:"78eb";s:45:"Visibility/class.tx_oelib_Visibility_Tree.php";s:4:"ebf1";s:42:"Model/class.tx_oelib_Model_BackEndUser.php";s:4:"f357";s:38:"Model/class.tx_oelib_Model_Country.php";s:4:"be4b";s:43:"Model/class.tx_oelib_Model_FrontEndUser.php";s:4:"ce17";s:48:"Model/class.tx_oelib_Model_FrontEndUserGroup.php";s:4:"5d00";s:39:"Model/class.tx_oelib_Model_Language.php";s:4:"8257";s:14:"doc/manual.sxw";s:4:"ac10";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.2.0-0.0.0',
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