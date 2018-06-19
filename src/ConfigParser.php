<?php

namespace machbarmacher\GdprDump;

use bomoko\MysqlCnfParser\MysqlCnfParser;

/**
 * Class ConfParser
 *
 * Parses key/value lines from mysql .cnf files.
 *
 * @package machbarmacher\GdprDump
 */
class ConfigParser
{

    /** @var array */
    private $config = [];

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getFiltered($sections, $keys = null)
    {
        $result = [];
        foreach ($this->config as $section => $values) {
            foreach ($values as $key => $value) {
                if (in_array($section,
                        $sections) && (!isset($keys) || in_array($key, $keys))
                ) {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Takes a .cnf file and adds its configuration settings to internal state
     *
     * @param $file
     */
    public function addFile($file)
    {
        $sections = MysqlCnfParser::parse($file);
        $this->config = array_replace_recursive($this->config, $sections);
    }

}
