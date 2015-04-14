<?php

namespace Library\Migrations;

use Library\Helpers\SchematicHelper;

class SchematicMappingImport extends AbstractSchematic
{
    /**
     * Runs the application, sets the environment configs based on the environment and runs the mapper and generator.
     */
    public function run()
    {


        SchematicHelper::writeln('<info>Mapping of (</info>' . $this->database . '<info>) is complete</info>');
    }
}
