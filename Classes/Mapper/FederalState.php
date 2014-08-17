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
 * This class represents a mapper for federal state models.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Mapper_FederalState extends Tx_Oelib_DataMapper {
	/**
	 * @var string
	 */
	protected $tableName = 'static_country_zones';

	/**
	 * @var string
	 */
	protected $modelClassName = 'Tx_Oelib_Model_FederalState';

	/**
	 * @var array the column names of additional combined keys
	 */
	protected $compoundKeyParts = array('zn_country_iso_2', 'zn_code');

	/**
	 * Finds a federal state by its ISO 3166-1 and ISO 3166-2 code.
	 *
	 * @param string $isoAlpha2CountryCode
	 *        the ISO 3166-1 alpha-2 country code to find, must not be empty
	 * @param string $isoAlpha2ZoneCode
	 *        the ISO 3166-2 code to find, must not be empty
	 *
	 * @return Tx_Oelib_Model_FederalState the federal state with the requested code
	 */
	public function findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCode($isoAlpha2CountryCode, $isoAlpha2ZoneCode) {
		return $this->findOneByCompoundKey(array('zn_country_iso_2' => $isoAlpha2CountryCode, 'zn_code' => $isoAlpha2ZoneCode));
	}
}