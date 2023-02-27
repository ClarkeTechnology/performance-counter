<?php

namespace ClarkeTechnology\PerformanceCounter\Singleton;

use ClarkeTechnology\PerformanceCounter\PerformanceCounter as BluePrint;

/**
 * Calculates the average iteration time for a given process
 *
 * Keys can be nested which enables you to measure the performance of inner and outer loops.
 * Designed to be used as a development utility tool. Recommended to be removed from code after use.
 *
 * @see PerformanceCounterTest::average_process_time_can_be_obtained_for_multiple_keys for a demo
 * of how this works
 *
 * @author Gary Clarke <clarketechnologyltd@gmail.com>
 */
final class PerformanceCounter extends BluePrint
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}