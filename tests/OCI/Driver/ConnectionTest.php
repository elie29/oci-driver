<?php

declare(strict_types = 1);

namespace OCI\Driver;

use OCI\OCITestCase;

class ConnectionTest extends OCITestCase
{

    /**
     * @expectedException OCI\Driver\DriverException
     */
    public function testConnectionThrowException(): void
    {
        $connection = new Connection('username', '1230', 'schema');
        $connection->connect();
    }
}
