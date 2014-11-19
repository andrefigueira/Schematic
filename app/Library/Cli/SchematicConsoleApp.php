<?php

namespace Library\Cli;

use Library\Cli\OutputAdapters\SymfonyOutput;
use Library\Database\Adapters\MysqlAdapter;
use Library\Helpers\SchematicHelper;
use Library\Logger\Log;
use Library\Migrations\Configurations;
use Library\Migrations\FileApi\Adapters\JsonAdapter;
use Library\Migrations\FileApi\Adapters\YamlAdapter;
use Library\Migrations\Schematic;
use Library\Updater\SchematicUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicConsoleApp extends Command
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
            'directory' => $input->getOption('dir')
        ));

        $directory = $config['directory'];
        $fileType = $config['fileType'];
        $database = $config['driver'];

        $schematic = new Schematic(
            new Log(),
            SchematicHelper::getDatabaseAdapter($database),
            $output,
            SchematicHelper::getFileTypeGeneratorAdapter($fileType, $output)
        );

        $schematic
            ->setFileFormatType($fileType)
            ->setDirectory($directory)
            ->setEnvironmentConfigs($environment)
            ->run();

    }

}