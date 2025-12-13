# OCI Driver

[![build](https://github.com/elie29/oci-driver/actions/workflows/php-build.yml/badge.svg)](https://github.com/elie29/oci-driver/actions/workflows/php-build.yml)
[![Coverage Status](https://coveralls.io/repos/github/elie29/oci-driver/badge.svg)](https://coveralls.io/github/elie29/oci-driver)
[![PHP Version](https://img.shields.io/packagist/php-v/elie29/oci-driver.svg)](https://packagist.org/packages/elie29/oci-driver)

## Text file encoding

- UTF-8

## Code style formatter

- PSR-4

## Installation

Run the command below to install via Composer:

```shell
composer require elie29/oci-driver
```

## Getting Started

OCI Query Builder provides a lightweight builder to dynamically create SQL queries.
It **does not** validate the query at all.

### Select builder

```php
// SELECT * FROM params ORDER BY name ASC
$sql = Select::start() // aka (new Select)
    ->column('*')
    ->from('params')
    ->orderBy('name')
    ->build();
```

### Select builder with union

```php
// SELECT p.id FROM params p UNION SELECT p.id FROM params_his p ORDER BY id ASC
$sql = Select::start() // aka (new Select)
    ->column('p.id')
    ->from('params', 'p')
    ->union()
    ->column('p.id')
    ->from('params_his', 'p')
    ->orderBy('id')
    ->build();
```

### Delete builder

```php
// DELETE FROM params WHERE id = 2
$sql = Delete::start() // aka (new Delete)
    ->from('params')
    ->where('id = 2')
    ->build();
```

### Update builder

```php
// UPDATE users u SET u.name = 'O''neil' WHERE u.user_id = 1
$sql = Update::start() // aka (new Update)
    ->table('users', 'u')
    ->set('u.name', Update::quote("O'neil"))
    ->where('u.user_id = 1')
    ->build();
```

### Insert builder

```php
// INSERT INTO params (user_id, name) VALUES (:id, :name)
$sql = Insert::start() // aka (new Insert)
    ->into('params')
    ->values([
        'user_id' => ':id',
        'name'    => ':name',
    ])
    ->build();
```

> More examples are found in tests/OCI/Query/Builder folder.

## Using OCI Driver Class

### Using the factory

The `Factory::create()` method is the recommended way to create a Driver instance. It provides several benefits:

**Purpose:**

- Automatically configures the Oracle session with proper NLS settings
- Sets up the appropriate debugger based on the environment
- Ensures consistent date/time and numeric formats across all queries

**Environment Parameter (`$env`):**

The second parameter defines the execution environment and controls debugging behavior:

- `'prod'` or `'production'`: Production mode - uses DebuggerDumb (no output)
- `'dev'` or `'development'` or `'test'`: Development mode - uses DebuggerDump (outputs debug information using
  VarDumper)

**Session Configuration:**

Factory automatically alters the Oracle session to set:

- `NLS_DATE_FORMAT`: 'YYYY-MM-DD' (ISO format)
- `NLS_TIMESTAMP_FORMAT`: 'YYYY-MM-DD HH24:MI:SS' (ISO format)
- `NLS_NUMERIC_CHARACTERS`: '.,' (dot as decimal separator, comma as thousand separator)

This eliminates the need to use `TO_CHAR` or `TO_DATE` in your SQL queries:

```php
// Create driver with development debugging
$driver = Factory::create(Provider::getConnection(), 'dev');

$sql = 'SELECT * FROM A1 WHERE N_DATE BETWEEN :YESTERDAY AND :TOMORROW';

$bind = (new Parameter())
    ->add(':YESTERDAY', date('Y-m-d', time() - 86400)) // Direct ISO format
    ->add(':TOMORROW', date('Y-m-d', time() + 86400));

$rows = $driver->fetchAllAssoc($sql, $bind);
```

### Insert/Update Example

#### With Autocommit

Autocommit is the default behavior of OCI Driver:

```php
$connection = oci_pconnect('username', 'pass', 'schema', 'UTF8');
$driver = Factory::create($connection, 'dev');

$sql = 'INSERT INTO A1 (N_NUM) VALUES (5)';
$count = $driver->executeUpdate($sql);
echo $count; // displays 1
```

#### With Transaction

To start a transaction, you should use beginTransaction as follows:

```php
$connection = oci_pconnect('username', 'pass', 'schema', 'UTF8');
$driver = Factory::create($connection, 'dev');

$driver->beginTransaction();

try {
   $count = $driver->executeUpdate($sql);
   $driver->commitTransaction();
   echo $count; // displays 1
} catch (DriverException $e) {
   echo $e->getMessage();
}
```

**N.B.**: When an error occurred using a transaction, rollback is called automatically.

#### Bind parameters

```php
$connection = oci_pconnect('username', 'pass', 'schema', 'UTF8');
$driver = Factory::create($connection, 'dev');

$sql = 'INSERT INTO A1 (N_CHAR, N_NUM, N_NUM_3) VALUES (:N1, :N2, :N3)';

$parameter = (new Parameter())
    ->add(':N1', 'c')
    ->add(':N2', 1)
    ->add(':N3', 0.24);

$count = $driver->executeUpdate($sql, $parameter);
echo $count; // displays 1
```

### Fetch one row

```php
$connection = oci_pconnect('username', 'pass', 'schema', 'UTF8');
$driver = Factory::create($connection, 'dev');

$sql = 'SELECT * FROM A1 WHERE N_NUM = 2';

$row = $driver->fetchAssoc($sql);
```

**N.B.**: For binding parameters, follow the same insertion example above.

### Fetch many rows

```php
$connection = oci_pconnect('username', 'pass', 'schema', 'UTF8');
$driver = Factory::create($connection, 'dev');

$sql = 'SELECT * FROM A1';

$rows = $driver->fetchAllAssoc($sql);
```

**N.B.**: For binding parameters, follow the same insertion example above.

## Prepare for the test

Before launching integration tests, you should follow these steps:

### 1. Configure database connection

Rename `config-connection.php.dist` in `./tests/integration` to `config-connection.php`:

```bash
cd tests/integration
cp config-connection.php.dist config-connection.php
```

Modify `USERNAME`, `PASSWORD` and `SCHEMA` according to your Oracle Database Information.

> SCHEMA could be one of the following:

- IP:PORT/SID eg: `11.22.33.25:12005/HR`
- SID name if you are executing the tests on the same database server or if you have a configured SID in tnsnames.ora
  - Use the following TNS:

      ```TNSNAMES
      (DESCRIPTION = 
        (ADDRESS = 
          (PROTOCOL = TCP)(HOST = DATABASE_IP)(PORT=DATABASE_PORT)
        )
        (CONNECT_DATA = 
          (SID=DATABASE_SCHEMA)(SERVER=DEDICATED|POOLED)
        )
      )
      ```

### 2. Create A1 and A2 tables

#### Option A: Using the automated setup script (Recommended)

Run the composer script to automatically create the required tables:

```bash
composer setup-tables
```

This will:

- Drop existing A1 and A2 tables if they exist
- Create fresh A1 and A2 tables with the correct structure
- Verify the connection and provide helpful error messages

#### Option B: Manual SQL creation

Alternatively, you can manually create the tables:

```sql
CREATE TABLE A1
(
  "N_CHAR"   CHAR(5 BYTE),
  "N_NUM"    NUMBER,
  "N_NUM_3"  NUMBER(6,3),
  "N_VAR"    VARCHAR2(4000),
  "N_CLOB"   CLOB,
  "N_DATE"   DATE,
  "N_TS"     TIMESTAMP,
  "N_LONG"   LONG
);

CREATE TABLE A2
(
  "N_LONG_RAW" LONG RAW
);
```

### 3. Run tests

Once the setup is complete, you can run the integration tests:

```bash
vendor/bin/phpunit --testsuite "Integration Tests"
```

## Development Prerequisites

### Test Structure

The project uses PHPUnit for testing with two test suites as configured in `phpunit.xml.dist`:

- **Unit Tests** (`tests/units/`): Fast, isolated tests that don't require database connections

  - Query Builder tests (Select, Insert, Update, Delete)
  - Helper utility tests (FloatUtils, ClauseInParamsHelper)

- **Integration Tests** (`tests/integration/`): Tests that require Oracle database connection

  - Driver tests (Connection, Query execution, Transaction management)
  - Factory and SessionInit tests

### Composer commands

- `setup-tables`: Creates A1 and A2 test tables in the Oracle database (required for integration tests)
- `test`: Runs PHPUnit tests without coverage
- `test-coverage`: Runs PHPUnit unit tests with code coverage
- `cover`: Runs tests with coverage and starts a local server at <http://localhost:5001>

You can run specific test suites:

```bash
vendor/bin/phpunit --testsuite "Unit Tests"
vendor/bin/phpunit --testsuite "Integration Tests"
```
