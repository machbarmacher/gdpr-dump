<?php

namespace machbarmacher\GdprDump;

use Ifsnop\Mysqldump\Mysqldump;

class MysqldumpGdpr extends Mysqldump {

  /** @var [string][string]string */
  protected $gdprExpressions;

  public function __construct($dsn = '', $user = '', $pass = '', array $dumpSettings = array(), array $pdoSettings = array()) {
    if (array_key_exists('gdpr-expressions', $dumpSettings)) {
      $this->gdprExpressions = $dumpSettings['gdpr-expressions'];
      unset($dumpSettings['gdpr-expressions']);
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
    return $columnStmt;
  }
}
