<?php

declare(strict_types = 1);

namespace OCI\Driver\Parameter;

/**
 * Binds query parameters correctly.
 */
class Parameter
{

    const DEFAULT_MAX_LEN = -1;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * For number/char/varchar values purpose.
     *
     * oci_bind_by_name is called on each binding: maxlength is no longer useful
     * unless for OUTPUT binding.
     *
     * @param string $column  Column to be bound.
     * @param mixed $variable Column value.
     * @param int $maxlength Mostly with default value.
     *
     * @return self
     */
    public function add(string $column, $variable, int $maxlength = self::DEFAULT_MAX_LEN): self
    {
        // Use SQLT_CHR for most types...
        return $this->genericAdd($column, $variable, $maxlength, SQLT_CHR);
    }

    /**
     * For LONG_RAW values purpose.
     *
     * @param string $column  Column to be bound.
     * @param mixed $variable Column value.
     *
     * @return self
     */
    public function addForLongRaw(string $column, $variable): self
    {
        return $this->genericAdd($column, $variable, self::DEFAULT_MAX_LEN, SQLT_LBI);
    }

    /**
     * We should call $lob->close (when writing data only)
     *  and $lob->free() manually after execution.
     *
     * @param resource $connexion Current OCI8 resource.
     * @param string $column CLob Column name.
     * @param mixed $data Null when reading from database.
     *
     * @return self
     */
    public function addForCLob($connexion, string $column, $data = null): self
    {
        /*@var \OCI_Lob $lob */
        $lob = oci_new_descriptor($connexion, OCI_D_LOB);

        if ($data) {
            $lob->writeTemporary($data, OCI_TEMP_BLOB);
        }

        $this->genericAdd($column, $lob, self::DEFAULT_MAX_LEN, OCI_B_CLOB);

        return $this;
    }

    /**
     * Get the value of a bound column in OUT mode
     *
     * @param string $column The bound column.
     *
     * @return mixed
     */
    public function getVariable(string $column)
    {
        return $this->attributes[$column]->variable;
    }

    /**
     * @return \stdClass[] Arrays of column, variable, length, type.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Keep it private to mastering supported types.
     *
     * @param string $column Column name prefexied by colon :
     * @param mixed $variable Data to be bound to.
     * @param int $maxlength Data maxlength.
     * @param int $type Supported types SQLT_CHR.
     *
     * @return Parameter
     */
    private function genericAdd(string $column, $variable, int $maxlength, int $type): self
    {
        $this->attributes[$column] = (object) [
            'variable' => $variable,
            'length'   => $maxlength,
            'type'     => $type,
        ];

        return $this;
    }
}
