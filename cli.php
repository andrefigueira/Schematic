#!/usr/bin/env php
<?php
/**
 * Cli tool for Schematic.
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */
require_once __DIR__.'/app/bootstrap.php';

use Library\Cli\SchematicConsoleApp;
use Library\Cli\SchematicExecuteConsoleApp;
use Library\Cli\SchematicGeneratorConsoleApp;
use Library\Cli\SchematicMappingImportConsoleApp;
use Library\Cli\SchematicSelfUpdateConsoleApp;
use Library\Cli\SchematicInitConsoleApp;
use Symfony\Component\Console\Application;

$application = new Application(APP_TITLE, APP_VERSION);

/**
 * The main migrations class which runs all updates to the database
 */
$application->add(new SchematicExecuteConsoleApp());

/**
 * Generates new schem files based on a template file
 */
$application->add(new SchematicGeneratorConsoleApp());

/**
 * Maps an existing database to schema files within the Schematic format
 */
$application->add(new SchematicMappingImportConsoleApp());

/**
 * Checks to see if there are any updates to Schematic in the trunk and updates to that latest stable version
 */
$application->add(new SchematicSelfUpdateConsoleApp());

/**
 * Installs the default setup for Schematic
 */
$application->add(new SchematicInitConsoleApp());

/**
 * Run the app yo
 */
$application->run();
