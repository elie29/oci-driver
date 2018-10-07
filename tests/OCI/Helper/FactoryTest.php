<?php

declare(strict_types = 1);

namespace OCI\Helper;

use Mockery;
use OCI\OCITestCase;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @runTestsInSeparateProcesses because Factory::get is based on static members.
 */
class FactoryTest extends OCITestCase
{

    public function testDevCreation()
    {
        // Mock dump in order not to print out data
        $mock = Mockery::mock('alias:' . VarDumper::class);
        $mock->shouldReceive('dump');

        Factory::init(Provider::getConnection(), 'dev');
        assertThat(Factory::get(), sameInstance(Factory::get()));
    }

    public function testProdCreation()
    {
        Factory::init(Provider::getConnection(), 'prod');
        assertThat(Factory::get(), sameInstance(Factory::get()));
    }

    public function testCreationNewInstance()
    {
        $first = Factory::create(Provider::getConnection(), 'prod');
        $second = Factory::create(Provider::getConnection(), 'prod');
        assertThat($first, not(sameInstance($second)));
    }
}
