<?php

declare(strict_types=1);

namespace Elie\OCI\Query\Builder;

class Delete extends AbstractCommonBuilder
{
    /**
     * Adds one table name to the query structure.
     *
     * @param string $table Table name.
     * @param string $alias Alias name.
     */
    public function from(string $table, string $alias = self::EMPTY): static
    {
        $this->query[self::TABLE] = $table . $this->getTableAlias($alias);
        return $this;
    }
    
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
