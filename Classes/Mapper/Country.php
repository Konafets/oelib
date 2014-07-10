<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon (mail@niels-pardon.de)
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
 * This class represents a mapper for countries.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Mapper_Country extends Tx_Oelib_DataMapper {
	/**
	 * @var string the name of the database table for this mapper
	 */
	protected $tableName = 'static_countries';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'Tx_Oelib_Model_Country';

	/**
	 * @var array the column names of additional string keys
	 */
	protected $additionalKeys = array('cn_iso_2', 'cn_iso_3');

	/**
	 * Finds a country by its ISO 3166-1 alpha-2 code.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record with the
	 *                                     provided ISO 3166-1 alpha-2 code
	 *
	 * @param string $isoAlpha2Code
	 *        the ISO 3166-1 alpha-2 code to find, must not be empty
	 *
	 * @return Tx_Oelib_Model_Country the country
	 */
	public function findByIsoAlpha2Code($isoAlpha2Code) {
		return $this->findOneByKey('cn_iso_2', $isoAlpha2Code);
	}

	/**
	 * Finds a country by its ISO 3166-1 alpha-3 code.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record with the
	 *                                     provided ISO 3166-1 alpha-3 code
	 *
	 * @param string $isoAlpha3Code
	 *        the ISO 3166-1 alpha-3 code to find, must not be empty
	 *
	 * @return Tx_Oelib_Model_Country the country
	 */
	public function findByIsoAlpha3Code($isoAlpha3Code) {
		return $this->findOneByKey('cn_iso_3', $isoAlpha3Code);
	}
}