<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TCA']['tx_oelib_testchild'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_oelib_testchild']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:oelib/Resources/Private/Language/locallang_db.xml:tx_oelib_test.title',
			'config' => array(
				'type' => 'none',
				'size' => '30',
			),
		),
		'parent' => array(
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => '',
			'config' => array(
				'type' => 'passthrough',
			),
		),
		'tx_oelib_parent2' => array(
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => '',
			'config' => array(
				'type' => 'passthrough',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2'),
	),
);