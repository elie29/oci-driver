<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

use Elie\OCI\Driver\DriverInterface;

/**
 * The following environment variables are required for the default date format:
 */
class SessionInit
{
    /** @var array */
    protected $defaultSessionVars = [
        'NLS_TIME_FORMAT' => FormatInterface::NLS_TIME,
        'NLS_DATE_FORMAT' => FormatInterface::NLS_DATE,
        'NLS_TIMESTAMP_FORMAT' => FormatInterface::NLS_TIMESTAMP,
        'NLS_TIMESTAMP_TZ_FORMAT' => FormatInterface::NLS_TIMESTAMP_TZ,
        'NLS_NUMERIC_CHARACTERS' => '.,',
    ];

    public function alterSession(DriverInterface $driver): void
    {
        $vars = [];

        foreach ($this->defaultSessionVars as $option => $value) {
            $vars[] = $option . "='" . $value . "'";
        }

        $driver->executeUpdate('ALTER SESSION SET ' . implode(' ', $vars));
    }
}
