<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_oelib_Attachment' for the 'oelib' extension.
 *
 * This class represents an e-mail attachment.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_oelib_Attachment {
	/**
	 * @var string the file name of the attachment
	 */
	private $fileName = '';

	/**
	 * @var string the content type of the attachment
	 */
	private $contentType = '';

	/**
	 * @var string the content of the attachment
	 */
	private $content = '';

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->content);
	}

	/**
	 * Sets the file name of the attachment.
	 *
	 * @param string the file name of the attachment, must not be empty
	 */
	public function setFileName($fileName) {
		if ($fileName == '') {
			throw new Exception('$fileName must not be empty.');
		}

		$this->fileName = $fileName;
	}

	/**
	 * Returns the file name of the attachment.
	 *
	 * @return string the file name of the attachment, will be empty if not set
	 */
	public function getFileName() {
		return $this->fileName;
	}

	/**
	 * Sets the content type of the attachment.
	 *
	 * @param string the content type of the attachment, must not be empty,
	 *               e.g. 'text/plain', 'image/jpeg' or 'application/octet-stream'
	 */
	public function setContentType($contentType) {
		if ($contentType == '') {
			throw new Exception('$contentType must not be empty.');
		}

		$this->contentType = $contentType;
	}

	/**
	 * Returns the content type of the attachment.
	 *
	 * @return string the content type of the attachment, will be empty if not set
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * Sets the content of the attachment.
	 *
	 * @param string the content of the attachment, may be empty
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Returns the content of the attachment.
	 *
	 * @return string the content of the attachment, might be empty
	 */
	public function getContent() {
		return $this->content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Attachment.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Attachment.php']);
}
?>