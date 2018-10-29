# OCI Driver

## Documentation

## Text file encoding
- UTF-8

## Code style formatter

- PSR-2

## Structure

- **src**: source code

- **tests**: unit tests files

- **vendor**: Dependencies classes

- **composer.json**: Dependencies configuration

- **phpunit.xml**: Phpunit configuration

## Install dependencies

### For development mode

Run `composer install`

## Using OCI Query Builder
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

### Insert/Update Example

#### With Autocommit
Autocommit is the default behaviour of OCI Driver:
```php
$connection = oci_pconnect('username', 'pass', 'schema', 'UTF8');
$driver = Factory::create($connection, 'dev');

$sql = 'INSERT INTO A1 (N_NUM) VALUES (5)';
$count = $driver->executeUpdate($sql);
echo $count; // displays 1
```

#### With Transaction
In order to start a transaction, you should use beginTransaction as follow:
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

## Prepare for test

Before launching unit tests, you should follow these steps:

### Create A1 and A2 tables
In order to launch tests, A1 and A2 tables should be created as follow:

```sql
    CREATE TABLE A1
    ("N_CHAR" CHAR(5 BYTE),
     "N_NUM" NUMBER,
     "N_NUM_3" NUMBER(6,3),
     "N_VAR" VARCHAR2,
     "N_CLOB" CLOB,
     "N_DATE" DATE,
     "N_TS" TIMESTAMP,
     "N_LONG" LONG);

    CREATE TABLE A2
    ("N_LONG_RAW" LONG RAW);
```

### Rename config file
Rename config-connection.php.dist in ./tests/OCI/Helper to config-connection.php

```console
    mv config-connection.php.dist config-connection.php
```

### Modify configuration
Modify USERNAME, PASSWORD and SCHEMA according to your Oracle Database Information

   > SCHEMA could be one of the following:

 - SID name if you are executing the tests on the same database server
   or if you have a configured SID in tnsnames.ora

- IP:PORT/SID eg: 11.22.33.25:12005/HR

 - Use the following TNS :
   > (DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=DATABASE_IP)(PORT=DATABASE_PORT))(CONNECT_DATA=(SID=DATABASE_SCHEMA)(SERVER=DEDICATED|POOLED)))

## Launch test

Run `composer test`

## Launch code coverage

Run `composer cover`