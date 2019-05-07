<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

class Insert extends AbstractBuilder
{

    /**
     * Adds one table name to the query structure.
     *
     * @param string $table Table name.
     *
     * @return self
     */
    public function into(string $table): self
    {
        $this->query[self::TABLE] = $table;
        return $this;
    }

    /**
     * Specifies values for an insert query indexed by column names.
     * Values should be bound or quoted correctly.
     *
     * <code>
     *    // INSERT INTO users (USER_ID, NAME, BIRTH_DATE) VALUES (3, 'O''neil', '21/11/79')<br/>
     *    $sql = Insert::start()
     *        ->into('users')
     *        ->values([
     *            'USER_ID'    => 3,
     *            'NAME'       => Insert::quote("O'neil"),
     *            'BIRTH_DATE' => Insert::quote('21/11/79'),
     *        ])
     *        ->build();
     *
     *    // INSERT INTO users (USER_ID) VALUES (:ID)<br/>
     *    $sql = Insert::start()
     *        ->into('users')
     *        ->values([
     *            'USER_ID' => ':ID',
     *        ])
     *        ->build();
     * </code>
     *
     * @param array $values The values to specify for the insert query indexed by column names.
     *
     * @return self
     */
    public function values(array $values): self
    {
        $this->query[self::VALUES] = $values;
        return $this;
    }

    /**
     * Useful when we need to return values after insertion.
     *
     * <code>
     *    // INSERT INTO users (USER_ID, NAME) VALUES (USER_SEQ.nextval, 'Elie') RETURNING USER_ID into :ID<br/>
     *    $sql = Insert::start()
     *        ->into('users')
     *        ->values([
     *            'USER_ID' => 'USER_SEQ.nextval',
     *            'NAME'    => Insert::quote('Elie'),
     *        ])
     *        ->returning([
     *            'USER_ID' => ':ID'
     *        ])
     *        ->build();
     * </code>
     *
     * @param string $colName Column Name.
     * @param string $bind Binded key.
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
        $res = 'INSERT INTO ' . $this->query[self::TABLE]
             . ' (' . implode(self::COMMA, array_keys($this->query[self::VALUES])) . ')'
             . ' VALUES (' . $this->implode(self::VALUES) . ')'
             . $this->addReturning();

        $this->reset();

        return $res;
    }
}
