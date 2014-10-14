<?php

namespace Controllers\Migrations\Generators;

class AbstractFileGenerator
{

    /** @var string The directory where the created file will be */
    protected $directory;

    /**
     * Sets the create file directory
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
     * Creates a new file with the data provided
     *
     * @param $name
     * @param $data
     * @throws \Exception
     */
    public function create($name, $data)
    {

        $newFileName = $this->directory . $name;

        if(@file_put_contents($newFileName, $data))
        {

            return true;

        }
        else
        {

            throw new \Exception('Unable to create new schema file: ' . $name . ' in directory: ' . $this->directory);

        }


    }

}