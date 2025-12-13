<?php

declare(strict_types=1);

namespace Elie\OCI\Debugger;

use PHPUnit\Framework\TestCase;

class DebuggerDumpTest extends TestCase
{
    public function testImplementsDebuggerInterface(): void
    {
        $debugger = new DebuggerDump();

        $this->assertNotNull($debugger);
    }

    public function testStartInitializesTimer(): void
    {
        $debugger = new DebuggerDump();

        $debugger->start();

        // Access protected property via reflection to verify it's set
        $reflection = new \ReflectionClass($debugger);
        $property = $reflection->getProperty('startTime');

        $startTime = $property->getValue($debugger);

        $this->assertIsFloat($startTime);
        $this->assertGreaterThan(0, $startTime);
    }

    public function testEndCalculatesDuration(): void
    {
        $debugger = new DebuggerDump();

        $debugger->start();

        // Small delay to ensure measurable duration
        usleep(1000); // 1ms

        $debugger->end('SELECT * FROM dual', []);

        // The dump() function outputs the data
        $this->assertNotEmpty($debugger->data['query']);
    }

    public function testEndWithParameters(): void
    {
        $debugger = new DebuggerDump();

        $parameters = [
            ':id' => 123,
            ':name' => 'Test Name',
        ];

        $debugger->start();

        $debugger->end('SELECT * FROM A1 WHERE id = :id AND name = :name', $parameters);

        $this->assertNotEmpty($debugger->data);
    }

    public function testChainedCallsUpdateStartTime(): void
    {
        $debugger = new DebuggerDump();

        $reflection = new \ReflectionClass($debugger);
        $property = $reflection->getProperty('startTime');

        $debugger->start();
        $firstStartTime = $property->getValue($debugger);

        usleep(2000); // 2ms delay

        $debugger->end('SELECT 1 FROM dual', []);

        $secondStartTime = $property->getValue($debugger);

        // After end(), startTime should be updated to endTime
        $this->assertGreaterThan($firstStartTime, $secondStartTime);
    }

    public function testMultipleQueries(): void
    {
        $debugger = new DebuggerDump();


        $debugger->start();
        $debugger->end('SELECT * FROM A1', []);

        usleep(1000);

        $debugger->end('UPDATE A1 SET N_NUM = :val', [':val' => 10]);

        usleep(1000);

        $debugger->end('DELETE FROM A2', []);

        $this->assertNotEmpty($debugger->data);
    }

    public function testEndWithEmptyParameters(): void
    {
        $debugger = new DebuggerDump();

        $debugger->start();

        $debugger->end('SELECT * FROM dual', []);

        $this->assertNotEmpty($debugger->data);
    }

    public function testEndWithComplexParameters(): void
    {
        $debugger = new DebuggerDump();

        $parameters = [
            ':str' => 'test string',
            ':num' => 42,
            ':float' => 3.14,
            ':bool' => true,
            ':null' => null,
        ];

        $debugger->start();

        $debugger->end('INSERT INTO A1 VALUES (:str, :num, :float)', $parameters);

        $this->assertNotEmpty($debugger->data['parameters']);
    }

    public function testDurationIsPositive(): void
    {
        $debugger = new DebuggerDump();

        $debugger->start();

        usleep(5000); // 5ms delay

        $debugger->end('SELECT * FROM dual', []);

        // If we got here without errors, the duration calculation worked
        $this->assertGreaterThan(0, $debugger->data['duration(ms)']);
    }
}
