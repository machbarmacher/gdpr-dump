<?php

namespace machbarmacher\GdprDump\ColumnTransformer;


class ColumnTransformerClear extends ColumnTransformer
{

    public static function getSupportedFormatters()
    {
        return ['clear'];
    }

    public function getValue()
    {
        return "";
    }
}