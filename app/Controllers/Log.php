<?php

/**
 * The schematic log is simple used to create log files for the schematic functions
 *
 */

namespace Controllers;

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

            $newFile = @file_put_contents($this->filePath, '');

            if(!$newFile){ throw new \Exception('Unable to create log file');}

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

        $logLine = $date->format('Y-m-d H:i:s') . PHP_EOL;
        $logLine .= $message . PHP_EOL;
        $logLine .= '--------------------------------------------------' .PHP_EOL . PHP_EOL;

        $put = @file_put_contents($this->filePath, $logLine, FILE_APPEND);

        if(!$put){ throw new \Exception('Unable to write to log');}

        return true;

    }

}