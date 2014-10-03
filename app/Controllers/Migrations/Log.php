<?php

/**
 * The schematic log is simple used to create log files for the schematic functions
 *
 */

namespace Controllers\Migrations;

class Log
{

    //The directory of the log
    private $dir = './logs/';

    //The default sql changes log
    private $file = 'sql-changes.log';

    /**
     * Create the directory if it doesn't exist on instantiation of the class
     *
     */
    public function __construct()
    {

        $this->exists();

    }

    /**
     * Checks to see if the log directory exists if not, it creates it
     *
     * @return bool
     * @throws \Exception
     *
     */
    public function exists()
    {

        $this->filePath = $this->dir . $this->file;

        if(!is_dir($this->dir)){ $this->createLogDir();}

        if(!file_exists($this->filePath))
        {

            $handle = @fopen($this->filePath, 'w');

            if(!$handle){ throw new \Exception('Unable to create log file: ' . $this->filePath);}

            fclose($handle);

        }

        return true;

    }

    /**
     * Create the log directory
     *
     * @throws \Exception
     *
     */
    private function createLogDir()
    {

        if(!mkdir($this->dir)){ throw new \Exception('Unable to create log directory');}

    }

    /**
     * Write to the log file set in a specific format
     *
     * @param $message
     * @return bool
     * @throws \Exception
     *
     */
    public function write($message)
    {

        $date = new \DateTime();

        $logLine = '[' . $date->format('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

        if(!file_exists($this->filePath))
        {

            $file = @fopen($this->filePath, 'r');

            if(!$file){ throw new \Exception('Unable to create log file: ' . $this->filePath);}

        }

        $put = @file_put_contents($this->filePath, $logLine, FILE_APPEND);

        if(!$put){ throw new \Exception('Unable to write to log: ' . $this->filePath);}

        return true;

    }

}