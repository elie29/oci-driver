<?php

declare(strict_types = 1);

namespace OCI\Driver;

use OCI\Driver\Parameter\Parameter;

interface DriverInterface
{

    /**
     * Returns current connection.
     *
     * @return resource
     */
    public function getConnection();

    /**
     * Swicth to no auto commit transaction.
     *
     * @return self
     */
    public function beginTransaction();

    /**
     * Commit ends current transaction if connection is set and
     * transaction mode is not autocommit.
     *
     * @return self
     */
    public function commitTransaction();

    /**
     * Rollabck ends current transaction if connection is set and
     * transaction mode is not autocommit.
     *
     * @return self
     */
    public function rollbackTransaction();

    /**
     * Other queries than select.
     *
     * @param string $sql Query could be bound.
     * @param Parameter $bind Optional for bound parameters.
     *
     *  @return int
     */
    public function executeUpdate($sql, Parameter $bind = null): int;

    /**
     * Fetch all data by column (indexed array).
     *
     * @param string $sql Query could be bound.
     * @param Parameter $bind Optional for bound parameters.
     *
     *  @return array
     */
    public function fetchColumns($sql, Parameter $bind = null): array;

    /**
     * Fetch one column (indexed array).
     *
     * @param string $sql Query could be bound.
     * @param Parameter $bind Optional for bound parameters.
     *
     *  @return array
     */
    public function fetchColumn($sql, Parameter $bind = null): array;

    /**
     * Fetch all data.
     *
     * @param string $sql Query could be bound.
     * @param Parameter $bind Optional for bound parameters.
     *
     *  @return array
     */
    public function fetchAllAssoc($sql, Parameter $bind = null): array;

    /**
     * Fetch one row.
     *
     * @param string $sql Query could be bound.
     * @param Parameter $bind Optional for bound parameters.
     *
     *  @return array
     */
    public function fetchAssoc($sql, Parameter $bind = null): array;

    /**
     * Executes only the query. Useful when dealing with CLob.
     *
     * @param string $sql Query could be bound.
     * @param Parameter $bind Optional for bound parameters.
     *
     * @return resource
     */
    public function executeQuery(string $sql, Parameter $bind = null);
}
