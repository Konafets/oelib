<?php
/*
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
 * This class represents a mapper for front-end users.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Mapper_FrontEndUser extends Tx_Oelib_DataMapper {
	/**
	 * @var string the name of the database table for this mapper
	 */
	protected $tableName = 'fe_users';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'Tx_Oelib_Model_FrontEndUser';

	/**
	 * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
	 */
	protected $relations = array(
		'usergroup' => 'tx_oelib_Mapper_FrontEndUserGroup',
	);

	/**
	 * @var string[] the column names of additional string keys
	 */
	protected $additionalKeys = array('username');

	/**
	 * Finds a front-end user by user name. Hidden user records will be
	 * retrieved as well.
	 *
	 * @throws tx_oelib_Exception_NotFound
	 *         if there is no front-end user with the provided user name in the
	 *         database
	 *
	 * @param string $userName
	 *        user name, case-insensitive, must not be empty
	 *
	 * @return Tx_Oelib_Model_FrontEndUser
	 *         model of the front-end user with the provided user name
	 */
	public function findByUserName($userName) {
		return $this->findOneByKey('username', $userName);
	}

	/**
	 * Returns the users which are in the groups with the given UIDs.
	 *
	 * @param string $groupUids
	 *        the UIDs of the user groups from which to get the users, must be a
	 *        comma-separated list of group UIDs, must not be empty
	 *
	 * @return Tx_Oelib_List<Tx_Oelib_Model_FrontEndUser> the found user models, will be empty if
	 *                       no users were found for the given groups
	 */
	public function getGroupMembers($groupUids) {
		if ($groupUids === '') {
			throw new InvalidArgumentException('$groupUids must not be an empty string.', 1331488505);
		}

		return $this->getListOfModels(
			Tx_Oelib_Db::selectMultiple(
				'*',
				$this->getTableName(),
				$this->getUniversalWhereClause() . ' AND ' .
					'usergroup REGEXP \'(^|,)(' . implode('|', t3lib_div::intExplode(',', $groupUids)) . ')($|,)\''
			)
		);
	}
}