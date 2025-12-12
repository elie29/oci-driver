<?php

declare(strict_types=1);

namespace OCI\Query\Builder;

interface BuilderInterface
{
    public const EMPTY   = '';
    public const COMMA   = ', ';
    public const SPACE   = ' ';
    public const COLUMNS = 'columns';
    public const FROM    = 'from';
    public const TABLE   = 'table';
    public const SELECT  = 'select';
    public const JOIN    = 'join';
    public const WHERE   = 'where';
    public const GROUPBY = 'groupBy';
    public const HAVING  = 'having';
    public const ORDERBY = 'orderBy';
    public const VALUES  = 'values';
    public const SET     = 'set';
    public const ASC     = 'ASC';
    public const DESC    = 'DESC';

    /**
     * Builds the complete SQL from the query parts.
     * Reset the query structure once called.
     */
    public function build(): string;
}
