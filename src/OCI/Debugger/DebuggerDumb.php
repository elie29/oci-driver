<?php

declare(strict_types=1);

namespace Elie\OCI\Debugger;

class DebuggerDumb implements DebuggerInterface
{

    public bool $started = false;

    public function start(): void
    {
        $this->started = true;
    }

    public function end(string $query, array $parameters): void
    {
        $this->started = false;
    }
}
