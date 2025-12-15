<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    public function testDevelopmentCases(): void
    {
        $this->assertTrue(Environment::Development->isDevelopment());
        $this->assertTrue(Environment::Test->isDevelopment());

        $this->assertFalse(Environment::Development->isProduction());
        $this->assertFalse(Environment::Test->isProduction());
    }

    public function testProductionCases(): void
    {
        $this->assertTrue(Environment::Production->isProduction());
        $this->assertFalse(Environment::Production->isDevelopment());
    }

    public function testFromStringDevelopment(): void
    {
        $this->assertEquals(Environment::Development, Environment::fromString('dev'));
        $this->assertEquals(Environment::Development, Environment::fromString('development'));
    }

    public function testFromStringTest(): void
    {
        $this->assertEquals(Environment::Test, Environment::fromString('test'));
    }

    public function testFromStringProduction(): void
    {
        $this->assertEquals(Environment::Production, Environment::fromString('prod'));
        $this->assertEquals(Environment::Production, Environment::fromString('production'));
    }

    public function testFromStringDefaultsToProduction(): void
    {
        $this->assertEquals(Environment::Production, Environment::fromString('unknown'));
        $this->assertEquals(Environment::Production, Environment::fromString(''));
        $this->assertEquals(Environment::Production, Environment::fromString('anything'));
    }

    public function testEnumValues(): void
    {
        $this->assertEquals('dev', Environment::Development->value);
        $this->assertEquals('test', Environment::Test->value);
        $this->assertEquals('prod', Environment::Production->value);
    }
}
