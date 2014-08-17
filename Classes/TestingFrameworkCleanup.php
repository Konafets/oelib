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