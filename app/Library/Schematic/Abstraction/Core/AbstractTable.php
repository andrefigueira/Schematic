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

	/**
	 * Creates an initial table with an tmp field, this is a temporary field to allow the table creation
	 *
	 * @return bool
	 */
	public function create()
	{
		$query = '
		CREATE TABLE `' . $this->getName() . '` (
  			`tmp` int(11) unsigned NOT NULL
		) ENGINE=' . $this->getEngine() . ' DEFAULT CHARSET=' . $this->getCharset() . ' COLLATION=' . $this->getCollation() . ';
		';

		$statement = $this->getDb()->prepare($query);

		return $statement->execute();
	}

	/**
	 * Updates the tables charset and collation
	 *
	 * @return bool
	 */
	public function update()
	{
		$query = '
		ALTER TABLE `' . $this->getName() . '`
  		CHARACTER SET ' . $this->getCharset() . ',
  		COLLATE ' . $this->getCollation() . ',
  		ENGINE=' . $this->getEngine() . ';
		';

		$statement = $this->getDb()->prepare($query);

		return $statement->execute();
	}
}