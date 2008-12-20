<?php

########################################################################
# Extension Manager/Repository config file for ext: "oelib"
#
# Auto generated 21-12-2008 00:36
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
	'modify_tables' => 'cache_pages,fe_groups,fe_users,pages,sys_template,tt_content,user_oelibtest_test,user_oelibtest_test_article_mm',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'author_company' => '',
	'version' => '0.5.0',
	'_md5_values_when_last_written' => 'a:78:{s:13:"changelog.txt";s:4:"fc7f";s:29:"class.tx_oelib_Autoloader.php";s:4:"3e73";s:29:"class.tx_oelib_DataMapper.php";s:4:"02f9";s:36:"class.tx_oelib_FakeConfiguration.php";s:4:"7774";s:30:"class.tx_oelib_FakeSession.php";s:4:"28ea";s:30:"class.tx_oelib_IdentityMap.php";s:4:"0c50";s:33:"class.tx_oelib_MapperRegistry.php";s:4:"0ceb";s:24:"class.tx_oelib_Model.php";s:4:"8fec";s:25:"class.tx_oelib_Object.php";s:4:"20db";s:31:"class.tx_oelib_PublicObject.php";s:4:"5662";s:26:"class.tx_oelib_Session.php";s:4:"645a";s:27:"class.tx_oelib_Template.php";s:4:"938a";s:35:"class.tx_oelib_TemplateRegistry.php";s:4:"a641";s:24:"class.tx_oelib_Timer.php";s:4:"b59f";s:38:"class.tx_oelib_abstractHeaderProxy.php";s:4:"6dee";s:33:"class.tx_oelib_abstractMailer.php";s:4:"9bc8";s:30:"class.tx_oelib_configcheck.php";s:4:"250f";s:37:"class.tx_oelib_configurationProxy.php";s:4:"40a6";s:21:"class.tx_oelib_db.php";s:4:"ab0c";s:33:"class.tx_oelib_emailCollector.php";s:4:"fbde";s:34:"class.tx_oelib_headerCollector.php";s:4:"dda2";s:37:"class.tx_oelib_headerProxyFactory.php";s:4:"2b39";s:32:"class.tx_oelib_mailerFactory.php";s:4:"4478";s:34:"class.tx_oelib_realHeaderProxy.php";s:4:"90fe";s:29:"class.tx_oelib_realMailer.php";s:4:"b69a";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"61cd";s:33:"class.tx_oelib_templatehelper.php";s:4:"3ddf";s:35:"class.tx_oelib_testingFramework.php";s:4:"fb4e";s:12:"ext_icon.gif";s:4:"b4bf";s:14:"ext_tables.php";s:4:"8a0e";s:14:"ext_tables.sql";s:4:"dfd8";s:22:"icon_tx_oelib_test.gif";s:4:"bd58";s:16:"locallang_db.xml";s:4:"c812";s:7:"tca.php";s:4:"0067";s:8:"todo.txt";s:4:"d400";s:28:"tx_oelib_commonConstants.php";s:4:"44b4";s:45:"Mapper/class.tx_oelib_Mapper_FrontEndUser.php";s:4:"4bf9";s:40:"Mapper/class.tx_oelib_Mapper_Testing.php";s:4:"c0c7";s:47:"Exception/class.tx_oelib_Exception_NotFound.php";s:4:"19df";s:44:"tests/class.tx_oelib_Autoloader_testcase.php";s:4:"6d8d";s:38:"tests/tx_oelib_DataMapper_testcase.php";s:4:"7ad9";s:45:"tests/tx_oelib_FakeConfiguration_testcase.php";s:4:"699d";s:39:"tests/tx_oelib_FakeSession_testcase.php";s:4:"5b1e";s:39:"tests/tx_oelib_IdentityMap_testcase.php";s:4:"0c21";s:42:"tests/tx_oelib_MapperRegistry_testcase.php";s:4:"9cd3";s:47:"tests/tx_oelib_Mapper_FrontEndUser_testcase.php";s:4:"658b";s:46:"tests/tx_oelib_Model_FrontEndUser_testcase.php";s:4:"cd0d";s:33:"tests/tx_oelib_Model_testcase.php";s:4:"218d";s:34:"tests/tx_oelib_Object_testcase.php";s:4:"9033";s:35:"tests/tx_oelib_Session_testcase.php";s:4:"343a";s:44:"tests/tx_oelib_TemplateRegistry_testcase.php";s:4:"9e6a";s:36:"tests/tx_oelib_Template_testcase.php";s:4:"dc3b";s:33:"tests/tx_oelib_Timer_testcase.php";s:4:"d752";s:46:"tests/tx_oelib_configurationCheck_testcase.php";s:4:"9721";s:46:"tests/tx_oelib_configurationProxy_testcase.php";s:4:"df40";s:30:"tests/tx_oelib_db_testcase.php";s:4:"b65b";s:46:"tests/tx_oelib_headerProxyFactory_testcase.php";s:4:"96a7";s:41:"tests/tx_oelib_mailerFactory_testcase.php";s:4:"0595";s:51:"tests/tx_oelib_salutationswitcherchild_testcase.php";s:4:"abc6";s:47:"tests/tx_oelib_templatehelperchild_testcase.php";s:4:"9499";s:44:"tests/tx_oelib_testingFramework_testcase.php";s:4:"f2b8";s:63:"tests/fixtures/class.tx_oelib_brokenColumnLessTestingMapper.php";s:4:"bbe3";s:62:"tests/fixtures/class.tx_oelib_brokenTableLessTestingMapper.php";s:4:"06c0";s:52:"tests/fixtures/class.tx_oelib_dummyObjectToCheck.php";s:4:"b2c6";s:57:"tests/fixtures/class.tx_oelib_salutationswitcherchild.php";s:4:"d1a4";s:53:"tests/fixtures/class.tx_oelib_templatehelperchild.php";s:4:"0dac";s:47:"tests/fixtures/class.tx_oelib_testingObject.php";s:4:"8258";s:60:"tests/fixtures/class.tx_oelib_tests_fixtures_NotIncluded.php";s:4:"089a";s:66:"tests/fixtures/class.tx_oelib_tests_fixtures_NotIncludedEither.php";s:4:"8d14";s:28:"tests/fixtures/locallang.xml";s:4:"c52b";s:25:"tests/fixtures/oelib.html";s:4:"59ca";s:23:"tests/fixtures/test.png";s:4:"c7b6";s:33:"tests/fixtures/user_oelibtest.t3x";s:4:"322c";s:34:"tests/fixtures/user_oelibtest2.t3x";s:4:"56c7";s:69:"tests/fixtures/pi1/class.tx_oelib_tests_fixtures_pi1_NotIncluded1.php";s:4:"b9a2";s:43:"Model/class.tx_oelib_Model_FrontEndUser.php";s:4:"468e";s:38:"Model/class.tx_oelib_Model_Testing.php";s:4:"1436";s:14:"doc/manual.sxw";s:4:"30aa";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.2-0.0.0',
			'typo3' => '4.0.0-0.0.0',
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