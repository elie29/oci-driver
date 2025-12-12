<?php

declare(strict_types=1);

namespace OCI;

use Mockery;
use PHPUnit\Framework\TestCase;

class OCITestCase extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
