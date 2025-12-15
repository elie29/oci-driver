<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

enum Environment: string
{
    case Development = 'dev';
    case Test = 'test';
    case Production = 'prod';

    public function isDevelopment(): bool
    {
        return in_array($this, [self::Development, self::Test], true);
    }

    public function isProduction(): bool
    {
        return $this === self::Production;
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            'dev', 'development' => self::Development,
            'test' => self::Test,
            default => self::Production,
        };
    }
}
