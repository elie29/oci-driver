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
     * @param DBDriverInterface $driver
     *
     * @return bool true session is initialised.
     */
    public function alterSession(DriverInterface $driver): bool
    {
        $vars = [];
        $sep = "'";

        foreach ($this->defaultSessionVars as $option => $value) {
            // option='value'
            $vars[] = sprintf('%s=%s%s%s', $option, $sep, $value, $sep);
        }

        return !! $driver->executeUpdate('ALTER SESSION SET ' . implode(' ', $vars));
    }
}
