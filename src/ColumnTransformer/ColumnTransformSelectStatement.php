<?php
/**
 * Created by PhpStorm.
 * User: bomoko
 * Date: 2018/05/25
 * Time: 9:56 AM
 */

namespace machbarmacher\GdprDump\ColumnTransformer;


class ColumnTransformSelectStatement extends ColumnTransformer
{
    private $value;

    public function __construct($tableName, $columnName, $expression)
    {
        parent::__construct($tableName, $columnName, $expression);
        $this->value = $expression;
    }

    public function getValue()
    {
        return $this->value;
    }
}