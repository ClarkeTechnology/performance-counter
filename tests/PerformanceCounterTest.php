<?php

namespace ClarkeTechnology\PerformanceCounter\Tests;

use PHPUnit\Framework\TestCase;
use ClarkeTechnology\PerformanceCounter\Dependency\PerformanceCounter;
use ClarkeTechnology\PerformanceCounter\PerformanceCounter as BluePrint;

class PerformanceCounterTest extends TestCase
{
    protected string $counterKey1 = 'test_counter1';
    protected string $counterKey2 = 'test_counter2';
    protected BluePrint $unit;

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
    public function average_process_time_can_be_obtained_for_multiple_keys(): void
    {
        $this->unit->start($this->counterKey1);

        usleep(random_int(100, 100000));

        for ($i = 1; $i <= 5; $i++) {
            $this->unit->start($this->counterKey2);
            usleep(random_int(100, 100000));
            $this->unit->stop($this->counterKey2);
        }

        $this->unit->stop($this->counterKey1);

        $this->assertGreaterThan(10, $this->unit->elapsedTime($this->counterKey1));
        $this->assertLessThan(300, $this->unit->elapsedTime($this->counterKey2));
    }

    /** @test */
    public function a_key_can_be_cleared(): void
    {
        $this->unit->start($this->counterKey1);

        $this->unit->start($this->counterKey2);

        $this->unit->clearKey($this->counterKey2);

        $this->assertContains($this->counterKey1, $this->unit->getKeys());
        $this->assertNotContains($this->counterKey2, $this->unit->getKeys());
    }

    /** @test */
    public function the_counter_can_be_reset(): void
    {
        $this->unit->start($this->counterKey1);

        $this->unit->start($this->counterKey2);

        $this->unit->reset();

        $this->assertEmpty($this->unit->getKeys());
    }

    /** @test */
    public function all_keys_can_be_ended_at_once(): void
    {
        $this->unit->start($this->counterKey1);
        $this->unit->start($this->counterKey2);

        $key1isRunningAtStart = $this->unit->isRunning($this->counterKey1);
        $key2isRunningAtStart = $this->unit->isRunning($this->counterKey2);

        $this->unit->stopAll();

        $this->assertTrue($key1isRunningAtStart);
        $this->assertTrue($key2isRunningAtStart);
        $this->assertFalse($this->unit->isRunning($this->counterKey1));
        $this->assertFalse($this->unit->isRunning($this->counterKey2));
    }

    /** @test */
    public function all_keys_can_be_retrieved_in_key_time_array_format(): void
    {
        $this->unit->start($this->counterKey1);
        $this->unit->start($this->counterKey2);
        $this->unit->stop($this->counterKey2);
        usleep(1000);
        $this->unit->stop($this->counterKey1);

        $this->assertSame([
            $this->counterKey1 => 1.0,
            $this->counterKey2 => 0.0
        ], $this->unit->all());
    }
}