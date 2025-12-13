<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

use Elie\OCI\Debugger\DebuggerDumb;
use Elie\OCI\Driver\Connection;
use Elie\OCI\Driver\Driver;
use Elie\OCI\Driver\DriverException;
use Elie\OCI\Driver\DriverInterface;
use Generator;

class Provider
{
    public static function dataWithNoBind(): Generator
    {
        yield 'simple-data' => [
            // 2.3685 inserted 2.369 because scale is 3
            2,
            2.3685,
            'CURRENT_TIMESTAMP',
            1856987,
        ];

        yield 'simple-data-with-null' => [
            'null',
            'null',
            'null',
            'null',
        ];

        yield 'simple-data-with-some-null' => [
            // 0.2364 inserted 0.236 because scale is 3
            'null',
            0.2364,
            'CURRENT_TIMESTAMP',
            'null',
        ];
    }

    /**
     * @throws DriverException
     */
    public static function getDriver(): DriverInterface
    {
        return new Driver(self::getConnection(), new DebuggerDumb());
    }

    /**
     * @return resource
     * @throws DriverException
     */
    public static function getConnection()
    {
        require_once dirname(__DIR__, 2) . '/config-connection.php';

        $connection = new Connection(USERNAME, PASSWORD, SCHEMA);

        return $connection->connect();
    }
}
