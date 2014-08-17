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
 * This class declares the function addHeader() for its inheritants. So they
 * need to implement the concrete behavior.
 *
 * Regarding the Strategy pattern, addHeader() represents the abstract strategy.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
abstract class Tx_Oelib_AbstractHeaderProxy {
	/**
	 * This function usually should add a HTTP header.
	 *
	 * @param string $header
	 *        HTTP header to send, e.g. 'Status: 404 Not Found', must not be empty
	 *
	 * @return void
	 */
	public abstract function addHeader($header);
}