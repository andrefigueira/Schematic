<?php

namespace Controllers\Migrations;

class SchematicFileGenerator
{

    protected $name;

    protected $directory;

    protected $schemaFile;

    public function setDir($directory)
    {

        $this->directory = $directory;

        return $this;

    }

    public function setName($name)
    {

        $this->name = $name;

        return $this;

    }

    public function getSchemaFile()
    {

        return $this->schemaFile;

    }

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