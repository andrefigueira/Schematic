<?php

namespace Library\Schematic\Abstraction\Core;

use Library\Schematic\Exceptions\SchematicApplicationException;

/**
 * Class AbstractTable
 * @package Library\Schematic\Abstraction\Core
 * @author Andre Figueira <andre.figueira@me.com>
 */
abstract class AbstractTable extends AbstractDatabaseItem
{
	public function exists()
	{
		$this->getDb()->query('use ' . $this->getDatabaseName());

		$tableName = $this->getName();

		$query = '
		SHOW TABLES LIKE :tableName
		';

		$statement = $this->getDb()->prepare($query);
		$statement->bindParam(':tableName', $tableName);

		if ($statement->execute() === false) {
			throw new SchematicApplicationException('Failed to check if table (' . $tableName . ') exists');
		}

		return (bool) $statement->rowCount();
	}

	public function create()
	{
		$query = '
		CREATE TABLE `' . $this->getName() . '` (
  			`id` int(11) unsigned NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		';

		$statement = $this->getDb()->prepare($query);

		return $statement->execute();
	}
}