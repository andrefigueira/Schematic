<?php

class SchematicTest extends PHPUnit_Framework_TestCase
{

    private $schematic;

    public function setUp()
    {

        $log = new \Controllers\Logger\Log();
        $mysql = new \Controllers\Database\Adapters\MysqlAdapter();
        $symfonyOutput = new Symfony\Component\Console\Output\ConsoleOutput();
        $output = new \Controllers\Cli\OutputAdapters\SymfonyOutput($symfonyOutput);

        $this->schematic = new \Controllers\Migrations\Schematic($log, $mysql, $output);

    }

    public function testCanCreateNewSqlFile()
    {

        $schematic = $this->schematic;
        $sqlFileCreation = $schematic->createSqlFile('test', 'test content');

        $this->assertTrue($sqlFileCreation);

    }

}