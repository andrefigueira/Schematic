<?php

namespace Library\Schematic\Abstraction;

use Library\Schematic\Abstraction\Core\AbstractField;
use Library\Schematic\Exceptions\SchematicApplicationException;

/**
 * Class Table
 * @package Library\Schematic\Abstraction
 * @author Andre Figueira <andre.figueira@me.com>
 */
class Field extends AbstractField
{
	/**
	 * @var string
	 */
	protected $databaseName;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $structure;

	/**
	 * @var array
	 */
	protected $actions = [];

	const VISUAL_CONNECTOR = '->';

	const UPDATED_ACTION_NAME = 'updated';

	const CREATED_ACTION_NAME = 'created';

	/**
	 * @return string
	 */
	public function getDatabaseName()
	{
		return $this->databaseName;
	}

	/**
	 * @param string $databaseName
	 * @return $this
	 */
	public function setDatabaseName($databaseName)
	{
		$this->databaseName = $databaseName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * @param string $tableName
	 * @return $this
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;

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
	 * Runs the field synchronization
	 *
	 * @return bool
	 * @throws \Library\Schematic\Exceptions\SchematicApplicationException
	 */
	public function save()
	{
		if ($this->getName() === null) {
			throw new SchematicApplicationException('Field name is not set');
		}

		if ($this->getStructure() === null) {
			throw new SchematicApplicationException('Field structure is not defined');
		}

		$output = $this->getDi()->get('output');

		// Check if field name exists to begin with
		if ($this->exists()) {
			if ($output->isVerbose()) {
				$output->writeln('<comment>---- Field: (' . $this->getDatabaseName() . Database::VISUAL_CONNECTOR . $this->getTableName() . Database::VISUAL_CONNECTOR . $this->getName() . ') exists</comment>');
			}

			if ($this->update()) {
				$output->writeln('<info>---- Field: (' . $this->getDatabaseName() . Database::VISUAL_CONNECTOR . $this->getTableName() . Database::VISUAL_CONNECTOR . $this->getName() . ') has been updated</info>');
			} else {
				throw new SchematicApplicationException('Unable to modify field ' . $this->getDatabaseName() . ':' . $this->getTableName() . ':' . $this->getName());
			}
		} else {
			if ($output->isVerbose()) {
				$output->writeln('<error>---- Field: (' . $this->getDatabaseName() . Database::VISUAL_CONNECTOR . $this->getTableName() . Database::VISUAL_CONNECTOR . $this->getName() . ') does not exist</error>');
			}

			if ($this->create()) {
				$output->writeln('<info>---- Field: (' . $this->getDatabaseName() . Database::VISUAL_CONNECTOR . $this->getTableName() . Database::VISUAL_CONNECTOR . $this->getName() . ') has been created</info>');
			} else {
				throw new SchematicApplicationException('Unable to create field ' . $this->getDatabaseName() . ':' . $this->getTableName() . ':' . $this->getName());
			}
		}

		if ($output->isVerbose()) {
			$output->writeln(PHP_EOL . '<fg=black;bg=green>---- Finished running field synchronisation</>');
		}

		return true;
	}
}