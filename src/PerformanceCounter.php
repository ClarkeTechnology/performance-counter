<?php

namespace ClarkeTechnology\PerformanceCounter;

abstract class PerformanceCounter
{
    protected array $start = [];
    protected array $iterationCount = [];
    protected array $totalElapsedTime = [];
    protected array $averageIterationTime = [];
    protected array $isRunning = [];

    /**
     * Capture the start time for one iteration for a given key
     */
    public function start($key): void
    {
        if (!isset($this->iterationCount[$key])) {
            $this->iterationCount[$key] = 0;
            $this->totalElapsedTime[$key] = 0;
            $this->isRunning[$key] = true;
        }

        $this->iterationCount[$key] ++;

        $this->start[$key] = microtime(true);
    }

    public function isRunning($key): bool
    {
        return $this->isRunning[$key];
    }

    /**
     * Capture the end time for one iteration for a given key
     */
    public function stop($key): void
    {
        $endTime = microtime(true);

        if (!$this->isRunning[$key]) {
            return;
        }

        $this->isRunning[$key] = false;

        $this->totalElapsedTime[$key] += round($endTime - $this->start[$key], 3) * 1000;

        $this->averageIterationTime[$key] = $this->totalElapsedTime[$key] / max($this->iterationCount[$key], 1);
    }

    public function stopAll(): void
    {
        foreach ($this->getKeys() as $key) {
            $this->stop($key);
        }
    }

    public function all(): array
    {
        return array_combine($this->getKeys(), $this->totalElapsedTime);
    }

    public function stopAndShow():array
    {
        $this->stopAll();

        return $this->all();
    }

    /**
     * Elapsed time between start and stop
     *
     * If started and stopped within a loop, the average iteration time will be returned
     */
    public function elapsedTime($key): float
    {
        return $this->averageIterationTime[$key];
    }

    public function clearKey($key): void
    {
        unset(
            $this->start[$key],
            $this->iterationCount[$key],
            $this->totalElapsedTime[$key],
            $this->averageIterationTime[$key]
        );
    }

    public function reset(): void
    {
        $this->start = [];
        $this->iterationCount = [];
        $this->totalElapsedTime = [];
        $this->averageIterationTime = [];
    }

    public function getKeys(): array
    {
        return array_keys($this->iterationCount);
    }
}