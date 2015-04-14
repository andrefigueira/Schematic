<?php
/**
 * This interface defines what should be created in new database adapters.
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

namespace Library\Database;

interface DatabaseInterface
{
    /**
     * Should set a protected host property.
     *
     * @param $host
     *
     * @return mixed
     */
    public function setHost($host);

    /**
     * Should set a protected username property.
     *
     * @param $username
     *
     * @return mixed
     */
    public function setUsername($username);

    /**
     * Should set a protected username property.
     *
     * @param $password
     *
     * @return mixed
     */
    public function setPassword($password);

    /**
     * Should set a protected dbName property.
     *
     * @param $dbName
     *
     * @return mixed
     */
    public function setDbName($dbName);

    /**
     * Should create a new database.
     *
     * @return mixed
     */
    public function createDatabase();

    /**
     * Should check if a table exists on the database.
     *
     * @return mixed
     */
    public function tableExists();

    /**
     * Should check if a field exists in a table on the database.
     *
     * @param $name
     *
     * @return mixed
     */
    public function fieldExists($name);

    /**
     * Should be able to run multiple queries at once.
     *
     * @param $query
     *
     * @return mixed
     */
    public function multiQuery($query);

    /**
     * Should be able to run queries on the database.
     *
     * @param $query
     *
     * @return mixed
     */
    public function query($query);

    /**
     * Should fetch fields from table in object format.
     *
     * @param $table
     *
     * @return mixed
     */
    public function showFields($table);

    /**
     * Should fetch an associative stdObject of a database with tables and fields and those field attributes.
     *
     * @return mixed
     */
    public function mapDatabase();

    /**
     * Should fetch database variables and return it in key, value standard object.
     *
     * @return mixed
     */
    public function fetchDatabaseVariables();
}
