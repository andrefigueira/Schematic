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

echo "
   _____      __                         __  _
  / ___/_____/ /_  ___  ____ ___  ____ _/ /_(______
  \__ \/ ___/ __ \/ _ \/ __ `__ \/ __ `/ __/ / ___/
 ___/ / /__/ / / /  __/ / / / / / /_/ / /_/ / /__
/____/\___/_/ /_/\___/_/ /_/ /_/\__,_/\__/_/\___/

";

$application = new Application(APP_NAME, APP_VERSION);

$application->add(new SchematicConsoleApp);
$application->add(new SchematicGeneratorConsoleApp);
$application->add(new SchematicMappingImportConsoleApp);

$application->run();