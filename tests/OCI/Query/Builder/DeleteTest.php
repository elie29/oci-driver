<?php

declare(strict_types=1);

namespace OCI\Query\Builder;

use PHPUnit\Framework\TestCase;

use function assertThat;
use function is;

class DeleteTest extends TestCase
{
    public function testSimpleDelete(): void
    {
        $sql = Delete::start()
            ->from('params')
            ->where('id > 1')
            ->build();

        assertThat($sql, is('DELETE FROM params WHERE id > 1'));
    }

    public function testSimpleDeleteWithQuotedValue(): void
    {
        $sql = Delete::start()
        ->from('users')
        ->where('name = ' . Delete::quote("O'neil"))
        ->build();

        assertThat($sql, is("DELETE FROM users WHERE name = 'O''neil'"));
    }

    public function testDeleteWithOneWhereCondition(): void
    {
        $sql = Delete::start()
        ->from('params', 'p')
        ->where('(p.name = :name AND p.id = :id) OR p.active = :active')
        ->build();

        $expected = 'DELETE FROM params p WHERE (p.name = :name AND p.id = :id) OR p.active = :active';
        assertThat($sql, $expected);
    }

    public function testDeleteWithAndWhereCondition(): void
    {
        $sql = Delete::start()
        ->from('params', 'p')
        ->where('p.id > 1')
        ->andWhere('(p.name = :name OR p.active = :active)')
        ->build();

        $expected = 'DELETE FROM params p WHERE p.id > 1 AND (p.name = :name OR p.active = :active)';

        assertThat($sql, is($expected));
    }

    public function testDeleteWithOrWhereCondition(): void
    {
        $sql = Delete::start()
        ->from('params', 'p')
        ->where('p.active = :active')
        ->orWhere('(p.name = :name AND p.id = :id)')
        ->build();

        $expected = 'DELETE FROM params p WHERE p.active = :active OR (p.name = :name AND p.id = :id)';

        assertThat($sql, is($expected));
    }
}
