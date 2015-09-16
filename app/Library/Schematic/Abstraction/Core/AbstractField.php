<?php

namespace Library\Schematic\Abstraction\Core;

/**
 * Class AbstractField
 * @package Library\Schematic\Abstraction\Core
 * @author Andre Figueira <andre.figueira@me.com>
 */
abstract class AbstractField extends AbstractDatabaseItem
{
	/**
	 * @var string
	 */
	public static $lastField;

	/**
	 * @return string
	 */
	public function getLastField()
	{
		return self::$lastField;
	}

	public function exists()
	{
		$this->getDb()->query('use ' . $this->getDatabaseName());

		$databaseName = $this->getDatabaseName();
		$tableName = $this->getTableName();
		$fieldName = $this->getName();

		$query = '
		SELECT *
		FROM information_schema.COLUMNS
		WHERE TABLE_SCHEMA = :databaseName
		AND TABLE_NAME = :tableName
		AND COLUMN_NAME = :fieldName
		';

		$statement = $this->getDb()->prepare($query);
		$statement->bindParam(':databaseName', $databaseName);
		$statement->bindParam(':tableName', $tableName);
		$statement->bindParam(':fieldName', $fieldName);

		if ($statement->execute() === false) {
			throw new SchematicApplicationException('Failed to check if field (' . $fieldName . ') exists');
		}

		return (bool) $statement->rowCount();
	}

	public function update()
	{
		$structure = $this->getStructure();

		$query = '
		ALTER TABLE ' . $this->getTableName() . '
		MODIFY COLUMN ' . $this->getName() . ' ' . $structure['type'] . '
			' . (isset($structure['null']) && $structure['null'] ? 'NULL' : ' NOT NULL ') . '
			' . (isset($structure['default']) ? 'DEFAULT "' . $structure['default'] . '"' : '') . '
			' . (self::$lastField != null ? 'after ' . self::$lastField : '') . ';
		';

		self::$lastField = $this->getName();

		$statement = $this->getDb()->prepare($query);

		return $statement->execute();
	}

	public function create()
	{
		$structure = $this->getStructure();

		$query = '
		ALTER TABLE ' . $this->getTableName() . '
		ADD ' . $this->getName() . ' ' . $structure['type'] . '
			' . (self::$lastField != null ? 'after ' . self::$lastField : '') . '
		';

		self::$lastField = $this->getName();

		$statement = $this->getDb()->prepare($query);

		return $statement->execute();
	}

}