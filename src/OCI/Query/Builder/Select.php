<?php

declare(strict_types=1);

namespace OCI\Query\Builder;

class Select extends AbstractCommonBuilder
{
    protected int $limit = 0;
    protected int $offset = 0;
    protected string $sql = '';
    protected bool $distinct = false;
    // Preserves tables with aliases used in join syntax.
    protected array $joins = [];

    /**
     * Add a column to the select.
     * <code>
     *    // SELECT p.id, p.name FROM params p<br/>
     *    $sql = Select::start()
     *        ->column('id', 'p')
     *        ->column('name', 'p')
     *        ->from('params', 'p')
     *        ->build();
     * </code>
     *
     * @param string $name Column name.
     * @param string $prefix Optional column prefix.
     */
    public function column(string $name, string $prefix = self::EMPTY): static
    {
        $prefix .= $prefix ? '.' : self::EMPTY;
        return $this->add(self::COLUMNS, $prefix . $name);
    }

    /**
     * Add DISTINCT to the select.
     * <code>
     *    // SELECT DISTINCT p.id, p.name FROM params p<br/>
     *    $sql = Select::start()
     *        ->distinct()
     *        ->column('id', 'p')
     *        ->column('name', 'p')
     *        ->from('params', 'p')
     *        ->build();
     * </code>
     */
    public function distinct(): static
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Add a list of columns to the select.
     *
     * <code>
     *    // SELECT p.id, p.name, p.active FROM params p<br/>
     *    $sql = Select::start()
     *        ->column('id', 'p')
     *        ->columns(['name', 'active'], 'p')
     *        ->from('params', 'p')
     *        ->build();
     * </code>
     *
     * @param array $list List of columns.
     * @param string $prefix Optional column prefix.
     */
    public function columns(array $list, string $prefix = self::EMPTY): static
    {
        $prefix .= $prefix ? '.' : self::EMPTY;

        return $this->add(self::COLUMNS, $prefix . implode(self::COMMA . $prefix, $list));
    }

    /**
     * Adds table name to the query structure.
     * When Select instance is passed, build is encompassed in parentheses.
     *
     *
     * <code>
     *    // SELECT p.id from params p, users u where p.user_id = u.user_id<br/>
     *    $sql = Select::start()
     *        ->column('id', 'p')
     *        ->from('params', 'p')
     *        ->from('users', 'u')
     *        ->where('p.user_id = u.user_id)
     *        ->build();
     * </code>
     *
     * @param string|Select $table Table name.
     * @param string $alias Alias name.
     */
    public function from(string|Select $table, string $alias = self::EMPTY): static
    {
        if ($table instanceof Select) {
            $table = '(' . $table->build() . ')';
        }
        return $this->add(self::FROM, $table . $this->getTableAlias($alias));
    }

    /**
     * Adds an inner join in the query structure.
     * If the table with the same alias exists already, we ignore the join.
     * <code>
     *    // SELECT p.* FROM params p INNER JOIN users u ON u.user_id = p.user_id<br/>
     *    $sql = Select::start()
     *        ->column('*', 'p')
     *        ->from('params', 'p')
     *        ->join('users', 'u', 'u.user_id = p.user_id')
     *        ->build();
     * </code>
     *
     * @param string|Select $table Table name.
     * @param string $alias Alias name.
     * @param string $condition Join condition.
     */
    public function join(string|Select $table, string $alias, string $condition): static
    {
        return $this->forJoin('INNER JOIN', $table, $alias, $condition);
    }

    /**
     * Adds a left join in the query structure.
     * If the table with the same alias exists already, we ignore the join.
     * <code>
     *    // SELECT p.* FROM params p LEFT JOIN users u ON u.user_id = p.user_id<br/>
     *    $sql = Select::start()
     *        ->column('*', 'p')
     *        ->from('params', 'p')
     *        ->leftJoin('users', 'u', 'u.user_id = p.user_id')
     *        ->build();
     * </code>
     *
     * @param string|Select $table Table name.
     * @param string $alias Alias name.
     * @param string $condition Join condition.
     */
    public function leftJoin(string|Select $table, string $alias, string $condition): static
    {
        return $this->forJoin('LEFT JOIN', $table, $alias, $condition);
    }

    /**
     * Adds a right join in the query structure.
     * If the table with the same alias exists already, we ignore the join.
     * <code>
     *    // SELECT u.* FROM params p RIGHT JOIN users u ON u.user_id = p.user_id<br/>
     *    $sql = Select::start()
     *        ->column('*', 'u')
     *        ->from('params', 'p')
     *        ->leftJoin('users', 'u', 'u.user_id = p.user_id')
     *        ->build();
     * </code>
     *
     * @param string|Select $table Table name.
     * @param string $alias Alias name.
     * @param string $condition Join condition.
     */
    public function rightJoin(string|Select $table, string $alias, string $condition): static
    {
        return $this->forJoin('RIGHT JOIN', $table, $alias, $condition);
    }

    /**
     * Adds a group by in the query structure.
     *
     * <code>
     *    // SELECT MAX(p.id) FROM params p GROUP BY p.user_id, p.name<br/>
     *    $sql = Select::start()
     *        ->column('MAX(p.id)')
     *        ->from('params', 'p')
     *        ->groupBy('p.user_id')
     *        ->groupBy('p.name')
     *        ->build();
     * </code>
     *
     * @param string $groupBy Group by condition.
     */
    public function groupBy(string $groupBy): static
    {
        return $this->add(self::GROUPBY, $groupBy);
    }

    /**
     * Adds predicate having to the query.
     * Alias of andHaving
     *
     * <code>
     *    // SELECT name, MAX(user_id) FROM params GROUP BY name HAVING MAX(user_id) > 3<br/>
     *    $sql = Select::start()
     *        ->columns(['name', 'MAX(user_id)'])
     *        ->from('params')
     *        ->groupBy('name')
     *        ->having('MAX(user_id) > 3')
     *        ->build()
     * </code>
     *
     * @param string $having having condition.
     */
    public function having(string $having): static
    {
        return $this->andHaving($having);
    }

    /**
     * Adds predicate and to the having part of the query.
     *
     * <code>
     *    // SELECT name, MAX(user_id) FROM params GROUP BY name HAVING MAX(user_id) > 3 AND name = :name<br/>
     *    $sql = Select::start()
     *        ->columns(['name', 'MAX(user_id)'])
     *        ->from('params')
     *        ->groupBy('name')
     *        ->having('MAX(user_id) > 3')
     *        ->andHaving('name = :name')
     *        ->build();;
     * </code>
     *
     * @param string $having having condition.
     *  <b>Parentheses are required when mixing or/and conditions.</b>
     *  <b>If no having exists, the andHaving is considered as a simple having.
     */
    public function andHaving(string $having): static
    {
        if ($this->query[self::HAVING]) {
            $having = 'AND ' . $having;
        }
        return $this->add(self::HAVING, $having);
    }

    /**
     * Adds predicate or to the having part of the query.
     * Should be added after a having.
     *
     * <code>
     *    // SELECT name, MAX(user_id) FROM params GROUP BY name HAVING MAX(user_id) > 3 OR name = :name<br/>
     *    $sql = Select::start()
     *        ->columns(['name', 'MAX(user_id)'])
     *        ->from('params')
     *        ->groupBy('name')
     *        ->having('MAX(user_id) > 3')
     *        ->orHaving('name = :name')
     *        ->build();;
     * </code>
     *
     * @param string $having having condition.
     *  <b>Parentheses are required when mixing or/and conditions.</b>
     */
    public function orHaving(string $having): static
    {
        return $this->add(self::HAVING, 'OR ' . $having);
    }

    /**
     * Append a union to the query structure.
     * It resets current order by.
     *
     * <code>
     *    // SELECT p.id FROM params p UNION SELECT p.id FROM params_his p ORDER BY id ASC<br/>
     *    // orderBy must be at the end<br/>
     *    $sql = Select::start()
     *        ->column('p.id')
     *        ->from('params', 'p')
     *        ->union()
     *        ->column('p.id')
     *        ->from('params_his', 'p')
     *        ->orderBy('id')
     *        ->build();
     * </code>
     */
    public function union(): static
    {
        $this->sql .= $this->buildPartial();
        $this->sql .= ' UNION ';

        $this->joins = [];

        return $this->reset();
    }

    /**
     * Append a union to the current Select with another Select.
     * It resets current order by.
     *
     * <code>
     *    // SELECT p.id FROM params p UNION SELECT p.id FROM params_his p ORDER BY id ASC<br/>
     *    // orderBy must be at the end and not within unionWith<br/>
     *    $sql = Select::start()
     *        ->column('p.id')
     *        ->from('params', 'p')
     *        ->unionWith(
     *          Select::start()
     *           ->column('p.id')
     *           ->from('params_his', 'p')
     *        )
     *        ->orderBy('id')
     *        ->build();
     * </code>
     *
     * @param Select $select Select Object without orderBy.
     */
    public function unionWith(Select $select): static
    {
        $this->union();

        $this->sql .= $select->build();

        return $this;
    }

    /**
     * Specifies an ordering for the query results.
     *
     * @param string $sort The ordering expression.
     * @param string $order The ordering direction. ASC by default.
     * @return self.
     */
    public function orderBy(string $sort, string $order = self::ASC): static
    {
        return $this->add(self::ORDERBY, $sort . self::SPACE . $order);
    }

    /**
     * Sets a limit and an offset to the current query.
     *
     * @param int $limit Rows limit > 0.
     * @param int $offset Optional offset >=0.
     */
    public function setLimit(int $limit, int $offset = 0): static
    {
        $this->limit = max($limit, 0);
        $this->offset = max($offset, 0);

        return $this;
    }

    public function build(): string
    {
        $res = $this->sql . $this->buildPartial();

        if ($this->query[self::ORDERBY]) {
            $res .= ' ORDER BY ' . $this->implode(self::ORDERBY);
        }

        if ($this->limit > 0) {
            $res = $this->changeQueryLimit($res);
        }

        $this->reset();

        $this->limit = 0;
        $this->offset = 0;
        $this->sql = '';
        $this->joins = [];
        $this->distinct = false;

        return $res;
    }

    /**
     * Constructs the SQL Without order by.
     */
    protected function buildPartial(): string
    {
        // Columns are required to build the query
        if (!$this->query[self::COLUMNS]) {
            return '';
        }

        $select = $this->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
        $res = $select . $this->implode(self::COLUMNS);
        $res .= ' FROM ' . $this->implode(self::FROM);

        if ($this->query[self::JOIN]) {
            $res .= self::SPACE . $this->implode(self::JOIN, self::SPACE);
        }

        if ($this->query[self::WHERE]) {
            $res .= ' WHERE ' . $this->implode(self::WHERE, self::SPACE);
        }

        if ($this->query[self::GROUPBY]) {
            $res .= ' GROUP BY ' . $this->implode(self::GROUPBY);
        }

        if ($this->query[self::HAVING]) {
            $res .= ' HAVING ' . $this->implode(self::HAVING, self::SPACE);
        }

        return $res;
    }

    /**
     * Encapsulates the original query in a pagination SQL pattern.
     *
     * @param string $query Original SQL.
     */
    protected function changeQueryLimit(string $query): string
    {
        $columns = ['a.*'];

        if ($this->offset > 0) {
            $columns[] = 'ROWNUM AS row_number';
        }

        $query = sprintf('SELECT %s FROM (%s) a', implode(self::COMMA, $columns), $query);
        $query .= sprintf(' WHERE ROWNUM <= %d', $this->offset + $this->limit);

        if ($this->offset > 0) {
            $query = sprintf('SELECT * FROM (%s) WHERE row_number >= %d', $query, $this->offset + 1);
        }

        return $query;
    }

    /**
     * @param string $join INNER JOIN, LEFT JOIN or RIGHT JOIN
     */
    protected function forJoin(string $join, $table, string $alias, string $condition): static
    {
        if ($table instanceof Select) {
            $table = '(' . $table->build() . ')';
        }

        $key = md5($alias . $table);

        if (isset($this->joins[$key])) {
            return $this;
        }

        $this->joins[$key] = true;

        return $this->add(self::JOIN, $join . self::SPACE . $table . self::SPACE . $alias . ' ON ' . $condition);
    }
}
