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
     * Add a list of columns to the insert. Used in conjunction with the select.
     *
     * @param array $list List of columns.
     *
     * @return self
     */
    public function columns(array $list): self
    {
        $this->query[self::COLUMNS][] = implode(self::COMMA, $list);
        return $this;
    }

    /**
     * Add a select builder to the insert.
     *
     * <code>
     *    // INSERT INTO A1 (N_CHAR, N_NUM) SELECT 'test', 1 FROM DUAL<br/>
     *    $sql = Insert::start()
     *      ->into('A1)
     *      ->columns(['N_CHAR', 'N_NUM']) // use columns and not values
     *      ->select(
     *          Select::start()
     *             ->columns(["'test'", 1])
     *             ->from('DUAL')
     *      )
     *      ->build();
     * </code>
     *
     * @param Select $select A select builder.
     *
     * @return self
     */
    public function select(Select $select): self
    {
        $this->query[self::SELECT] = $select->build();
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
        $res = 'INSERT INTO ' . $this->query[self::TABLE] . $this->buildPartial();

        $this->reset();

        return $res;
    }

    protected function buildPartial(): string
    {
        if ($this->query[self::SELECT]) {
            return ' (' . $this->implode(self::COLUMNS) . ') ' . $this->query[self::SELECT];
        }

        return ' (' . implode(self::COMMA, array_keys($this->query[self::VALUES])) . ')'
            . ' VALUES (' . $this->implode(self::VALUES) . ')' . $this->addReturning();
    }
}
