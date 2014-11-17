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
                'name',
                InputArgument::REQUIRED,
                'What do you want to call the schema file?'
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

        $name = $input->getArgument('name');
        $directory = $input->getArgument('dir');
        $fileType = $input->getArgument('fileType');

        $directory = $directory . $fileType . '/';

        $output->writeln('<info>Generating schema file</info>');

        $schematicFileGenerator = new SchematicFileGenerator();
        $schematicFileGenerator
            ->setFileFormatType($fileType)
            ->setDirectory($directory)
            ->setName($name)
            ->run();

        $output->writeln('<info>Generated schema file successfully!</info>');
        $output->writeln('<info>' . $schematicFileGenerator->getSchemaFile(). '</info>');

    }

}