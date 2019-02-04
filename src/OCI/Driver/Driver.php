<?php

declare(strict_types = 1);

namespace OCI\Driver;

use OCI\Debugger\DebuggerInterface;
use OCI\Driver\Parameter\Parameter;

class Driver implements DriverInterface
{

    /** OPTIONS pour oci_fetch */
    public const FETCH_ALL_OPT = OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS;
    public const FETCH_ARRAY_OPT = OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS;
    public const FETCH_ONE_COL = OCI_NUM+OCI_RETURN_NULLS+OCI_RETURN_LOBS;

    /**
     * @var resource
     */
    protected $connection;

    /**
     * @var DebuggerInterface
     */
    protected $debugger;

    /**
     * @var int Autocommit by default
     */
    protected $commitOption = OCI_COMMIT_ON_SUCCESS;

    /**
     * @param resource $connection
     * @param DebuggerInterface $debugger
     */
    public function __construct($connection, DebuggerInterface $debugger)
    {
        $this->connection = $connection;
        $this->debugger = $debugger;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function beginTransaction(): self
    {
        $this->commitOption = OCI_NO_AUTO_COMMIT;
        return $this;
    }

    public function commitTransaction(): self
    {
        if ($this->connection && OCI_NO_AUTO_COMMIT === $this->commitOption) {
            oci_commit($this->connection);
        }
        $this->commitOption = OCI_COMMIT_ON_SUCCESS;
        return $this;
    }

    public function rollbackTransaction(): self
    {
        if ($this->connection && OCI_NO_AUTO_COMMIT === $this->commitOption) {
            oci_rollback($this->connection);
        }
        $this->commitOption = OCI_COMMIT_ON_SUCCESS;
        return $this;
    }

    public function executeUpdate($sql, Parameter $bind = null): int
    {
        $statement = $this->executeQuery($sql, $bind);

        $count = oci_num_rows($statement);

        oci_free_statement($statement);

        return (int) $count;
    }

    public function fetchColumns($sql, Parameter $bind = null): array
    {
        $statement = $this->executeQuery($sql, $bind);

        $data = [];

        oci_fetch_all($statement, $data, 0, -1, self::FETCH_ONE_COL);

        oci_free_statement($statement);

        return $data ?: [];
    }

    public function fetchColumn($sql, Parameter $bind = null): array
    {
        $data = $this->fetchColumns($sql, $bind);

        return $data[0] ?? [];
    }

    public function fetchAllAssoc($sql, Parameter $bind = null): array
    {
        $statement = $this->executeQuery($sql, $bind);

        $data = [];

        oci_fetch_all($statement, $data, 0, -1, self::FETCH_ALL_OPT);

        oci_free_statement($statement);

        return $data ?: [];
    }

    public function fetchAssoc($sql, Parameter $bind = null): array
    {
        $statement = $this->executeQuery($sql, $bind);

        $data = oci_fetch_array($statement, self::FETCH_ARRAY_OPT);

        oci_free_statement($statement);

        return $data ?: [];
    }

    /**
     * <b>YOU SHOULD MANUALLY CALL oci_free_statement!</b>
     */
    public function executeQuery($sql, Parameter $bind = null)
    {
        $statement = oci_parse($this->connection, $sql);

        if ($statement === false) {
            $this->error($sql, $this->connection);
        }

        $this->ociExecuteAndDebug($statement, $sql, $bind);

        return $statement;
    }

    private function ociExecuteAndDebug($statement, string $sql, Parameter $bind = null): void
    {
        $this->debugger->start();

        $attributes = $bind ? $bind->getAttributes() : [];

        foreach ($attributes as $column => $params) {
            oci_bind_by_name($statement, $column, $params->variable, $params->length, $params->type);
        }

        if (! @oci_execute($statement, $this->commitOption)) {
            $this->error($sql, $statement);
        }

        $this->debugger->end($sql, $attributes);
    }

    private function error(string $sql, $resource = null): void
    {
        $ociError = oci_error($resource);

        $message = 'OCI Driver error';
        if ($ociError) {
            $message = sprintf('SQL error: %s, SQL: %s', $ociError['message'], $sql);
        }

        if (OCI_NO_AUTO_COMMIT === $this->commitOption) {
            $this->rollbackTransaction();
        }

        throw new DriverException($message);
    }
}
