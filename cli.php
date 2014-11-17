#!/usr/bin/env php
<?php
/**
 * Cli tool for Schematic
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

require_once __DIR__ . '/app/bootstrap.php';

use Library\Cli\SchematicConsoleApp;
use Library\Cli\SchematicGeneratorConsoleApp;
use Library\Cli\SchematicMappingImportConsoleApp;
use Symfony\Component\Console\Application;

$application = new Application(APP_NAME, APP_VERSION);

$application->add(new SchematicConsoleApp);
$application->add(new SchematicGeneratorConsoleApp);
$application->add(new SchematicMappingImportConsoleApp);

$application->run();