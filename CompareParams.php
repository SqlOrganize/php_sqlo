<?php

namespace SqlOrganize\Sql;

class CompareParams
{
    public array $data; // Data to compare
    public ?array $ignoreFields;
    public bool $ignoreNull;
    public bool $ignoreNonExistent;
    public ?array $fieldsToCompare;
}
