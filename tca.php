<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['tx_oelib_test'] = array(
	'ctrl' => $TCA['tx_oelib_test']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,starttime,endtime,title,friend,owner,children,related_records,composition',
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'none',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0',
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'none',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y')),
				),
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:oelib/locallang_db.xml:tx_oelib_test.title',
			'config' => array(
				'type' => 'none',
				'size' => '30',
			),
		),
		'friend' => array(
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'Friend (n:1 relation within the same table):',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_oelib_test',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'owner' => array(
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'Owner (n:1 relation to another table):',
			'config' => array(
				'type' => 'group',
				'foreign_table' => 'fe_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'children' => array(
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'Children (m:n relation using a comma-separated list)',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_oelib_test',
				'size' => 4,
				'minitems' => 0,
				'maxitems' => 99,
			),
		),
		'related_records' => array(
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'Related records (m:n relation using an m:n table)',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_oelib_test',
				'size' => 4,
				'minitems' => 0,
				'maxitems' => 99,
				'MM' => 'tx_oelib_test_article_mm',
			),
		),
		'composition' => array(
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'Related records (1:n relation using a foreign field)',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_oelib_testchild',
				'foreign_field' => 'parent',
				'foreign_sortby' => 'title',
				'size' => 4,
				'minitems' => 0,
				'maxitems' => 99
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, friend, owner, children, related_records, composition'),
	),
	'palettes' => array(
		'1' => array('showitem' => 'starttime, endtime'),
	),
);

$TCA['tx_oelib_testchild'] = array(
	'ctrl' => $TCA['tx_oelib_test']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title',
	),
	'columns' => array(
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:oelib/locallang_db.xml:tx_oelib_test.title',
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
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2'),
	),
);
?>