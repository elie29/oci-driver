<?php

declare(strict_types = 1);

namespace OCI\Helper;

use OCI\Driver\DriverInterface;

/**
 * The following environment variables are required for default date format:
 *
 * NLS_TIME_FORMAT='HH24:MI:SS'
 * NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'
 * NLS_TIMESTAMP_FORMAT='YYYY-MM-DD HH24:MI:SS'
 * NLS_TIMESTAMP_TZ_FORMAT='YYYY-MM-DD HH24:MI:SS TZH:TZM'
 */
class SessionInit
{

    /**
     * @var array
     */
    protected $defaultSessionVars = [
        'NLS_TIME_FORMAT' => 'HH24:MI:SS',
        'NLS_DATE_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
        'NLS_TIMESTAMP_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
        'NLS_TIMESTAMP_TZ_FORMAT' => 'YYYY-MM-DD HH24:MI:SS TZH:TZM',
        'NLS_NUMERIC_CHARACTERS' => '.,',
    ];

    /**
     * @param DriverInterface $driver
     */
    public function alterSession(DriverInterface $driver): void
    {
        $vars = [];

        foreach ($this->defaultSessionVars as $option => $value) {
            $vars[] = $option . "='" . $value . "'";
        }

        $driver->executeUpdate('ALTER SESSION SET ' . implode(' ', $vars));
    }
}
