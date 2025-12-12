<?php

declare(strict_types=1);

namespace OCI\Helper;

use PHPUnit\Framework\TestCase;

class FloatUtilsTest extends TestCase
{
    public function testConvertWithFr(): void
    {
        $locale = setlocale(LC_ALL, 0);

        setlocale(LC_ALL, 'fr_FR');

        $this->assertSame('5000.256', FloatUtils::convert(5000.256));

        setlocale(LC_ALL, $locale);
    }

    public function testDefaultConvert(): void
    {
        $this->assertSame('5000.256', FloatUtils::convert(5000.256));
    }
}
