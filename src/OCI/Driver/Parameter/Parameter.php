<?php

declare(strict_types=1);

namespace Elie\OCI\Driver\Parameter;

use Elie\OCI\Helper\FloatUtils;
use InvalidArgumentException;
use stdClass;

/**
 * Binds query parameters correctly.
 */
class Parameter
{
    public const DEFAULT_MAX_LEN = -1;

    private array $attributes = [];

    /**
     * For number/char/varchar values purpose.
     *
     * `oci_bind_by_name` is called on each binding: maxlength is no longer useful
     * unless for OUTPUT binding.
     *
     * @param string $column Column to be bound (must start with ':').
     * @param mixed $variable Column value.
     * @param int $maxlength Mostly with a default value.
     * @throws InvalidArgumentException If the column name doesn't start with ':'.
     */
    public function add(string $column, mixed $variable, int $maxlength = self::DEFAULT_MAX_LEN): static
    {
        if (!str_starts_with($column, ':')) {
            throw new InvalidArgumentException("Column name must start with ':'. Got: $column");
        }

        if (is_float($variable)) {
            // float is inserted with SQL_CHR and should ALWAYS contain . as decimal separator
            $variable = FloatUtils::convert($variable);
        }
        // Use SQLT_CHR for most types...
        return $this->genericAdd($column, $variable, $maxlength, SQLT_CHR);
    }

    /**
     * For LONG_RAW values purpose.
     *
     * @param string $column Column to be bound.
     * @param mixed $variable Column value.
     */
    public function addForLongRaw(string $column, mixed $variable): self
    {
        return $this->genericAdd($column, $variable, self::DEFAULT_MAX_LEN, SQLT_LBI);
    }

    /**
     * We should call $lob->close (when writing data only)
     *  and $lob->free() manually after execution.
     *
     * @param resource $connexion Current OCI8 resource.
     * @param string $column CLob Column name.
     * @param mixed|null $data Null when reading from a database.
     */
    public function addForCLob($connexion, string $column, mixed $data = null): static
    {
        $lob = oci_new_descriptor($connexion, OCI_D_LOB);

        if ($data) {
            $lob->writeTemporary($data, OCI_TEMP_BLOB);
        }

        $this->genericAdd($column, $lob, self::DEFAULT_MAX_LEN, OCI_B_CLOB);

        return $this;
    }

    /**
     * Get the value of a bound column in `OUT` mode
     *
     * @param string $column The bound column.
     * @return mixed
     */
    public function getVariable(string $column): mixed
    {
        return $this->attributes[$column]->variable;
    }

    /**
     * @return stdClass[] Arrays of column, variable, length, type.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Keep it private to mastering supported types.
     *
     * @param string $column Column name prefixed by colon :
     * @param mixed $variable Data to be bound to.
     * @param int $maxlength Data maxlength.
     * @param int $type Supported types SQLT_CHR.
     * @return Parameter
     */
    private function genericAdd(string $column, mixed $variable, int $maxlength, int $type): static
    {
        $this->attributes[$column] = (object)[
            'variable' => $variable,
            'maxlength' => $maxlength,
            'type' => $type,
        ];

        return $this;
    }
}
