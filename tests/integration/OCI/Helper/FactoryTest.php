<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

use Elie\OCI\Driver\DriverException;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @throws DriverException
     */
    public function testDevCreation()
    {
        // Suppress output for dev mode which uses DebuggerDump
        ob_start();
        Factory::init(Provider::getConnection(), 'dev');
        ob_end_clean();
        
        $this->assertSame(Factory::get(), Factory::get());
    }

    /**
     * @throws DriverException
     */
    public function testProdCreation()
    {
        Factory::init(Provider::getConnection(), 'prod');
        $this->assertSame(Factory::get(), Factory::get());
    }

    /**
     * @throws DriverException
     */
    public function testCreationNewInstance()
    {
        $first = Factory::create(Provider::getConnection(), 'prod');
        $second = Factory::create(Provider::getConnection(), 'prod');
        $this->assertNotSame($second, $first);
    }
}
