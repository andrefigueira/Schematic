<?php

namespace Library\Schematic\Abstraction\Core;

abstract class AbstractDatabaseItem
{
	/**
	 * @var Object
	 */
	protected $di;

	/**
	 * @var \PDO
	 */
	protected $db;

	/**
	 * @var array
	 */
	protected $messages = [];

	/**
	 * @return Object
	 */
	public function getDi()
	{
		return $this->di;
	}

	/**
	 * @param Object $di
	 * @return $this
	 */
	public function setDi($di)
	{
		$this->di = $di;

		$this->setDependencies();

		return $this;
	}

	public function setDependencies()
	{
		$this->setDb($this->getDi()->get('db'));
	}

	/**
	 * @return \PDO
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * @param \PDO $db
	 * @return $this
	 */
	public function setDb($db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * @param mixed $messages
	 * @return $this
	 */
	public function setMessages($messages)
	{
		$this->messages[] = $messages;

		return $this;
	}

}