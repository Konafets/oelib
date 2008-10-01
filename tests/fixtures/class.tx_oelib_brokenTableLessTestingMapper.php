<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee <typo3-coding@oliverklee.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_dataMapper.php');

/**
 * Class 'tx_oelib_brokenTableLessTestingMapper' for the 'oelib' extension.
 *
 * This class represents a mapper that is broken because it has no table name
 * defined.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_brokenTableLessTestingMapper extends tx_oelib_dataMapper {
	/**
	 * @var	string		a comma-separated list of DB column names to retrieve
	 * 					or "*" for all columns
	 */
	protected $columns = '*';

	/**
	 * Creates a model of the correct type for this mapper and fills it with
	 * the data provided as $data.
	 *
	 * @param	array				the data with which the model should be
	 * 								filled, may be empty
	 *
	 * @return	tx_oelib_testingModel		the filled model
 	 */
	protected function createAndFillModel(array $data) {
		$model = t3lib_div::makeInstance('tx_oelib_testingModel');
		$model->setData($data);

		return $model;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_brokenTableLessTestingMapper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_brokenTableLessTestingMapper.php']);
}
?>