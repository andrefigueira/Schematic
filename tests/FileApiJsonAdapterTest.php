<?php

class FileApiJsonAdapterTest extends PHPUnit_Framework_TestCase
{

    private $adapter;

    public function setUp()
    {

        $symfonyOutput = new Symfony\Component\Console\Output\ConsoleOutput();
        $output = new \Library\Cli\OutputAdapters\SymfonyOutput($symfonyOutput);
        $this->adapter = new \Library\Migrations\FileApi\Adapters\JsonAdapter($output);

    }

    public function testConvertToObjectConvertsToObjectCorrectly()
    {

        $array = array(
            'testkey' => 'testvalue'
        );

        $json = json_encode($array);

        $result = $this->adapter->convertToObject($json);

        $this->assertTrue(is_object($result));

    }

}