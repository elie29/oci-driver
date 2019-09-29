<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

class Update extends AbstractCommonBuilder
{

    /**
     * Adds one table name to the query structure.
     *
     * @param string $table Table name.
     * @param string $alias Alias name.
     *
     * @return self
     */
    public function table(string $table, string $alias = self::EMPTY): self
    {
        $this->query[self::TABLE] = $table . $this->getTableAlias($alias);
        return $this;
    }

    /**
     * Sets a value to a key.
     * <code>
     *    // UPDATE users SET id = :id<br/>
     *    $sql = Update::start()
     *        ->table('users')
     *        ->set('id', ':id')
     *        ->build();
     *
     *    // Update users set name = O''neil<br/>
     *    $sql = Update::start()
     *        ->table('users')
     *        ->set('name', Update::quotes("O'neil"))
     *        ->build();
     * </code>
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function set(string $key, $value): self
    {
        return $this->add(self::SET, $key .' = ' . $value);
    }

    /**
     * Useful when we need to return values after update.
     *
     *    // UPDATE users SET id = :id RETURNING DESC INTO :DESC<br/>
     *    $sql = Update::start()
     *        ->table('users')
     *        ->set('id', ':id')
     *        ->returning('DESC', ':DESC')
     *        ->build();
     *
     * @param string $colName Column Name.
     * @param string $bind Bound key.
     *
     * @return self
     */
    public function returning(string $colName, string $bind): self
    {
        $this->returning[$colName] = $bind;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \OCI\Query\Builder\BuilderInterface::build()
     */
    public function build(): string
    {
        $res  = 'UPDATE ' . $this->query[self::TABLE];
        $res .= ' SET ' . $this->implode(self::SET);

        if ($this->query[self::WHERE]) {
            $res .= ' WHERE ' . $this->implode(self::WHERE, self::SPACE);
        }

        $res .= $this->addReturning();

        $this->reset();

        return $res;
    }
}
