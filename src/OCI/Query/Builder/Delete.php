<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

class Delete extends AbstractCommonBuilder
{

    /**
     * Adds one table name to the query structure.
     *
     * @param string $table Table name.
     * @param string $alias Alias name.
     *
     * @return self
     */
    public function from(string $table, string $alias = self::EMPTY): self
    {
        $this->query[self::TABLE] = $table . ($alias ? ' ' . $alias : self::EMPTY);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \OCI\Query\Builder\BuilderInterface::build()
     */
    public function build(): string
    {
        $res = 'DELETE FROM ' . $this->query[self::TABLE];

        if ($this->query[self::WHERE]) {
            $res .= ' WHERE ' . $this->implode(self::WHERE, self::SPACE);
        }

        $this->reset();

        return $res;
    }
}
