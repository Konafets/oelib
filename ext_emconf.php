<?php

########################################################################
# Extension Manager/Repository config file for ext: "oelib"
#
# Auto generated 23-05-2008 22:57
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
	'version' => '0.4.1',
	'_md5_values_when_last_written' => 'a:39:{s:13:"changelog.txt";s:4:"f29f";s:38:"class.tx_oelib_abstractHeaderProxy.php";s:4:"6ecc";s:33:"class.tx_oelib_abstractMailer.php";s:4:"5513";s:30:"class.tx_oelib_configcheck.php";s:4:"9385";s:37:"class.tx_oelib_configurationProxy.php";s:4:"c537";s:33:"class.tx_oelib_emailCollector.php";s:4:"827b";s:34:"class.tx_oelib_headerCollector.php";s:4:"138e";s:37:"class.tx_oelib_headerProxyFactory.php";s:4:"d510";s:32:"class.tx_oelib_mailerFactory.php";s:4:"873b";s:34:"class.tx_oelib_realHeaderProxy.php";s:4:"0b4c";s:29:"class.tx_oelib_realMailer.php";s:4:"458f";s:37:"class.tx_oelib_salutationswitcher.php";s:4:"21e4";s:33:"class.tx_oelib_templatehelper.php";s:4:"1059";s:35:"class.tx_oelib_testingFramework.php";s:4:"b608";s:24:"class.tx_oelib_timer.php";s:4:"ce7a";s:12:"ext_icon.gif";s:4:"b4bf";s:14:"ext_tables.php";s:4:"fd35";s:14:"ext_tables.sql";s:4:"c591";s:22:"icon_tx_oelib_test.gif";s:4:"61a5";s:16:"locallang_db.xml";s:4:"a70b";s:7:"tca.php";s:4:"9433";s:8:"todo.txt";s:4:"d400";s:28:"tx_oelib_commonConstants.php";s:4:"cf7b";s:46:"tests/tx_oelib_configurationCheck_testcase.php";s:4:"0b46";s:46:"tests/tx_oelib_configurationProxy_testcase.php";s:4:"5bc4";s:46:"tests/tx_oelib_headerProxyFactory_testcase.php";s:4:"6141";s:41:"tests/tx_oelib_mailerFactory_testcase.php";s:4:"333f";s:51:"tests/tx_oelib_salutationswitcherchild_testcase.php";s:4:"0f9c";s:47:"tests/tx_oelib_templatehelperchild_testcase.php";s:4:"f369";s:44:"tests/tx_oelib_testingFramework_testcase.php";s:4:"c7c5";s:33:"tests/tx_oelib_timer_testcase.php";s:4:"daa1";s:52:"tests/fixtures/class.tx_oelib_dummyObjectToCheck.php";s:4:"7be0";s:57:"tests/fixtures/class.tx_oelib_salutationswitcherchild.php";s:4:"a83c";s:53:"tests/fixtures/class.tx_oelib_templatehelperchild.php";s:4:"c6bf";s:28:"tests/fixtures/locallang.xml";s:4:"c52b";s:25:"tests/fixtures/oelib.html";s:4:"59ca";s:33:"tests/fixtures/user_oelibtest.t3x";s:4:"425c";s:34:"tests/fixtures/user_oelibtest2.t3x";s:4:"2ddf";s:14:"doc/manual.sxw";s:4:"fed0";}',
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