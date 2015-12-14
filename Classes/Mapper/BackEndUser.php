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
 * This class represents a mapper for back-end users.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Mapper_BackEndUser extends Tx_Oelib_DataMapper {
	/**
	 * @var string the name of the database table for this mapper
	 */
	protected $tableName = 'be_users';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'Tx_Oelib_Model_BackEndUser';

	/**
	 * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
	 */
	protected $relations = array(
		'usergroup' => 'tx_oelib_Mapper_BackEndUserGroup',
	);

	/**
	 * @var string[] the column names of additional string keys
	 */
	protected $additionalKeys = array('username');

	/**
	 * Finds a back-end user by user name. Hidden user records will be retrieved
	 * as well.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no back-end user with the
	 *                                     provided user name in the be_user
	 *                                     table
	 *
	 * @param string $userName
	 *        user name, case-insensitive, must not be empty
	 *
	 * @return Tx_Oelib_Model_BackEndUser model of the back-end user with the
	 *                                    provided user name
	 */
	public function findByUserName($userName) {
		return $this->findOneByKey('username', $userName);
	}

	/**
	 * Finds a back-end user by CLI key.
	 *
	 * Note: This function must only be called if the constant "TYPO3_cliKey"
	 * is defined.
	 *
	 * @return Tx_Oelib_Model_BackEndUser model of the back-end user for the
	 *                                    defined CLI key
	 */
	public function findByCliKey() {
		if (!defined('TYPO3_cliKey')) {
			throw new BadMethodCallException(
				'Please make sure the constant "TYPO3_cliKey" is defined before using this function. Usually this is done ' .
					'automatically when executing "/typo3/cli_dispatch.phpsh".',
				1331488485
			);
		}

		$userName = $GLOBALS['TYPO3_CONF_VARS']
			['SC_OPTIONS']['GLOBAL']['cliKeys'][TYPO3_cliKey][1];

		return $this->findByUserName($userName);
	}

	/**
	 * Reads a record from the database by UID (from this mapper's table). Also
	 * hidden records will be retrieved.
	 *
	 * @throws tx_oelib_Exception_NotFound if there is no record in the DB
	 *                                     with the UID $uid
	 *
	 * @param int $uid
	 *        the UID of the record to retrieve, must be > 0
	 *
	 * @return array the record from the database, will not be empty
	 */
	protected function retrieveRecordByUid($uid) {
		$authentication = $this->getBackEndUserAuthentication();
		if (Tx_Oelib_BackEndLoginManager::getInstance()->isLoggedIn() && ((int)$authentication->user['uid'] === $uid)) {
			$data = $authentication->user;
		} else {
			$data = parent::retrieveRecordByUid($uid);
		}

		return $data;
	}

	/**
	 * Returns $GLOBALS['BE_USER'].
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackEndUserAuthentication() {
		return $GLOBALS['BE_USER'];
	}
}