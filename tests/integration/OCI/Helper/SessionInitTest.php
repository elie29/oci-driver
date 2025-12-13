<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

use Elie\OCI\Driver\DriverException;
use Elie\OCI\Driver\DriverInterface;
use Elie\OCI\OCITestCase;
use Mockery;
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

    /**
     * @throws DriverException
     */
    #[DoesNotPerformAssertions]
    public function testSessionInitWithARealInstanceDriver()
    {
        $driver = Provider::getDriver();

        $session = new SessionInit();
        $session->alterSession($driver);
    }
}
