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
 * This interfaces represents an e-mail role, e.g. a sender or a recipient.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
interface Tx_Oelib_Interface_MailRole {
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