<?php

declare(strict_types=1);

namespace Elie\OCI\Driver;

use Elie\OCI\Debugger\DebuggerInterface;
use Elie\OCI\Driver\Parameter\Parameter;
use Elie\OCI\Helper\Factory;
use Elie\OCI\Helper\FormatInterface;
use Elie\OCI\Helper\Provider;
use Elie\OCI\Helper\SessionInit;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class DriverTest extends TestCase
{
    /**
     * @throws DriverException
     */
    public static function setUpBeforeClass(): void
    {
        $driver = Provider::getDriver();
        $driver->executeUpdate('TRUNCATE TABLE A1');
        $driver->executeUpdate('TRUNCATE TABLE A2');
    }

    /**
     * @throws DriverException
     */
    public static function tearDownAfterClass(): void
    {
        self::setUpBeforeClass();
    }

    /**
     * @throws DriverException
     */
    public function testCreateInstanceWithMock(): void
    {
        $connection = Provider::getConnection();
        $debugger = $this->createMock(DebuggerInterface::class);
        $driver = new Driver($connection, $debugger);

        $this->assertNotEmpty($driver);
    }

    public function testExecuteWithException(): void
    {
        $this->expectException(DriverException::class);
        $driver = Provider::getDriver();
        $sql = 'Select FROM A1';
        $driver->executeQuery($sql);
    }

    /**
     * @throws DriverException
     */
    public function testBoundExecuteWithException(): void
    {
        $this->expectException(DriverException::class);
        $driver = Provider::getDriver();
        $sql = 'Select FROM A1 WHERE N_NUM = :N_NUM';
        $param = new Parameter();
        $driver->executeQuery($sql, $param->add(':N_NUM', 5));
    }

    /**
     * @throws DriverException
     */
    public function testExecuteTransactionWithException(): void
    {
        $this->expectException(DriverException::class);
        $driver = Provider::getDriver();
        $driver->beginTransaction();
        $sql = 'Select FROM A1';
        $driver->executeQuery($sql);
    }

    /**
     * @throws DriverException
     */
    public function testBeginRollbackInAutoCommitMode(): void
    {
        $driver = Provider::getDriver();

        $res = $driver->commitTransaction();
        $this->assertSame($driver, $res);

        $res = $driver->rollbackTransaction();
        $this->assertSame($driver, $res);
    }

    /**
     * @throws DriverException
     */
    #[DataProviderExternal(Provider::class, 'dataWithNoBind')]
    public function testExecuteUpdateWithoutBindNorTransaction($num, $num3, $ts, $long): void
    {
        $driver = Provider::getDriver();

        $sql = "INSERT INTO A1 (N_NUM, N_NUM_3, N_TS, N_LONG) VALUES ($num, $num3, $ts, $long)";

        $res = $driver->executeUpdate($sql);

        $this->assertSame(1, $res);
    }

    /**
     * @throws DriverException
     */
    #[DataProviderExternal(Provider::class, 'dataWithNoBind')]
    public function testExecuteUpdateWithoutBindWithTransactionRollback($num, $num3, $ts, $long): void
    {
        $driver = Provider::getDriver();
        $sql = "INSERT INTO A1 (N_NUM, N_NUM_3, N_TS, N_LONG) VALUES ($num, $num3, $ts, $long)";

        $driver->beginTransaction();
        $res = $driver->executeUpdate($sql);
        $driver->rollbackTransaction();

        $this->assertSame(1, $res);
    }

    /**
     * @throws DriverException
     */
    #[DataProviderExternal(Provider::class, 'dataWithNoBind')]
    public function testExecuteUpdateWithoutBindAndTransactionCommit($num, $num3, $ts, $long): void
    {
        $driver = Provider::getDriver();
        $sql = "INSERT INTO A1 (N_NUM, N_NUM_3, N_TS, N_LONG) VALUES ($num, $num3, $ts, $long)";

        $driver->beginTransaction();
        $res = $driver->executeUpdate($sql);
        $driver->rollbackTransaction();

        $this->assertSame(1, $res);
    }

    /**
     * @throws DriverException
     */
    public function testExecuteUpdateWithBindAndTransactionRollback(): void
    {
        $driver = Provider::getDriver();
        $sql = 'INSERT INTO A1 (N_CHAR, N_NUM, N_NUM_3, N_VAR, N_DATE, N_TS, N_LONG) VALUES '
            . '(:N1, :N2, :N3, :N4, TO_DATE(:N5, \'DD-MM-YYYY\'), TO_TIMESTAMP(:N6, \'DD-MM-YYYY HH24:MI:SS\'), :N7)';

        $driver->beginTransaction();
        $bind = new Parameter();
        $bind->add(':N1', 'c')
            ->add(':N2', 1)
            ->add(':N3', 0.24)
            ->add(':N4', 'test')
            ->add(':N5', date('d-m-Y')) // should respect to_date fmt
            ->add(':N6', date('d-m-Y H:i:s')) // should respect to_timestamp fmt
            ->add(':N7', 18596);

        $res = $driver->executeUpdate($sql, $bind);
        $driver->commitTransaction();

        $this->assertSame(1, $res);
    }

    /**
     * @throws DriverException
     */
    public function testExecuteUpdateWithBindAndSessionInit(): void
    {
        $driver = Provider::getDriver();
        $session = new SessionInit();
        $session->alterSession($driver);

        $sql = 'INSERT INTO A1 (N_CHAR, N_NUM, N_NUM_3, N_VAR, N_DATE, N_TS, N_LONG) VALUES '
            . '(:N1, :N2, :N3, :N4, :N5, :N6, :N7)';

        $driver->beginTransaction();
        $bind = new Parameter();
        $bind->add(':N1', 'c')
            ->add(':N2', 1)
            ->add(':N3', 0.24)
            ->add(':N4', 'test')
            ->add(':N5', '2018-08-12') // ISO Format
            ->add(':N6', '2018-11-09 12:35:36') // ISO Format
            ->add(':N7', 18596);

        $res = $driver->executeUpdate($sql, $bind);
        $driver->rollbackTransaction();

        $this->assertSame(1, $res);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testFetchColumns()
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_NUM, N_NUM_3 FROM A1';

        $cols = $driver->fetchColumns($sql);
        $this->assertCount(2, $cols);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testFetchColumn()
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_NUM, N_NUM_3 FROM A1';

        $cols = $driver->fetchColumn($sql);
        $this->assertNotEmpty($cols);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testFetchAssocWithBind(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_NUM FROM A1 WHERE N_NUM = :N1 AND N_NUM_3 = :N2';

        $bind = new Parameter();
        $bind->add(':N1', 150)
            ->add(':N2', 2.05);

        $row = $driver->fetchAssoc($sql, $bind);

        $this->assertEmpty($row);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testFetchAllAssocWithBind(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_NUM FROM A1 WHERE N_NUM = :N1 AND N_NUM_3 = :N2';

        $bind = new Parameter();
        $bind->add(':N1', 150)
            ->add(':N2', 2.091);

        $row = $driver->fetchAllAssoc($sql, $bind);

        $this->assertEmpty($row);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testFetchAssocWithoutBind(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT * FROM A1 WHERE N_NUM = 2';

        $row = $driver->fetchAssoc($sql);

        $this->assertNotEmpty($row);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testFetchAllAssocWithoutBind(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT * FROM A1';

        $row = $driver->fetchAllAssoc($sql);

        $this->assertNotEmpty($row);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithBindAndTransactionRollback')]
    public function testFetchAllWithNlsSessionAndDateCompare(): void
    {
        $driver = Factory::create(Provider::getConnection(), 'test');

        $sql = 'SELECT * FROM A1 WHERE N_DATE BETWEEN :YESTERDAY AND :TOMORROW';

        $bind = (new Parameter())
            ->add(':YESTERDAY', date(FormatInterface::PHP_DATE, time() - 86400)) // N_DATE type is DATE
            ->add(':TOMORROW', date(FormatInterface::PHP_DATE, time() + 86400));

        $rows = $driver->fetchAllAssoc($sql, $bind);

        $this->assertNotEmpty($rows);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testFetchSimpleCount(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT count(*) NB FROM A1';

        $row = $driver->fetchAssoc($sql);

        $nb = (int)$row['NB'];

        $this->assertGreaterThan(2, $nb);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testFetchCountWithUnion(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT count(*) NB FROM A1 '
            . 'UNION '
            . 'SELECT count(*) NB FROM dual';

        $cols = $driver->fetchAllAssoc($sql);

        $sum = array_sum(array_column($cols, 'NB'));

        $this->assertGreaterThan(2, $sum);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testUpdateDataWithClobBind(): void
    {
        $driver = Provider::getDriver();
        $sql = 'Update A1 SET N_CLOB = :LOB WHERE N_NUM = :NUM';

        $bind = new Parameter();
        $bind->add(':NUM', 2);

        // Write mode
        $bind->addForCLob($driver->getConnection(), ':LOB', file_get_contents(__FILE__));

        $count = $driver->executeUpdate($sql, $bind);

        $lob = $bind->getVariable(':LOB');
        $lob->close();
        $lob->free();

        $this->assertSame(1, $count);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testUpdateDataWithClobBind')]
    public function testFetchDataWithClob(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_CLOB FROM A1 WHERE N_CLOB IS NOT NULL';

        $row = $driver->fetchAssoc($sql);

        $this->assertNotEmpty($row);
        $this->assertNotEmpty($row['N_CLOB']);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testReadDataWithClob(): void
    {
        $driver = Provider::getDriver();

        $sql = 'INSERT INTO A1 (N_NUM, N_CLOB) VALUES (:N1, EMPTY_CLOB()) RETURNING N_CLOB INTO :myLob';

        $bind = new Parameter();
        $bind->add(':N1', 5);

        // Read mode
        $bind->addForCLob($driver->getConnection(), ':myLob');

        $driver->beginTransaction();

        $count = $driver->executeUpdate($sql, $bind);

        $lob = $bind->getVariable(':myLob');
        $lob->save('My very Long Data');
        $lob->free();

        $driver->rollbackTransaction();

        $this->assertSame(1, $count);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testInsertWithReturning(): void
    {
        $driver = Provider::getDriver();

        // useful with SEQ.nextval
        $sql = 'INSERT INTO A1 (N_NUM) VALUES (:N1) RETURNING N_NUM INTO :myNum';

        $bind = new Parameter();
        $bind->add(':N1', 1258);
        $bind->add(':myNum', null, 38); // maxlength for output variable is required

        $driver->beginTransaction();

        $driver->executeUpdate($sql, $bind);

        $num = (int)$bind->getVariable(':myNum');

        $driver->rollbackTransaction();

        $this->assertSame(1258, $num);
    }

    /**
     * @throws DriverException
     */
    #[Depends('testExecuteUpdateWithoutBindNorTransaction')]
    public function testReadDataWithClobAndFunctionCall(): void
    {
        $driver = Provider::getDriver();

        // Using Oracle's built-in TO_CLOB() function
        $sql = 'BEGIN :myLob := TO_CLOB(:N1); END;';

        $bind = new Parameter();
        $bind->add(':N1', 'Test CLOB content with some data');

        // Read mode
        $bind->addForCLob($driver->getConnection(), ':myLob');

        $statement = $driver->executeQuery($sql, $bind);

        $lob = $bind->getVariable(':myLob');

        // In a loop, pay attention to free the result
        $row = $lob->load();

        $lob->free();
        oci_free_statement($statement);

        $this->assertNotEmpty($row);
        $this->assertStringContainsString('Test CLOB content', $row);
    }

    /**
     * @throws DriverException
     */
    public function testInsertDataWithNoBindingOfLongRaw(): void
    {
        $driver = Provider::getDriver();

        $value = bin2hex('Any long raw as hex value');
        $sql = "INSERT INTO A2 (N_LONG_RAW) VALUES ('$value')";

        $res = $driver->executeUpdate($sql);

        $this->assertSame(1, $res);
    }

    /**
     * @throws DriverException
     */
    public function testInsertDataWithLongRawBinding(): void
    {
        $driver = Provider::getDriver();

        $sql = 'INSERT INTO A2 (N_LONG_RAW) VALUES (:VAL)';

        $bind = new Parameter();
        $bind->addForLongRaw(':VAL', bin2hex('Any long raw as hex value'));

        $res = $driver->executeUpdate($sql, $bind);

        $this->assertSame(1, $res);
    }
}
