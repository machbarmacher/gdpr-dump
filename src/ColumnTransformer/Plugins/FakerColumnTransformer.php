<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use Faker\Factory;
use Faker\Provider\Base;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;

class FakerColumnTransformer extends ColumnTransformer
{

    private static $generator;
    private static $uniqueGenerator;

    public static $formatterTansformerMap = [
        'name' => 'name',
        'phoneNumber' => 'phoneNumber',
        'username' => 'username',
        'password' => 'password',
        'email' => 'email',
        'date' => 'date',
        'longText' => 'paragraph',
        'number' => 'randomNumber',
        'randomText' => 'sentence',
        'text' => 'sentence',
        'uri' => 'url',
        'companySuffix' => 'companySuffix',
    ];


    protected function getSupportedFormatters()
    {
        return array_keys(self::$formatterTansformerMap);
    }

    public function __construct()
    {
        if (!isset(self::$generator)) {
            self::$generator = Factory::create();
            self::$uniqueGenerator = Factory::create()->unique();
          foreach(self::$generator->getProviders() as $provider)
            {
                $clazz = new \ReflectionClass($provider);
                $methods = $clazz->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach($methods as $m)
                {
                    if(strpos($m->name, '__') === 0) continue;
                    self::$formatterTansformerMap[$m->name] = $m->name;
                }
            }
        }
    }

    public function getValue($expression)
    {
        $arguments = $expression['arguments'] ?: [];
        if (!empty($expression['unique'])) {
          return self::$uniqueGenerator->format(self::$formatterTansformerMap[$expression['formatter']], $arguments);
        }

        return self::$generator->format(self::$formatterTansformerMap[$expression['formatter']], $arguments);
    }
}
