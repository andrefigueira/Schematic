<?php

namespace Controllers;

class Schematic
{

    private $dir = 'schemas';

    private $schema;

    public $sql = '';

    public function __construct()
    {

        $this->schemaDir = dirname(dirname(__DIR__)) . '/' . $this->dir . '/';

    }

    public function connect()
    {

        $this->db = new \mysqli($this->schema->connection->host, $this->schema->connection->user, $this->schema->connection->pass);

    }

    public function exists()
    {

        if(is_dir($this->schemaDir))
        {

            if(!$this->isEmptyDir($this->schemaDir))
            {

                $files = scandir($this->schemaDir);

                foreach($files as $file)
                {

                    if($file != '.' && $file != '..')
                    {

                        $specificSchemaDir = $this->schemaDir . $file;
                        $specificSchemaConfFile = $specificSchemaDir . '/schema.json';

                        if(file_exists($specificSchemaConfFile))
                        {

                            $this->schema = @file_get_contents($specificSchemaConfFile);

                            if($this->schema)
                            {

                                $this->schema = json_decode($this->schema);

                            }
                            else
                            {

                                throw new \Exception('Unable to load schema file');

                            }

                        }
                        else
                        {

                            throw new \Exception('Schema json file does not exist');

                        }

                    }

                }

                return true;

            }
            else
            {

                throw new \Exception('No schemas in folder');

            }

        }
        else
        {

            throw new \Exception('Schema folder does not exist');

        }

    }

    public function generate()
    {

        $this->connect();

        foreach($this->schema->database->tables as $table => $settings)
        {

            $this->generateTableSql($table, $settings);

            $this->db->select_db($this->schema->database->general->name);

            $result = $this->db->query($this->sql);

            if($result)
            {

                echo 'Generated Schema Successfully';

            }
            else
            {

                throw new \Exception('Failed to generate schema: ' . $this->db->error);

            }

        }

    }

    public function generateTableSql($table, $settings)
    {

        $fieldSql = '';

        foreach($settings->fields as $field => $fieldSettings)
        {

            $fieldSql .= '`' . $field . '` ' . $fieldSettings->type . '(' . $fieldSettings->length . ') ' . $fieldSettings->autoIncrement . ' DEFAULT ' . $fieldSettings->null . ',';

        }

        $fieldSql = substr($fieldSql, 0, -1);

        //Query to create the table if it doesn't exist indicating a first time run
        $this->sql .= '
        CREATE TABLE IF NOT EXISTS `'. $table . '` (
          ' . $fieldSql . '
        ) ENGINE=' . $this->schema->database->general->engine . ' DEFAULT CHARSET=' . $this->schema->database->general->charset . ';
        ';

        //Query to update the table only if it already exists
        $this->sql .= '

        ';

    }

    public function isEmptyDir($dir)
    {

        if(!is_readable($dir)) return null;
        return (count(scandir($dir)) == 2);

    }

}