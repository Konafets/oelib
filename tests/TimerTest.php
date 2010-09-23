<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the tx_oelib_Timer class in the "oelib" extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_TimerTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_Timer
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = tx_oelib_Timer::getInstance();
	}

	protected function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}

	public function testGetInstance() {
		$this->assertTrue(is_object($this->fixture));
	}

	public function testSingleton() {
		$this->assertSame($this->fixture, tx_oelib_Timer::getInstance());
	}

	public function testStatisticsWithoutBuckets() {
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertTrue(
			is_array($statistics)
		);
		$this->assertEquals(
			0, count($statistics)
		);
		$this->assertNotContains(
			'<td>', $this->fixture->getStatistics()
		);
	}

	public function testStatisticsTableContainsTableHeadersWithScope() {
		$this->fixture->openBucket();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertContains(
			'<th scope="col">', $statisticsAsHtml
		);
	}

	public function testStatisticsForDefaultBucketWithDelay() {
		$this->fixture->openBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertEquals(
			1, count($statistics)
		);
		$this->assertEquals(
			'default', $statistics[0]['bucketName']
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertContains(
			(string) $statistics[0]['absoluteTime'], $statisticsAsHtml
		);
	}

	public function testStatisticsForDefaultBucketWithDelayUsingShortcut() {
		tx_oelib_Timer::oB();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertEquals(
			1, count($statistics)
		);
		$this->assertEquals(
			'default', $statistics[0]['bucketName']
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertContains(
			(string) $statistics[0]['absoluteTime'], $statisticsAsHtml
		);
	}

	public function testStatisticsForOneBucketWithDelay() {
		$this->fixture->openBucket('test');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertEquals(
			1, count($statistics)
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertContains(
			(string) $statistics[0]['absoluteTime'], $statisticsAsHtml
		);
	}

	public function testStatisticsForOneBucketWithDelayUsingShortcut() {
		tx_oelib_Timer::oB('test');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertEquals(
			1, count($statistics)
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertContains(
			(string) $statistics[0]['absoluteTime'], $statisticsAsHtml
		);
	}

	public function testStatisticsForOneBucketWithoutDelay() {
		$this->fixture->openBucket('test');
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertEquals(
			1, count($statistics)
		);
		$this->assertEquals(
			'test', $statistics[0]['bucketName']
		);
	}

	public function testStopTimer() {
		$this->fixture->openBucket('test');
		$this->fixture->stopTimer();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertEquals(
			0, $statistics[0]['absoluteTime'], '', .05
		);
	}

	public function testStatisticsCloseAllBuckets() {
		$this->fixture->openBucket('test');
		$statistics = $this->fixture->getStatistics();
		// Sleeps 10000 microseconds (= 1/100 second).
		usleep(10000);
		$this->assertEquals(
			$statistics, $this->fixture->getStatistics()
		);
	}

	public function testStatisticsForTwoSecondIsBigger() {
		$this->fixture->openBucket('test_1');
		$this->fixture->openBucket('test_2');
		// Sleeps 10000 microseconds (= 1/100 second).
		usleep(10000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertEquals(
			2, count($statistics)
		);
		$this->assertEquals(
			'test_2', $statistics[0]['bucketName']
		);
		$this->assertTrue(
			$statistics[0]['absoluteTime'] > $statistics[1]['absoluteTime']
		);
	}

	public function testStatisticsForTwoBucketsReopenFirst() {
		$this->fixture->openBucket('test_1');
		$this->fixture->openBucket('test_2');
		$this->fixture->openBucket('test_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertEquals(
			2, count($statistics)
		);
		$this->assertEquals(
			'test_1', $statistics[0]['bucketName']
		);
		$this->assertTrue(
			$statistics[0]['absoluteTime'] > $statistics[1]['absoluteTime']
		);
	}

	public function testHtmlSpecialCharsForBucketName() {
		$this->fixture->openBucket('foo&bar');
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertEquals(
			'foo&bar', $statistics[0]['bucketName']
		);
		$this->assertContains(
			'foo&amp;bar', $this->fixture->getStatistics()
		);
	}

	public function testDestroyAllBuckets() {
		$this->fixture->openBucket('test');
		$this->fixture->destroyAllBuckets();
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertTrue(
			is_array($statistics)
		);
		$this->assertEquals(
			0, count($statistics)
		);
		$this->assertNotContains(
			'<td>', $this->fixture->getStatistics()
		);
	}

	public function testReturnFromNoBucketDoesNotOpenAnyBuckets() {
		$this->fixture->returnToPreviousBucket();
		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertTrue(
			is_array($statistics)
		);
		$this->assertEquals(
			0, count($statistics)
		);
	}

	public function testReturnFromFirstBucketClosesBucketAndStopsTimer() {
		$this->fixture->openBucket('test');
		$this->fixture->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertEquals(
			1, count($statistics)
		);
		$this->assertEquals(
			0, $statistics[0]['absoluteTime'], '', .04
		);
	}

	public function testReturnFromSecondBucketReopensFirstBucket() {
		$this->fixture->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('bucket_2');
		$this->fixture->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertEquals(
			'bucket_1', $statistics[0]['bucketName']
		);
		$this->assertEquals(
			'bucket_2', $statistics[1]['bucketName']
		);
		$this->assertEquals(
			.2, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertEquals(
			0, $statistics[1]['absoluteTime'], '', .04
		);
	}

	public function testReturnFromSecondBucketReopensFirstBucketUsingShortcut() {
		$this->fixture->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('bucket_2');
		tx_oelib_Timer::rB();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertEquals(
			'bucket_1', $statistics[0]['bucketName']
		);
		$this->assertEquals(
			'bucket_2', $statistics[1]['bucketName']
		);
		$this->assertEquals(
			.2, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertEquals(
			0, $statistics[1]['absoluteTime'], '', .04
		);
	}

	public function testReturnFromThirdBucketTwoTimesReopensFirstBucket() {
		$this->fixture->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('bucket_2');
		$this->fixture->openBucket('bucket_3');
		$this->fixture->returnToPreviousBucket();
		$this->fixture->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertEquals(
			3, count($statistics)
		);
		$this->assertEquals(
			'bucket_1', $statistics[0]['bucketName']
		);
		$this->assertEquals(
			.2, $statistics[0]['absoluteTime'], '', .04
		);
	}

	public function testReturnFromSecondBucketClosesBucketAndStopsTimer() {
		$this->fixture->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('bucket_2');
		$this->fixture->returnToPreviousBucket();
		$this->fixture->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertEquals(
			2, count($statistics)
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertEquals(
			0, $statistics[1]['absoluteTime'], '', .04
		);
	}

	public function testOpenSameBucketTwiceWillAllowOnlyOnePreviousBucket() {
		$this->fixture->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->returnToPreviousBucket();
		$this->fixture->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertEquals(
			1, count($statistics)
		);
		$this->assertEquals(
			.2, $statistics[0]['absoluteTime'], '', .04
		);
	}
}
?>