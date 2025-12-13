<?php

declare(strict_types=1);

namespace Elie\OCI\Debugger;

/**
 * @uses symfony/var-dumper
 */
class DebuggerDump implements DebuggerInterface
{
    /** @var float */
    protected float $startTime;

    // Useful for testing
    public array $data = [];

    public function start(): void
    {
        $this->startTime = 1000 * microtime(true);
    }

    public function end(string $query, array $parameters): void
    {
        $endTime = 1000 * microtime(true);

        $this->data = [
            'query' => $query,
            'parameters' => $parameters,
            'duration(ms)' => round($endTime - $this->startTime, 1),
        ];

        dump($this->data);

        // keep chaining
        $this->startTime = $endTime;
    }
}
