<?php

declare(strict_types=1);

namespace Elie\OCI\Driver;

use Elie\OCI\Debugger\DebuggerInterface;
use Elie\OCI\Driver\Parameter\Parameter;

class Driver implements DriverInterface
{
    /** OPTIONS pour oci_fetch */
    public const FETCH_ALL_OPT = OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS;
    public const FETCH_ARRAY_OPT = OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS;
    public const FETCH_ONE_COL = OCI_NUM + OCI_RETURN_NULLS + OCI_RETURN_LOBS;

    /** @var resource */
    protected mixed $connection;

    protected DebuggerInterface $debugger;

    /** @var int Autocommit by default */
    protected int $commitOption = OCI_COMMIT_ON_SUCCESS;

    /**
     * @param resource $connection
     */
    public function __construct(mixed $connection, DebuggerInterface $debugger)
    {
        $this->connection = $connection;
        $this->debugger = $debugger;
    }

    public function getConnection(): mixed
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

    /**
     * @throws DriverException
     */
    public function executeUpdate(string $sql, ?Parameter $bind = null): int
    {
        $statement = $this->executeQuery($sql, $bind);

        $count = oci_num_rows($statement);

        oci_free_statement($statement);

        return (int)$count;
    }

    /**
     * @throws DriverException
     */
    public function fetchColumns(string $sql, ?Parameter $bind = null): array
    {
        $statement = $this->executeQuery($sql, $bind);

        $data = [];

        oci_fetch_all($statement, $data, 0, -1, self::FETCH_ONE_COL);

        oci_free_statement($statement);

        return $data ?: [];
    }

    /**
     * @throws DriverException
     */
    public function fetchColumn(string $sql, ?Parameter $bind = null): array
    {
        $data = $this->fetchColumns($sql, $bind);

        return $data[0] ?? [];
    }

    /**
     * @throws DriverException
     */
    public function fetchAllAssoc(string $sql, ?Parameter $bind = null): array
    {
        $statement = $this->executeQuery($sql, $bind);

        $data = [];

        oci_fetch_all($statement, $data, 0, -1, self::FETCH_ALL_OPT);

        oci_free_statement($statement);

        return $data ?: [];
    }

    /**
     * @throws DriverException
     */
    public function fetchAssoc(string $sql, ?Parameter $bind = null): array
    {
        $statement = $this->executeQuery($sql, $bind);

        $data = oci_fetch_array($statement, self::FETCH_ARRAY_OPT);

        oci_free_statement($statement);

        return $data ?: [];
    }

    /**
     * YOU SHOULD MANUALLY CALL oci_free_statement!
     * @throws DriverException
     */
    public function executeQuery(string $sql, ?Parameter $bind = null): mixed
    {
        $statement = oci_parse($this->connection, $sql);

        if ($statement === false) {
            $this->error($sql, $bind, $this->connection); // @codeCoverageIgnore
        }

        $this->ociExecuteAndDebug($statement, $sql, $bind);

        return $statement;
    }

    /**
     * @param resource $statement
     * @param string $sql
     * @param Parameter|null $bind
     * @throws DriverException
     */
    private function ociExecuteAndDebug($statement, string $sql, Parameter $bind = null): void
    {
        $this->debugger->start();

        $attributes = $bind ? $bind->getAttributes() : [];

        foreach ($attributes as $column => $params) {
            oci_bind_by_name($statement, $column, $params->variable, $params->maxlength, $params->type);
        }

        if (!@oci_execute($statement, $this->commitOption)) {
            $this->error($sql, $bind, $statement);
        }

        $this->debugger->end($sql, $attributes);
    }

    /**
     * @param string $sql
     * @param Parameter|null $bind
     * @param resource|null $resource
     * @throws DriverException
     */
    private function error(string $sql, Parameter $bind = null, $resource = null): void
    {
        $ociError = oci_error($resource) ?: ['message' => 'OCI Driver unknown error'];
        $attributes = $bind ? $bind->getAttributes() : [];

        $message = sprintf(
            'SQL error: %s, SQL: %s, BIND: %s',
            $ociError['message'],
            $sql,
            json_encode($attributes)
        );

        $this->rollbackTransaction();

        throw new DriverException($message);
    }
}
