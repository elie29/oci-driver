<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

abstract class AbstractBuilder implements BuilderInterface
{

    protected $query = [];

    // Used in Insert/Update class
    protected $returning = [];

    /**
     * Creates a default query array structure.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Shortcut to start building the query.
     * <code>
     *    // SELECT u.name FROM users u<br/>
     *    $sql = Select::start()
     *        ->column('name', 'u')
     *        ->from('users', 'u')
     *        ->build();
     * </code>
     *
     * @return static
     */
    public static function start(): self
    {
        return new static;
    }

    /**
     * Quotes value safely.
     *
     * @param string $value Value to be quoted.
     *
     * @return string quoted value and surrounded by single quotes.
     */
    public static function quote(string $value): string
    {
        $value = str_replace("'", "''", $value);
        return '\'' . addcslashes($value, "\000\n\r\\\032") . '\'';
    }

    /**
     * Reset the query array structure.
     *
     * @return self
     */
    protected function reset(): self
    {
        $this->query = [
            self::COLUMNS => [], // select
            self::FROM    => [], // select
            self::TABLE   => '', // insert, update, delete
            self::SELECT  => '', // insert
            self::JOIN    => [], // select
            self::WHERE   => [], // select, update, delete
            self::GROUPBY => [], // select
            self::HAVING  => [], // select
            self::ORDERBY => [], // select
            self::VALUES  => [], // insert
            self::SET     => [], // update
        ];

        $this->returning = [];

        return $this;
    }

    /**
     * @param string $part Available parts are: 'columns', 'from', 'join', 'set', 'where',
     *  'groupBy', 'having', 'orderBy', 'values'.
     *
     * @param string $separator Comma by default
     *
     * @return string
     */
    protected function implode(string $part, string $separator = self::COMMA): string
    {
        return implode($separator, $this->query[$part]);
    }

    /**
     * Used in Insert/Update class.
     *
     * @return string
     */
    protected function addReturning(): string
    {
        if (! $this->returning) {
            return self::EMPTY;
        }

        return ' RETURNING ' . implode(self::COMMA, array_keys($this->returning)) .
            ' INTO ' . implode(self::COMMA, $this->returning);
    }
}
