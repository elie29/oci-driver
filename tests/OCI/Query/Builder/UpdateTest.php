<?php

declare(strict_types=1);

namespace OCI\Query\Builder;

use PHPUnit\Framework\TestCase;

use function assertThat;
use function is;

class UpdateTest extends TestCase
{
    public function testSimpleUpdateWithQuotedString(): void
    {
        $sql = Update::start()
            ->table('users', 'u')
            ->set('u.name', Update::quote("O'neil"))
            ->where('u.id = 1')
            ->build();

        $expected = "UPDATE users u SET u.name = 'O''neil' WHERE u.id = 1";

        assertThat($sql, is($expected));
    }

    public function testSimpleUpdateUnquotedString(): void
    {
        $sql = Update::start()
        ->table('users', 'u')
        ->set('u.name', Update::quote("Helmut")) // quote is still required for fixed value
        ->where('u.id = 1')
        ->build();

        $expected = "UPDATE users u SET u.name = 'Helmut' WHERE u.id = 1";

        assertThat($sql, is($expected));
    }

    public function testSimpleUpdateWithInt(): void
    {
        $sql = Update::start()
        ->table('users', 'u')
        ->set('u.visible', 1)
        ->where('u.id = 10')
        ->build();

        $expected = "UPDATE users u SET u.visible = 1 WHERE u.id = 10";
        assertThat($sql, is($expected));
    }

    public function testUpdateWithAndWhere(): void
    {
        $sql = Update::start()
        ->table('params', 'p')
        ->set('p.id', ':id')
        ->where('p.name = :name')
        ->andWhere('(p.id = :id OR p.active = :active)')
        ->build();

        $expected = 'UPDATE params p SET p.id = :id WHERE p.name = :name AND (p.id = :id OR p.active = :active)';

        assertThat($sql, is($expected));
    }

    public function testUpdateWithReturning(): void
    {
        $sql = Update::start()
        ->table('params', 'p')
        ->set('p.id', ':id')
        ->where('p.name = :name')
        ->andWhere('(p.id = :id OR p.active = :active)')
        ->returning('p.desc', ':myDesc')
        ->returning('p.lib', ':myLib')
        ->build();

        $expected = 'UPDATE params p SET p.id = :id WHERE p.name = :name AND (p.id = :id OR p.active = :active) '
        . 'RETURNING p.desc, p.lib INTO :myDesc, :myLib';

        assertThat($sql, is($expected));
    }

    /**
     * UPDATE params p1
     * SET p1.name = :name
     * WHERE p1.id = :id
     * AND EXISTS (
     *    SELECT 1
     *    FROM (SELECT id FROM params WHERE active = :active ORDER BY name DESC) p2
     *    WHERE p2.id = p1.id
     *    AND ROWNUM  = 1
     * );
     */
    public function testUpdateUsingSelect(): void
    {
        $update = Update::start()
        ->table('params', 'p1')
        ->set('p1.name', ':name')
        ->where('p1.id = :id')
        ->andWhere("EXISTS ({$this->lastParam()})")
        ->build();

        $expected = 'UPDATE params p1 SET p1.name = :name WHERE '
              . 'p1.id = :id AND EXISTS (SELECT 1 FROM (SELECT id FROM '
              . 'params WHERE active = :active ORDER BY name DESC) p2 '
              . 'WHERE p2.id = p1.id AND ROWNUM = 1)';

        assertThat($update, is($expected));
    }

    private function lastParam(): string
    {
        $lastParamId = Select::start()
        ->column('id')
        ->from('params')
        ->where('active = :active')
        ->orderBy('name', 'DESC'); // if build is called, parentheses become required

        return Select::start()
        ->column('1')
        ->from($lastParamId, 'p2')
        ->where('p2.id = p1.id')
        ->andWhere('ROWNUM = 1')
        ->build();
    }
}
