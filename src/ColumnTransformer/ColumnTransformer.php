<?php

namespace machbarmacher\GdprDump\ColumnTransformer;


abstract class ColumnTransformer
{

    private $tableName;

    private $columnName;

    public static function create($tableName, $columnName, $expression)
    {
        if (is_array($expression) && in_array($expression['formatter'],
                ColumnTransformFaker::getSupportedFormatters())
        ) {
            return new ColumnTransformFaker($tableName, $columnName,
                $expression);
        } elseif (is_array($expression) && in_array($expression['formatter'],
                ColumnTransformerClear::getSupportedFormatters())
        ) {
            return new ColumnTransformerClear($tableName, $columnName,
                $expression);
        } else {
            throw new ParseExpressionException("Unable to parse given transform expression for table:{$tableName} column:{$columnName}");
        }
    }

    abstract public function getValue();

    public function __construct($tableName, $columnName, $expression)
    {
        $this->tableName = $tableName;
        $this->columnName = $columnName;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

}