<?php

declare(strict_types = 1);

namespace OCI\Helper;

use Generator;

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
}
