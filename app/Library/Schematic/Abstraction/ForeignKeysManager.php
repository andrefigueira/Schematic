<?php

namespace Library\Schematic\Abstraction;

use Library\Schematic\Abstraction\Core\AbstractField;
use Library\Schematic\Exceptions\SchematicApplicationException;

/**
 * Class ForeignKeysManager
 * @package Library\Schematic\Abstraction
 * @author Andre Figueira <andre.figueira@me.com>
 */
class ForeignKeysManager
{
	/**
	 * @var \DI\ContainerBuilder
	 */
	protected $di;

	/**
	 * @var array
	 */
	protected $fields;

	/**
	 * @return \DI\ContainerBuilder
	 */
	public function getDi()
	{
		return $this->di;
	}

	/**
	 * @param \DI\ContainerBuilder $di
	 * @return $this
	 */
	public function setDi($di)
	{
		$this->di = $di;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * @param array $fields
	 * @return $this
	 */
	public function setFields($fields)
	{
		$this->fields = $fields;

		return $this;
	}

	public function createRelation()
	{

	}

	public function save()
	{
		foreach ($this->getFields() as $field) {
			if ($this->relationExists() === false) {
				$this->createRelation($field);
			}
		}

		return true;
	}
}