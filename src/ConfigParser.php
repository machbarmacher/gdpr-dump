<?php

namespace machbarmacher\GdprDump;

/**
 * Class ConfParser
 *
 * Parses key/value lines from mysql .cnf files.
 *
 * @package machbarmacher\GdprDump
 */
class ConfigParser {

  /** @var array */
  private $config = [];

  /**
   * @return array
   */
  public function getConfig() {
    return $this->config;
  }

  public function getFiltered($sections, $keys = NULL) {
    $result = [];
    foreach ($this->config as $section => $values) {
      foreach ($values as $key => $value) {
        if (in_array($section, $sections) && (!isset($keys) || in_array($key, $keys))) {
          $result[$key] = $value;
        }
      }
    }
    return $result;
  }

  public function addConfig($configString) {
    $lines = preg_split('/\R/mu', $configString);
    // This is too lax but good enough.
    $keyRE = '[a-zA-Z0-9_-]+';
    $sections = [];
    $currentSection = 'NONE';
    $success = array_walk($lines, function ($line) use (&$sections, &$currentSection, $keyRE) {
      if (preg_match("/^\s*\\[($keyRE)\\]\s*$/u", $line, $m)) {
        $currentSection = $m[1];
      }
      elseif (preg_match("/^\s*($keyRE)\s*=\s*(.*)$/u", $line, $m)) {
        $key = $m[1];
        $value = $m[2];
        $value = strtr($value, ['\b' => chr(8), '\t' => "\t", '\n' => "\n", '\r' => "\r", '\s' => ' ', '\\\\' => '\\']);
        if (preg_match('/^"(.*?)"(\s*#.*)?$/u', $value, $m) || preg_match('^/\'(.*?)\'(\s*#.*)?$/u', $value, $m)) {
          $value = $m[1];
        }
        $sections[$currentSection][$key] = $value;
      }
    });
    if (!$success) {
      throw new \LogicException('Error parsing config sections.');
    }
    $this->config = array_replace_recursive($this->config, $sections);
  }

  public function addFile($file) {
    $this->addConfig(file_get_contents($file));
  }

}
