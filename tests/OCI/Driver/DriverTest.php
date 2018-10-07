<?php

declare(strict_types = 1);

namespace OCI\Driver;

use Mockery;
use OCI\Debugger\DebuggerInterface;
use OCI\Driver\Driver;
use OCI\Driver\Parameter\Parameter;
use OCI\Helper\Provider;
use OCI\OCITestCase;

class DriverTest extends OCITestCase
{

    private $errors = [];

    protected function setUp()
    {
        parent::setUp();
        // for trigger_error capturing
        set_error_handler(function () {
            $this->errors[] = func_get_args();
        });
    }

    public static function setUpBeforeClass()
    {
        $driver = Provider::getDriver();
        $driver->executeUpdate('TRUNCATE TABLE A1');
        $driver->executeUpdate('TRUNCATE TABLE A2');
    }

    public static function tearDownAfterClass()
    {
        self::setUpBeforeClass();
    }

    public function testCreateInstanceWithMock()
    {
        $debugger = Mockery::mock(DebuggerInterface::class);
        $driver = new Driver('test', $debugger);

        assertThat($driver, anInstanceOf(DriverInterface::class));
    }

    /**
     * @expectedException OCI\Driver\DriverException
     */
    public function testExecuteWithException()
    {
        $driver = Provider::getDriver();
        $sql = 'Select FROM A1';
        $driver->executeQuery($sql);
    }

    /**
     * @expectedException OCI\Driver\DriverException
     */
    public function testExecuteTransactionWithException()
    {
        $driver = Provider::getDriver();
        $driver->beginTransaction();
        $sql = 'Select FROM A1';
        $driver->executeQuery($sql);
    }

    /**
     * @dataProvider OCI\Helper\Provider::dataWithNoBind
     */
    public function testExecuteUpdateWithoutBindNorTransaction($num, $num3, $ts, $long)
    {
        $driver = Provider::getDriver();

        $sql = "INSERT INTO A1 (N_NUM, N_NUM_3, N_TS, N_LONG) VALUES ($num, $num3, $ts, $long)";

        $res = $driver->executeUpdate($sql);

        assertThat($res, is(1));
    }

    /**
     * @dataProvider OCI\Helper\Provider::dataWithNoBind
     */
    public function testExecuteUpdateWithoutBindWithTransactionRollback($num, $num3, $ts, $long)
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
    public function testExecuteUpdateWithoutBindAndTransactionCommit($num, $num3, $ts, $long)
    {
        $driver = Provider::getDriver();
        $sql = "INSERT INTO A1 (N_NUM, N_NUM_3, N_TS, N_LONG) VALUES ($num, $num3, $ts, $long)";

        $driver->beginTransaction();
        $res = $driver->executeUpdate($sql);
        $driver->commitTransaction();

        assertThat($res, is(1));
    }

    public function testExecuteUpdateWithBindAndTransactionRollback()
    {
        $driver = Provider::getDriver();
        $sql = 'INSERT INTO A1 (N_CHAR, N_NUM, N_NUM_3, N_VAR, N_DATE, N_TS, N_LONG) VALUES '
             . '(:N1, :N2, :N3, :N4, TO_DATE(:N5, \'YYYY-MM-DD\'), TO_TIMESTAMP(:N6, \'YYYY-MM-DD HH24:MI:SS\'), :N7)';

        $driver->beginTransaction();
        $bind = new Parameter();
        $bind->add(':N1', 'c')
            ->add(':N2', 1)
            ->add(':N3', 0.24)
            ->add(':N4', 'test')
            ->add(':N5', '2018-08-08')
            ->add(':N6', '2018-08-09 1235:36')
            ->add(':N7', 18596);

        $res = $driver->executeUpdate($sql, $bind);
        $driver->rollbackTransaction();

        assertThat($res, is(1));
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchAssocWithBind()
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
    public function testFetchAllAssocWithBind()
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
    public function testFetchAssocWithoutBind()
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT * FROM A1 WHERE N_NUM = 2';

        $row = $driver->fetchAssoc($sql);

        assertThat($row, nonEmptyArray());
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchAllAssocWithoutBind()
    {
        $driver = Provider::getDriver();
        $sql = 'SELECT * FROM A1';

        $row = $driver->fetchAllAssoc($sql);

        assertThat($row, nonEmptyArray());
    }

    /**
     * @depends testExecuteUpdateWithoutBindNorTransaction
     */
    public function testFetchSimpleCount()
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
    public function testFetchCountWithUnion()
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
    public function testUpdateDataWithClobBind()
    {
        $driver = Provider::getDriver();
        $sql = 'Update A1 SET N_CLOB = :LOB WHERE N_NUM = :NUM';

        $bind = new Parameter();
        $bind->add(':NUM', 2);

        // Write mode
        $bind->addForCLob($driver->getConnexion(), ':LOB', file_get_contents(__FILE__));

        $count = $driver->executeUpdate($sql, $bind);

        $lob = $bind->getVariable(':LOB');
        $lob->close();
        $lob->free();

        assertThat($count, is(2));
    }

    /**
     * @depends testUpdateDataWithClobBind
     */
    public function testFetchDataWithClob()
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
    public function testReadDataWithClob()
    {
        $driver = Provider::getDriver();

        $sql = 'INSERT INTO A1 (N_NUM, N_CLOB) VALUES (:N1, EMPTY_CLOB()) RETURNING N_CLOB INTO :myLob';

        $bind = new Parameter();
        $bind->add(':N1', 5);

        // Read mode
        $bind->addForCLob($driver->getConnexion(), ':myLob');

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
    public function testReadDataWithClobAndFuctionCall()
    {
        $driver = Provider::getDriver();

        // TESTCLOB is a Function already created
        $sql = 'BEGIN :myLob := TESTCLOB(:N1); END;';

        $bind = new Parameter();
        $bind->add(':N1', 2);

        // Read mode
        $bind->addForCLob($driver->getConnexion(), ':myLob');

        $statement = $driver->executeQuery($sql, $bind);

        $lob = $bind->getVariable(':myLob');

        // In a loop, pay attention to free the result
        $row = $lob->load();

        $lob->free();
        oci_free_statement($statement);

        assertThat($row, nonEmptyString());
    }

    public function testInsertDataWithNoBindingOfLongRaw()
    {
        $driver = Provider::getDriver();

        $value = bin2hex('Any long raw as hex value');
        $sql = "INSERT INTO A2 (N_LONG_RAW) VALUES ('$value')";

        $res = $driver->executeUpdate($sql);

        assertThat($res, is(1));
    }

    public function testInsertDataWithLongRawBinding()
    {
        $driver = Provider::getDriver();

        $sql = 'INSERT INTO A2 (N_LONG_RAW) VALUES (:VAL)';

        $bind = new Parameter();
        $bind->addForLongRaw(':VAL', bin2hex('Any long raw as hex value'));

        $res = $driver->executeUpdate($sql, $bind);

        assertThat($res, is(1));
    }
}
