<?php

namespace machbarmacher\GdprDump\ColumnTransformer;


use machbarmacher\GdprDump\ColumnTransformer\Plugins\ClearColumnTransformer;
use machbarmacher\GdprDump\ColumnTransformer\Plugins\FakerColumnTransformer;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class ColumnTransformer
{

    const COLUMN_TRANSFORM_REQUEST = "columntransform.request";

    private $tableName;

    private $columnName;

    protected static $dispatcher;

    public static function addTransformer(ColumnTransformer $columnTransformer)
    {
        self::setUp();
        self::$dispatcher->addListener(self::COLUMN_TRANSFORM_REQUEST, $columnTransformer);
    }

    public static function setUp()
    {
        if (!isset(self::$dispatcher)) {
            self::$dispatcher = new EventDispatcher();
            self::addTransformer(new FakerColumnTransformer());
            self::addTransformer(new ClearColumnTransformer());
        }
    }

    public static function replaceValue($tableName, $columnName, $expression)
    {
        self::setUp();
        $event = new ColumnTransformEvent($tableName, $columnName, $expression);
        self::$dispatcher->dispatch(self::COLUMN_TRANSFORM_REQUEST, $event);
        if ($event->isReplacementSet()) {
            return $event->getReplacementValue();
        }

        return false;
    }

    public function __invoke(ColumnTransformEvent $event)
    {
        if (in_array(
            ($event->getExpression())['formatter'],
            $this->getSupportedFormatters()
        )) {
            $event->setReplacementValue($this->getValue($event));
        }
    }

    abstract public function getValue(ColumnTransformEvent $event);

    abstract protected function getSupportedFormatters();
}
