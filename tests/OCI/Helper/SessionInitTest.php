<?php

declare(strict_types = 1);

namespace Common\Db;

use Mockery;
use OCI\Driver\DriverInterface;
use OCI\Helper\SessionInit;
use PHPUnit\Framework\TestCase;

class SessionInitTest extends TestCase
{

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAlterSession()
    {
        $sql = "ALTER SESSION SET NLS_TIME_FORMAT='HH24:MI:SS' "
             . "NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS' "
             . "NLS_TIMESTAMP_FORMAT='YYYY-MM-DD HH24:MI:SS' "
             . "NLS_TIMESTAMP_TZ_FORMAT='YYYY-MM-DD HH24:MI:SS TZH:TZM' "
             . "NLS_NUMERIC_CHARACTERS='.,'";

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('executeUpdate')
            ->with($sql)->once();

        $init = new SessionInit();
        $init->alterSession($driver);
    }
}
