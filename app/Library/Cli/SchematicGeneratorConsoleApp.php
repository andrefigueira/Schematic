<?php

namespace Library\Cli;

use Library\Cli\OutputAdapters\SymfonyOutput;
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

        $directory = $input->getOption('dir');
        $fileType = $input->getOption('fileType');

        $config = SchematicHelper::init($output, array(
            'fileType' => $input->getOption('fileType'),
            'directory' => $input->getOption('dir')
        ));

        $directory = $config['directory'];
        $fileType = $config['fileType'];
        $database = $config['driver'];

        $helper = $this->getHelper('question');

        $output->writeln('<info>Generating schema file</info>');

        $question = new Question('Please enter a database name: ');
        $databaseName = $helper->ask($input, $output, $question);

        $question = new Question('Please enter a table name: ');
        $tableName = $helper->ask($input, $output, $question);

        $output->writeln('Table name: ' . $tableName);

        $schematicFileGenerator = new SchematicFileGenerator();
        $schematicFileGenerator
            ->setFileFormatType($fileType)
            ->setDirectory($directory)
            ->setName($databaseName)
            ->setDatabaseName($databaseName)
            ->setTableName($tableName)
            ->run();

        $output->writeln('<info>Generated schema file (' . $schematicFileGenerator->getSchemaFile(). ') successfully!</info>');

    }

}