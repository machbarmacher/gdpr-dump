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
        $this->setTransformTableRowHook([$this, 'hookTransformRow']);
        parent::__construct($dsn, $user, $pass, $dumpSettings, $pdoSettings);
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

    protected function hookTransformRow($tableName, array $row)
    {
        foreach ($row as $colName => &$colValue) {
            if (!empty($this->gdprReplacements[$tableName][$colName])) {
                $replacement = ColumnTransformer::replaceValue($tableName, $colName, $this->gdprReplacements[$tableName][$colName]);
                if($replacement !== FALSE) {
                    $colValue = $replacement;
                }
            }
        }
        return $row;
    }

}
