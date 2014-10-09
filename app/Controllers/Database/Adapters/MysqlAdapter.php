<?php

namespace Controllers\Database\Adapters;

use Controllers\Database\DatabaseInterface;

class MysqlAdapter implements DatabaseInterface
{

    protected $db;

    protected $host;

    protected $username;

    protected $password;

    public function connect()
    {

        $this->db = new \mysqli($this->host, $this->username, $this->password);

        if($this->db->connect_errno){ throw new \Exception($this->db->connect_error);}

    }

    public function setHost($host)
    {

        $this->host = $host;

        return $this;

    }

    public function setUsername($username)
    {

        $this->username = $username;

        return $this;

    }

    public function setPassword($password)
    {

        $this->password = $password;

        return $this;

    }

    public function createDatabase($name)
    {

        return $this->db->query('CREATE DATABASE IF NOT EXISTS `' . $name . '`');

    }

}