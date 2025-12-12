<?php

declare(strict_types=1);

namespace OCI\Query\Builder;

use PHPUnit\Framework\TestCase;

class InsertTest extends TestCase
{
    public function testSimpleInsert(): void
    {
        $sql = Insert::start()
            ->into('users')
            ->values([
                'USER_ID' => ':ID',
                'NAME' => ':NAME',
                'BIRTH_DATE' => ':B_DATE',
            ])
            ->build();

        $expected = 'INSERT INTO users (USER_ID, NAME, BIRTH_DATE) VALUES (:ID, :NAME, :B_DATE)';

        $this->assertSame($expected, $sql);
    }

    public function testSimpleInsertWithoutBinding(): void
    {
        $sql = Insert::start()
            ->into('users')
            ->values([
                'USER_ID' => 3,
                'NAME' => Insert::quote("O'neil"),
                'BIRTH_DATE' => Insert::quote('21/11/79'),
            ])
            ->build();

        $expected = "INSERT INTO users (USER_ID, NAME, BIRTH_DATE) VALUES (3, 'O''neil', '21/11/79')";

        $this->assertSame($expected, $sql);
    }

    public function testSimpleInsertWithReturning(): void
    {
        $sql = Insert::start()
            ->into('users')
            ->values([
                'USER_ID' => 3,
                'NAME' => Insert::quote("O'neil"),
                'BIRTH_DATE' => Insert::quote('21/11/79'),
            ])
            ->returning('desc', ':myDesc')
            ->returning('lib', ':myLib')
            ->build();

        $expected = "INSERT INTO users (USER_ID, NAME, BIRTH_DATE) VALUES (3, 'O''neil', '21/11/79') "
            . 'RETURNING desc, lib INTO :myDesc, :myLib';

        $this->assertSame($expected, $sql);
    }

    public function testInsertSelect(): void
    {
        $select = Select::start()
            ->columns(["'test'", 1])
            ->from('DUAL');

        $sql = Insert::start()
            ->into('A1')
            ->columns(['N_CHAR', 'N_NUM'])
            ->select($select)
            ->build();

        $expected = "INSERT INTO A1 (N_CHAR, N_NUM) SELECT 'test', 1 FROM DUAL";

        $this->assertSame($expected, $sql);
    }
}
