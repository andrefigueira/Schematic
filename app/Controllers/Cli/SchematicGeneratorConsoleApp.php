<?php

namespace Controllers\Cli;

use Controllers\Cli\OutputAdapters\SymfonyOutput;
use Controllers\Database\Adapters\MysqlAdapter;
use Controllers\Logger\Log;
use Controllers\Migrations\Schematic;
use Controllers\Migrations\SchematicFileGenerator;
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
            ->setDescription('Generates the database schema JSON files')
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