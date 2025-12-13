<?php

declare(strict_types=1);

namespace Elie\OCI\Debugger;

class DebuggerDumb implements DebuggerInterface
{
    public function start(): void
    {
    }

    public function end(string $query, array $parameters): void
    {
    }
}
