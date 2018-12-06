<?php

declare(strict_types = 1);

namespace OCI\Helper;

use OCI\Driver\DriverException;
use OCI\Driver\Parameter\Parameter;
use OCI\OCITestCase;

class ClauseInParamsHelperTest extends OCITestCase
{

    public function testGetBoundParamsThrowsException(): void
    {
        $this->expectException(DriverException::class);

        $param = new Parameter();

        $values = array_fill(0, 1000, 1);
        ClauseInParamsHelper::getBoundParams($values, ':ID', $param);
    }

    public function testGetBoundParamsWithValidValues(): void
    {
        $values = array_fill(0, 999, 1);
        $param = new Parameter();

        $key = ClauseInParamsHelper::getBoundParams($values, ':ID', $param);

        assertThat($key, startsWith(':ID'));
        assertThat($param->getAttributes(), arrayValue());
    }
}

