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
        parent::__construct($dsn, $user, $pass, $dumpSettings, $pdoSettings);

        $this->setTransformColumnValueHook(
            function ($tableName, $colName, $colValue, $row) {
                if (!empty($this->gdprReplacements[$tableName][$colName])) {
                    if ($this->replacementConditionsMet($this->gdprReplacements[$tableName][$colName], $colValue)) {
                        $replacement = ColumnTransformer::replaceValue(
                        $tableName,
                        $colName,
                        $colValue,
                        $this->gdprReplacements[$tableName][$colName]
                    );
                        if ($replacement !== false) {
                            return $replacement;
                        }
                    }
                }

                return $colValue;
            }
        );
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
                print "/* SELECT ".implode(
                        ",",
                        $columnStmt
                    )." FROM `$tableName` */\n\n";
            }
        }

        return $columnStmt;
    }

    /**
     * Check if any conditions are set for a replacement.
     * 
     * If conditions are present, return TRUE if they are met, or FALSE if any
     * one of them is not met.
     *
     * @param [array] $replacement_details Array of replacement information
     *  taken from gdpr_replacements JSON file.
     * @param mixed $current_value Current value of column
     * @return bool
     */
    private function replacementConditionsMet($replacement_details, $current_value) {
        
        // Early bailout
        if (empty($replacement_details['conditions'])) {
            return TRUE;
        }

        $evaluation = FALSE;

        foreach ($replacement_details['conditions'] as $condition) {
            $comparand = $condition['comparand'];
            $operator = $condition['operator'];
            switch ($operator) {
                case '=':
                $evaluation = $comparand == $current_value;
                break;

                case '!=':
                $evaluation = $comparand != $current_value;
                break;

                case 'empty':
                $evaluation = empty($current_value);
                break;

                case '!empty':
                $evaluation = !empty($current_value);
                break;

                case '<';
                $evaluation = $current_value < $comparand;
                break;

                case '>';
                $evaluation = $current_value > $comparand;
                break;

                default:
                throw new Exception('Unsupported operand: ' . $operator);
                break;
            }
            
            if ($evaluation === FALSE) {
                return FALSE;
            }
        }
        return $evaluation;
    }
}
