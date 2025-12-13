<?php

declare(strict_types=1);

namespace Elie\OCI\Helper;

use Elie\OCI\Driver\DriverException;
use Elie\OCI\Driver\Parameter\Parameter;

class ClauseInParamsHelper
{
    public const MAX_AUTHORISED_IN_VALUES = 999;

    /**
     * Prepares values to be bound with in clause and Parameters object.
     * <b>NB.: If values exceed 999, DriverException is thrown.</b>
     *
     * <code>
     *  $param = new Parameter();
     *
     *  $driver = Factory::get();
     *
     *  // `:ID1`, `:ID2`, `:ID3` and values bound in `$params`<br/>
     *  $keys = ClauseInParamsHelper::getBoundParams([1, 2, 3],`:ID`, $param);
     *
     *  $sql = "SELECT * FROM users WHERE id IN ($keys)";
     *
     *  $res = $driver->fetchAllAssoc($sql, $param);
     * </code>
     *
     * @param array $values List of values to be bound.
     * @param string $prefix Column prefix with: at the beginning.
     * @param Parameter $param Parameter instance for value-bound purpose.
     * @throws DriverException
     */
    public static function getBoundParams(array $values, string $prefix, Parameter $param): string
    {
        static::assertMax($values);

        $i = 1;
        $keys = [];

        foreach ($values as $value) {
            $k = $prefix . $i++;
            $param->add($k, $value);
            $keys[] = $k;
        }

        return implode(', ', $keys);
    }

    /**
     * @throws DriverException
     */
    protected static function assertMax(array $values): void
    {
        if (count($values) > self::MAX_AUTHORISED_IN_VALUES) {
            throw new DriverException(self::MAX_AUTHORISED_IN_VALUES . ' only allowed with oci');
        }
    }
}
