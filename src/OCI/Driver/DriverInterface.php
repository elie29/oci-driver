<?php

declare(strict_types = 1);

namespace OCI\Driver;

use OCI\Driver\Parameter\Parameter;

interface DriverInterface
{

    /**
     * Swicth to no auto commit transaction.
     *
     * @return self
     */
    public function beginTransaction();

    /**
     * Commit ends current transaction.
     *
     * @return self
     */
    public function commitTransaction();

    /**
     * Returns current connexion.
     *
     * @return resource
     */
    public function getConnexion();

    /**
     * Rollabck ends current transaction.
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
    public function executeUpdate($sql, Parameter $bind = null);

    /**
     * Fetch all data.
     *
     * @param string $sql Query could be bound.
     * @param Parameter $binds Optional for bound parameters.
     *
     *  @return array
     */
    public function fetchAllAssoc($sql, Parameter $bind = null);

    /**
     * Fetch one row.
     *
     * @param string $sql Query could be bound.
     * @param Parameter $binds Optional for bound parameters.
     *
     *  @return array
     */
    public function fetchAssoc($sql, Parameter $bind = null);

    /**
     * Executes only the query. Useful when dealing with CLob.
     *
     * @param string $sql Query could be bound.
     * @param Parameter $binds Optional for bound parameters.
     *
     * @return resource
     */
    public function executeQuery($sql, Parameter $bind = null);
}

