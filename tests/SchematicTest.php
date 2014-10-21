<?php

class SchematicTest extends PHPUnit_Framework_TestCase
{

    private $schematic;

    public function setUp()
    {

        $log = new \Library\Logger\Log();
        $mysql = new \Library\Database\Adapters\MysqlAdapter();
        $symfonyOutput = new Symfony\Component\Console\Output\ConsoleOutput();
        $output = new \Library\Cli\OutputAdapters\SymfonyOutput($symfonyOutput);
        $fileGenerator = new \Library\Migrations\FileApi\Adapters\JsonAdapter($output);

        $this->schematic = new \Library\Migrations\Schematic($log, $mysql, $output, $fileGenerator);

    }

    public function testCanCreateNewSqlFile()
    {

        $schematic = $this->schematic;
        $sqlFileCreation = $schematic->createSqlFile('test', 'test content');

        $this->assertTrue($sqlFileCreation);

    }

}