<?php

declare(strict_types = 1);

namespace OCI\Query\Builder;

interface BuilderInterface
{

     const EMPTY   = '';
     const COMMA   = ', ';
     const SPACE   = ' ';
     const COLUMNS = 'columns';
     const FROM    = 'from';
     const TABLE   = 'table';
     const JOIN    = 'join';
     const WHERE   = 'where';
     const GROUPBY = 'groupBy';
     const HAVING  = 'having';
     const ORDERBY = 'orderBy';
     const VALUES  = 'values';
     const SET     = 'set';

    /**
     * Builds the complete SQL from the query parts.
     * Reset the query structure once called.
     *
     * @return string
     */
    public function build(): string;
}
