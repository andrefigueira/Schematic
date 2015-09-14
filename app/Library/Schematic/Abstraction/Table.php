<?php

namespace Library\Schematic\Abstraction;

use Library\Schematic\Abstraction\Core\AbstractTable;
use Library\Schematic\Exceptions\SchematicApplicationException;

/**
 * Class Table
 * @package Library\Schematic\Abstraction
 * @author Andre Figueira <andre.figueira@me.com>
 */
class Table extends AbstractTable
{

	/**
	 * @var string
	 */
	protected $databaseName;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $structure;

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

	public function save()
	{
		if ($this->getName() === null) {
			throw new SchematicApplicationException('Table name is not set');
		}

		if ($this->getStructure() === null) {
			throw new SchematicApplicationException('Table structure is not defined');
		}

		$output = $this->getDi()->get('output');

		// Check if table name exists to begin with
		if ($this->exists()) {
			$output->writeln('<comment>Table: ' . $this->getDatabaseName() . Database::VISUAL_CONNECTOR . $this->getName() . ' exists</comment>');
		} else {
			$output->writeln('<error>Table: ' . $this->getDatabaseName() . Database::VISUAL_CONNECTOR . $this->getName() . ' does not exist</error>');
			if ($this->create()) {
				$output->writeln('<info>Table: ' . $this->getDatabaseName() . Database::VISUAL_CONNECTOR . $this->getName() . ' has been created</info>');
			} else {
				throw new SchematicApplicationException('Unable to create table ' . $this->getDatabaseName() . ':' . $this->getName());
			}
		}

		$output->writeln('<info>-- Running table synchronisation</info>');

		$iteration = 0;

		foreach ($this->getStructure()['fields'] as $fieldName => $fieldStructure) {
			$field = new Field();

			if ($iteration == 0) { $field::$lastField = null;}

			$field->setDi($this->getDi());

			$field
				->setDatabaseName($this->getDatabaseName())
				->setTableName($this->getName())
				->setName($fieldName)
				->setStructure($fieldStructure)
			;

			if ($field->save()) {
				$field->setLastField($field->getName());
				$output->writeln('<green>Finished updating field</green>');
			} else {
				foreach ($field->getMessages() as $message) {
					$output->writeln('<error>' . $message['content'] . '</error>');
				}
			}

			$iteration++;
		}

		if ($this->clearUnschemedFields()) {

		} else {
			$output->writeln('<error>Failed to delete unschemed fields</error>');
		}

		$output->writeln('<bg=yellow;fg=black;options=bold>Finished running table synchronisation</>');
	}

	public function clearUnschemedFields()
	{
		$fields = $this->getStructure()['fields'];

		foreach ($databaseFields as $databaseField) {
			if (!array_key_exists($databaseFieldName, $fields)) {

			}
		}
	}
}