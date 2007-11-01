<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

// unserialize the configuration array
$globalConfiguration = unserialize(
	$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['oelib']
);

if (isset($globalConfiguration['enableGlobalFunctions'])
	&& $globalConfiguration['enableGlobalFunctions']
) {
	/**
	 * Starts the timer and sets the bucket which the time will be added to.
	 *
	 * If the timer is already running, the previous bucket will be closed
	 * first.
	 *
	 * @param	string	the name of the bucket to open
	 */
	function oB($bucketName = 'default') {
		tx_oelib_timer::getInstance()->openBucket($bucketName);
	}

	/**
	 * Closes the current bucker and returns to the previous bucket (from the
	 * stack of previously used buckets).
	 *
	 * If there is not previous bucket, the timer will be stopped.
	 */
	function rB() {
		tx_oelib_timer::getInstance()->returnToPreviousBucket();
	}
}
?>
