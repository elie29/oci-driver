<?php

declare(strict_types=1);

/**
 * Setup script to create A1 and A2 tables for integration tests
 *
 * Usage: composer setup-tables
 */
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Elie\OCI\Helper\Provider;

try {
    $driver = Provider::getDriver();

    echo "Creating test tables...\n";

    // Drop tables if they exist
    echo "Dropping existing tables (if any)...\n";
    try {
        $driver->executeUpdate('DROP TABLE A1');
        echo "🚧 Table A1 dropped\n";
    } catch (Exception $e) {
        echo "✔️ Table A1 does not exist (skipping)\n";
    }

    try {
        $driver->executeUpdate('DROP TABLE A2');
        echo "🚧 Table A2 dropped\n";
    } catch (Exception $e) {
        echo "✔️ Table A2 does not exist (skipping)\n";
    }

    // Create A1 table
    $sqlA1 = <<<SQL
        CREATE TABLE A1 (
            "N_CHAR"   CHAR(5 BYTE),
            "N_NUM"    NUMBER,
            "N_NUM_3"  NUMBER(6,3),
            "N_VAR"    VARCHAR2(4000),
            "N_CLOB"   CLOB,
            "N_DATE"   DATE,
            "N_TS"     TIMESTAMP,
            "N_LONG"   LONG
        )
        SQL;

    $driver->executeUpdate($sqlA1);
    echo "🚀 Table A1 created successfully\n";

    // Create A2 table
    $sqlA2 = <<<SQL
        CREATE TABLE A2 (
            "N_LONG_RAW" LONG RAW
        )
        SQL;

    $driver->executeUpdate($sqlA2);
    echo "🚀 Table A2 created successfully\n";
    echo "\nSetup completed successfully!\n";
    exit(0);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "\nPlease ensure:\n";
    echo "  1. Oracle database is running and accessible\n";
    echo "  2. config-connection.php exists in tests/integration/\n";
    echo "  3. Database credentials are correct\n";
    exit(1);
}
