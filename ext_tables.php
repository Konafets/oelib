<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die('Access denied.');

ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'One is Enough Library');

$GLOBALS['TCA']['tx_oelib_test'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:oelib/Resources/Private/Language/locallang_db.xml:tx_oelib_test',
		'readOnly' => 1,
		'adminOnly' => 1,
		'rootLevel' => 1,
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => FALSE,
		'default_sortby' => 'ORDER BY uid',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Test.php',
		'iconfile' => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/Test.gif',
	),
);

$GLOBALS['TCA']['tx_oelib_testchild'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:oelib/Resources/Private/Language/locallang_db.xml:tx_oelib_test',
		'readOnly' => 1,
		'adminOnly' => 1,
		'rootLevel' => 1,
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => FALSE,
		'default_sortby' => 'ORDER BY uid',
		'delete' => 'deleted',
		'dynamicConfigFile' => ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/TestChild.php',
		'iconfile' => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/Test.gif',
	),
);

$addToFeInterface = (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 6002000);
ExtensionManagementUtility::addTCAcolumns(
	'fe_users',
	array(
		'tx_oelib_is_dummy_record' => array(),
	),
	$addToFeInterface
);
