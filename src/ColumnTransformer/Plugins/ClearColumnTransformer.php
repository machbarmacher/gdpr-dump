<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;

class ClearColumnTransformer extends ColumnTransformer
{

    protected function getSupportedFormatters()
    {
        return ['clear'];
    }

    public function getValue($expression)
    {
        return "";
    }
}