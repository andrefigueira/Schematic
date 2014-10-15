<?php

namespace Controllers\Cli;

use Controllers\Cli\OutputAdapters\SymfonyOutput;
use Controllers\Database\Adapters\MysqlAdapter;
use Controllers\Logger\Log;
use Controllers\Migrations\Generators\Adapters\JsonAdapter;
use Controllers\Migrations\SchematicMappingImport;
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

            default:
                throw new \Exception('We can only generate JSON files for now...');

        }

        $output->writeln('<info>Beginning migrations</info>');

        $log = new Log();

        $schematic = new SchematicMappingImport($log, $db, $fileTypeGenerator);
        $schematic
            ->setDir($directory)
            ->setDatabase($dbName)
            ->setEnvironmentConfigs($environment)
            ->run()
        ;

        $output->writeln('<info>Migrations completed successfully!</info>');

    }

}