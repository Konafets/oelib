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
 * Testcase for the timer class in the 'oelib' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_oelib
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('oelib')
	.'class.tx_oelib_timer.php');

class tx_oelib_timer_testcase extends tx_phpunit_testcase {
	private $fixture;

	protected function setUp() {
		$this->fixture =& tx_oelib_timer::getInstance();
	}

	protected function tearDown() {
		$this->fixture->destroyAllBuckets();
		unset($this->fixture);
	}


	public function testGetInstance() {
		$this->assertTrue(is_object($this->fixture));
	}

	public function testSingleton() {
		$this->assertSame($this->fixture, tx_oelib_timer::getInstance());
	}

	public function testStatisticsWithoutBuckets() {
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertEquals(
			0, count($statistics)
		);
		$this->assertNotContains(
			'<td>', $this->fixture->getStatistics()
		);
	}

	public function testStatisticsForOneBucket() {
		$this->fixture->openBucket('test');
		// Sleep 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$statisticsAsHtml = $this->fixture->getStatistics();
		$this->assertEquals(
			1, count($statistics)
		);
		$this->assertContains(
			'.1', (string) $statistics[0]['absoluteTime']
		);
		$this->assertContains(
			'.1', $statisticsAsHtml
		);
	}

	public function testStopTimer() {
		$this->fixture->openBucket('test');
		// Sleep 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->stopTimer();
		// Sleep 200000 microseconds (= 2/10 second).
		usleep(200000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertContains(
			'.1', (string) $statistics[0]['absoluteTime']
		);
	}

	public function testStatisticsCloseAllBuckets() {
		$this->fixture->openBucket('test');
		// Sleep 10000 microseconds (= 1/100 second).
		usleep(10000);
		$statistics = $this->fixture->getStatistics();
		usleep(10000);
		$this->assertEquals(
			$statistics, $this->fixture->getStatistics()
		);
	}

	public function testStatisticsForTwoSecondIsBigger() {
		$this->fixture->openBucket('test_1');
		// Sleep 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('test_2');
		// Sleep 200000 microseconds (= 2/10 second).
		usleep(200000);
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
		// Sleep 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('test_2');
		// Sleep 100000 microseconds (= 1/10 second).
		usleep(100000);
		$this->fixture->openBucket('test_1');
		// Sleep 100000 microseconds (= 1/10 second).
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
		// Sleep 100000 microseconds (= 1/10 second).
		usleep(100000);
		$statistics = $this->fixture->getStatisticsAsRawData();
		$this->assertEquals(
			'foo&bar', $statistics[0]['bucketName']
		);
		$this->assertContains(
			'foo&amp;bar', $this->fixture->getStatistics()
		);
	}
}

?>
