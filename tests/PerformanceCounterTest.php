<?php

namespace ClarkeTechnology\PerformanceCounter\Tests;

use PHPUnit\Framework\TestCase;
use ClarkeTechnology\PerformanceCounter\PerformanceCounter;

class PerformanceCounterTest extends TestCase
{
    private string $counterKey1 = 'test_counter1';
    private string $counterKey2 = 'test_counter2';
    private PerformanceCounter $unit;

    protected function setUp(): void
    {
        $this->unit = new PerformanceCounter();
    }

    protected function tearDown(): void
    {
        $this->unit->reset();        
        unset($this->unit);        
    }

    /** @test */
    public function average_lap_time_can_be_obtained(): void
    {
//        $this->unit->start($this->counterKey1);

        for ($i = 1; $i <= 5; $i++) {
            usleep(random_int(100, 1000));
            $this->unit->clock($this->counterKey1, $i);
        }

        $averageLapTime = $this->unit->averageLapTime($this->counterKey1);

        $this->assertCount(5, $this->unit->getTimings($this->counterKey1));
        $this->assertIsFloat($averageLapTime);
    }

    /** @test */
    public function elapsed_time_can_be_obtained_for_multiple_keys(): void
    {
        $this->unit->clock($this->counterKey1);

        for ($i = 1; $i <= 5; $i++) {
            $this->unit->clock($this->counterKey2);
            usleep(random_int(100, 1000));
        }

        $this->unit->stop($this->counterKey1);

        $this->assertIsFloat($this->unit->elapsedTime($this->counterKey1));
        $this->assertIsFloat($this->unit->elapsedTime($this->counterKey2));
    }

    /** @test */
    public function a_key_can_be_cleared(): void
    {
        $this->unit->clock($this->counterKey1);

        $this->unit->clock($this->counterKey2);

        $this->unit->clearKey($this->counterKey2);

        $this->assertContains($this->counterKey1, $this->unit->getKeys());
        $this->assertNotContains($this->counterKey2, $this->unit->getKeys());
    }

    /** @test */
    public function the_counter_can_be_reset(): void
    {
        $this->unit->clock($this->counterKey1);

        $this->unit->clock($this->counterKey2);

        $this->unit->reset();

        $this->assertEmpty($this->unit->getKeys());
    }

    /** @test */
    public function all_keys_can_be_ended_at_once(): void
    {
        $this->unit->clock($this->counterKey1);
        $this->unit->clock($this->counterKey2);

        $key1isRunningAtStart = $this->unit->isRunning($this->counterKey1);
        $key2isRunningAtStart = $this->unit->isRunning($this->counterKey2);

        $this->unit->stopAll();

        $this->assertTrue($key1isRunningAtStart);
        $this->assertTrue($key2isRunningAtStart);
        $this->assertFalse($this->unit->isRunning($this->counterKey1));
        $this->assertFalse($this->unit->isRunning($this->counterKey2));
    }

    /** @test */
    public function a_lap_time_can_be_retrieved(): void
    {
        $lapTimes = [];

        for ($i = 1; $i <= 5; $i++) {
            $lapTimes[$i] = $this->unit->clock($this->counterKey1);
            usleep(random_int(100, 1000));
        }

        $this->assertCount(5, $lapTimes);

        $this->assertSame([
            $this->counterKey1.':0:',
            $this->counterKey1.':1:',
            $this->counterKey1.':2:',
            $this->counterKey1.':3:',
            $this->counterKey1.':4:',
        ], array_keys($this->unit->getTimings($this->counterKey1)));

        $this->assertTrue(
            $this->unit->getTimings($this->counterKey1)[$this->counterKey1.':0:'] <
            $this->unit->getTimings($this->counterKey1)[$this->counterKey1.':1:']);
        $this->assertTrue(
            $this->unit->getTimings($this->counterKey1)[$this->counterKey1.':1:'] <
            $this->unit->getTimings($this->counterKey1)[$this->counterKey1.':2:']);
        $this->assertTrue(
            $this->unit->getTimings($this->counterKey1)[$this->counterKey1.':2:'] <
            $this->unit->getTimings($this->counterKey1)[$this->counterKey1.':3:']);
        $this->assertTrue(
            $this->unit->getTimings($this->counterKey1)[$this->counterKey1.':3:'] <
            $this->unit->getTimings($this->counterKey1)[$this->counterKey1.':4:']);
    }

    /** @test */
    public function lapkeys_are_created_correctly(): void
    {
        $this->unit->clock($this->counterKey1);

        $lapOne = $this->unit->clock($this->counterKey1, 'lap-one');
        $lapTwo = $this->unit->clock($this->counterKey1, 'lap-two');

        $this->assertEquals($this->counterKey1.':1:lap-one', key($lapOne));
        $this->assertEquals($this->counterKey1.':2:lap-two', key($lapTwo));
    }

    /** @test */
    public function the_counter_is_started_by_lap_if_the_key_does_not_exist(): void
    {
        $start = $this->unit->clock($this->counterKey1, 'start');
        $lap1 = $this->unit->clock($this->counterKey1, 'lap');

        $this->assertEquals($this->counterKey1.':0:start', key($start));
        $this->assertEquals($this->counterKey1.':1:lap', key($lap1));
    }

    /** @test */
    public function all_data_can_be_retrieved_for_a_given_key(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->unit->clock($this->counterKey1, "lap-$i");
            usleep(random_int(100, 1000));
        }

        $data = $this->unit->get($this->counterKey1);

        $this->assertEquals(['start', 'total_elapsed_time', 'lap_count', 'average_lap_time', 'laps'], array_keys($data));
        $this->assertIsArray($data['laps']);
    }
}
