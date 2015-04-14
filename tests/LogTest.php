<?php

class LogTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        date_default_timezone_set('UTC');

        $this->log = new \Library\Logger\Log();
    }

    public function testExistsMethodSucceedsInTestingLogFolderExists()
    {
        $this->assertTrue($this->log->exists());
    }

    public function testCanWriteToLog()
    {
        $this->assertTrue($this->log->write('unit test'));
    }
}
