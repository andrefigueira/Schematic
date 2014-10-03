<?php

namespace Controllers\Cli;

use Controllers\Migrations\Schematic;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicConsoleApp extends Command
{

    protected function configure()
    {
        $this
            ->setName('migrations:run')
            ->setDescription('Generates the database schema based on the JSON files')
            ->addArgument(
                'dir',
                InputArgument::REQUIRED,
                'What is the folder the schema files live in?'
            )
            ->addArgument(
                'env',
                InputArgument::REQUIRED,
                'What is the environment?'
            )
            ->addOption(
                'debug',
                'd',
                InputOption::VALUE_NONE,
                'If set will turn on debug'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $directory = $input->getArgument('dir');
        $environment = $input->getArgument('env');

        $debug = $input->getOption('debug');

        $output->writeln('<info>Beginning migrations</info>');

        $schematic = new Schematic();
        $schematic
            ->setDir($directory)
            ->setEnvironmentConfigs($environment)
        ;


        $dir = new \DirectoryIterator($directory);

        foreach($dir as $fileInfo)
        {

            $schematic->setSchemaFile($fileInfo->getFilename());

            if(!$fileInfo->isDot() && $fileInfo->getFilename() != 'config')
            {

                if($schematic->exists())
                {

                    $schematic->generate();

                }
                else
                {

                    throw new \Exception('No schematics exist...');

                }

            }

        }

    }

}