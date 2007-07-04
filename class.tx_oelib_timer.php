<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_oelib_timer' for the 'oelib' extension.
 *
 * This singleton class provides functions for performance measurement.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

define('LF', chr(10));

class tx_oelib_timer {
	/** whether the timer currently is running */
	var $isRunning = false;

	/** the name of the current time bucket */
	var $currentBucketName = '';

	/** the time buckets with their names as keys */
	var $buckets = array();

	/** the sum of all measured time */
	var $allTime = 0;

	/** the time from the last startTimer() call */
	var $lastTime = 0;

	/**
	 * Retrieves the singleton timer instance. This function usually should be
	 * called statically.
	 *
	 * @return	object		a reference to the singleton timer object
	 *
	 * @access	public
	 */
	function &getInstance() {
		// We use an array as singleton container because a direct object
		// doesn't work (we would get a new instance every time).
		static $instance = array();

		if (!is_object($instance[0])) {
			$instance[0] =& t3lib_div::makeInstance('tx_oelib_timer');
		}

		return $instance[0];
	}

	/**
	 * Starts the timer and sets the bucket which the time will be added to.
	 *
	 * If the timer is already running, the previous bucket will be closed
	 * first.
	 *
	 * @param	string	the name of the bucket to open
	 *
	 * @access	public
	 */
	function openBucket($bucketName = 'default') {
		$this->closeCurrentBucket();
		$this->currentBucketName = $bucketName;
		$this->isRunning = true;

		return;
	}

	/**
	 * Stops the timer and adds the passed time to the current bucket.
	 *
	 * @access	public
	 */
	function stopTimer() {
		$this->closeCurrentBucket();
		$this->isRunning = false;

		return;
	}

	/**
	 * Gets the statistics as a HTML table with one row per bucket, containing
	 * the bucket name, the absolute amount of time in seconds in the bucket
	 * and the percentage of the total time (from all buckets summed up).
	 *
	 * The table will be sorted with the biggest buckets first.
	 *
	 * @return	string		a HTML table with the statistics
	 *
	 * @access	public
	 */
	function getStatistics() {
		$this->stopTimer();

		// Put the biggest performance culprits on top of the list.
		arsort($this->buckets, SORT_NUMERIC);

		$result .= '<table summary="statistics">'.LF;
		$result .= '  <thead>'.LF;
		$result .= '    <tr>'.LF;
		$result .= '      <th>Total time</th><th>'.$this->allTime
			.'&nbsp;s</th><th>100&nbsp;%</th>'.LF;
		$result .= '    </tr>'.LF;
		$result .= '  </thead>'.LF;
		$result .= '  <tbody>'.LF;

		foreach ($this->buckets as $bucketName => $bucketTime) {
			$result .= '    <tr>'.LF;
			$result .= '      <td>'.htmlspecialchars($bucketName).'</td>';
			$result .= '      <td>'.$bucketTime.'&nbsp;s'.'</td>';
			$result .= '      <td>'.($bucketTime * 100 / $this->allTime).'&nbsp;%'
				.'</td>'.LF;
			$result .= '    </tr>'.LF;
		}

		$result .= '  </tbody>'.LF;
		$result .= '</table>'.LF;

		return $result;
	}

	/**
	 * Closes the current bucket (if the timer is running), adds the passed
	 * time to it and set $this->lastTime to the current time.
	 *
	 * @access	private
	 */
	function closeCurrentBucket() {
		$currentTime = $this->getCurrentTime();

		if ($this->isRunning) {
			$usedTime = $currentTime - $this->lastTime;
			$this->buckets[$this->currentBucketName] += $usedTime;
			$this->allTime += $usedTime;
		}

		$this->lastTime = $currentTime;

		return;
	}

	/**
	 * Gets the current time.
	 *
	 * @return	float		the current time in seconds
	 *
	 * @access	private
	 */
	function getCurrentTime() {
		list($low, $high) = split(' ', microtime());

		return $high + $low;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_timer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_timer.php']);
}

?>
