<?php

declare(strict_types = 1);

namespace Common\Db;

use Mockery;
use OCI\Driver\DriverInterface;
use OCI\Helper\Provider;
use OCI\Helper\SessionInit;
use OCI\OCITestCase;

class SessionInitTest extends OCITestCase
{

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

    /**
     * @doesNotPerformAssertions
     */
    public function testSessionInitWithARealInstanceDriver()
    {
        $driver = Provider::getDriver();

        $session = new SessionInit();
        $session->alterSession($driver);
    }
}
