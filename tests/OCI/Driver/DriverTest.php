<?php

declare(strict_types = 1);

namespace OCI\Driver;

use Mockery;
use OCI\Debugger\DebuggerInterface;
use OCI\Driver\Parameter\Parameter;
use OCI\Helper\Factory;
use OCI\Helper\Format;
use OCI\Helper\Provider;
use OCI\Helper\SessionInit;
use OCI\OCITestCase;

class DriverTest extends OCITestCase
{

    public static function setUpBeforeClass(): void
    {
        $driver = Provider::getDriver();
        $driver->executeUpdate('TRUNCATE TABLE A1');
        $driver->executeUpdate('TRUNCATE TABLE A2');
    }

    public static function tearDownAfterClass(): void
    {
        self::setUpBeforeClass();
    }

    public function testCreateInstanceWithMock(): void
    {
        $connection = Provider::getConnection();
        $debugger = Mockery::mock(DebuggerInterface::class);
        $driver = new Driver($connection, $debugger);

        assertThat($driver, anInstanceOf(DriverInterface::class));
    }

    /**
     * @expectedException OCI\Driver\DriverException
     */
    public function testExecuteWithException(): void
    {
        $driver = Provider::getDriver();
        $sql = 'Select FROM A1';
        $driver->executeQuery($sql);
    }

    /**
     * @expectedException OCI\Driver\DriverException
     */
    public function testBoundExecuteWithException(): void
    {
        $driver = Provider::getDriver();
        $sql = 'Select FROM A1 WHERE N_NUM = :N_NUM';
        $param = new Parameter();
        $driver->executeQuery($sql, $param->add(':N_NUM', 5));
    }

    /**
     * @expectedException OCI\Driver\DriverException
     */
    public function testExecuteTransactionWithException(): void
    {
        $driver = Provider::getDriver();
        $driver->beginTransaction();
        $sql = 'Select FROM A1';
        $driver->executeQuery($sql);
    }

    public function testBeginRollbackInAutoCommitMode(): void
    {
        $driver = Provider::getDriver();

        $res = $driver->commitTransaction();
        assertThat($res, is($driver));

        $res = $driver->rollbackTransaction();
        assertThat($res, is($driver));
    }

    /**
     * @dataProvider OCI\Helper\Provider::dataWithNoBind
     */
    public function testExecuteUpdateWithoutBindNorTransaction($num, $num3, $ts, $long): void
    {
        $driver = Provider::getDriver();

        $sql = "INSERT INTO A1 (N_NUM, N_NUM_3, N_TS, N_LONG) VALUES ($num, $num3, $ts, $long)";

        $res = $driver->executeUpdate($sql);

        assertThat($res, is(1));
    }

    /**
     * @dataProvider OCI\Helper\Provider::dataWithNoBind
     */
    public function testExecuteUpdateWithoutBindWithTransactionRollback($num, $num3, $ts, $long): void
    {
        $driver = Provider::getDriver();
        $sql = "INSERT INTO A1 (N_NUM, N_NUM_3, N_TS, N_LONG) VALUES ($num, $num3, $ts, $long)";

        $driver->beginTransaction();
        $res = $driver->executeUpdate($sql);
        $driver->rollbackTransaction();

        assertThat($res, is(1));
    }

    /**
     * @dataProvider OCI\Helper\Provider::dataWithNoBind
     */
    public function testExecuteUpdateWithoutBindAndTransactionCommit($num, $num3, $ts, $long): void
    {
        $driver = Provider::getDriver();
        $sql = "INSERT INTO A1 (N_NUM, N_NUM_3, N_TS, N_LONG) VALUES ($num, $num3, $ts, $long)";

        $driver->beginTransaction();
        $res = $driver->executeUpdate($sql);
        $driver->rollbackTransaction();

        assertThat($res, is(1));
    }

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

        assertThat($res, is(1));
    }

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

        assertThat($res, is(1));
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchColumns()
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_NUM, N_NUM_3 FROM A1';

        $cols = $driver->fetchColumns($sql);
        assertThat($cols, arrayWithSize(2));
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchColumn()
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_NUM, N_NUM_3 FROM A1';

        $cols = $driver->fetchColumn($sql);
        assertThat($cols, nonEmptyArray());
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchAssocWithBind(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_NUM FROM A1 WHERE N_NUM = :N1 AND N_NUM_3 = :N2';

        $bind = new Parameter();
        $bind->add(':N1', 150)
            ->add(':N2', 2.05);

        $row = $driver->fetchAssoc($sql, $bind);

        assertThat($row, emptyArray());
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchAllAssocWithBind(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_NUM FROM A1 WHERE N_NUM = :N1 AND N_NUM_3 = :N2';

        $bind = new Parameter();
        $bind->add(':N1', 150)
            ->add(':N2', 2.091);

        $row = $driver->fetchAllAssoc($sql, $bind);

        assertThat($row, emptyArray());
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchAssocWithoutBind(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT * FROM A1 WHERE N_NUM = 2';

        $row = $driver->fetchAssoc($sql);

        assertThat($row, nonEmptyArray());
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchAllAssocWithoutBind(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT * FROM A1';

        $row = $driver->fetchAllAssoc($sql);

        assertThat($row, nonEmptyArray());
    }

    /**
     * @depends testExecuteUpdateWithBindAndTransactionRollback
     */
    public function testFetchAllWithNlsSessionAndDateCompare(): void
    {
        $driver = Factory::create(Provider::getConnection(), 'test');

        $sql = 'SELECT * FROM A1 WHERE N_DATE BETWEEN :YESTERDAY AND :TOMORROW';

        $bind = (new Parameter())
            ->add(':YESTERDAY', date(Format::PHP_DATE, time() - 86400)) // N_DATE type is DATE
            ->add(':TOMORROW', date(Format::PHP_DATE, time() + 86400));

        $rows = $driver->fetchAllAssoc($sql, $bind);

        assertThat($rows, nonEmptyArray());
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchSimpleCount(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT count(*) NB FROM A1';

        $row = $driver->fetchAssoc($sql);

        $nb = (int) $row['NB'];

        assertThat($nb, is(greaterThan(2)));
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchCountWithUnion(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT count(*) NB FROM A1 '
             . 'UNION '
             . 'SELECT count(*) NB FROM dual';

        $cols = $driver->fetchAllAssoc($sql);

        $sum = array_sum(array_column($cols, 'NB'));

        assertThat($sum, is(greaterThan(2)));
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
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

        assertThat($count, is(1));
    }

    /**
     * @depends testUpdateDataWithClobBind
     */
    public function testFetchDataWithClob(): void
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT N_CLOB FROM A1 WHERE N_CLOB IS NOT NULL';

        $row = $driver->fetchAssoc($sql);

        assertThat($row, nonEmptyArray());
        assertThat($row['N_CLOB'], nonEmptyString());
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
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

        assertThat($count, is(1));
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testInsertWithReturning(): void
    {
        $driver = Provider::getDriver();

        // useful with SEQ.nextval
        $sql = 'INSERT INTO A1 (N_NUM) VALUES (:N1) RETURNING N_NUM INTO :myNum';

        $bind = new Parameter();
        $bind->add(':N1', 1258);
        $bind->add(':myNum', null, 38); // maxlength for output variable is required

        $driver->beginTransaction();

        $count = $driver->executeUpdate($sql, $bind);

        $num = (int) $bind->getVariable(':myNum');

        $driver->rollbackTransaction();

        assertThat($num, is(identicalTo(1258)));
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testReadDataWithClobAndFunctionCall(): void
    {
        $driver = Provider::getDriver();

        // TESTCLOB is a Function already created
        $sql = 'BEGIN :myLob := TESTCLOB(:N1); END;';

        $bind = new Parameter();
        $bind->add(':N1', 2);

        // Read mode
        $bind->addForCLob($driver->getConnection(), ':myLob');

        $statement = $driver->executeQuery($sql, $bind);

        $lob = $bind->getVariable(':myLob');

        // In a loop, pay attention to free the result
        $row = $lob->load();

        $lob->free();
        oci_free_statement($statement);

        assertThat($row, nonEmptyString());
    }

    public function testInsertDataWithNoBindingOfLongRaw(): void
    {
        $driver = Provider::getDriver();

        $value = bin2hex('Any long raw as hex value');
        $sql = "INSERT INTO A2 (N_LONG_RAW) VALUES ('$value')";

        $res = $driver->executeUpdate($sql);

        assertThat($res, is(1));
    }

    public function testInsertDataWithLongRawBinding(): void
    {
        $driver = Provider::getDriver();

        $sql = 'INSERT INTO A2 (N_LONG_RAW) VALUES (:VAL)';

        $bind = new Parameter();
        $bind->addForLongRaw(':VAL', bin2hex('Any long raw as hex value'));

        $res = $driver->executeUpdate($sql, $bind);

        assertThat($res, is(1));
    }
}
