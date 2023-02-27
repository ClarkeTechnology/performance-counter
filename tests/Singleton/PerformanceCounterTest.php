<?php

namespace ClarkeTechnology\PerformanceCounter\Tests\Singleton;

use PHPUnit\Framework\TestCase;
use ClarkeTechnology\PerformanceCounter\Singleton\PerformanceCounter;
use ClarkeTechnology\PerformanceCounter\PerformanceCounter as BluePrint;
use \ClarkeTechnology\PerformanceCounter\Tests\PerformanceCounterTest as TestBluePrint;

/**
 * Uses a Singleton instance as the unit under test
 */
class PerformanceCounterTest extends TestBluePrint
{
    protected BluePrint $unit;

    protected function setUp(): void
    {
        $this->unit = PerformanceCounter::getInstance();
    }
}
