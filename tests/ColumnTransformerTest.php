<?php
use machbarmacher\GdprDump\ColumnTransformer\ColumnTransformer;

use \PHPUnit\Framework\TestCase;

class ColumnTransformerTest extends TestCase
{

    public function testCreatingOfNewExpressionStatement()
    {
        $jsonData = '{"users_field_data":{"name":"uid","mail":"uid","init":"uid","pass":{"transformer":"faker", "replacement":""}}}';
        $gdprEspressions = json_decode($jsonData, TRUE);
        $tableName = "users_field_data";
        $columnName = "name";
        $result = ColumnTransformer::create($tableName, $columnName, $gdprEspressions[$tableName][$columnName]);
        $this->assertTrue($result instanceof \machbarmacher\GdprDump\ColumnTransformer\ColumnTransformSelectStatement);
    }

    
}
