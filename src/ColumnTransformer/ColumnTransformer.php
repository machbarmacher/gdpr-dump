<?php

namespace machbarmacher\GdprDump\ColumnTransformer;


use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use machbarmacher\GdprDump\ColumnTransformer\Plugins\ColumnTransformerClear;
use machbarmacher\GdprDump\ColumnTransformer\Plugins\ColumnTransformerFaker;

abstract class ColumnTransformer
{

    const COLUMN_TRANSFORM_REQUEST = "columntransform.request";

    private $tableName;

    private $columnName;

    protected static $dispatcher;


    public static function setUp()
    {
        if (!isset(self::$dispatcher)) {
            self::$dispatcher = new EventDispatcher();

            self::$dispatcher->addListener(self::COLUMN_TRANSFORM_REQUEST,
              new ColumnTransformerFaker());
            self::$dispatcher->addListener(self::COLUMN_TRANSFORM_REQUEST,
              new ColumnTransformerClear());
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
        if (in_array(($event->getExpression())['formatter'],
          $this->getSupportedFormatters())) {
            $event->setReplacementValue($this->getValue($event->getExpression()));
        }
    }

    abstract public function getValue($expression);

    abstract protected function getSupportedFormatters();
}