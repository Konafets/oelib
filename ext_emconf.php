<?php

########################################################################
# Extension Manager/Repository config file for ext: "oelib"
#
# Auto generated 04-11-2008 11:45
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
	'version' => '0.4.3',
	'_md5_values_when_last_written' => 'a:69:{s:13:"changelog.txt";s:4:"ff2d";s:38:"class.tx_oelib_abstractHeaderProxy.php";s:4:"6dee";s:33:"class.tx_oelib_abstractMailer.php";s:4:"9bc8";s:30:"class.tx_oelib_configcheck.php";s:4:"1eb1";s:37:"class.tx_oelib_configurationProxy.php";s:4:"40a6";s:29:"class.tx_oelib_dataMapper.php";s:4:"56d6";s:21:"class.tx_oelib_db.php";s:4:"ab0c";s:33:"class.tx_oelib_emailCollector.php";s:4:"3377";s:36:"class.tx_oelib_fakeConfiguration.php";s:4:"d3d8";s:30:"class.tx_oelib_fakeSession.php";s:4:"9f40";s:34:"class.tx_oelib_headerCollector.php";s:4:"6aa2";s:37:"class.tx_oelib_headerProxyFactory.php";s:4:"e931";s:30:"class.tx_oelib_identityMap.php";s:4:"fa5b";s:32:"class.tx_oelib_mailerFactory.php";s:4:"d462";s:33:"class.tx_oelib_mapperRegistry.php";s:4:"0892";s:24:"class.tx_oelib_model.php";s:4:"740f";s:25:"class.tx_oelib_object.php";s:4:"71ad";s:31:"class.tx_oelib_publicObject.php";s:4:"18be";s:34:"class.tx_oelib_realHeaderProxy.php";s:4:"efbc";s:29:"class.tx_oelib_realMailer.php";s:4:"3f38";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"61cd";s:26:"class.tx_oelib_session.php";s:4:"8ceb";s:33:"class.tx_oelib_templatehelper.php";s:4:"f3e2";s:35:"class.tx_oelib_testingFramework.php";s:4:"4131";s:24:"class.tx_oelib_timer.php";s:4:"cb3d";s:12:"ext_icon.gif";s:4:"b4bf";s:14:"ext_tables.php";s:4:"8a0e";s:14:"ext_tables.sql";s:4:"dfd8";s:22:"icon_tx_oelib_test.gif";s:4:"bd58";s:16:"locallang_db.xml";s:4:"c812";s:7:"tca.php";s:4:"0067";s:8:"todo.txt";s:4:"d400";s:28:"tx_oelib_commonConstants.php";s:4:"8201";s:38:"models/class.tx_oelib_frontEndUser.php";s:4:"aadb";s:45:"mappers/class.tx_oelib_frontEndUserMapper.php";s:4:"05f9";s:40:"mappers/class.tx_oelib_testingMapper.php";s:4:"80d3";s:47:"exceptions/class.tx_oelib_notFoundException.php";s:4:"58ec";s:46:"tests/tx_oelib_configurationCheck_testcase.php";s:4:"7ab8";s:46:"tests/tx_oelib_configurationProxy_testcase.php";s:4:"b3b8";s:38:"tests/tx_oelib_dataMapper_testcase.php";s:4:"7dc3";s:30:"tests/tx_oelib_db_testcase.php";s:4:"5296";s:45:"tests/tx_oelib_fakeConfiguration_testcase.php";s:4:"6e3e";s:39:"tests/tx_oelib_fakeSession_testcase.php";s:4:"b032";s:46:"tests/tx_oelib_frontEndUserMapper_testcase.php";s:4:"0ca6";s:40:"tests/tx_oelib_frontEndUser_testcase.php";s:4:"6f27";s:46:"tests/tx_oelib_headerProxyFactory_testcase.php";s:4:"8f7e";s:39:"tests/tx_oelib_identityMap_testcase.php";s:4:"2f24";s:41:"tests/tx_oelib_mailerFactory_testcase.php";s:4:"c78a";s:42:"tests/tx_oelib_mapperRegistry_testcase.php";s:4:"5995";s:33:"tests/tx_oelib_model_testcase.php";s:4:"51e5";s:34:"tests/tx_oelib_object_testcase.php";s:4:"6160";s:51:"tests/tx_oelib_salutationswitcherchild_testcase.php";s:4:"1274";s:35:"tests/tx_oelib_session_testcase.php";s:4:"35f7";s:47:"tests/tx_oelib_templatehelperchild_testcase.php";s:4:"b7e4";s:44:"tests/tx_oelib_testingFramework_testcase.php";s:4:"5533";s:33:"tests/tx_oelib_timer_testcase.php";s:4:"8fe7";s:63:"tests/fixtures/class.tx_oelib_brokenColumnLessTestingMapper.php";s:4:"07a5";s:62:"tests/fixtures/class.tx_oelib_brokenTableLessTestingMapper.php";s:4:"7aa2";s:52:"tests/fixtures/class.tx_oelib_dummyObjectToCheck.php";s:4:"864f";s:57:"tests/fixtures/class.tx_oelib_salutationswitcherchild.php";s:4:"67b3";s:53:"tests/fixtures/class.tx_oelib_templatehelperchild.php";s:4:"67a2";s:46:"tests/fixtures/class.tx_oelib_testingModel.php";s:4:"ee57";s:47:"tests/fixtures/class.tx_oelib_testingObject.php";s:4:"96c4";s:28:"tests/fixtures/locallang.xml";s:4:"c52b";s:25:"tests/fixtures/oelib.html";s:4:"59ca";s:23:"tests/fixtures/test.png";s:4:"c7b6";s:33:"tests/fixtures/user_oelibtest.t3x";s:4:"322c";s:34:"tests/fixtures/user_oelibtest2.t3x";s:4:"56c7";s:14:"doc/manual.sxw";s:4:"fff8";}',
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