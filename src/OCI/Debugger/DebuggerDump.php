<?php

declare(strict_types = 1);

namespace OCI\Debugger;

use Debugger;
use OCI\Driver\Parameter\Parameter;

/**
 * @uses symfony/var-dumper
 */
class DebuggerDump implements DebuggerInterface
{

    private $startTime = 0;

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
    public function end(string $query, Parameter $parameter = null, $result = null): void
    {
        $duration = 1000 * microtime(true);

        dump(compact($query, $parameter, $result, $duration));

        // keep chaining
        $this->startTime = $duration;
    }
}
