<?php

class SchematicTest extends PHPUnit_Framework_TestCase
{

    public function testJsonCanBeReadFromSchemaFolder()
    {

        $schematic = new \Controllers\Schematic();
        $schematic->schemaDir = './schemas/';
        $schematic->schemaFile = 'schema.json';
        $schematic->exists();

        $decodedJsonObject = $schematic->schema;

        $this->assertObjectHasAttribute('schematic', $decodedJsonObject);

    }

    public function testCanCreateNewSqlFile()
    {

        $schematic = new \Controllers\Schematic();
        $sqlFileCreation = $schematic->createSqlFile('test', 'test content');

        $this->assertTrue($sqlFileCreation);

    }

}