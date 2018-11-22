<?php

declare(strict_types = 1);

namespace OCI\Debugger;

use Debugger;

/**
 * @uses symfony/var-dumper
 */
class DebuggerDump implements DebuggerInterface
{

    protected $startTime = 0;

    /**
     * {@inheritDoc}
     * @see \Common\Utils\Debugger\DebuggerInterface::start()
     */
    public function start(): void
    {
        $this->startTime = 1000 * microtime(true);
    }

    /**
     * {@inheritDoc}
     * @see \Common\Utils\Debugger\DebuggerInterface::end()
     */
    public function end(string $query, array $parameters): void
    {
        $endTime = 1000 * microtime(true);

        dump([
            'query' => $query,
            'parameters' => $parameters,
            'duration(ms)' => round($endTime - $this->startTime, 1)
        ]);

        // keep chaining
        $this->startTime = $endTime;
    }
}
