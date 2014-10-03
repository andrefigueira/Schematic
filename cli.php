<?php
/**
 * Cli tool for Schematic
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

require_once __DIR__ . '/app/bootstrap.php';

use Controllers\Cli\SchematicConsoleApp;
use Symfony\Component\Console\Application;

$application = new Application(APP_NAME, APP_VERSION);

//Add the order file generator console app, this generates SO and PAY files for the AP K3 integration
$application->add(new SchematicConsoleApp);

$application->run();