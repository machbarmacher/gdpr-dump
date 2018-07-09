<?php
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;

use \PHPUnit\Framework\TestCase;

class ColumnTransformerTest extends TestCase
{
    private $jsonData = '{"users_field_data":{"name":"uid","mail":{"formatter":"clear"},"init":"uid","pass":{"transformer":"faker", "formatter":"password"}}}';

    public function testCreatingNewFakerStatement()
    {
        $gdprExpressions = json_decode($this->jsonData, TRUE);
        $tableName = "users_field_data";
        $columnName = "pass";
        $result = ColumnTransformer::replaceValue($tableName, $columnName, $gdprExpressions[$tableName][$columnName]);
        $this->assertTrue(is_string($result));
    }

    public function testCreatingNewClearStatement()
    {
        $gdprExpressions = json_decode($this->jsonData, TRUE);
        $tableName = "users_field_data";
        $columnName = "mail";
        $result = ColumnTransformer::replaceValue($tableName, $columnName, $gdprExpressions[$tableName][$columnName]);
        $this->assertTrue(is_string($result) && strlen($result) == 0);
    }
}
