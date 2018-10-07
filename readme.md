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

## Prepare for test

Before launching unit tests, you should follow these steps:

### Create A1 and A2 tables
In order to launch tests, A1 and A2 tables should be created as follow:

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

### Rename config file
Rename config-connection.php.dist in ./tests/OCI/Helper to config-connection.php

    mv config-connection.php.dist config-connection.php

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