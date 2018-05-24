<?php

namespace machbarmacher\GdprDump;

use Ifsnop\Mysqldump\Mysqldump;

class MysqldumpGdpr extends Mysqldump {

  /** @var [string][string]string */
  protected $gdprExpressions;

  /** @var bool */
  protected $debugSql;

  public function __construct($dsn = '', $user = '', $pass = '', array $dumpSettings = array(), array $pdoSettings = array()) {
    if (array_key_exists('gdpr-expressions', $dumpSettings)) {
      $this->gdprExpressions = $dumpSettings['gdpr-expressions'];
      unset($dumpSettings['gdpr-expressions']);
    }
    if (array_key_exists('debug-sql', $dumpSettings)) {
      $this->debugSql = $dumpSettings['debug-sql'];
      unset($dumpSettings['debug-sql']);
    }
    parent::__construct($dsn, $user, $pass, $dumpSettings, $pdoSettings);
  }

  public function getColumnStmt($tableName) {
    $columnStmt = parent::getColumnStmt($tableName);
    $columnTypes = $this->tableColumnTypes()[$tableName];
    foreach (array_keys($columnTypes) as $i => $columnName) {
      if (!empty($this->gdprExpressions[$tableName][$columnName])) {
        $expression = $this->gdprExpressions[$tableName][$columnName];
        $columnStmt[$i] = "$expression as $columnName";
      }
    }
    if ($this->debugSql) {
      print "/* SELECT " . implode(",", $columnStmt) . " FROM `$tableName` */\n\n";
    }
    return $columnStmt;
  }

  /**
   * helps with structuring the gdpr-expression data slightly
   * The default behaviour is to assume that we're looking at a simple expression
   * However, if we allow the structure to be slightly thicker, we're able to add
   * faker commands
   */
  protected function getGDPRExpression($tableName, $columnName) {
    if(!empty($this->gdprExpressions[$tableName][$columnName])) {
      $gdprTransformationData = $this->gdprExpressions[$tableName][$columnName];
      if(\is_object($gdprTransformationData)) {

      } else {
        return $gdprTransformationData;
      }
    }
  }

  proteced function getTranformation($expression) {
      return $expression;
}

  /**
   * Here, instead of changing the expression itself, changing the output
   */
  protected function hookTransformColumnValue($tableName, $colName, $colValue)
  {
    
  }


}
