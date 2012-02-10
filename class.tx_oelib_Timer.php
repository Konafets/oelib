<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2011 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_oelib_Timer' for the 'oelib' extension.
 *
 * This singleton class provides functions for performance measurement.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_Timer {
	/**
	 * @var boolean whether the timer currently is running
	 */
	private $isRunning = FALSE;

	/**
	 * @var string the name of the current time bucket
	 */
	private $currentBucketName = '';

	/**
	 * @var array a stack of previously used buckets, starting with the first
	 *            bucket
	 */
	private $previousBucketNames = array();

	/**
	 * @var array the time buckets with their names as keys
	 */
	private $buckets = array();

	/**
     * @var float the sum of all measured time
     */
	private $allTime = 0.00;

	/**
	 * @var float the time from the last startTimer() call
	 */
	private $lastTime = 0.00;

	/**
	 * @var tx_oelib_Timer the singleton instance
	 */
	private static $instance = NULL;

	/**
	 * The constructor. Use getInstance() instead.
	 */
	private function __construct() {
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		$this->destroyAllBuckets();
	}

	/**
	 * Retrieves the singleton timer instance. This function usually should be
	 * called statically.
	 *
	 * @return tx_oelib_Timer a reference to the singleton timer object
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_oelib_Timer();
		}

		return self::$instance;
	}

	/**
	 * Purges the current instance so that getInstance will create a new
	 * instance.
	 */
	public static function purgeInstance() {
		if (self::$instance) {
			self::$instance->__destruct();
		}
		self::$instance = NULL;
	}

	/**
	 * Starts the timer and sets the bucket which the time will be added to.
	 *
	 * If the timer is already running, the previous bucket will be closed
	 * first.
	 *
	 * @param string $bucketName the name of the bucket to open, must not be empty
	 */
	public function openBucket($bucketName = 'default') {
		if ($bucketName != $this->currentBucketName) {
			$this->closeCurrentBucket();
			$this->previousBucketNames[] = $this->currentBucketName;
			$this->currentBucketName = $bucketName;
		}

		$this->isRunning = TRUE;
	}

	/**
	 * Starts the timer and sets the bucket which the time will be added to.
	 *
	 * If the timer is already running, the previous bucket will be closed
	 * first.
	 *
	 * This is a static shortcut for openBucket.
	 *
	 * @see openBucket
	 *
	 * @param string $bucketName the name of the bucket to open, must not be empty
	 */
	public static function oB($bucketName = 'default') {
		self::getInstance()->openBucket($bucketName);
	}

	/**
	 * Stops the timer and adds the passed time to the current bucket.
	 */
	public function stopTimer() {
		$this->closeCurrentBucket();
		$this->isRunning = FALSE;
	}

	/**
	 * Gets the statistics as an arry sorted descending by time.
	 * Each array element will be an array in itself, containing the following
	 * keys:
	 * - bucketName (string, not htmlspecialchared yet)
	 * - absoluteTime (float, in seconds)
	 * - relativeTime (float, in percent)
	 *
	 * @return array two-dimensional array with the times of all buckets,
	 *               will be empty if there are no buckets
	 */
	public function getStatisticsAsRawData() {
		$this->stopTimer();
		$this->clearAllPreviousBuckets();

		// Put the biggest performance culprits on top of the list.
		arsort($this->buckets, SORT_NUMERIC);

		$result = array();

		foreach ($this->buckets as $bucketName => $bucketTime) {
			$result[] = array(
				'bucketName' => $bucketName,
				'absoluteTime' => $bucketTime,
				'relativeTime' => $bucketTime * 100 / $this->allTime
			);
		}

		return $result;
	}

	/**
	 * Gets the statistics as a HTML table with one row per bucket, containing
	 * the bucket name, the absolute amount of time in seconds in the bucket
	 * and the percentage of the total time (from all buckets summed up).
	 *
	 * The table will be sorted with the biggest buckets first.
	 *
	 * @return string a HTML table with the statistics
	 */
	public function getStatistics() {
		$rawStatistics = $this->getStatisticsAsRawData();

		$result = '<table summary="statistics">' . LF;
		$result .= '  <thead>' . LF;
		$result .= '    <tr>' . LF;
		$result .= '      <th scope="col">Total time</th>' .
			'<th scope="col">' . $this->allTime . '&nbsp;s</th><th>100&nbsp;%</th>' . LF;
		$result .= '    </tr>' . LF;
		$result .= '  </thead>' . LF;
		$result .= '  <tbody>' . LF;

		foreach ($rawStatistics as $bucketData) {
			$result .= '    <tr>' . LF;
			$result .= '      <td>' . htmlspecialchars($bucketData['bucketName']) . '</td>';
			$result .= '      <td>' . $bucketData['absoluteTime'] . '&nbsp;s</td>';
			$result .= '      <td>' . $bucketData['relativeTime'] . '&nbsp;%</td>' . LF;
			$result .= '    </tr>' . LF;
		}

		$result .= '  </tbody>' . LF;
		$result .= '</table>' . LF;

		return $result;
	}

	/**
	 * Stops all timers and deletes all buckets.
	 *
	 * After this, a completely new sets of buckets can be created.
	 */
	public function destroyAllBuckets() {
		$this->stopTimer();

		foreach (array_keys($this->buckets) as $key) {
			unset($this->buckets[$key]);
		}
	}

	/**
	 * Closes the current bucket (if the timer is running), adds the passed
	 * time to it and set $this->lastTime to the current time.
	 *
	 * Note: This function does not stop the timer.
	 */
	private function closeCurrentBucket() {
		$currentTime = microtime(TRUE);

		if ($this->isRunning) {
			$usedTime = $currentTime - $this->lastTime;
			$this->buckets[$this->currentBucketName] += $usedTime;
			$this->allTime += $usedTime;
		}

		$this->lastTime = $currentTime;
	}

	/**
	 * Closes the current bucker and returns to the previous bucket (from the
	 * stack of previously used buckets).
	 *
	 * If there is not previous bucket, the timer will be stopped.
	 */
	public function returnToPreviousBucket() {
		$this->closeCurrentBucket();
		$previousBucketName = array_pop($this->previousBucketNames);

		if ($previousBucketName !== NULL) {
			$this->currentBucketName = $previousBucketName;
			$this->isRunning = TRUE;
		} else {
			$this->stopTimer();
		}
	}

	/**
	 * Closes the current bucker and returns to the previous bucket (from the
	 * stack of previously used buckets).
	 *
	 * If there is not previous bucket, the timer will be stopped.
	 *
	 * This is a static shortcut for returnToPreviousBucket.
	 *
	 * @see returnToPreviousBucket
	 */
	public static function rB() {
		self::getInstance()->returnToPreviousBucket();
	}

	/**
	 * Empties the stack of previous buckets.
	 */
	private function clearAllPreviousBuckets() {
		$this->previousBucketNames = array();
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Timer.php']) {
	include_once ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/oelib/class.tx_oelib_Timer.php']);
}
?>