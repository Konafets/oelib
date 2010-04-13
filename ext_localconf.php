<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/tests/fixtures/class.tx_oelib_tests_fixtures_Empty.php']
	= t3lib_extMgm::extPath('oelib') . 'tests/fixtures/xclass/class.ux_tx_oelib_tests_fixtures_Empty.php';

$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_oelib_Double3Validator'] = 'EXT:oelib/class.tx_oelib_Double3Validator.php';

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'][]
	= 'EXT:oelib/class.tx_oelib_TestingFrameworkCleanup.php:' .
		'&tx_oelib_TestingFrameworkCleanup';
?>
