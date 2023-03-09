<?php

namespace ClarkeTechnology\PerformanceCounter;

abstract class PerformanceCounter
{
    protected array $start = [];
    protected array $iterationCount = [];
    protected array $totalElapsedTime = [];
    protected array $averageIterationTime = [];
    protected array $isRunning = [];
    protected array $lapCount = [];
    protected array $laps = [];

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

        $this->iterationCount[$key]++;

        $this->start[$key] = microtime(true);
        $this->lapCount[$key] = 0;
    }

    public function isRunning($key): bool
    {
        return $this->isRunning[$key];
    }

    public function stopAndShow(): array
    {
        $this->stopAll();

        return $this->all();
    }

    public function stopAll(): void
    {
        foreach ($this->getKeys() as $key) {
            $this->stop($key);
        }
    }

    public function getKeys(): array
    {
        return array_keys($this->iterationCount);
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

    public function all(): array
    {
        return array_combine($this->getKeys(), $this->totalElapsedTime);
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
            $this->averageIterationTime[$key],
            $this->lapCount[$key],
        );
    }

    public function reset(): void
    {
        $this->start = [];
        $this->iterationCount = [];
        $this->totalElapsedTime = [];
        $this->averageIterationTime = [];
        $this->lapCount = [];
    }

    public function get($key): array
    {
        return [
            'start' => $this->start[$key],
            'iteration_count' => $this->iterationCount[$key],
            'total_elapsed_time' => $this->totalElapsedTime[$key],
            'average_iteration_time' => $this->averageIterationTime[$key],
            'lap_count' => $this->lapCount[$key],
        ];
    }

    public function lap($key, $newKey = null): array
    {
        $lapTime = microtime(true);

        if ($newKey) {
            $this->setFrozenKey($newKey, $lapTime);
        }

        $this->lapCount[$key]++;

        $lapKey = $this->lapCount[$key].':'.$newKey;

        $this->laps[$key][$lapKey] = round($lapTime - $this->start[$key], 3) * 1000;

        return [
            $lapKey => round($lapTime - $this->start[$key], 3) * 1000
        ];
    }

    public function setFrozenKey($key, $elapsedTime): void
    {
        if (array_key_exists($key, $this->iterationCount)) {
            throw new \RuntimeException("Unable to set frozen key because the key $key already exists");
        }

        $this->start[$key] = $elapsedTime;
        $this->iterationCount[$key] = 1;
        $this->totalElapsedTime[$key] = $elapsedTime;
        $this->averageIterationTime[$key] = $elapsedTime;
        $this->isRunning[$key] = false;
        $this->lapCount[$key] = 1;
    }

    public function laps($key): array
    {
        return $this->laps[$key];
    }
}
