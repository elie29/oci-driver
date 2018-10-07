<?php

declare(strict_types = 1);

namespace OCI;

use Mockery;
use PHPUnit\Framework\TestCase;

class OCITestCase extends TestCase
{

    private $errors = [];

    protected function setUp(): void
    {
        parent::setUp();
        // for trigger_error capturing
        set_error_handler(function () {
            $this->errors[] = func_get_args();
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
