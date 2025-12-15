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

    /**
     * @throws DriverException
     */
    public function testTimestampFormatIsApplied()
    {
        $driver = Provider::getDriver();

        $session = new SessionInit();
        $session->alterSession($driver);

        // Verify the TIMESTAMP format is applied
        $result = $driver->fetchAssoc("SELECT SYSTIMESTAMP AS CURRENT_TIMESTAMP FROM dual");

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('CURRENT_TIMESTAMP', $result);
        // Verify the timestamp format matches NLS_TIMESTAMP_FORMAT (YYYY-MM-DD HH24:MI:SS)
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $result['CURRENT_TIMESTAMP']);
    }

    /**
     * @throws DriverException
     */
    public function testNumericCharactersIsApplied()
    {
        $driver = Provider::getDriver();

        $session = new SessionInit();
        $session->alterSession($driver);

        // Insert a decimal number and verify it uses the correct format
        // NLS_NUMERIC_CHARACTERS='., means dot for decimal, comma for grouping
        $result = $driver->fetchAssoc("SELECT 1234.56 AS NUM_VALUE FROM dual");

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('NUM_VALUE', $result);
        // Should return a numeric value correctly with dot as a decimal separator
        $this->assertEquals(1234.56, (float)$result['NUM_VALUE']);
    }
}
