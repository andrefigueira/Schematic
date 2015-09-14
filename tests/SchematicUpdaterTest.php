<?php

class SchematicUpdaterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $output = new Symfony\Component\Console\Output\ConsoleOutput();
        $this->schematicUpdater = new \Library\Updater\SchematicUpdater($output);
    }

    public function testGetLatestVersionChecksumReturningValidChecksum()
    {
        $this->schematicUpdater->isCurrentVersionLatest();

        $checksum = $this->schematicUpdater->getLatestVersionChecksum();

        $this->assertTrue((strlen($checksum) == 32 && ctype_xdigit($checksum)));
    }
}
