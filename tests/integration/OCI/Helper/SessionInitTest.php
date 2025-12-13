<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

use Mockery;
use Elie\OCI\Driver\DriverInterface;
use Elie\OCI\OCITestCase;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

class SessionInitTest extends OCITestCase
{
    #[DoesNotPerformAssertions]
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

    #[DoesNotPerformAssertions]
    public function testSessionInitWithARealInstanceDriver()
    {
        $driver = Provider::getDriver();

        $session = new SessionInit();
        $session->alterSession($driver);
    }
}
