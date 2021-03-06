<?php

declare(strict_types = 1);

namespace OCI\Helper;

use Generator;
use OCI\Debugger\DebuggerDumb;
use OCI\Driver\Connection;
use OCI\Driver\Driver;
use OCI\Driver\DriverInterface;

class Provider
{

    public static function dataWithNoBind(): Generator
    {
        yield 'simple-data' => [
            // 2.3685 inserted 2.369 because scale is 3
            2, 2.3685, 'CURRENT_TIMESTAMP', 1856987
        ];

        yield 'simple-data-with-null' => [
            'null', 'null', 'null', 'null'
        ];

        yield 'simple-data-with-some-null' => [
            // 0.2364 inserted 0.236 because scale is 3
            'null', 0.2364, 'CURRENT_TIMESTAMP', 'null'
        ];
    }

    public static function getDriver(): DriverInterface
    {
        return new Driver(self::getConnection(), new DebuggerDumb());
    }

    /**
     * @return resource
     */
    public static function getConnection()
    {
        require_once 'config-connection.php';

        $connection = new Connection(USERNAME, PASSWORD, SCHEMA);

        return $connection->connect();
    }
}
