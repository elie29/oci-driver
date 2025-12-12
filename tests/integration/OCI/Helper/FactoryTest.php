<?php

declare(strict_types=1);

namespace OCI\Helper;

use Mockery;
use OCI\Driver\DriverException;
use OCI\OCITestCase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\VarDumper\VarDumper;

#[RunTestsInSeparateProcesses]
class FactoryTest extends OCITestCase
{
    /**
     * @throws DriverException
     */
    public function testDevCreation()
    {
        // Mock dump in order not to print out data
        $mock = Mockery::mock('alias:' . VarDumper::class);
        $mock->shouldReceive('dump');

        Factory::init(Provider::getConnection(), 'dev');
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
