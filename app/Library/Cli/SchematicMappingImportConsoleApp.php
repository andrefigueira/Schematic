<?php

namespace Library\Cli;

use Library\Helpers\SchematicHelper;
use Library\Logger\Log;
use Library\Migrations\SchematicMappingImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicMappingImportConsoleApp extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrations:mapping')
            ->setDescription('Generates the database schema based on an existing database')
            ->addOption(
                'dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Where are the schema files to run?'
            )
            ->addOption(
                'fileType',
                'f',
                InputOption::VALUE_REQUIRED,
                'What filetype are the schema files?'
            )
            ->addArgument(
                'env',
                InputArgument::REQUIRED,
                'What is the environment?'
            )
            ->addArgument(
                'db',
                InputArgument::REQUIRED,
                'Which database do you want to construct?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $environment = $input->getArgument('env');
        $dbName = $input->getArgument('db');

        $config = SchematicHelper::init($output, array(
            'fileType' => $input->getOption('fileType'),
            'directory' => $input->getOption('dir'),
            'environment' => $environment,
        ));

        $directory = $config['directory'];
        $fileType = $config['fileType'];
        $database = $config['driver'];

        $databaseAdapterClass = '\Library\Database\Adapters\\'.ucfirst($database).'Adapter';
        $fileAdapterClass = '\Library\Migrations\FileApi\Adapters\\'.ucfirst($fileType).'Adapter';

        $output->writeln('<info>Beginning database mapping</info>');

        $schematic = new SchematicMappingImport(
            new Log(),
            new $databaseAdapterClass(),
            $output,
            new $fileAdapterClass($output)
        );

        $schematic
            ->setFileFormatType($fileType)
            ->setDirectory($directory)
            ->setDatabase($dbName)
            ->setEnvironmentConfigs($config['environmentConfigs'])
            ->run()
        ;
    }
}
