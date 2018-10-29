<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

/**
 * Select, Delete, Update could use where, andWhere, orWhere.
 */
abstract class AbstractCommonBuilder extends AbstractBuilder
{

    /**
     * Adds predicate to the query.
     *
     * <code>
     *     // DELETE FROM params p WHERE (p.name = :name AND p.id = :id) OR p.active = :active<br/>
     *     $sql = Delete::start()
     *         ->from('params', 'p')
     *         ->where('(p.name = :name AND p.id = :id) OR p.active = :active')
     *         ->build();
     * </code>
     *
     * @param string $condition where condition.
     *
     * @return self
     */
    public function where(string $condition): self
    {
        return $this->add(self::WHERE, $condition);
    }

    /**
     * Adds predicate "and" to the query.
     * Should be added after a where.
     *
     * <code>
     *     // UPDATE params p SET p.id = :id WHERE p.name = :name AND (p.id = :id OR p.active = :active)<br/>
     *     $sql = Update::start()
     *         ->table('params', 'p')
     *         ->set('p.id', ':id')
     *         ->where('p.name = :name')
     *         ->andWhere('(p.id = :id OR p.active = :active)')
     *         ->build();
     * </code>
     *
     * @param string $condition Simple where condition.
     *  <b>Parentheses are required when mixing or/and conditions.</b>
     *
     * @return self
     */
    public function andWhere(string $condition): self
    {
        return $this->add(self::WHERE, 'AND ' . $condition);
    }

    /**
     * Adds predicate "or" to the query.
     * Should be added after a where.
     *
     * <code>
     *     // SELECT p.* FROM params p WHERE p.name = :name OR (p.id = :id AND p.active = :active)<br/>
     *     $sql = Select::start()
     *         ->column('p.*)
     *         ->from('params', 'p')
     *         ->where('p.name = :name')
     *         ->orWhere('(p.id = :id AND p.active = :active)')
     *         ->build();
     * </code>
     *
     * @param string $condition Simple where condition.
     *  <b>Parentheses are required when mixing or/and conditions.</b>
     *
     * @return self
     */
    public function orWhere(string $condition): self
    {
        return $this->add(self::WHERE, 'OR ' . $condition);
    }

    /**
     * Adds sql part to the query structure.
     *
     * @param string $part Available parts are: 'columns', 'from', 'join', 'set', 'where',
     *  'groupBy', 'having' and 'orderBy'.
     *
     * @param string $sqlPart
     *
     * @return self
     */
    protected function add(string $part, string $sqlPart): self
    {
        $this->query[$part][] = $sqlPart;
        return $this;
    }

    /**
     * @param string $alias Empty string or an alias.
     */
    protected function getTableAlias(string $alias): string
    {
        return $alias ? self::SPACE . $alias : self::EMPTY;
    }
}
