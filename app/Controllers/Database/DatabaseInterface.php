<?php

namespace Controllers\Database;

interface DatabaseInterface
{

    public function setHost($host);

    public function setUsername($username);

    public function setPassword($password);

    public function setDbName($dbName);

    public function createDatabase($name);

    public function tableExists($name);

    public function fieldExists($table, $name);

    public function multiQuery($query);

    public function query($query);

    public function showFields($table);

}