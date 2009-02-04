<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class 'tx_oelib_Mapper_FrontEndUser' for the 'oelib' extension.
 *
 * This class represents a mapper for front-end users.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Mapper_FrontEndUser extends tx_oelib_DataMapper {
	/**
	 * @var string the name of the database table for this mapper
	 */
	protected $tableName = 'fe_users';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'tx_oelib_Model_FrontEndUser';

	/**
	 * Gets the currently logged in front-end user.
	 *
	 * @return tx_oelib_Model_FrontEndUser the logged in front-end user, will
	 *                                     be null if no user is logged in or
	 *                                     if there is no front end
	 */
	public function getLoggedInUser() {
		if (!isset($GLOBALS['TSFE']) || !$GLOBALS['TSFE']
			|| !is_array($GLOBALS['TSFE']->fe_user->user)
		) {
			return null;
		}

		$data = $GLOBALS['TSFE']->fe_user->user;
		$uid = $data['uid'];

		try {
			$model = $this->map->get($uid);
		} catch (tx_oelib_Exception_NotFound $exception) {
			// The data already is in memory. So there's no need to read it from
			// the DB again.
			$model = $this->createAndFillModel($data);
			$this->map->add($model);
		}

		return $model;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Mapper/class.tx_oelib_Mapper_FrontEndUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Mapper/class.tx_oelib_Mapper_FrontEndUser.php']);
}
?>