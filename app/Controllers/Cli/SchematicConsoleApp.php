<?php

namespace Controllers\Cli;

use Controllers\Cli\OutputAdapters\SymfonyOutput;
use Controllers\Database\Adapters\MysqlAdapter;
use Controllers\Logger\Log;
use Controllers\Migrations\Generators\Adapters\JsonAdapter;
use Controllers\Migrations\Generators\Adapters\YamlAdapter;
use Controllers\Migrations\Schematic;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicConsoleApp extends Command
{

    protected function configure()
    {
        $this
            ->setName('migrations:execute')
            ->setDescription('Executes the database migration based on the JSON schema files')
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
                throw new \Exception('We can only generate JSON and YAML files for now...');

        }

        $directory = $directory . $fileType . '/';

        $output->writeln('<info>Beginning migrations</info>');

        $log = new Log();

        $schematic = new Schematic($log, $db, $schematicOutput, $fileTypeGenerator);
        $schematic
            ->setFileFormatType($fileType)
            ->setDirectory($directory)
            ->setEnvironmentConfigs($environment)
            ->run()
        ;

        $output->writeln('<info>Migrations completed successfully!</info>');

    }

}