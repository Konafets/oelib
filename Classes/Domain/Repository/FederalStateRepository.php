<?php
/***************************************************************
* Copyright notice
*
* (c) 2012-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Repository for federal state models.
 *
 * @deprecated Do not use this class. It will be copied to the static_info_tables extension and then removed.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Domain_Repository_FederalStateRepository extends Tx_Oelib_Domain_Repository_AbstractReadOnlyRepository {
	/**
	 * Initializes the repository.
	 *
	 * @return void
	 */
	public function initializeObject() {
		/** @var $querySettings Tx_Extbase_Persistence_Typo3QuerySettings */
		$querySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->setRespectStoragePage(FALSE);
		$this->setDefaultQuerySettings($querySettings);
	}
}
?>