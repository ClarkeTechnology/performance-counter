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
        $this->unit = PerformanceCounter::getInstance();
        $this->unit->reset();
    }

    protected function tearDown(): void
    {
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
            $this->unit->end($this->counterKey2);
        }

        $this->unit->end($this->counterKey1);

        $this->assertGreaterThan(10, $this->unit->elapsedTime($this->counterKey1));
        $this->assertLessThan(100, $this->unit->elapsedTime($this->counterKey2));
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
}