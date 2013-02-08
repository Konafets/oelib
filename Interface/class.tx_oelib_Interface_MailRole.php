<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon (mail@niels-pardon.de)
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
 * Interface 'tx_oelib_Interface_MailRole' for the 'oelib' extension.
 *
 * This interfaces represents an e-mail role, e.g. a sender or a recipient.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
interface tx_oelib_Interface_MailRole {
	/**
	 * Returns the real name of the e-mail role.
	 *
	 * @return string the real name of the e-mail role, might be empty
	 */
	public function getName();

	/**
	 * Returns the e-mail address of the e-mail role.
	 *
	 * @return string the e-mail address of the e-mail role, might be empty
	 */
	public function getEmailAddress();
}
?>