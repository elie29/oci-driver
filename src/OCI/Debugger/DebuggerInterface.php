<?php

declare(strict_types = 1);

namespace OCI\Debugger;

interface DebuggerInterface
{

    /**
     * Called before the query execution.
     * Useful to set on the timing.
     */
    public function start(): void;

    /**
     * Called after the query execution.
     *
     * @param string $query The sql to be executed.
     * @param array $parameters Parameter binding.
     */
    public function end(string $query, array $parameters): void;
}
