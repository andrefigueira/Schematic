<?php

namespace Library\Schematic\Abstraction\Core;
use Library\Schematic\Exceptions\SchematicApplicationException;

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

	/**
	 * Checks if the field exists
	 *
	 * @return bool
	 * @throws \Library\Schematic\Exceptions\SchematicApplicationException
	 */
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

	/**
	 * Updates the field
	 *
	 * @return bool
	 * @throws \Library\Schematic\Exceptions\SchematicApplicationException
	 */
	public function update()
	{
		$structure = $this->getStructure();

		$query = '
		ALTER TABLE ' . $this->getTableName() . '
		MODIFY COLUMN ' . $this->getName() . ' ' . $structure['type'] . '
			' . (isset($structure['null']) && $structure['null'] ? 'NULL' : ' NOT NULL ') . '
			' . (isset($structure['default']) ? 'DEFAULT "' . $structure['default'] . '"' : '') . '
			' . (self::$lastField != null ? 'after ' . self::$lastField : '') . '
			' . $this->getIndexSql() . '
			;
		';

		self::$lastField = $this->getName();

		$statement = $this->getDb()->prepare($query);

		return $statement->execute();
	}

	/**
	 * Creates the field
	 *
	 * @return bool
	 * @throws \Library\Schematic\Exceptions\SchematicApplicationException
	 */
	public function create()
	{
		$structure = $this->getStructure();

		$query = '
		ALTER TABLE ' . $this->getTableName() . '
		ADD ' . $this->getName() . ' ' . $structure['type'] . '
			' . (isset($structure['null']) && $structure['null'] ? 'NULL' : ' NOT NULL ') . '
			' . (isset($structure['default']) ? 'DEFAULT "' . $structure['default'] . '"' : '') . '
			' . (self::$lastField != null ? 'after ' . self::$lastField : '') . '
			' . $this->getIndexSql() . '
		';

		self::$lastField = $this->getName();

		$statement = $this->getDb()->prepare($query);

		return $statement->execute();
	}

	/**
	 * Specify valid index types
	 *
	 * @return array
	 */
	protected function validIndexTypes()
	{
		return array(
			'INDEX',
			'UNIQUE',
			'PRIMARY KEY',
			'FULLTEXT',
		);
	}

	/**
	 * Runs a check on index type to check it's valid
	 *
	 * @param $type
	 * @return bool
	 */
	protected function isValidIndex($type)
	{
		return in_array($type, $this->validIndexTypes());
	}

	/**
	 * Checks if the primary key exists on the field
	 *
	 * @return bool
	 * @throws \Library\Schematic\Exceptions\SchematicApplicationException
	 */
	protected function indexExists()
	{
		$query = '
		SHOW INDEXES
        FROM ' . $this->getTableName() . '
        WHERE Column_name = "' . $this->getName() . '"
		';

		$statement = $this->getDb()->prepare($query);

		if ($statement->execute() === false) {
			throw new SchematicApplicationException('Failed to check if field (' . $this->getName() . ') if primary key exists');
		}

		return (bool) $statement->rowCount();
	}

	/**
	 * Fetches the SQL for creating indexes
	 *
	 * @return string
	 * @throws \Library\Schematic\Exceptions\SchematicApplicationException
	 */
	protected function getIndexSql()
	{
		$structure = $this->getStructure();
		$sql = '';

		if (isset($structure['index'])) {
			$index = $structure['index'];

			if ($this->isValidIndex($index)) {
				if ($this->indexExists() === false) {
					$sql = ', ADD ' . $index . ' (`' . $this->getName() . '`)';
				}
			} else {
				throw new SchematicApplicationException('Invalid index type: ' . $index);
			}
		}

		return $sql;
	}
}