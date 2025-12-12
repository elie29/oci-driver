<?php

declare(strict_types=1);

namespace OCI\Helper;

interface FormatInterface
{
    /**
     * oracle NLS session format
     */
    public const NLS_TIME         = 'HH24:MI:SS';
    public const NLS_DATE         = 'YYYY-MM-DD HH24:MI:SS';
    public const NLS_TIMESTAMP    = 'YYYY-MM-DD HH24:MI:SS';
    public const NLS_TIMESTAMP_TZ = 'YYYY-MM-DD HH24:MI:SS TZH:TZM';

    /**
     * php date format equivalent to oracle session format
     */
    public const PHP_TIME         = 'H:i:s';
    public const PHP_DATE         = 'Y-m-d H:i:s';
    public const PHP_TIMESTAMP    = 'Y-m-d H:i:s';
    public const PHP_TIMESTAMP_TZ = 'Y-m-d H:i:s P';
}
