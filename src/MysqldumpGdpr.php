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
        $columnStmt[$i] = $this->gdprExpressions[$tableName][$columnName];
      }
    }
    if ($this->debugSql) {
      print "/* SELECT " . implode(",", $columnStmt) . " FROM `$tableName` */";
    }
    return $columnStmt;
  }
}
