<?php

namespace machbarmacher\GdprDump\ColumnTransformer;


abstract class ColumnTransformer
{

    private $tableName;

    private $columnName;

    public static function create($tableName, $columnName, $expression)
    {
        if (is_array($expression) && $expression['transformer'] == 'faker') {
            return new ColumnTransformFaker($tableName, $columnName,
              $expression);
        } else {
            if (is_string($expression)) {
                return new ColumnTransformSelectStatement($tableName,
                  $columnName, $expression);
            } else {
                throw new ParseExpressionException("Unable to parse given transform expression for table:{$tableName} column:{$columnName}");
            }
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