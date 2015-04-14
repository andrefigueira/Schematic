<?php

namespace Library\Cli;

use Library\Installer\SchematicInstaller;
use Library\Migrations\Configurations;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SchematicInitConsoleApp extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Sets up default configuration for schematic including schema folder and schematic config file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $dialog = $this->getHelper('dialog');

        $output->writeln('<info>Initializing Schematic for this project...</info>');

        $fileTypes = Configurations::allowedConfigurationFileTypes();

        $fileTypeResult = $dialog->select(
            $output,
            'Please select which file type to use (yaml or json)',
            $fileTypes,
            0
        );

        $fileType = $fileTypes[$fileTypeResult];

        $output->writeln('<comment>Using: '.$fileType.'</comment>');

        $fileAdapterClass = '\Library\Migrations\FileApi\Adapters\\'.ucfirst($fileType).'Adapter';

        $schematicInstaller = new SchematicInstaller($output, new $fileAdapterClass($output));
        $schematicInstaller
            ->setFileFormatType($fileType)
            ->run();
    }
}
