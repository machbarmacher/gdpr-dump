<?php

namespace machbarmacher\GdprDump;

use Ifsnop\Mysqldump\Mysqldump;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformerFaker;

class MysqldumpGdpr extends Mysqldump
{

    /** @var [string][string]string */
    protected $gdprExpressions;

    /** @var [string][string]string */
    protected $gdprReplacements;

    /** @var bool */
    protected $debugSql;

    /** @var string */
    protected $locale = 'en_EN';

    public function __construct(
        $dsn = '',
        $user = '',
        $pass = '',
        array $dumpSettings = [],
        array $pdoSettings = []
    ) {
        if (array_key_exists('gdpr-expressions', $dumpSettings)) {
            $this->gdprExpressions = $dumpSettings['gdpr-expressions'];
            unset($dumpSettings['gdpr-expressions']);
        }

        if (array_key_exists('gdpr-replacements', $dumpSettings)) {
            $this->gdprReplacements = $dumpSettings['gdpr-replacements'];
            unset($dumpSettings['gdpr-replacements']);
        }

        if (array_key_exists('debug-sql', $dumpSettings)) {
            $this->debugSql = $dumpSettings['debug-sql'];
            unset($dumpSettings['debug-sql']);
        }

        if (array_key_exists('locale', $dumpSettings)) {
          $this->locale = $dumpSettings['locale'];
          unset($dumpSettings['locale']);
        }

        $this->setTransformTableRowHook(function ($tableName, array $row) {
          foreach ($row AS $colName => $colValue) {
            $excludeRow = self::excludeRowCheck($row, $tableName, $colName);
            if (!$excludeRow) {
              $replacement = ColumnTransformer::replaceValue($tableName, $colName, $this->gdprReplacements[$tableName][$colName], $this->locale);
              if ($replacement !== FALSE) {
                $row[$colName] = $replacement;
              }
            }
          }
          return $row;
        });

        parent::__construct($dsn, $user, $pass, $dumpSettings, $pdoSettings);
    }

    private function excludeRowCheck($row, $tableName, $colName) {
      $table_exludes = $this->gdprReplacements[$tableName]['_exclude'];

      if (!empty($this->gdprReplacements[$tableName][$colName])) {
        foreach ($row as $row_column_name => $row_column_value) {
          if ($table_exludes[$row_column_name]) {

            // Check for direct matches.
            if (in_array($row_column_value, $table_exludes[$row_column_name])) {
              return TRUE;
            }

            // Check for partial matches.
            foreach ($table_exludes[$row_column_name] AS $value_to_ignore) {
              if (strpos($value_to_ignore, '*') !== FALSE) {
                $stripped_value_to_ignore = str_replace('*', '', $value_to_ignore);
                if (strpos($row_column_value, $stripped_value_to_ignore) !== FALSE) {
                  return TRUE;
                }
              }
            }
          }
        }
      }

      return FALSE;
    }

    public function getColumnStmt($tableName)
    {
        $columnStmt = parent::getColumnStmt($tableName);
        if (!empty($this->gdprExpressions[$tableName])) {
            $columnTypes = $this->tableColumnTypes()[$tableName];
            foreach (array_keys($columnTypes) as $i => $columnName) {
                if (!empty($this->gdprExpressions[$tableName][$columnName])) {
                    $expression = $this->gdprExpressions[$tableName][$columnName];
                    $columnStmt[$i] = "$expression as $columnName";
                }
            }
            if ($this->debugSql) {
                print "/* SELECT " . implode(",",
                        $columnStmt) . " FROM `$tableName` */\n\n";
            }
        }
        return $columnStmt;
    }

}
