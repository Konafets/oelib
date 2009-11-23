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
	 * @var array the (possible) relations of the created models in the format
	 *            DB column name => mapper name
	 */
	protected $relations = array(
		'usergroup' => 'tx_oelib_Mapper_FrontEndUserGroup',
	);

	/**
	 * @var array the column names of additional string keys
	 */
	protected $additionalKeys = array('username');

	/**
	 * Gets the currently logged-in front-end user.
	 *
	 * @return tx_oelib_Model_FrontEndUser the logged-in front-end user, will
	 *                                     be null if no user is logged in or
	 *                                     if there is no front end
	 *
	 * @deprecated 2009-03-02 Will be removed in oelib 0.8.0. Use tx_oelib_FrontEndLoginManager::getLoggedInUser
	 */
	public function getLoggedInUser() {
		return tx_oelib_FrontEndLoginManager::getInstance()->getLoggedInUser();
	}

	/**
	 * Reads a record from the database by UID (from this mapper's table). Also
	 * hidden records will be retrieved.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record in the DB
	 *                                     with the UID $uid
	 *
	 * @param integer the UID of the record to retrieve, must be > 0
	 *
	 * @return array the record from the database, will not be empty
	 */
	protected function retrieveRecordByUid($uid) {
		if (tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn() &&
			($GLOBALS['TSFE']->fe_user->user['uid'] == $uid)
		) {
			$data = $GLOBALS['TSFE']->fe_user->user;
		} else {
			$data = parent::retrieveRecordByUid($uid);
		}

		return $data;
	}

	/**
	 * Returns the users which are in the groups with the given UIDs.
	 *
	 * @param string the UIDs of the user groups from which to get the users,
	 *               must be a comma-separated list of group UIDs, must not be
	 *               empty
	 *
	 * @return tx_oelib_List the found user models, will be empty if
	 *                       no users were found for the given groups
	 */
	public function getGroupMembers($groupUids) {
		if ($groupUids == '') {
			throw new Exception('$groupUids must not be an empty string.');
		}

		return $this->getListOfModels(
			tx_oelib_db::selectMultiple(
				'*',
				$this->tableName,
				$this->getUniversalWhereClause() . ' AND ' .
					'usergroup REGEXP \'(^|,)(' .
					implode('|', t3lib_div::intExplode(',', $groupUids)) .
					')($|,)\''
			)
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Mapper/class.tx_oelib_Mapper_FrontEndUser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/Mapper/class.tx_oelib_Mapper_FrontEndUser.php']);
}
?>