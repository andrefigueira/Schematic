<?php

namespace Library\Migrations;

/**
 * The schematic file generator creates a file.
 *
 * Class SchematicFileGenerator
 * @package Library\Migrations
 */
class SchematicFileGenerator
{
    /**
     * @var string The name of the file to generate
     */
    protected $name;

    /**
     * @var string The directory to create the file in
     */
    protected $directory;

    /**
     * @var string The schema file to be created
     */
    protected $schemaFile;

    /**
     * @var string The format type to use
     */
    protected $formatType;

    /**
     * @var string The name to use for the database
     */
    protected $databaseName;

    /**
     * @var string The name to use for the table
     */
    protected $tableName;

    /**
     * Setter for the format type.
     *
     * @param $formatType
     * @return $this
     */
    public function setFileFormatType($formatType)
    {
        $this->formatType = $formatType;

        return $this;
    }

    /**
     * The setter for the directory.
     *
     * @param $directory
     * @return $this
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * The setter for the file name.
     *
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $databaseName
     * @return $this
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;

        return $this;
    }

    /**
     * @param string $tableName
     *
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Fetches the generated schema file name.
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return $this->schemaFile;
    }

    /**
     * Fetches a template to create the file from.
     *
     * @throws \Exception
     */
    private function fetchTemplate()
    {
        $directory = __DIR__;

        $templateFile = $directory.'/template/schema.'.$this->formatType;

        if (file_exists($templateFile)) {
            $fileContents = @file_get_contents($templateFile);

            if ($fileContents) {
                return $fileContents;
            } else {
                throw new \Exception('Unable to open file: '.$templateFile);
            }
        } else {
            throw new \Exception('File does not exist: '.$templateFile);
        }
    }

    /**
     * Runs the generation of the file based on the existing template.
     *
     * @throws \Exception
     */
    public function run()
    {
        $template = $this->fetchTemplate();

        $search = array(
            '{{ app_title }}',
            '{{ app_version }}',
            '{{ database_name }}',
            '{{ table_name }}',
        );

        $replace = array(
            APP_NAME,
            APP_VERSION,
            $this->databaseName,
            $this->tableName,
        );

        $template = str_replace($search, $replace, $template);

        $this->schemaFile = $this->directory.$this->name.'.'.$this->formatType;

        if (file_exists($this->schemaFile)) {
            throw new \Exception('Schema file already exists: '.$this->schemaFile);
        }

        if (file_put_contents($this->schemaFile, $template)) {
            return true;
        } else {
            throw new \Exception('Unable to create schema file, please check permissions...');
        }
    }
}
