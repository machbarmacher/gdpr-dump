<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformEvent;

class ClearColumnTransformer extends ColumnTransformer
{

    protected function getSupportedFormatters()
    {
        return ['clear'];
    }

    public function getValue(ColumnTransformEvent $event)
    {
        return "";
    }
}
