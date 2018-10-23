<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

abstract class AbstractBuilder implements BuilderInterface
{

    protected $query = [];

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
     *    // SELECT u.name FROM users u
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
            'columns' => [], // select
            'from'    => [], // select
            'table'   => '', // insert, update, delete
            'join'    => [], // select
            'where'   => [], // select, update, delete
            'groupBy' => [], // select
            'having'  => [], // select
            'orderBy' => [], // select
            'values'  => [], // insert
            'set'     => [], // update
        ];
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
}
