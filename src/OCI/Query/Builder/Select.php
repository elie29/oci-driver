<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

class Select extends AbstractCommonBuilder
{

    protected $limit  = 0;
    protected $offset = 0;
    protected $sql    = '';

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
     * @param string $alias Optional column alias.
     *
     * @return self
     */
    public function column(string $name, string $alias = self::EMPTY): self
    {
        $alias .= ($alias ? '.' : self::EMPTY);
        return $this->add(self::COLUMNS, $alias . $name);
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
     * @param string $alias Optional columns alias.
     *
     * @return self
     */
    public function columns(array $list, string $alias = self::EMPTY): self
    {
        $alias .= ($alias ? '.' : self::EMPTY);

        return $this->add(self::COLUMNS, $alias . implode(self::COMMA . $alias, $list));
    }

    /**
     * Adds table name to the query structure.
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
     *
     * @return self
     */
    public function from($table, string $alias = self::EMPTY): self
    {
        if ($table instanceof Select) {
            $table = '(' . $table->build() . ')';
        }
        return $this->add(self::FROM, $table . ($alias ? ' ' . $alias : self::EMPTY));
    }

    /**
     * Adds an inner join in the query structure.
     * <code>
     *    // SELECT p.* FROM params p INNER JOIN users u ON u.user_id = p.user_id<br/>
     *    $sql = Select::start()
     *        ->column('*', 'p')
     *        ->from('params', 'p')
     *        ->join('users', 'u', 'u.user_id = p.user_id')
     *        ->build();
     * </code>
     *
     * @param string $table $table Table name.
     * @param string $alias Alias name.
     * @param string $condition Join condition.
     *
     * @return self
     */
    public function join(string $table, string $alias, string $condition): self
    {
        return $this->add(self::JOIN, 'INNER JOIN ' . $table . self::SPACE . $alias . ' ON ' . $condition);
    }

    /**
     * Adds a left join in the query structure.
     * <code>
     *    // SELECT p.* FROM params p LEFT JOIN users u ON u.user_id = p.user_id<br/>
     *    $sql = Select::start()
     *        ->column('*', 'p')
     *        ->from('params', 'p')
     *        ->leftJoin('users', 'u', 'u.user_id = p.user_id')
     *        ->build();
     * </code>
     *
     * @param string $table $table Table name.
     * @param string $alias Alias name.
     * @param string $condition Join condition.
     *
     * @return self
     */
    public function leftJoin(string $table, string $alias, string $condition): self
    {
        return $this->add(self::JOIN, 'LEFT JOIN ' . $table . self::SPACE . $alias . ' ON ' . $condition);
    }

    /**
     * Adds a right join in the query structure.
     *
     * <code>
     *    // SELECT u.* FROM params p RIGHT JOIN users u ON u.user_id = p.user_id<br/>
     *    $sql = Select::start()
     *        ->column('*', 'u')
     *        ->from('params', 'p')
     *        ->leftJoin('users', 'u', 'u.user_id = p.user_id')
     *        ->build();
     * </code>
     *
     * @param string $table $table Table name.
     * @param string $alias Alias name.
     * @param string $condition Join condition.
     *
     * @return self
     */
    public function rightJoin(string $table, string $alias, string $condition): self
    {
        return $this->add(self::JOIN, 'RIGHT JOIN ' . $table . self::SPACE . $alias . ' ON ' . $condition);
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
     *
     * @return self
     */
    public function groupBy(string $groupBy): self
    {
        return $this->add(self::GROUPBY, $groupBy);
    }

    /**
     * Adds predicate having to the query.
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
     *
     * @return self
     */
    public function having(string $having): self
    {
        return $this->add(self::HAVING, $having);
    }

    /**
     * Adds predicate and to the having part of the query.
     * Should be added after a having.
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
     *
     * @return self
     */
    public function andHaving(string $having): self
    {
        return $this->add(self::HAVING, 'AND ' . $having);
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
     *
     * @return self
     */
    public function orHaving(string $having): self
    {
        return $this->add(self::HAVING, 'OR ' . $having);
    }

    /**
     * Append a union to the query structure.
     *
     * <code>
     *    // SELECT p.id FROM params p UNION SELECT p.id FROM params_his p ORDER BY id ASC<br/>
     *    $sql = Select::start()
     *        ->column('p.id')
     *        ->from('params', 'p')
     *        ->union()
     *        ->column('p.id')
     *        ->from('params_his', 'p')
     *        ->orderBy('id')
     *        ->build();
     * </code>
     *
     * @return self
     */
    public function union(): self
    {
        $this->sql .= $this->buildPartial();
        $this->sql .= ' UNION ';

        return $this->reset();
    }

    /**
     * Specifies an ordering for the query results.
     *
     * @param string $sort  The ordering expression.
     * @param string $order The ordering direction. ASC by default.
     *
     * @return self.
     */
    public function orderBy(string $sort, string $order = 'ASC'): self
    {
        return $this->add(self::ORDERBY, $sort . ' ' . $order);
    }

    /**
     * Sets a limit and an offset to the current query.
     *
     * @param int $limit Rows limit > 0.
     * @param int $offset Optional offset >=0.
     *
     * @return self
     */
    public function setLimit(int $limit, int $offset = 0): self
    {
        $this->limit = $limit > 0 ? $limit : 0;
        $this->offset = $offset > 0 ? $offset : 0;

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \OCI\Query\Builder\BuilderInterface::build()
     */
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
        $this->sql = '';

        return $res;
    }

    /**
     * Constructs the SQL Without order by.
     *
     * @return string
     */
    protected function buildPartial(): string
    {
        $res  = 'SELECT ' . $this->implode(self::COLUMNS);
        $res .= ' FROM ' .  $this->implode(self::FROM);

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
     *
     * @return string
     */
    protected function changeQueryLimit(string $query): string
    {
        $columns = ['a.*'];

        if ($this->offset > 0) {
            $columns[] = 'ROWNUM AS row_number';
        }

        $query  = sprintf('SELECT %s FROM (%s) a', implode(self::COMMA, $columns), $query);
        $query .= sprintf(' WHERE ROWNUM <= %d', $this->offset + $this->limit);

        if ($this->offset > 0) {
            $query = sprintf('SELECT * FROM (%s) WHERE row_number >= %d', $query, $this->offset + 1);
        }

        return $query;
    }
}
