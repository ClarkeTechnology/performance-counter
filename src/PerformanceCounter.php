<?php

namespace ClarkeTechnology\PerformanceCounter;

final class PerformanceCounter
{
    private array $start = [];
    private array $totalElapsedTime = [];
    private array $averageLapTime = [];
    private array $isRunning = [];
    private array $lapCount = [];
    private array $timings = [];
    private int $multiplier;

    /**
     * @param int $multiplier ms = 1000, µs = 1000000
     */
    public function __construct(int $multiplier = 1000)
    {
        $this->multiplier = $multiplier;
    }

    private function start($key): void
    {
        if (!isset($this->start[$key])) {
            $this->start[$key] = microtime(true);
            $this->lapCount[$key] = 0;
            $this->totalElapsedTime[$key] = 0;
            $this->averageLapTime[$key] = 0;
            $this->isRunning[$key] = true;
        }
    }

    public function isRunning($key): bool
    {
        return $this->isRunning[$key];
    }

    public function stopAndShow(): array
    {
        $this->stopAll();

        return $this->allAverageLapTimes();
    }

    public function stopAll(): void
    {
        foreach ($this->getKeys() as $key) {
            $this->stop($key);
        }
    }

    public function getKeys(): array
    {
        return array_keys($this->start);
    }

    public function stop($key): void
    {
        $stopTime = microtime(true);

        if (!$this->isRunning[$key]) {
            return;
        }

        $this->isRunning[$key] = false;

        $this->totalElapsedTime[$key] += ($stopTime - $this->start[$key]) * $this->multiplier;

        $this->averageLapTime[$key] = $this->averageLapTime($key);
    }

    public function allAverageLapTimes(): array
    {
        return array_combine($this->getKeys(), $this->averageLapTime);
    }

    public function allElapsedTimes(): array
    {
        return array_combine($this->getKeys(), $this->totalElapsedTime);
    }

    public function elapsedTime($key): float
    {
        return $this->totalElapsedTime[$key];
    }

    public function clearKey($key): void
    {
        unset(
            $this->start[$key],
            $this->totalElapsedTime[$key],
            $this->averageLapTime[$key],
            $this->lapCount[$key],
            $this->isRunning[$key],
            $this->timings[$key],
        );
    }

    public function reset(): void
    {
        $this->start = [];
        $this->totalElapsedTime = [];
        $this->averageLapTime = [];
        $this->lapCount = [];
        $this->isRunning = [];
        $this->timings = [];
    }

    public function get($key): array
    {
        return [
            'start' => $this->start[$key],
            'total_elapsed_time' => $this->totalElapsedTime[$key],
            'lap_count' => $this->lapCount[$key],
            'average_lap_time' => $this->averageLapTime($key),
            'laps' => $this->getTimings($key)
        ];
    }

    public function clock($key, $newKey = null): array
    {
        if (!isset($this->start[$key])) {
            $this->start($key);
            $lapKey = $key .':0:'. $newKey;
            $this->timings[$key][$lapKey] = 0;
            return [$lapKey => 0];
        }

        $lapCapture = microtime(true);

        $lapTime = ($lapCapture - $this->start[$key]) * $this->multiplier;

        $lapKey = $key .':'. ++$this->lapCount[$key] .':'. $newKey;

        $this->timings[$key][$lapKey] = $lapTime;

        $this->totalElapsedTime[$key] += $lapTime;

        $this->averageLapTime[$key] = $this->averageLapTime($key);

        return [
            $lapKey => $lapTime
        ];
    }

    public function averageLapTime($key): float
    {
        return $this->totalElapsedTime[$key] / max($this->lapCount[$key], 1);
    }

    public function getTimings($key): array
    {
        return $this->timings[$key];
    }
}
