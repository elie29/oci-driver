<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

use Elie\OCI\Driver\DriverException;
use Elie\OCI\Driver\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class ClauseInParamsHelperTest extends TestCase
{
    public function testGetBoundParamsThrowsException(): void
    {
        $this->expectException(DriverException::class);

        $param = new Parameter();

        $values = array_fill(0, 1000, 1);
        ClauseInParamsHelper::getBoundParams($values, ':ID', $param);
    }

    /**
     * @throws DriverException
     */
    public function testGetBoundParamsWithValidValues(): void
    {
        $values = array_fill(0, 999, 1);
        $param = new Parameter();

        $key = ClauseInParamsHelper::getBoundParams($values, ':ID', $param);

        $this->assertStringStartsWith(':ID', $key);
        $this->assertIsArray($param->getAttributes());
    }
}
