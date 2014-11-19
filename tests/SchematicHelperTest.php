<?php

class SchematicHelperTest extends PHPUnit_Framework_TestCase
{

    /**
     * Sets up the Sumfony console output for use in the rest of the tests
     */
    public function setUp()
    {

        $this->consoleOutput = new Symfony\Component\Console\Output\ConsoleOutput();

    }

    /**
     * Checks that we are returning an array still for the checking of database drivers
     */
    public function testValidDatabaseDriversReturnsArray()
    {

        $this->assertTrue(is_array(\Library\Helpers\SchematicHelper::validDatabaseDrivers()));

    }

    /**
     * Checks that the validFileTypes return a valid array
     */
    public function testValidFileTypesReturnsArray()
    {

        $this->assertTrue(is_array(\Library\Helpers\SchematicHelper::validFileTypes()));

    }

    /**
     * Checks to see if we get a valid file api adapter from the adapter fetcher
     */
    public function testGetFileTypeGeneratorAdapterReturnsInstanceOfYamlAdapterClass()
    {

        $this->assertInstanceOf('Library\Migrations\FileApi\Adapters\YamlAdapter', \Library\Helpers\SchematicHelper::getFileTypeGeneratorAdapter('yaml', $this->consoleOutput));

    }

    /**
     * Checks to see if we get a valid file api adapter from the adapter fetcher
     */
    public function testGetFileTypeGeneratorAdapterReturnsInstanceOfJsonAdapterClass()
    {

        $this->assertInstanceOf('Library\Migrations\FileApi\Adapters\JsonAdapter', \Library\Helpers\SchematicHelper::getFileTypeGeneratorAdapter('json', $this->consoleOutput));

    }

    public function testInitReturnsArray()
    {

        $this->assertTrue(is_array(\Library\Helpers\SchematicHelper::init($this->consoleOutput, array(
            'fileType' => 'yaml'
        ))));

    }

}