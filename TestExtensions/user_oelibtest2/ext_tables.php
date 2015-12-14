<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TCA']['user_oelibtest2_test'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:user_oelibtest2/locallang_db.xml:user_oelibtest2_test',
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
		'dynamicConfigFile' => ExtensionManagementUtility::extPath($_EXTKEY) . 'tca.php',
		'iconfile'          => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'icon_user_oelibtest2_test.gif',
	),
);
