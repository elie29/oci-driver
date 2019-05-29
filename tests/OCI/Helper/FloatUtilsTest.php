<?php

declare(strict_types = 1);

namespace OCI\Helper;

use OCI\OCITestCase;

class FloatUtilsTest extends OCITestCase
{

    public function testConvertWithFr(): void
    {
        $locale = setlocale(LC_ALL, 0);

        setlocale(LC_ALL, 'fr_FR');

        assertThat(FloatUtils::convert(5000.256), '5000.256');

        setlocale(LC_ALL, $locale);
    }

    public function testDefaultConvert(): void
    {
        assertThat(FloatUtils::convert(5000.256), '5000.256');
    }
}
