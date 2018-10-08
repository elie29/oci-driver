<?php

declare(strict_types = 1);

namespace OCI\Helper;

use OCI\Debugger\DebuggerDumb;
use OCI\Debugger\DebuggerDump;
use OCI\Debugger\DebuggerInterface;
use OCI\Driver\Driver;
use OCI\Driver\DriverInterface;
use OCI\Helper\SessionInit;

/**
 * Helper for creating new or one instance of an OCI Driver Service.
 * Useful in case we don't have a container.
 */
class Factory
{

    private static $connection;
    private static $env = 'dev';

    /**
     * <b>Initialize the factory with the main connection</b>
     *
     * @param resource $connection
     * @param string $env dev|prod for DebuggerInterface
     */
    public static function init($connection, string $env): void
    {
        self::$connection = $connection;
        self::$env = $env;
    }

    /**
     * @return DriverInterface A singleton OCI Driver service
     *  based on a previous call to self::init.
     */
    public static function get(): DriverInterface
    {
        static $driver = null;

        if ($driver === null) {
            $driver = self::create(self::$connection, self::$env);
        }

        return $driver;
    }

    /**
     * @param resource $connection
     * @param string $env dev|prod for DebuggerInterface
     *
     * @return DriverInterface A new instance of OCI Driver service.
     */
    public static function create($connection, string $env): DriverInterface
    {
        $debugger = $env === 'dev'
            ? new DebuggerDump()
            : new DebuggerDumb();

        $db = new Driver($connection, $debugger);

        // Init Oracle session
        $init = new SessionInit();
        $init->alterSession($db);

        return $db;
    }
}
