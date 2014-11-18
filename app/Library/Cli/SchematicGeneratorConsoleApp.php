<?php

namespace Library\Cli;

use Library\Cli\OutputAdapters\SymfonyOutput;
use Library\Database\Adapters\MysqlAdapter;
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

        //Do some output! and setup our schematic instance!
        $schematicOutput = new SymfonyOutput($output);

        $updater = new SchematicUpdater($output);
        $helper = $this->getHelper('question');

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