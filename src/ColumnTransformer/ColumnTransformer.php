<?php

namespace machbarmacher\GdprDump\ColumnTransformer;


abstract class ColumnTransformer {

    public static function create($tableName, $columnName, $expression) {
        if(is_object($expression)) {
            return new ColumnTransformFaker();
        } else if (is_string($expression)) {
            return new ColumnTransformSelectStatement();
        } else {
            throw new ParseExpressionException("Unable to parse given transform expression for table:{$tableName} column:{$columnName}");
        }
    }

}