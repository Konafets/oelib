<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Tests/Unit/Fixtures/Empty.php']
	= t3lib_extMgm::extPath('oelib') . 'Tests/Unit/Fixtures/Xclass/Empty.php';

$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_oelib_Double3Validator'] = 'EXT:oelib/Classes/Double3Validator.php';

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'][]
	= 'EXT:oelib/Classes/TestingFrameworkCleanup.php:&tx_oelib_TestingFrameworkCleanup';

