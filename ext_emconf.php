<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "oelib".
 *
 * Auto generated 06-01-2015 19:56
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
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'be_users,be_groups,fe_groups,fe_users,pages,sys_template,tt_content',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'author_company' => 'oliverklee.de',
	'version' => '0.9.50',
	'_md5_values_when_last_written' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.5.0-7.0.99',
			'typo3' => '6.2.0-7.9.99',
			'static_info_tables' => '2.0.8-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'extbase' => '6.2.0-6.2.99',
		),
	),
	'suggests' => array(
	),
	'autoload' => array(
		'classmap' => array(
			'Classes',
			'Tests',
		),
	),
);