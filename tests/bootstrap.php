<?php

declare(strict_types=1);

use Symfony\Component\VarDumper\VarDumper;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('SQLT_CHR')) {
    define('SQLT_CHR', 1);
}

if (!defined('SQLT_LBI')) {
    define('SQLT_LBI', 24);
}

@unlink(__DIR__ . '/logs.log');
VarDumper::setHandler(function ($var) {
    file_put_contents(__DIR__ . '/logs.log', print_r($var, true), FILE_APPEND | LOCK_EX);
});
