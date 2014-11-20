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

    public function testInitReturnsArray()
    {

        $this->assertTrue(is_array(\Library\Helpers\SchematicHelper::init($this->consoleOutput, array(
            'fileType' => 'yaml'
        ))));

    }

}