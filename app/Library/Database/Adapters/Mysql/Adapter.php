<?php

namespace Library\Database\Adapters\Mysql;

use Library\Database\Adapters\Interfaces\AdapterInterface;

class Adapter implements AdapterInterface
{

    public $db;

    protected $host = '127.0.0.1';

    protected $user = 'root';

    protected $pass = '';

    protected $databaseName;

    public function __construct($databaseName)
    {

        $this->setDatabaseName($databaseName);

        $this->connect();

    }

    public function setDatabaseName($databaseName)
    {

        $this->databaseName = $databaseName;

    }

    protected function connect()
    {

        try
        {

            $this->db = new \PDO('mysql:host=' . $this->host . ';dbname=' . $this->databaseName . ';', $this->user, $this->pass);

            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        }
        catch(\Exception $e)
        {

            echo $e->getMessage();

        }

    }

}