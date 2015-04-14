<?php

class SchematicFileGeneratorTest extends PHPUnit_Framework_TestCase
{
    public $schematicFileGenerator;

    public function setUp()
    {
        $this->schematicFileGenerator = new \Library\Migrations\SchematicFileGenerator();
    }

    public function testSetFileFormatTypeSetsFileFormatType()
    {
        $this->schematicFileGenerator->setFileFormatType('yaml');

        $this->assertSame('yaml', PHPUnit_Framework_Assert::readAttribute($this->schematicFileGenerator, 'formatType'));
    }

    public function testSetFileFormatReturnsObject()
    {
        $this->assertTrue(is_object($this->schematicFileGenerator->setFileFormatType('yaml')));
    }

    public function testSetDirectorySetsDirectory()
    {
        $this->schematicFileGenerator->setDirectory('/foo');

        $this->assertSame('/foo', PHPUnit_Framework_Assert::readAttribute($this->schematicFileGenerator, 'directory'));
    }

    public function testSetDirectoryReturnsObject()
    {
        $this->assertTrue(is_object($this->schematicFileGenerator->setDirectory('/foo')));
    }

    public function testSetDatabaseNameSetsDatabaseName()
    {
        $this->schematicFileGenerator->setDatabaseName('foo');

        $this->assertSame('foo', PHPUnit_Framework_Assert::readAttribute($this->schematicFileGenerator, 'databaseName'));
    }

    public function testSetDatabaseNameReturnsObject()
    {
        $this->assertTrue(is_object($this->schematicFileGenerator->setDatabaseName('foo')));
    }

    public function testSetTableNameSetsTableName()
    {
        $this->schematicFileGenerator->setTableName('foo');

        $this->assertSame('foo', PHPUnit_Framework_Assert::readAttribute($this->schematicFileGenerator, 'tableName'));
    }

    public function testSetTableNameReturnsObject()
    {
        $this->assertTrue(is_object($this->schematicFileGenerator->setTableName('foo')));
    }

    public function testRunCreatesSchemaFile()
    {
        define('APP_NAME', 'Schematic');
        define('APP_VERSION', 'x');

        $result = $this->schematicFileGenerator
            ->setDirectory('schemas')
            ->setDatabaseName('foo')
            ->setTableName('foo')
            ->setFileFormatType('yaml')
            ->run();

        $this->assertTrue($result);

        unlink('schemas.yaml');
    }
}
