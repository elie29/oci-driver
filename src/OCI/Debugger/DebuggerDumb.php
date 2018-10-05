<?php

declare(strict_types = 1);

namespace OCI\Debugger;

use OCI\Driver\Parameter\Parameter;

class DebuggerDumb implements DebuggerInterface
{

    public function start(): void
    {
    }

    public function end(string $query, Parameter $parameter, $result = null): void
    {
    }
}
