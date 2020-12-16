<?php

namespace machbarmacher\GdprDump\ColumnTransformer;


use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use machbarmacher\GdprDump\ColumnTransformer\Plugins\ClearColumnTransformer;
use machbarmacher\GdprDump\ColumnTransformer\Plugins\FakerColumnTransformer;

abstract class ColumnTransformer
{

    const COLUMN_TRANSFORM_REQUEST = "columntransform.request";

    private $tableName;

    private $columnName;

    protected static $dispatcher;


    public static function setUp($locale)
    {
        if (!isset(self::$dispatcher)) {
            self::$dispatcher = new EventDispatcher();

            self::$dispatcher->addListener(self::COLUMN_TRANSFORM_REQUEST,
              new FakerColumnTransformer($locale));
            self::$dispatcher->addListener(self::COLUMN_TRANSFORM_REQUEST,
              new ClearColumnTransformer());
        }

    }

    public static function replaceValue($tableName, $columnName, $expression, $locale)
    {
        self::setUp($locale);
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
