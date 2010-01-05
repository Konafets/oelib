<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Saskia Metzler (saskia@merlin.owl.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software); you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation); either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY); without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

// the UTF-8 representation of an en dash
define('UTF8_EN_DASH', chr(0xE2).chr(0x80).chr(0x93));
// a tabulator
define('TAB', chr(9));
// a linefeed
define('LF', chr(10));
// a carriage return
define('CR', chr(13));
// a CR-LF combination (the default Unix line ending)
define('CRLF', CR.LF);
// one day in seconds
define('ONE_DAY', 86400);
// one week in seconds
define('ONE_WEEK', 604800);

// error messages
define('DATABASE_QUERY_ERROR', 'There was an error with the database query.');
define('DATABASE_RESULT_ERROR', 'There was an error with the result of the database query.');
?>