<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

use Elie\OCI\Driver\DriverException;
use Elie\OCI\Driver\DriverInterface;
use PHPUnit\Framework\TestCase;

class SessionInitTest extends TestCase
{
    public function testAlterSession()
    {
        $sql = "ALTER SESSION SET NLS_TIME_FORMAT='HH24:MI:SS' "
            . "NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS' "
            . "NLS_TIMESTAMP_FORMAT='YYYY-MM-DD HH24:MI:SS' "
            . "NLS_TIMESTAMP_TZ_FORMAT='YYYY-MM-DD HH24:MI:SS TZH:TZM' "
            . "NLS_NUMERIC_CHARACTERS='.,'";

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())
            ->method('executeUpdate')
            ->with($sql);

        $init = new SessionInit();
        $init->alterSession($driver);
    }

    /**
     * @throws DriverException
     */
    public function testSessionInitWithARealInstanceDriver()
    {
        $driver = Provider::getDriver();

        $session = new SessionInit();
        $session->alterSession($driver);

        // Verify the NLS settings are applied by selecting SYSDATE directly
        // It should already be formatted as YYYY-MM-DD HH24:MI:SS
        $result = $driver->fetchAssoc("SELECT SYSDATE AS CURRENT_DATE FROM dual");

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('CURRENT_DATE', $result);
        // Verify the date format matches our NLS_DATE_FORMAT setting (YYYY-MM-DD HH24:MI:SS)
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result['CURRENT_DATE']);
    }
}
