<?php
namespace machbarmacher\GdprDump\ColumnTransformer;
use Faker\Factory;

class ColumnTransformFaker extends ColumnTransformer
{
    private static $factory;

    public function __construct($tableName, $columnName, $expression)
    {
        parent::__construct($tableName, $columnName, $expression);
        if(!is_object(self::$factory)) {
            self::$factory = Factory::create();
        }
    }

    public function getValue($uniqueId = null) {
        return self::$factory->text();
    }
}