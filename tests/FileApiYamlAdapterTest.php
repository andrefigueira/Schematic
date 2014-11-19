<?php

class FileApiYamlAdapterTest extends PHPUnit_Framework_TestCase
{

    private $adapter;

    public function setUp()
    {

        $output = new Symfony\Component\Console\Output\ConsoleOutput();
        $this->adapter = new \Library\Migrations\FileApi\Adapters\YamlAdapter($output);

    }

    public function testConvertToObjectConvertsToObjectCorrectly()
    {

        $array = array(
            'testkey' => 'testvalue'
        );

        $dumper = new \Symfony\Component\Yaml\Dumper();
        $yaml = $dumper->dump($array);

        $result = $this->adapter->convertToObject($yaml);

        $this->assertTrue(is_object($result));

    }

    public function testConvertToFormatCorrectlyConvertsToFormat()
    {

        $array = array(
            'testkey' => 'testvalue'
        );

        $result = trim($this->adapter->convertToFormat($array));

        $this->assertSame('testkey: testvalue', $result);

    }

}