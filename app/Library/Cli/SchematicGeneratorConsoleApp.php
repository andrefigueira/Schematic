<?php

namespace Library\Cli;

use Library\Cli\OutputAdapters\SymfonyOutput;
use Library\Database\Adapters\MysqlAdapter;
use Library\Logger\Log;
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
            ->addArgument(
                'dir',
                InputArgument::REQUIRED,
                'What is the folder the schema files live in?'
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
        $helper = $this->getHelper('question');

        if(!$updater->isCurrentVersionLatest())
        {

            $output->writeln('<comment>Your version of Schematic is out of date, please run schematic self-update to get the latest version...</comment>');

        }

        $directory = $input->getArgument('dir');
        $fileType = $input->getArgument('fileType');

        $directory = $directory . $fileType . '/';

        $output->writeln('<info>Generating schema file</info>');

        $question = new Question('Please enter a file name for the schema file: ');
        $fileName = $helper->ask($input, $output, $question);

        $question = new Question('Please enter a database name: ');
        $databaseName = $helper->ask($input, $output, $question);

        $question = new Question('Please enter a table name: ');
        $tableName = $helper->ask($input, $output, $question);

        $output->writeln('Table name: ' . $tableName);

        $schematicFileGenerator = new SchematicFileGenerator();
        $schematicFileGenerator
            ->setFileFormatType($fileType)
            ->setDirectory($directory)
            ->setName($fileName)
            ->setDatabaseName($databaseName)
            ->setTableName($tableName)
            ->run();

        $output->writeln('<info>Generated schema file successfully!</info>');
        $output->writeln('<info>' . $schematicFileGenerator->getSchemaFile(). '</info>');

    }

}