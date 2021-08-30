<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use Faker\Factory;
use Faker\Provider\Base;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;

class FakerColumnTransformer extends ColumnTransformer
{

    private static $generator;

    // These are kept for backward compatibility
    private static $formatterTansformerMap = [
        'longText' => 'paragraph',
        'number' => 'randomNumber',
        'randomText' => 'sentence',
        'text' => 'sentence',
        'uri' => 'url',
    ];


    protected function getSupportedFormatters()
    {
        return array_keys(self::$formatterTansformerMap);
    }

    public function __construct($locale)
    {
        if (!isset(self::$generator)) {
            self::$generator = Factory::create($locale);
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
        return self::$generator->format(self::$formatterTansformerMap[$expression['formatter']]);
    }
}
