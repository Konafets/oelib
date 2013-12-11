<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class takes care of cleaning up oelib after the testing framework.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_TestingFrameworkCleanup {
	/**
	 * Cleans up oelib after running a test.
	 *
	 * @return void
	 */
	public function cleanUp() {
		Tx_Oelib_ConfigurationProxy::purgeInstances();
		Tx_Oelib_BackEndLoginManager::purgeInstance();
		Tx_Oelib_ConfigurationRegistry::purgeInstance();
		Tx_Oelib_FrontEndLoginManager::purgeInstance();
		tx_oelib_Geocoding_Google::purgeInstance();
		tx_oelib_headerProxyFactory::purgeInstance();
		Tx_Oelib_MailerFactory::purgeInstance();
		Tx_Oelib_MapperRegistry::purgeInstance();
		Tx_Oelib_PageFinder::purgeInstance();
		Tx_Oelib_Session::purgeInstances();
		Tx_Oelib_TemplateHelper::purgeCachedConfigurations();
		Tx_Oelib_Timer::purgeInstance();
		Tx_Oelib_TranslatorRegistry::purgeInstance();
	}
}