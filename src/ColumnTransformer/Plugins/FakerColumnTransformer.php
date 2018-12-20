<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use Faker\Factory;
use Faker\Provider\Base;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;

class FakerColumnTransformer extends ColumnTransformer
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
        'integer' => 'randomNumber',
        'float' => 'randomFloat',
        'creditCard' => 'creditCardNumber',
        'randomText' => 'sentence',
        'text' => 'sentence',
        'uri' => 'url',
    ];


    protected function getSupportedFormatters()
    {
        return array_keys(self::$formatterTansformerMap);
    }

    public function __construct()
    {
        if (!isset(self::$factory)) {
            self::$factory = Factory::create();
        }
    }

    public function getValue($expression)
    {
        return self::$factory->format(self::$formatterTansformerMap[$expression['formatter']]);
    }
}
