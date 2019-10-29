<?php

namespace machbarmacher\GdprDump\ColumnTransformer\Plugins;

use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformEvent;

class VerbatimColumnTransformer extends ColumnTransformer
{

    protected function getSupportedFormatters()
    {
        return ['verbatim'];
    }

    public function getValue(ColumnTransformEvent $event)
    {
        return $event->getExpression()['value'];
    }
}
