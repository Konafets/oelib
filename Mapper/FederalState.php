<?php
/***************************************************************
* Copyright notice
*
* (c) 2012 Oliver Klee <typo3-coding@oliverklee.de>
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

/**
 * This class represents a mapper for federal state models.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Mapper_FederalState extends tx_oelib_DataMapper {
	/**
	 * @var string
	 */
	protected $tableName = 'static_country_zones';

	/**
	 * @var string
	 */
	protected $modelClassName = 'tx_oelib_Model_FederalState';

	/**
	 * @var array the column names of additional string keys
	 */
	protected $additionalKeys = array('zn_code');

	/**
	 * Finds a federal state by its ISO 3166-1 alpha-2 code.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record with the provided ISO 3166-1 alpha-2 code
	 *
	 * @param string $isoAlpha2Code
	 *        the ISO 3166-1 alpha-2 code to find, must not be empty
	 *
	 * @return tx_oelib_Model_FederalState the federal state with the requested code
	 */
	public function findByIsoAlpha2Code($isoAlpha2Code) {
		return $this->findOneByKey('zn_code', $isoAlpha2Code);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Mapper/FederalState.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/Mapper/FederalState.php']);
}
?>