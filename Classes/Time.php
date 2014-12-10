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
 * This class provides time-related constants.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_oelib_Time {
	/**
	 * the number of seconds per minute
	 *
	 * @var integer
	 */
	const SECONDS_PER_MINUTE = 60;

	/**
	 * the number of seconds per hour
	 *
	 * @var integer
	 */
	const SECONDS_PER_HOUR = 3600;

	/**
	 * the number of seconds per day
	 *
	 * @var integer
	 */
	const SECONDS_PER_DAY = 86400;

	/**
	 * the number of seconds per week
	 *
	 * @var integer
	 */
	const SECONDS_PER_WEEK = 604800;

	/**
	 * the number of seconds per year (only for non-leap years),
	 * use with caution
	 *
	 * @var integer
	 */
	const SECONDS_PER_YEAR = 220752000;
}