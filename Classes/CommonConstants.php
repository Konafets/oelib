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
 * These constants will be removed in oelib 0.9.0.
 *
 * @deprecated 2010-09-25
 */

// the UTF-8 representation of an en dash
define('UTF8_EN_DASH', chr(0xE2) . chr(0x80) . chr(0x93));
// one day in seconds
define('ONE_DAY', 86400);
// one week in seconds
define('ONE_WEEK', 604800);

// error messages
define('DATABASE_QUERY_ERROR', 'There was an error with the database query.');
define('DATABASE_RESULT_ERROR', 'There was an error with the result of the database query.');