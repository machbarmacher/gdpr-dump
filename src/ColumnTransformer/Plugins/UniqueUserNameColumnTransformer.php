<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use Faker\Factory;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformEvent;

class UniqueUsernameColumnTransformer extends ColumnTransformer
{

    private static $generator;
    private static $nameCache = [];

    protected function getSupportedFormatters()
    {
        return ['uniqueUsername'];
    }

    public function __construct()
    {
        if (!isset(self::$generator)) {
            $locale = substr($_SERVER['LANG'], 0, 5);
            self::$generator = Factory::create($locale);
        }
    }

    public function getValue(ColumnTransformEvent $event)
    {
        $temp_name = self::$generator->format('name');
        while (isset(self::$nameCache[$temp_name])) {
            $temp_name = $this->uniquify($temp_name);
        }
        self::$nameCache[$temp_name] = TRUE;
        return $temp_name;
    }

    private function uniquify($name) {
        return $name .= rand(0,1000);
    }
}
