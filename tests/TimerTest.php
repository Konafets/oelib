<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Test case.
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

	/**
	 * @test
	 */
	public function getInstance() {
		$this->assertTrue(is_object($this->fixture));
	}

	/**
	 * @test
	 */
	public function singleton() {
		$this->assertSame($this->fixture, tx_oelib_Timer::getInstance());
	}

	/**
	 * @test
	 */
	public function statisticsWithoutBuckets() {
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertTrue(
			is_array($statistics)
		);
		$this->assertSame(
			0, count($statistics)
		);
		$this->assertNotContains(
			'<td>', $this->fixture->getStatistics()
		);
	}

	/**
	 * @test
	 */
	public function statisticsTableContainsTableHeadersWithScope() {
		$this->fixture->openBucket();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertContains(
			'<th scope="col">', $statisticsAsHtml
		);
	}

	/**
	 * @test
	 */
	public function statisticsForDefaultBucketWithDelay() {
		$this->fixture->openBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertSame(
			1, count($statistics)
		);
		$this->assertSame(
			'default', $statistics[0]['bucketName']
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertContains(
			(string) $statistics[0]['absoluteTime'], $statisticsAsHtml
		);
	}

	/**
	 * @test
	 */
	public function statisticsForDefaultBucketWithDelayUsingShortcut() {
		tx_oelib_Timer::oB();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertSame(
			1, count($statistics)
		);
		$this->assertSame(
			'default', $statistics[0]['bucketName']
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertContains(
			(string) $statistics[0]['absoluteTime'], $statisticsAsHtml
		);
	}

	/**
	 * @test
	 */
	public function statisticsForOneBucketWithDelay() {
		$this->fixture->openBucket('test');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertSame(
			1, count($statistics)
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertContains(
			(string) $statistics[0]['absoluteTime'], $statisticsAsHtml
		);
	}

	/**
	 * @test
	 */
	public function statisticsForOneBucketWithDelayUsingShortcut() {
		tx_oelib_Timer::oB('test');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertSame(
			1, count($statistics)
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertContains(
			(string) $statistics[0]['absoluteTime'], $statisticsAsHtml
		);
	}

	/**
	 * @test
	 */
	public function statisticsForOneBucketWithoutDelay() {
		$this->fixture->openBucket('test');
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertSame(
			1, count($statistics)
		);
		$this->assertSame(
			'test', $statistics[0]['bucketName']
		);
	}

	/**
	 * @test
	 */
	public function stopTimer() {
		$this->fixture->openBucket('test');
		$this->fixture->stopTimer();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertEquals(
			0, $statistics[0]['absoluteTime'], '', .05
		);
	}

	/**
	 * @test
	 */
	public function statisticsCloseAllBuckets() {
		$this->fixture->openBucket('test');
		$statistics = $this->fixture->getStatistics();
		// Sleeps 10000 microseconds (= 1/100 second).
		usleep(10000);
		$this->assertSame(
			$statistics, $this->fixture->getStatistics()
		);
	}

	/**
	 * @test
	 */
	public function statisticsForTwoSecondIsBigger() {
		$this->fixture->openBucket('test_1');
		$this->fixture->openBucket('test_2');
		// Sleeps 10000 microseconds (= 1/100 second).
		usleep(10000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertSame(
			2, count($statistics)
		);
		$this->assertSame(
			'test_2', $statistics[0]['bucketName']
		);
		$this->assertTrue(
			$statistics[0]['absoluteTime'] > $statistics[1]['absoluteTime']
		);
	}

	/**
	 * @test
	 */
	public function statisticsForTwoBucketsReopenFirst() {
		$this->fixture->openBucket('test_1');
		$this->fixture->openBucket('test_2');
		$this->fixture->openBucket('test_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertSame(
			2, count($statistics)
		);
		$this->assertSame(
			'test_1', $statistics[0]['bucketName']
		);
		$this->assertTrue(
			$statistics[0]['absoluteTime'] > $statistics[1]['absoluteTime']
		);
	}

	/**
	 * @test
	 */
	public function htmlSpecialCharsForBucketName() {
		$this->fixture->openBucket('foo&bar');
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertSame(
			'foo&bar', $statistics[0]['bucketName']
		);
		$this->assertContains(
			'foo&amp;bar', $this->fixture->getStatistics()
		);
	}

	/**
	 * @test
	 */
	public function destroyAllBuckets() {
		$this->fixture->openBucket('test');
		$this->fixture->destroyAllBuckets();
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertTrue(
			is_array($statistics)
		);
		$this->assertSame(
			0, count($statistics)
		);
		$this->assertNotContains(
			'<td>', $this->fixture->getStatistics()
		);
	}

	/**
	 * @test
	 */
	public function returnFromNoBucketDoesNotOpenAnyBuckets() {
		$this->fixture->returnToPreviousBucket();
		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertTrue(
			is_array($statistics)
		);
		$this->assertSame(
			0, count($statistics)
		);
	}

	/**
	 * @test
	 */
	public function returnFromFirstBucketClosesBucketAndStopsTimer() {
		$this->fixture->openBucket('test');
		$this->fixture->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertSame(
			1, count($statistics)
		);
		$this->assertEquals(
			0, $statistics[0]['absoluteTime'], '', .04
		);
	}

	/**
	 * @test
	 */
	public function returnFromSecondBucketReopensFirstBucket() {
		$this->fixture->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('bucket_2');
		$this->fixture->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertSame(
			'bucket_1', $statistics[0]['bucketName']
		);
		$this->assertSame(
			'bucket_2', $statistics[1]['bucketName']
		);
		$this->assertEquals(
			.2, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertEquals(
			0, $statistics[1]['absoluteTime'], '', .04
		);
	}

	/**
	 * @test
	 */
	public function returnFromSecondBucketReopensFirstBucketUsingShortcut() {
		$this->fixture->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('bucket_2');
		tx_oelib_Timer::rB();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertSame(
			'bucket_1', $statistics[0]['bucketName']
		);
		$this->assertSame(
			'bucket_2', $statistics[1]['bucketName']
		);
		$this->assertEquals(
			.2, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertEquals(
			0, $statistics[1]['absoluteTime'], '', .04
		);
	}

	/**
	 * @test
	 */
	public function returnFromThirdBucketTwoTimesReopensFirstBucket() {
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

		$this->assertSame(
			3, count($statistics)
		);
		$this->assertSame(
			'bucket_1', $statistics[0]['bucketName']
		);
		$this->assertEquals(
			.2, $statistics[0]['absoluteTime'], '', .04
		);
	}

	/**
	 * @test
	 */
	public function returnFromSecondBucketClosesBucketAndStopsTimer() {
		$this->fixture->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('bucket_2');
		$this->fixture->returnToPreviousBucket();
		$this->fixture->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->fixture->getStatisticsAsRawData();

		$this->assertSame(
			2, count($statistics)
		);
		$this->assertEquals(
			.1, $statistics[0]['absoluteTime'], '', .04
		);
		$this->assertEquals(
			0, $statistics[1]['absoluteTime'], '', .04
		);
	}

	/**
	 * @test
	 */
	public function openSameBucketTwiceWillAllowOnlyOnePreviousBucket() {
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

		$this->assertSame(
			1, count($statistics)
		);
		$this->assertEquals(
			.2, $statistics[0]['absoluteTime'], '', .04
		);
	}
}
?>