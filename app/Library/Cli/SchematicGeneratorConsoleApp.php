<?php

namespace Library\Cli;

use Library\Database\Adapters\MysqlAdapter;
use Library\Helpers\SchematicHelper;
use Library\Logger\Log;
use Library\Migrations\Configurations;
use Library\Migrations\Schematic;
use Library\Migrations\SchematicFileGenerator;
use Library\Updater\SchematicUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SchematicGeneratorConsoleApp extends Command
{

    protected function configure()
    {
        $this
            ->setName('migrations:generate')
            ->setDescription('Generates the database schema files')
            ->addOption(
                'dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'What is the folder the schema files live in?'
            )
            ->addOption(
                'fileType',
                'f',
                InputOption::VALUE_REQUIRED,
                'Which type of schema file do you want to make?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $config = SchematicHelper::init($output, array(
            'fileType' => $input->getOption('fileType'),
            'directory' => $input->getOption('dir'),
            'environment' => null
        ));

        $directory = $config['directory'];
        $fileType = $config['fileType'];

        $helper = $this->getHelper('question');

        $output->writeln('<info>Generating schema file</info>');

        $question = new Question('<fg=blue>Please enter a database name:</fg=blue> ');
        $databaseName = $helper->ask($input, $output, $question);

        $question = new Question('<fg=blue>Please enter a table name:</fg=blue> ');
        $tableName = $helper->ask($input, $output, $question);

        $output->writeln('<info>Table name:</info> ' . $tableName);

        $schematicFileGenerator = new SchematicFileGenerator();
        $schematicFileGenerator
            ->setFileFormatType($fileType)
            ->setDirectory($directory)
            ->setName($databaseName)
            ->setDatabaseName($databaseName)
            ->setTableName($tableName)
            ->run();

        $output->writeln('<info>Generated schema file (</info>' . $schematicFileGenerator->getSchemaFile(). '<info>) successfully!</info>');

    }

}