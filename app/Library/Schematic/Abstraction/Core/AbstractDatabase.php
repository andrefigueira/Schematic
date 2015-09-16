<?php

namespace Library\Schematic\Abstraction\Core;
use Library\Schematic\Exceptions\SchematicApplicationException;

/**
 * Class AbstractDatabase
 * @package Library\Schematic\Abstraction\Core
 * @author Andre Figueira <andre.figueira@me.com>
 */
abstract class AbstractDatabase extends AbstractDatabaseItem
{
	public function exists()
	{
		// Set the PDO error mode
		$this->getDb()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		$databaseName = $this->getName();

		$query = '
		SELECT schema_name
		FROM information_schema.schemata
		WHERE schema_name = :databaseName
		';

		$statement = $this->getDb()->prepare($query);
		$statement->bindParam(':databaseName', $databaseName);

		if ($statement->execute() === false) {
			throw new SchematicApplicationException('Failed to check if database exists');
		}

		return (bool) $statement->rowCount();
	}

	public function create()
	{
		$query = '
		CREATE DATABASE IF NOT EXISTS `' . $this->getName() . '`
		CHARACTER SET ' . $this->getCharset() . ' COLLATE ' . $this->getCollation() . ';
		';

		$statement = $this->getDb()->prepare($query);

		return $statement->execute();
	}

	public function update()
	{
		$query = '
		ALTER DATABASE `' . $this->getName() . '`
		CHARACTER SET ' . $this->getCharset() . ' COLLATE ' . $this->getCollation() . ';
		';

		$statement = $this->getDb()->prepare($query);

		return $statement->execute();
	}
}

