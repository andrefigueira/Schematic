<?php

class FileApiJsonAdapterTest extends PHPUnit_Framework_TestCase
{
    private $adapter;

    public function setUp()
    {
        $output = new Symfony\Component\Console\Output\ConsoleOutput();
        $this->adapter = new \Library\Migrations\FileApi\Adapters\JsonAdapter($output);
    }

    public function testConvertToObjectConvertsToObjectCorrectly()
    {
        $array = array(
            'testkey' => 'testvalue',
        );

        $json = json_encode($array);

        $result = $this->adapter->convertToObject($json);

        $this->assertTrue(is_object($result));
    }
}
