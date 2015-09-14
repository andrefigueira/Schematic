<?php

namespace Library\Cli;

use Library\Updater\SchematicUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicSelfUpdateConsoleApp extends Command
{
    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Updates schematic.phar to the latest version.')
            ->addOption(
                'debug',
                'd'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $debug = $input->getOption('debug');

        $updater = new SchematicUpdater($output);
        $updater->debug = $debug;

        $output->writeln('<info>Checking current version...</info>');

        if ($updater->isUpdaterRunningFromCliPhp() === true && $debug === false) {
            $output->writeln('<error>Unable to update Schematic running from cli.php! Can only update schematic.phar</error>');
        } else {
            if ($updater->isCurrentVersionLatest()) {
                $output->writeln('<info>You already have Schematic version '.$updater->getLatestVersionChecksum().'</info>');
            } else {
                $output->writeln('<info>Schematic is out of date, installing latest version from server...</info>');

                $updater->updateSchematic();
            }
        }
    }
}
