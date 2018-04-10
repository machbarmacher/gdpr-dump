<?php

namespace machbarmacher\GdprDump;

use Ifsnop\Mysqldump\Mysqldump;

class MysqldumpGdpr extends Mysqldump {

  /** @var [string][string]string */
  protected $gdprExpressions;

  /**
   * Set GDPR expressions.
   *
   * @param [string][string]string $gdprExpressions
   *   Array of SQL expressions, keyed by table and column name.
   */
  public function setGdprExpressions($gdprExpressions) {
    $this->gdprExpressions = $gdprExpressions;
  }


  public function getColumnStmt($tableName) {
    $columnStmt = parent::getColumnStmt($tableName);
    $columnTypes = $this->tableColumnTypes()[$tableName];
    foreach (array_keys($columnTypes) as $i => $columnName) {
      if (!empty($this->gdprExpressions[$tableName][$columnName])) {
        $columnStmt[$i] = $this->gdprExpressions[$tableName][$columnName];
      }
    }
    return $columnStmt;
  }
}
