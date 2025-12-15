<?php

declare(strict_types=1);

namespace Elie\OCI\Driver;

use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testConnectionThrowException(): void
    {
        $this->expectException(DriverException::class);
        $connection = new Connection('username', '1230', 'schema');
        $connection->connect();
    }

    /**
     * @throws DriverException
     */
    public function testConnectionOk(): void
    {
        require_once dirname(__DIR__, 2) . '/config-connection.php';
        $connection = new Connection(USERNAME, PASSWORD, SCHEMA);
        $resource = $connection->connect();

        $this->assertIsResource($resource);
    }
}
