<?php

declare(strict_types = 1);

namespace OCI\Debugger;

use OCI\Driver\Parameter\Parameter;

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
     * @param Parameter $parameter Parameter binding.
     * @param mixed $result Any further information.
     */
    public function end(string $query, Parameter $parameter = null, $result = null): void;
}
