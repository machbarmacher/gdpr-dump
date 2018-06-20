<?php

namespace machbarmacher\GdprDump\ColumnTransformer;

use Faker\Factory;
use Faker\Provider\Base;

class ColumnTransformFaker extends ColumnTransformer
{

    private static $factory;

    public static $formatterTansformerMap = [
        'name' => 'name',
        'phoneNumber' => 'phoneNumber',
        'username' => 'username',
        'password' => 'password',
        'email' => 'email',
        'date' => 'date',
        'longText' => 'paragraph',
        'number' => 'number',
        'randomText' => 'sentence',
        'text' => 'sentence',
        'uri' => 'url',
    ];


    private $formatter;

    public static function getSupportedFormatters()
    {
        return array_keys(self::$formatterTansformerMap);
    }

    public function __construct($tableName, $columnName, $expression)
    {
        parent::__construct($tableName, $columnName, $expression);
        if (!isset(self::$factory)) {
            self::$factory = Factory::create();
        }

        if (!isset($expression['formatter']) || !array_key_exists($expression['formatter'], self::$formatterTansformerMap)) {
            throw new ParseExpressionException("Invalid Faker provider for table:{$tableName} column:{$columnName}");
        }

        $this->formatter = self::$formatterTansformerMap[$expression['formatter']];
    }

    public function getValue($uniqueId = null)
    {
        return self::$factory->format($this->formatter);
    }
}