<?php

declare(strict_types = 1);

namespace OCI\Helper;

use OCI\Driver\DriverInterface;

/**
 * The following environment variables are required for default date format:
 * @see Format
 */
class SessionInit
{

    /**
     * @var array
     */
    protected $defaultSessionVars = [
        'NLS_TIME_FORMAT' => Format::NLS_TIME,
        'NLS_DATE_FORMAT' => Format::NLS_DATE,
        'NLS_TIMESTAMP_FORMAT' => Format::NLS_TIMESTAMP,
        'NLS_TIMESTAMP_TZ_FORMAT' => Format::NLS_TIMESTAMP_TZ,
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
