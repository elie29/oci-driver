<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{

    public function testColumnSelect(): void
    {
        $sql = Select::start()
            ->column('COL1')
            ->column('COL2', 't2')
            ->columns(['COL1', 'COL2'])
            ->columns(['COL3', 'COL4'], 't2')
            ->build();

        assertThat($sql, is('SELECT COL1, t2.COL2, COL1, COL2, t2.COL3, t2.COL4 FROM '));
    }

    public function testColumnFromSelect(): void
    {
        $sql = Select::start()
            ->column('active, u.*')
            ->from('params')
            ->from('users', 'u')
            ->build();

        assertThat($sql, is('SELECT active, u.* FROM params, users u'));
    }

    public function testFromWithInnerSelect(): void
    {
        $sql = Select::start()
            ->column('*')
            ->from(Select::start()->column('*')->from('users'))
            ->orderBy('name', 'DESC')
            ->build();

        assertThat($sql, is('SELECT * FROM (SELECT * FROM users) ORDER BY name DESC'));
    }

    public function testColumnFromJoinSelect(): void
    {
        $sql = Select::start()
            ->column('p.*')
            ->from('params')
            ->from('params', 'p')
            ->join('users', 'u', 'u.user_id = p.user_id')
            ->join('users', 'u', 'u.user_id = p.user_id') // should not be added twice
            ->leftJoin('users', 'u2', 'u2.user_id = p.user_id')
            ->rightJoin('params', 'p2', 'p2.user_id = p.user_id')
            ->build();

        $expected = 'SELECT p.* FROM params, params p '
                  . 'INNER JOIN users u ON u.user_id = p.user_id '
                  . 'LEFT JOIN users u2 ON u2.user_id = p.user_id '
                  . 'RIGHT JOIN params p2 ON p2.user_id = p.user_id';

        assertThat($sql, is($expected));
    }

    public function testSelectWhereOnly(): void
    {
        $sql = Select::start()
            ->column('p.*')
            ->from('params', 'p')
            ->where('p.id = 1')
            ->Where('(p.id = 5 OR p.id = 3)')
            ->build();

        $expected = 'SELECT p.* FROM params p WHERE p.id = 1 AND (p.id = 5 OR p.id = 3)';
        assertThat($sql, is($expected));
    }

    public function testSelectWhereAnd(): void
    {
        $sql = Select::start()
            ->column('p.*')
            ->from('params', 'p')
            ->where('p.id = 1')
            ->andWhere('(p.id = 5 OR p.id = 3)')
            ->build();

        $expected = 'SELECT p.* FROM params p WHERE p.id = 1 AND (p.id = 5 OR p.id = 3)';
        assertThat($sql, is($expected));
    }

    public function testSelectWhereAndReversed(): void
    {
        $sql = Select::start()
            ->column('p.*')
            ->from('params', 'p')
            ->andWhere('(p.id = 5 OR p.id = 3)')
            ->where('p.id = 1')
            ->build();

        $expected = 'SELECT p.* FROM params p WHERE (p.id = 5 OR p.id = 3) AND p.id = 1';
        assertThat($sql, is($expected));
    }

    public function testSelectWhereOr(): void
    {
        $sql = Select::start()
            ->column('p.*')
            ->from('params', 'p')
            ->where('p.id = 1')
            ->orWhere('(p.id = 5 AND p.id = 3)')
            ->build();

        $expected = 'SELECT p.* FROM params p WHERE p.id = 1 OR (p.id = 5 AND p.id = 3)';
        assertThat($sql, is($expected));
    }

    public function testSelectOrderBy(): void
    {
        $sql = Select::start()
            ->column('id', 'p')
            ->columns(['name', 'active'], 'p')
            ->from('params', 'p')
            ->orderBy('p.id', 'DESC NULLS FIRST')
            ->build();

        $expected = 'SELECT p.id, p.name, p.active FROM params p ORDER BY p.id DESC NULLS FIRST';
        assertThat($sql, is($expected));
    }

    public function testSelectUnionOrderBy(): void
    {
        $sql = Select::start()
            ->column('p.id')
            ->from('params', 'p')
            ->union()
            ->column('p.id')
            ->from('params_his', 'p')
            ->orderBy('id')
            ->build();

        $expected = 'SELECT p.id FROM params p UNION SELECT p.id FROM params_his p ORDER BY id ASC';
        assertThat($sql, is($expected));
    }

    public function testSelectUnionWithOrderBy(): void
    {
        $sql = Select::start()
            ->column('p.id')
            ->from('params', 'p')
            ->unionWith(Select::start()
                ->column('p.id')
                ->from('params_his', 'p'))
            ->orderBy('id')
            ->build();

        $expected = 'SELECT p.id FROM params p UNION SELECT p.id FROM params_his p ORDER BY id ASC';
        assertThat($sql, is($expected));
    }

    public function testSelectWithLimit(): void
    {
        $sql = Select::start()
            ->column('*')
            ->from('params', 'p')
            ->setLimit(3)
            ->orderBy('p.name')
            ->build();

        $expected = 'SELECT a.* FROM (SELECT * FROM params p ORDER BY p.name ASC) a WHERE ROWNUM <= 3';
        assertThat($sql, is($expected));
    }

    public function testSelectWithLimitAndOffset(): void
    {
        $sql = Select::start()
            ->column('*')
            ->from('params', 'p')
            ->setLimit(3, 1)
            ->orderBy('p.name')
            ->build();

        $expected = 'SELECT * ' .
                    'FROM (' .
                        'SELECT a.*, ROWNUM AS row_number ' .
                        'FROM (SELECT * FROM params p ORDER BY p.name ASC) a ' .
                        'WHERE ROWNUM <= 4) ' .
                    'WHERE row_number >= 2';

        assertThat($sql, is($expected));
    }

    public function testSelectWithGroupBy(): void
    {
        $sql = Select::start()
            ->column('MAX(p.id)')
            ->from('params', 'p')
            ->groupBy('p.user_id')
            ->build();

        $expected = 'SELECT MAX(p.id) FROM params p GROUP BY p.user_id';
        assertThat($sql, is($expected));
    }

    public function testSelectWithTwoGroupBy(): void
    {
        $sql = Select::start()
            ->column('MAX(p.id)')
            ->from('params', 'p')
            ->groupBy('p.user_id')
            ->groupBy('p.name')
            ->build();

        $expected = 'SELECT MAX(p.id) FROM params p GROUP BY p.user_id, p.name';
        assertThat($sql, is($expected));
    }

    public function testSelectWithGroupByHaving(): void
    {
        $sql = Select::start()
            ->columns(['name', 'MAX(user_id)'])
            ->from('params')
            ->groupBy('name')
            ->having('MAX(user_id) > 3')
            ->build();

        $expected = 'SELECT name, MAX(user_id) FROM params GROUP BY name HAVING MAX(user_id) > 3';
        assertThat($sql, is($expected));
    }

    public function testSelectWithGroupByAndHaving(): void
    {
        $sql = Select::start()
            ->columns(['name', 'MAX(user_id)'])
            ->from('params')
            ->groupBy('name')
            ->having('MAX(user_id) > 3')
            ->andHaving('name = :name')
            ->build();

        $expected = 'SELECT name, MAX(user_id) FROM params GROUP BY name HAVING MAX(user_id) > 3 AND name = :name';
        assertThat($sql, is($expected));
    }

    public function testSelectWithGroupByMultiHaving(): void
    {
        $sql = Select::start()
            ->columns(['name', 'MAX(user_id)'])
            ->from('params')
            ->groupBy('name')
            ->having('MAX(user_id) > 3')
            ->having('name = :name')
            ->build();

        $expected = 'SELECT name, MAX(user_id) FROM params GROUP BY name HAVING MAX(user_id) > 3 AND name = :name';
        assertThat($sql, is($expected));
    }

    public function testSelectWithGroupByOrHaving(): void
    {
        $sql = Select::start()
            ->columns(['name', 'MAX(user_id)'])
            ->from('params')
            ->groupBy('name')
            ->having('MAX(user_id) > 3')
            ->orHaving('name = :name')
            ->build();

        $expected = 'SELECT name, MAX(user_id) FROM params GROUP BY name HAVING MAX(user_id) > 3 OR name = :name';
        assertThat($sql, is($expected));
    }
}
