<?php

namespace Library\Cli;

use Library\Cli\OutputAdapters\SymfonyOutput;
use Library\Database\Adapters\MysqlAdapter;
use Library\Logger\Log;
use Library\Migrations\Configurations;
use Library\Migrations\FileApi\Adapters\JsonAdapter;
use Library\Migrations\FileApi\Adapters\YamlAdapter;
use Library\Migrations\SchematicMappingImport;
use Library\Updater\SchematicUpdater;
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

        $directory = $input->getOption('dir');
        $fileType = $input->getOption('fileType');
        $environment = $input->getArgument('env');
        $dbName = $input->getArgument('db');

        $updater = new SchematicUpdater($output);
        $schematicOutput = new SymfonyOutput($output);

        if(!$updater->isCurrentVersionLatest())
        {

            $output->writeln('<comment>Your version of Schematic is out of date, please run schematic self-update to get the latest version...</comment>');

        }

        //Check where we are reading our configurations from, the options or the config file
        $migrationsConfigurations = new Configurations($schematicOutput);
        $settingFileType = $migrationsConfigurations->fileType;

        if(!$settingFileType && !$fileType)
        {

            throw new \Exception('There is no setting file e.g. .schematic.yaml defined, so pass in the file type or create the config file using -ft...');

        }

        if(!isset($migrationsConfigurations->config->directory) && !$directory)
        {

            throw new \Exception('There is no directory setting in the ' . $migrationsConfigurations::CONFIG_FILE_NAME . ' config file, so path is through as an option using -d...');

        }

        //Set defaults for the options if the config file is set
        if($directory)
        {

            $output->writeln('<comment>Using directory (' . $directory . ') passed in command!</comment>');

        }
        else
        {

            $directory = $migrationsConfigurations->config->directory;

        }

        if($fileType)
        {

            $output->writeln('<comment>Using fileType (' . $fileType . ') passed in command!</comment>');

        }
        else
        {

            $fileType = $settingFileType;


        }

        $database = 'mysql';

        switch($database)
        {

            case 'mysql':
                $db = new MysqlAdapter();
                break;

            default:
                throw new \Exception('Must be valid adapter.. e.g. mysql');

        }

        switch($fileType)
        {

            case 'json':
                $fileTypeGenerator = new JsonAdapter($schematicOutput);
                break;

            case 'yaml':
                $fileTypeGenerator = new YamlAdapter($schematicOutput);
                break;

            default:
                throw new \Exception('We can only generate JSON files for now...');

        }

        $output->writeln('<info>Beginning migrations</info>');

        $log = new Log();

        $schematic = new SchematicMappingImport($log, $db, $schematicOutput, $fileTypeGenerator);

        $schematic
            ->setFileFormatType($fileType)
            ->setDirectory($directory)
            ->setDatabase($dbName)
            ->setEnvironmentConfigs($environment)
            ->run()
        ;

        $output->writeln('<info>Migrations completed successfully!</info>');

    }

}