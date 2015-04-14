<?php

namespace Library\Cli;

use Library\Helpers\SchematicHelper;
use Library\Logger\Log;
use Library\Migrations\Schematic;
use Library\Migrations\SchematicExecute;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicExecuteConsoleApp extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrations:execute')
            ->setDescription('Executes the database migration based on the schema files')
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $environment = $input->getArgument('env');

        $config = SchematicHelper::init($output, array(
            'fileType' => $input->getOption('fileType'),
            'directory' => $input->getOption('dir'),
            'environment' => $environment,
        ));

        $directory = $config['directory'];
        $fileType = $config['fileType'];
        $database = $config['driver'];

        $fileAdapterClass = '\Library\Migrations\FileApi\Adapters\\'.ucfirst($fileType).'Adapter';

        $schematic = new SchematicExecute(
            new Log(),
            new $fileAdapterClass($output)
        );

        $schematic
            ->setFileFormatType($fileType)
            ->setDirectory($directory)
            ->setEnvironmentConfigs($config['environmentConfigs'])
            ->run();
    }
}
