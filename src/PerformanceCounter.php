<?php

namespace ClarkeTechnology\PerformanceCounter;

final class PerformanceCounter
{
    private array $start = [];
    private array $totalElapsedTime = [];
    private array $averageLapTime = [];
    private array $isRunning = [];
    private array $lapCount = [];
    private array $laps = [];
    private int $multiplier;

    /**
     * @param int $multiplier ms = 1000, µs = 1000000
     */
    public function __construct(int $multiplier = 1000)
    {
        $this->multiplier = $multiplier;
    }

    public function start($key): void
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
            $this->laps[$key],
        );
    }

    public function reset(): void
    {
        $this->start = [];
        $this->totalElapsedTime = [];
        $this->averageLapTime = [];
        $this->lapCount = [];
        $this->isRunning = [];
        $this->laps = [];
    }

    public function get($key): array
    {
        return [
            'start' => $this->start[$key],
            'total_elapsed_time' => $this->totalElapsedTime[$key],
            'lap_count' => $this->lapCount[$key],
            'average_lap_time' => $this->averageLapTime($key),
            'laps' => $this->laps($key)
        ];
    }

    public function lap($key, $newKey = null): array
    {
        if (!isset($this->start[$key])) {
            $this->start($key);
            return ['0:'.$newKey => 0];
        }

        $lapCapture = microtime(true);

        if ($newKey) {
            $this->setFrozenKey($newKey, $lapCapture);
        }

        $lapTime = ($lapCapture - $this->start[$key]) * $this->multiplier;

        $lapKey = ++$this->lapCount[$key].':'.$newKey;

        $this->laps[$key][$lapKey] = $lapTime;

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

    public function setFrozenKey($key, $elapsedTime): void
    {
        if (array_key_exists($key, $this->start)) {
            throw new \RuntimeException("Unable to set frozen key because the key $key already exists");
        }

        $this->start[$key] = microtime(true);
        $this->totalElapsedTime[$key] = $elapsedTime;
        $this->isRunning[$key] = false;
        $this->lapCount[$key] = 1;
        $this->averageLapTime[$key] = $elapsedTime;
        $this->laps[$key] = [];
    }

    public function laps($key): array
    {
        return $this->laps[$key] ?? [];
    }
}
