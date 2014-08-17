<?php
/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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