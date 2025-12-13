<?php

declare(strict_types=1);

namespace Elie\OCI\Debugger;

use PHPUnit\Framework\TestCase;

class DebuggerDumbTest extends TestCase
{

    public function testImplementsDebuggerInterface(): void
    {
        $debugger = new DebuggerDumb();

        $this->assertNotEmpty($debugger);
        $this->assertFalse($debugger->started);
    }

    public function testStartDoesNothing(): void
    {
        $debugger = new DebuggerDumb();

        // Should not throw any exception
        $debugger->start();

        $this->assertTrue($debugger->started);
    }

    public function testEndDoesNothing(): void
    {
        $debugger = new DebuggerDumb();

        // Should not throw any exception
        $debugger->end('SELECT * FROM dual', [':param' => 'value']);

        $this->assertFalse($debugger->started);
    }

    public function testChainedCalls(): void
    {
        $debugger = new DebuggerDumb();

        // Multiple calls should work without issues
        $debugger->start();
        $debugger->end('SELECT * FROM A1', []);
        $debugger->start();
        $debugger->end('UPDATE A1 SET N_NUM = :val', [':val' => 10]);
        $debugger->start();
        $debugger->end('DELETE FROM A2', []);

        $this->assertFalse($debugger->started);
    }

    public function testEndWithEmptyParameters(): void
    {
        $debugger = new DebuggerDumb();

        $debugger->start();
        $debugger->end('SELECT 1 FROM dual', []);

        $this->assertFalse($debugger->started);
    }

    public function testEndWithComplexParameters(): void
    {
        $debugger = new DebuggerDumb();

        $parameters = [
            ':str' => 'test string',
            ':num' => 42,
            ':float' => 3.14,
            ':bool' => true,
            ':null' => null,
            ':array' => ['nested', 'array'],
        ];

        $debugger->start();
        $debugger->end('SELECT * FROM A1 WHERE id = :num', $parameters);

        $this->assertFalse($debugger->started);
    }
}
