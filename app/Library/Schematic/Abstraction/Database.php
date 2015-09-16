<?php

namespace Library\Schematic\Abstraction;

use Library\Schematic\Abstraction\Core\AbstractDatabase;
use Library\Schematic\Exceptions\SchematicApplicationException;
use Library\Schematic\Validators\SchematicValidator;

/**
 * Class Database
 * @package Library\Schematic
 * @author Andre Figueira <andre.figueira@me.com>
 */
class Database extends AbstractDatabase
{

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $charset;

	/**
	 * @var string
	 */
	protected $collation;

	/**
	 * @var string
	 */
	protected $engine;

	/**
	 * @var array
	 */
	protected $structure;

	const VISUAL_CONNECTOR = '->';

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * @param string $charset
	 * @return $this
	 */
	public function setCharset($charset)
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCollation()
	{
		return $this->collation;
	}

	/**
	 * @param string $collation
	 * @return $this
	 */
	public function setCollation($collation)
	{
		$this->collation = $collation;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEngine()
	{
		return $this->engine;
	}

	/**
	 * @param string $engine
	 * @return $this
	 */
	public function setEngine($engine)
	{
		$this->engine = $engine;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getStructure()
	{
		return $this->structure;
	}

	/**
	 * @param array $structure
	 * @return $this
	 */
	public function setStructure($structure)
	{
		$this->structure = $structure;

		return $this;
	}

	/**
	 * Runs the database synchronization
	 *
	 * @throws \Library\Schematic\Exceptions\SchematicApplicationException
	 */
	public function save()
	{
		if ($this->getName() === null) {
			throw new SchematicApplicationException('Database name not set in Schema file');
		}

		if ($this->getStructure() === null) {
			throw new SchematicApplicationException('Structure has not been defined');
		}

		// Process the structure
		if (SchematicValidator::validate($this->getStructure()) === false) {
			throw new SchematicApplicationException(SchematicValidator::getMessage());
		}

		$output = $this->getDi()->get('output');

		// Check if DB name exists to begin with
		if ($this->exists()) {
			$output->writeln('<comment>Database: ' . $this->getName() . ' exists</comment>');

			if ($this->update()) {
				$output->writeln('<info>Database: ' . $this->getName() . ' has been updated</info>');
			} else {
				throw new SchematicApplicationException('Unable to update database ' . $this->getName() . ': ');
			}
		} else {
			$output->writeln('<error>Database: ' . $this->getName() . ' does not exist</error>');

			if ($this->create()) {
				$output->writeln('<info>Database: ' . $this->getName() . ' has been created</info>');
			} else {
				throw new SchematicApplicationException('Unable to create database ' . $this->getName());
			}
		}

		$output->writeln('<info>Running table synchronisation</info>');

		// We're expecting one table per schematic file, but more would be processed if they exist...
		foreach ($this->getStructure() as $tableName => $tableStructure) {
			$table = new Table();

			$table->setDi($this->getDi());

			$table
				->setDatabaseName($this->getName())
				->setName($tableName)
				->setCharset($this->getCharset())
				->setCollation($this->getCollation())
				->setEngine($this->getEngine())
				->setStructure($tableStructure)
			;

			if ($table->save()) {
				$output->writeln('<green>Finished updating table</green>');
			} else {
				foreach ($table->getMessages() as $message) {
					$output->writeln('<error>' . $message['content'] . '</error>');
				}
			}
		}

		return true;
	}
}