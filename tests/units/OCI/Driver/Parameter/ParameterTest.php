<?php

declare(strict_types=1);

namespace Elie\OCI\Driver\Parameter;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testAddWithString(): void
    {
        $param = new Parameter();
        $result = $param->add(':name', 'John Doe');

        $this->assertSame($param, $result);

        $attributes = $param->getAttributes();
        $this->assertCount(1, $attributes);
        $this->assertArrayHasKey(':name', $attributes);
        $this->assertEquals('John Doe', $attributes[':name']->variable);
    }

    public function testAddWithInteger(): void
    {
        $param = new Parameter();
        $param->add(':id', 42);

        $attributes = $param->getAttributes();
        $this->assertEquals(42, $attributes[':id']->variable);
    }

    public function testAddWithFloat(): void
    {
        $param = new Parameter();
        $param->add(':price', 19.99);

        $attributes = $param->getAttributes();
        // Float should be converted to string with dot as a decimal separator
        $this->assertIsString($attributes[':price']->variable);
        $this->assertEquals('19.99', $attributes[':price']->variable);
    }

    public function testAddWithFloatAndCommaLocale(): void
    {
        // Save current locale
        $currentLocale = setlocale(LC_NUMERIC, '0');

        // Set French locale, which uses comma as a decimal separator
        setlocale(LC_NUMERIC, 'fr_FR.UTF-8', 'fr_FR', 'French_France.1252', 'French');

        $param = new Parameter();
        $param->add(':amount', 1234.56);

        // Restore locale
        setlocale(LC_NUMERIC, $currentLocale);

        $attributes = $param->getAttributes();
        // Should always use dot, regardless of locale
        $this->assertEquals('1234.56', $attributes[':amount']->variable);
    }

    public function testAddWithNull(): void
    {
        $param = new Parameter();
        $param->add(':nullable', null);

        $attributes = $param->getAttributes();
        $this->assertNull($attributes[':nullable']->variable);
    }

    public function testAddWithBoolean(): void
    {
        $param = new Parameter();
        $param->add(':active', true);

        $attributes = $param->getAttributes();
        $this->assertTrue($attributes[':active']->variable);
    }

    public function testAddWithMaxLength(): void
    {
        $param = new Parameter();
        $param->add(':output', null, 100);

        $attributes = $param->getAttributes();
        $this->assertEquals(100, $attributes[':output']->maxlength);
    }

    public function testAddForLongRaw(): void
    {
        $param = new Parameter();
        $hexValue = bin2hex('Binary data');
        $param->addForLongRaw(':raw_data', $hexValue);

        $attributes = $param->getAttributes();
        $this->assertArrayHasKey(':raw_data', $attributes);
        $this->assertEquals($hexValue, $attributes[':raw_data']->variable);
        $this->assertEquals(SQLT_LBI, $attributes[':raw_data']->type);
    }

    public function testGetVariable(): void
    {
        $param = new Parameter();
        $param->add(':name', 'Test Value');

        $value = $param->getVariable(':name');

        $this->assertEquals('Test Value', $value);
    }

    public function testChainedAdd(): void
    {
        $param = new Parameter();
        $result = $param
            ->add(':id', 1)
            ->add(':name', 'John')
            ->add(':active', true);

        $this->assertSame($param, $result);
        $this->assertCount(3, $param->getAttributes());
    }

    public function testMultipleParameters(): void
    {
        $param = new Parameter();
        $param->add(':id', 100);
        $param->add(':name', 'Product');
        $param->add(':price', 49.99);
        $param->add(':stock', 50);

        $attributes = $param->getAttributes();
        $this->assertCount(4, $attributes);

        $this->assertEquals(100, $attributes[':id']->variable);
        $this->assertEquals('Product', $attributes[':name']->variable);
        $this->assertEquals('49.99', $attributes[':price']->variable);
        $this->assertEquals(50, $attributes[':stock']->variable);
    }

    public function testDefaultMaxLength(): void
    {
        $param = new Parameter();
        $param->add(':test', 'value');

        $attributes = $param->getAttributes();
        $this->assertEquals(Parameter::DEFAULT_MAX_LEN, $attributes[':test']->maxlength);
    }

    public function testZeroFloat(): void
    {
        $param = new Parameter();
        $param->add(':zero', 0.0);

        $attributes = $param->getAttributes();
        $this->assertEquals('0', $attributes[':zero']->variable);
    }

    public function testNegativeFloat(): void
    {
        $param = new Parameter();
        $param->add(':negative', -25.75);

        $attributes = $param->getAttributes();
        $this->assertEquals('-25.75', $attributes[':negative']->variable);
    }

    public function testVerySmallFloat(): void
    {
        $param = new Parameter();
        $param->add(':small', 0.0001);

        $attributes = $param->getAttributes();
        $variable = $attributes[':small']->variable;
        $this->assertIsString($variable);
        $this->assertStringContainsString('.', $variable);
    }

    public function testVeryLargeFloat(): void
    {
        $param = new Parameter();
        $param->add(':large', 999999.99);

        $attributes = $param->getAttributes();
        $this->assertEquals('999999.99', $attributes[':large']->variable);
    }

    public function testAddWithInvalidColumnName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Column name must start with ':'. Got: invalid");

        $param = new Parameter();
        $param->add('invalid', 'value');
    }

    public function testAddWithValidColumnName(): void
    {
        $param = new Parameter();
        $param->add(':valid', 'value');

        $attributes = $param->getAttributes();
        $this->assertArrayHasKey(':valid', $attributes);
    }
}
