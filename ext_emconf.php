<?php

########################################################################
# Extension Manager/Repository config file for ext: "oelib"
#
# Auto generated 05-02-2009 15:16
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
	'version' => '0.5.2',
	'_md5_values_when_last_written' => 'a:41:{s:13:"changelog.txt";s:4:"96d0";s:29:"class.tx_oelib_Autoloader.php";s:4:"6dd8";s:29:"class.tx_oelib_DataMapper.php";s:4:"ed39";s:36:"class.tx_oelib_FakeConfiguration.php";s:4:"e451";s:30:"class.tx_oelib_FakeSession.php";s:4:"500a";s:30:"class.tx_oelib_IdentityMap.php";s:4:"8b24";s:33:"class.tx_oelib_MapperRegistry.php";s:4:"8e40";s:24:"class.tx_oelib_Model.php";s:4:"35cc";s:25:"class.tx_oelib_Object.php";s:4:"8bfe";s:31:"class.tx_oelib_PublicObject.php";s:4:"2a6e";s:26:"class.tx_oelib_Session.php";s:4:"049f";s:27:"class.tx_oelib_Template.php";s:4:"df5e";s:35:"class.tx_oelib_TemplateRegistry.php";s:4:"03c2";s:24:"class.tx_oelib_Timer.php";s:4:"d63d";s:38:"class.tx_oelib_abstractHeaderProxy.php";s:4:"d904";s:33:"class.tx_oelib_abstractMailer.php";s:4:"d614";s:30:"class.tx_oelib_configcheck.php";s:4:"26d5";s:37:"class.tx_oelib_configurationProxy.php";s:4:"cd48";s:21:"class.tx_oelib_db.php";s:4:"ffda";s:33:"class.tx_oelib_emailCollector.php";s:4:"cb40";s:34:"class.tx_oelib_headerCollector.php";s:4:"2939";s:37:"class.tx_oelib_headerProxyFactory.php";s:4:"a623";s:32:"class.tx_oelib_mailerFactory.php";s:4:"6642";s:34:"class.tx_oelib_realHeaderProxy.php";s:4:"bb28";s:29:"class.tx_oelib_realMailer.php";s:4:"b636";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"94b9";s:33:"class.tx_oelib_templatehelper.php";s:4:"8edb";s:35:"class.tx_oelib_testingFramework.php";s:4:"993e";s:12:"ext_icon.gif";s:4:"b4bf";s:14:"ext_tables.php";s:4:"8a0e";s:14:"ext_tables.sql";s:4:"dfd8";s:22:"icon_tx_oelib_test.gif";s:4:"bd58";s:16:"locallang_db.xml";s:4:"c812";s:7:"tca.php";s:4:"0067";s:8:"todo.txt";s:4:"d400";s:28:"tx_oelib_commonConstants.php";s:4:"daae";s:45:"Mapper/class.tx_oelib_Mapper_FrontEndUser.php";s:4:"a639";s:51:"Exception/class.tx_oelib_Exception_AccessDenied.php";s:4:"0f15";s:47:"Exception/class.tx_oelib_Exception_NotFound.php";s:4:"ab50";s:43:"Model/class.tx_oelib_Model_FrontEndUser.php";s:4:"6bbb";s:14:"doc/manual.sxw";s:4:"57db";}',
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