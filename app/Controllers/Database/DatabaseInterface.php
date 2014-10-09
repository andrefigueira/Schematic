<?php

namespace Controllers\Database;

interface DatabaseInterface
{

    public function createDatabase($name);

    public function setHost($host);

    public function setUsername($username);

    public function setPassword($password);

}