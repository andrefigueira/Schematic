<?php

namespace Library\Cli;

use Library\Cli\OutputAdapters\SymfonyOutput;
use Library\Database\Adapters\MysqlAdapter;
use Library\Logger\Log;
use Library\Migrations\FileApi\Adapters\JsonAdapter;
use Library\Migrations\FileApi\Adapters\YamlAdapter;
use Library\Migrations\SchematicMappingImport;
use Library\Updater\SchematicUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicMappingImportConsoleApp extends Command
{

    protected function configure()
    {
        $this
            ->setName('migrations:mapping')
            ->setDescription('Generates the database schema based on an existing database')
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
            ->addArgument(
                'db',
                InputArgument::REQUIRED,
                'Which database do you want to construct?'
            )
            ->addArgument(
                'fileType',
                InputArgument::REQUIRED,
                'Which type of schema file do you want to make?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $updater = new SchematicUpdater($output);

        if(!$updater->isCurrentVersionLatest())
        {

            $output->writeln('<comment>Your version of Schematic is out of date, please run schematic self-update to get the latest version...</comment>');

        }

        $directory = $input->getArgument('dir');
        $environment = $input->getArgument('env');
        $dbName = $input->getArgument('db');
        $fileType = $input->getArgument('fileType');

        $schematicOutput = new SymfonyOutput($output);

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

        $directory = $directory . $fileType . '/';

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