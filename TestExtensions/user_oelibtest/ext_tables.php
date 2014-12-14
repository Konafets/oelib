<?php
defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TCA']['user_oelibtest_test'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:user_oelibtest/locallang_db.xml:user_oelibtest_test',
		'label'     => 'title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_user_oelibtest_test.gif',
	),
);
