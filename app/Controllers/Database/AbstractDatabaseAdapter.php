<?php
/**
 * This abstract class defines some specific getters or setters for the database adapters
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

namespace Controllers\Database;

abstract class AbstractDatabaseAdapter
{

    /** @var string The host to connect to */
    protected $host;

    /** @var string The username to use to connect */
    protected $username;

    /** @var string The password to use to connect */
    protected $password;

    /** @var string The database name to use */
    protected $dbName;

    /**
     * Setter for the host
     *
     * @param $host
     * @return $this
     */
    public function setHost($host)
    {

        $this->host = $host;

        return $this;

    }

    /**
     * Setter for the username
     *
     * @param $username
     * @return $this
     */
    public function setUsername($username)
    {

        $this->username = $username;

        return $this;

    }

    /**
     * Setter for the password
     *
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {

        $this->password = $password;

        return $this;

    }

    /**
     * Setter for the dbName
     *
     * @param $dbName
     * @return $this
     */
    public function setDbName($dbName)
    {

        $this->dbName = $dbName;

        return $this;

    }

}