<?php

declare(strict_types = 1);

namespace OCI\Helper;

class FloatUtils
{

    // Converts a float value to a string by keeping "." as decimal separator.
    public static function convert(float $variable): string
    {
        $commaIsDecimalSeparator = (string) 1.5 === '1,5';

        if ($commaIsDecimalSeparator) {
            return str_replace(',', '.', (string) $variable);
        }

        return (string) $variable;
    }
}
