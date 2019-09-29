<?php

declare(strict_types = 1);

namespace OCI\Driver;

use OCI\OCITestCase;

class ConnectionTest extends OCITestCase
{

    public function testConnectionThrowException(): void
    {
        $this->expectException(DriverException::class);
        $connection = new Connection('username', '1230', 'schema');
        $connection->connect();
    }
}
