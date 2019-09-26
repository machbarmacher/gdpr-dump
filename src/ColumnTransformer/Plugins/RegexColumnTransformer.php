<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformEvent;

class RegexColumnTransformer extends ColumnTransformer
{

    protected function getSupportedFormatters()
    {
        return ['regex'];
    }

    public function getValue(ColumnTransformEvent $event)
    {
        $existing_column_value = $event->getValue();
        $regexes = $event->getExpression()['regexes'];
        foreach ($regexes as $regex_operation) {
            error_log(print_r($regex_operation, TRUE));
            $existing_column_value = preg_replace($regex_operation['regex'], $regex_operation['replacement'], $existing_column_value);
        }
        return $existing_column_value;
    }
}
