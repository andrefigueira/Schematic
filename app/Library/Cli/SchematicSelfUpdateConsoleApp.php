<?php

namespace Library\Cli;

use Library\Updater\SchematicUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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

        $updater = new SchematicUpdater($output);

        $debug = $input->getOption('debug');

        if($updater->isUpdaterRunningFromCliPhp() === true && $debug === false)
        {

            $output->writeln('<error>Unable to update Schematic running from cli.php! Can only update schematic.phar</error>');

        }
        else
        {

            if($updater->isCurrentVersionLatest())
            {

                $output->writeln('<info>Your version of Schematic is up to date.</info>');

            }
            else
            {

                $updater->updateSchematic();

            }

        }

    }

}