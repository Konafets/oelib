<?php

########################################################################
# Extension Manager/Repository config file for ext: "oelib"
#
# Auto generated 10-03-2008 23:40
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Oliver\'s extension library',
	'description' => 'This extension provides useful stuff for extension development: helper functions for templating, salutation switching, automatic configuration checks and performance benchmarking.',
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
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'cache_pages,fe_groups,fe_users,pages,sys_template,tt_content,user_oelibtest_test,user_oelibtest_test_article_mm',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'author_company' => '',
	'version' => '0.4.0',
	'_md5_values_when_last_written' => 'a:29:{s:13:"changelog.txt";s:4:"f17c";s:33:"class.tx_oelib_abstractMailer.php";s:4:"a77e";s:30:"class.tx_oelib_configcheck.php";s:4:"7fbe";s:37:"class.tx_oelib_configurationProxy.php";s:4:"f14d";s:33:"class.tx_oelib_emailCollector.php";s:4:"5b43";s:32:"class.tx_oelib_mailerFactory.php";s:4:"6386";s:29:"class.tx_oelib_realMailer.php";s:4:"b6cc";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"31bb";s:33:"class.tx_oelib_templatehelper.php";s:4:"f458";s:35:"class.tx_oelib_testingFramework.php";s:4:"5851";s:24:"class.tx_oelib_timer.php";s:4:"b2c1";s:21:"ext_conf_template.txt";s:4:"e275";s:12:"ext_icon.gif";s:4:"b4bf";s:14:"ext_tables.sql";s:4:"e84f";s:8:"todo.txt";s:4:"1237";s:28:"tx_oelib_commonConstants.php";s:4:"20d8";s:46:"tests/tx_oelib_configurationCheck_testcase.php";s:4:"d8f1";s:46:"tests/tx_oelib_configurationProxy_testcase.php";s:4:"07dc";s:41:"tests/tx_oelib_mailerFactory_testcase.php";s:4:"4554";s:51:"tests/tx_oelib_salutationswitcherchild_testcase.php";s:4:"40c5";s:47:"tests/tx_oelib_templatehelperchild_testcase.php";s:4:"cfd9";s:44:"tests/tx_oelib_testingFramework_testcase.php";s:4:"4bc3";s:33:"tests/tx_oelib_timer_testcase.php";s:4:"d572";s:52:"tests/fixtures/class.tx_oelib_dummyObjectToCheck.php";s:4:"cdae";s:57:"tests/fixtures/class.tx_oelib_salutationswitcherchild.php";s:4:"b8c5";s:53:"tests/fixtures/class.tx_oelib_templatehelperchild.php";s:4:"ea5e";s:28:"tests/fixtures/locallang.xml";s:4:"c52b";s:25:"tests/fixtures/oelib.html";s:4:"59ca";s:14:"doc/manual.sxw";s:4:"5cb6";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.0-0.0.0',
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