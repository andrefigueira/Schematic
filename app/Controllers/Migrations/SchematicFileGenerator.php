<?php
/**
 * The schematic file generator creates a file
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

namespace Controllers\Migrations;

class SchematicFileGenerator
{

    /** @var string The name of the file to generate */
    protected $name;

    /** @var string The directory to create the file in */
    protected $directory;

    /** @var string The schema file to be created */
    protected $schemaFile;

    /**
     * The setter for the directory
     *
     * @param $directory
     * @return $this
     */
    public function setDir($directory)
    {

        $this->directory = $directory;

        return $this;

    }

    /**
     * The setter for the file name
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
     * Fetches the generated schema file name
     *
     * @return string
     */
    public function getSchemaFile()
    {

        return $this->schemaFile;

    }

    /**
     * Fetches a template to create the file from
     *
     * @throws \Exception
     */
    private function fetchTemplate()
    {

        $directory = __DIR__;

        $templateFile = $directory . '/template/schema.json';

        if(file_exists($templateFile))
        {

            $fileContents = @file_get_contents($templateFile);

            if($fileContents)
            {

                return $fileContents;

            }
            else
            {

                throw new \Exception('Unable to open file: ' . $templateFile);

            }

        }
        else
        {

            throw new \Exception('File does not exist: ' . $templateFile);

        }

    }

    /**
     * Runs the generation of the file based on the existing template
     *
     * @throws \Exception
     */
    public function run()
    {

        $template = $this->fetchTemplate();

        $this->schemaFile = $this->directory . $this->name . '.json';

        if(file_exists($this->schemaFile)){ throw new \Exception('Schema file already exists: ' . $this->schemaFile);}

        if(file_put_contents($this->schemaFile, $template))
        {

            return true;

        }
        else
        {

            throw new \Exception('Unable to create schema file, please check permissions...');

        }

    }

}