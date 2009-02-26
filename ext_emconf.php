<?php

########################################################################
# Extension Manager/Repository config file for ext: "oelib"
#
# Auto generated 02-01-2009 17:07
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
	'dependencies' => '',
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
	'version' => '0.5.99',
	'_md5_values_when_last_written' => 'a:80:{s:13:"changelog.txt";s:4:"c16f";s:29:"class.tx_oelib_Autoloader.php";s:4:"fc9c";s:29:"class.tx_oelib_DataMapper.php";s:4:"9c99";s:36:"class.tx_oelib_FakeConfiguration.php";s:4:"e451";s:30:"class.tx_oelib_FakeSession.php";s:4:"500a";s:30:"class.tx_oelib_IdentityMap.php";s:4:"8b24";s:33:"class.tx_oelib_MapperRegistry.php";s:4:"8e40";s:24:"class.tx_oelib_Model.php";s:4:"35cc";s:25:"class.tx_oelib_Object.php";s:4:"8bfe";s:31:"class.tx_oelib_PublicObject.php";s:4:"2a6e";s:26:"class.tx_oelib_Session.php";s:4:"049f";s:27:"class.tx_oelib_Template.php";s:4:"c130";s:35:"class.tx_oelib_TemplateRegistry.php";s:4:"03c2";s:24:"class.tx_oelib_Timer.php";s:4:"0327";s:38:"class.tx_oelib_abstractHeaderProxy.php";s:4:"d904";s:33:"class.tx_oelib_abstractMailer.php";s:4:"9ba6";s:30:"class.tx_oelib_configcheck.php";s:4:"ec28";s:37:"class.tx_oelib_configurationProxy.php";s:4:"cd48";s:21:"class.tx_oelib_db.php";s:4:"5481";s:33:"class.tx_oelib_emailCollector.php";s:4:"cb40";s:34:"class.tx_oelib_headerCollector.php";s:4:"2939";s:37:"class.tx_oelib_headerProxyFactory.php";s:4:"a623";s:32:"class.tx_oelib_mailerFactory.php";s:4:"6642";s:34:"class.tx_oelib_realHeaderProxy.php";s:4:"bb28";s:29:"class.tx_oelib_realMailer.php";s:4:"b636";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"a9a4";s:33:"class.tx_oelib_templatehelper.php";s:4:"6d1a";s:35:"class.tx_oelib_testingFramework.php";s:4:"82a8";s:12:"ext_icon.gif";s:4:"b4bf";s:14:"ext_tables.php";s:4:"8a0e";s:14:"ext_tables.sql";s:4:"dfd8";s:22:"icon_tx_oelib_test.gif";s:4:"bd58";s:16:"locallang_db.xml";s:4:"c812";s:7:"tca.php";s:4:"0067";s:8:"todo.txt";s:4:"d400";s:28:"tx_oelib_commonConstants.php";s:4:"daae";s:45:"Mapper/class.tx_oelib_Mapper_FrontEndUser.php";s:4:"ad4d";s:51:"Exception/class.tx_oelib_Exception_AccessDenied.php";s:4:"0f15";s:47:"Exception/class.tx_oelib_Exception_NotFound.php";s:4:"ab50";s:44:"tests/class.tx_oelib_Autoloader_testcase.php";s:4:"b623";s:38:"tests/tx_oelib_DataMapper_testcase.php";s:4:"ab89";s:45:"tests/tx_oelib_FakeConfiguration_testcase.php";s:4:"f4e0";s:39:"tests/tx_oelib_FakeSession_testcase.php";s:4:"5f38";s:39:"tests/tx_oelib_IdentityMap_testcase.php";s:4:"d738";s:42:"tests/tx_oelib_MapperRegistry_testcase.php";s:4:"6d86";s:47:"tests/tx_oelib_Mapper_FrontEndUser_testcase.php";s:4:"2e04";s:46:"tests/tx_oelib_Model_FrontEndUser_testcase.php";s:4:"f818";s:33:"tests/tx_oelib_Model_testcase.php";s:4:"5eca";s:34:"tests/tx_oelib_Object_testcase.php";s:4:"df46";s:35:"tests/tx_oelib_Session_testcase.php";s:4:"dc8f";s:44:"tests/tx_oelib_TemplateRegistry_testcase.php";s:4:"c691";s:36:"tests/tx_oelib_Template_testcase.php";s:4:"af63";s:33:"tests/tx_oelib_Timer_testcase.php";s:4:"80aa";s:46:"tests/tx_oelib_configurationCheck_testcase.php";s:4:"376c";s:46:"tests/tx_oelib_configurationProxy_testcase.php";s:4:"09cb";s:30:"tests/tx_oelib_db_testcase.php";s:4:"73fb";s:46:"tests/tx_oelib_headerProxyFactory_testcase.php";s:4:"7839";s:41:"tests/tx_oelib_mailerFactory_testcase.php";s:4:"7eea";s:51:"tests/tx_oelib_salutationswitcherchild_testcase.php";s:4:"4447";s:47:"tests/tx_oelib_templatehelperchild_testcase.php";s:4:"4a74";s:44:"tests/tx_oelib_testingFramework_testcase.php";s:4:"2b9f";s:52:"tests/fixtures/class.tx_oelib_dummyObjectToCheck.php";s:4:"4301";s:57:"tests/fixtures/class.tx_oelib_salutationswitcherchild.php";s:4:"439f";s:53:"tests/fixtures/class.tx_oelib_templatehelperchild.php";s:4:"b649";s:47:"tests/fixtures/class.tx_oelib_testingObject.php";s:4:"8ddf";s:72:"tests/fixtures/class.tx_oelib_tests_fixtures_ColumnLessTestingMapper.php";s:4:"e1d5";s:71:"tests/fixtures/class.tx_oelib_tests_fixtures_ModelLessTestingMapper.php";s:4:"4eeb";s:60:"tests/fixtures/class.tx_oelib_tests_fixtures_NotIncluded.php";s:4:"319d";s:66:"tests/fixtures/class.tx_oelib_tests_fixtures_NotIncludedEither.php";s:4:"9d4c";s:71:"tests/fixtures/class.tx_oelib_tests_fixtures_TableLessTestingMapper.php";s:4:"d130";s:62:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingMapper.php";s:4:"1262";s:61:"tests/fixtures/class.tx_oelib_tests_fixtures_TestingModel.php";s:4:"d7c1";s:28:"tests/fixtures/locallang.xml";s:4:"c52b";s:25:"tests/fixtures/oelib.html";s:4:"59ca";s:23:"tests/fixtures/test.png";s:4:"c7b6";s:33:"tests/fixtures/user_oelibtest.t3x";s:4:"322c";s:34:"tests/fixtures/user_oelibtest2.t3x";s:4:"56c7";s:69:"tests/fixtures/pi1/class.tx_oelib_tests_fixtures_pi1_NotIncluded1.php";s:4:"d241";s:43:"Model/class.tx_oelib_Model_FrontEndUser.php";s:4:"6bbb";s:14:"doc/manual.sxw";s:4:"57db";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.2-0.0.0',
			'typo3' => '4.0.0-0.0.0',
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