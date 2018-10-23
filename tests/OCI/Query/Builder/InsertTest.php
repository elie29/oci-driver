<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

use PHPUnit\Framework\TestCase;

class InsertTest extends TestCase
{

    public function testSimpleInsert(): void
    {
        $sql = Insert::start()
            ->into('users')
            ->values([
                'USER_ID'    => ':ID',
                'NAME'       => ':NAME',
                'BIRTH_DATE' => ':B_DATE',
            ])
            ->build();

        $expected = 'INSERT INTO users (USER_ID, NAME, BIRTH_DATE) VALUES (:ID, :NAME, :B_DATE)';

        assertThat($sql, is($expected));
    }

    public function testSimpleInsertWithoudBinding(): void
    {
        $sql = Insert::start()
            ->into('users')
            ->values([
                'USER_ID'    => 3,
                'NAME'       => Insert::quote("O'neil"),
                'BIRTH_DATE' => Insert::quote('21/11/79'),
            ])
            ->build();

        $expected = "INSERT INTO users (USER_ID, NAME, BIRTH_DATE) VALUES (3, 'O''neil', '21/11/79')";

        assertThat($sql, is($expected));
    }
}
