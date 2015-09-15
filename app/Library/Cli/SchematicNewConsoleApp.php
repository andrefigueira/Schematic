<?php

namespace Library\Cli;

use DI\ContainerBuilder;
use Library\Helpers\SchematicHelper;
use Library\Schematic\Abstraction\Database;
use Library\Schematic\Interpretter\SchematicInterpretter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicNewConsoleApp extends Command
{
	protected function configure()
	{
		$this
			->setName('migrations:run')
			->setDescription('Executes the database migration based on the schema files')
			->addOption(
				'dir',
				'd',
				InputOption::VALUE_REQUIRED,
				'Where are the schema files to run?'
			)
			->addOption(
				'fileType',
				'f',
				InputOption::VALUE_REQUIRED,
				'What filetype are the schema files?'
			)
			->addArgument(
				'env',
				InputArgument::REQUIRED,
				'What is the environment?'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$environment = $input->getArgument('env');

		$di = ContainerBuilder::buildDevContainer();

		$di->set('config', function () use ($input, $di, $environment) {
			return SchematicHelper::init([
				'fileType' => $input->getOption('fileType'),
				'directory' => $input->getOption('dir'),
				'environment' => $environment,
			], $di);
		});

		$di->set('db', function () use ($di) {
			$config = $di->get('config');

			$host = $config['environmentConfigs']->host;
			$username = $config['environmentConfigs']->user;
			$password = $config['environmentConfigs']->pass;

			return new \PDO('mysql:host=' . $host . ';', $username, $password);
		});

		$di->set('output', function() use ($output) {
			return $output;
		});

		$di->set('tableHelper', function() {
			return $this->getHelper('table');
		});

		$schematicStructure = new SchematicInterpretter();
		$schematicStructure->setDi($di);

		$databases = $schematicStructure->getDatabases();

		if (count($databases) > 0) {
			foreach ($databases as $databaseStructure) {
				$database = new Database();

				$database->setDi($di);

				$database
					->setName($databaseStructure['database']['general']['name'])
					->setStructure($databaseStructure['database']['tables'])
				;

				if ($database->save()) {
					$output->writeln('<green>Finished updating database</green>');
				} else {
					foreach ($database->getMessages() as $message) {
						$output->writeln('<error>' . $message['content'] . '</error>');
					}
				}
			}
		} else {
			$output->writeln('<comment>No databases fetched in structure</comment>');
		}

		$output->writeln(PHP_EOL . '<bg=green;fg=black;options=bold>Migrations completed</>');
	}
}
