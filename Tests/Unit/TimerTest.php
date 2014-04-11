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
class Tx_Oelib_TimerTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_Timer
	 */
	private $subject;

	protected function setUp() {
		$this->subject = Tx_Oelib_Timer::getInstance();
	}

	protected function tearDown() {
		$this->subject->destroyAllBuckets();
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getInstance() {
		$this->assertTrue(is_object($this->subject));
	}

	/**
	 * @test
	 */
	public function singleton() {
		$this->assertSame($this->subject, Tx_Oelib_Timer::getInstance());
	}

	/**
	 * @test
	 */
	public function statisticsWithoutBuckets() {
		$statistics = $this->subject->getStatisticsAsRawData();
		$this->assertTrue(
			is_array($statistics)
		);
		$this->assertSame(
			0, count($statistics)
		);
		$this->assertNotContains(
			'<td>', $this->subject->getStatistics()
		);
	}

	/**
	 * @test
	 */
	public function statisticsTableContainsTableHeadersWithScope() {
		$this->subject->openBucket();
		$statisticsAsHtml = $this->subject->getStatistics();
		$this->assertContains(
			'<th scope="col">', $statisticsAsHtml
		);
	}

	/**
	 * @test
	 */
	public function statisticsForDefaultBucketWithDelay() {
		$this->subject->openBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->subject->getStatisticsAsRawData();
		$statisticsAsHtml = $this->subject->getStatistics();
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
		Tx_Oelib_Timer::oB();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->subject->getStatisticsAsRawData();
		$statisticsAsHtml = $this->subject->getStatistics();
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
		$this->subject->openBucket('test');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->subject->getStatisticsAsRawData();
		$statisticsAsHtml = $this->subject->getStatistics();
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
		Tx_Oelib_Timer::oB('test');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->subject->getStatisticsAsRawData();
		$statisticsAsHtml = $this->subject->getStatistics();
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
		$this->subject->openBucket('test');
		$statistics = $this->subject->getStatisticsAsRawData();
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
		$this->subject->openBucket('test');
		$this->subject->stopTimer();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->subject->getStatisticsAsRawData();
		$this->assertEquals(
			0, $statistics[0]['absoluteTime'], '', .05
		);
	}

	/**
	 * @test
	 */
	public function statisticsCloseAllBuckets() {
		$this->subject->openBucket('test');
		$statistics = $this->subject->getStatistics();
		// Sleeps 10000 microseconds (= 1/100 second).
		usleep(10000);
		$this->assertSame(
			$statistics, $this->subject->getStatistics()
		);
	}

	/**
	 * @test
	 */
	public function statisticsForTwoSecondIsBigger() {
		$this->subject->openBucket('test_1');
		$this->subject->openBucket('test_2');
		// Sleeps 10000 microseconds (= 1/100 second).
		usleep(10000);
		$statistics = $this->subject->getStatisticsAsRawData();
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
		$this->subject->openBucket('test_1');
		$this->subject->openBucket('test_2');
		$this->subject->openBucket('test_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->subject->getStatisticsAsRawData();
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
		$this->subject->openBucket('foo&bar');
		$statistics = $this->subject->getStatisticsAsRawData();
		$this->assertSame(
			'foo&bar', $statistics[0]['bucketName']
		);
		$this->assertContains(
			'foo&amp;bar', $this->subject->getStatistics()
		);
	}

	/**
	 * @test
	 */
	public function destroyAllBuckets() {
		$this->subject->openBucket('test');
		$this->subject->destroyAllBuckets();
		$statistics = $this->subject->getStatisticsAsRawData();
		$this->assertTrue(
			is_array($statistics)
		);
		$this->assertSame(
			0, count($statistics)
		);
		$this->assertNotContains(
			'<td>', $this->subject->getStatistics()
		);
	}

	/**
	 * @test
	 */
	public function returnFromNoBucketDoesNotOpenAnyBuckets() {
		$this->subject->returnToPreviousBucket();
		$statistics = $this->subject->getStatisticsAsRawData();

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
		$this->subject->openBucket('test');
		$this->subject->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->subject->getStatisticsAsRawData();

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
		$this->subject->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->subject->openBucket('bucket_2');
		$this->subject->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->subject->getStatisticsAsRawData();

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
		$this->subject->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->subject->openBucket('bucket_2');
		Tx_Oelib_Timer::rB();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->subject->getStatisticsAsRawData();

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
		$this->subject->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->subject->openBucket('bucket_2');
		$this->subject->openBucket('bucket_3');
		$this->subject->returnToPreviousBucket();
		$this->subject->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->subject->getStatisticsAsRawData();

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
		$this->subject->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->subject->openBucket('bucket_2');
		$this->subject->returnToPreviousBucket();
		$this->subject->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->subject->getStatisticsAsRawData();

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
		$this->subject->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->subject->openBucket('bucket_1');
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->subject->returnToPreviousBucket();
		$this->subject->returnToPreviousBucket();
		// Sleeps 100000 microseconds (= 1/10 second).
		usleep(100000);

		$statistics = $this->subject->getStatisticsAsRawData();

		$this->assertSame(
			1, count($statistics)
		);
		$this->assertEquals(
			.2, $statistics[0]['absoluteTime'], '', .04
		);
	}
}