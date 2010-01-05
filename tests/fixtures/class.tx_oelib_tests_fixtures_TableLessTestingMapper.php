<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_tests_fixtures_TableLessTestingMapper' for the 'oelib' extension.
 *
 * This class represents a mapper that is broken because it has no table name
 * defined.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_tests_fixtures_TableLessTestingMapper extends tx_oelib_DataMapper {
	/**
	 * @var string a comma-separated list of DB column names to retrieve
	 *             or "*" for all columns
	 */
	protected $columns = '*';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'tx_oelib_tests_fixtures_TestingModel';
}
?>