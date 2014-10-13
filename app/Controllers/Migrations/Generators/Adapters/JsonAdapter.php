<?php

namespace Controllers\Migrations\Generators\Adapters;

use Controllers\Migrations\Generators\AbstractFileGenerator;
use Controllers\Migrations\Generators\FileGeneratorInferface;

class JsonAdapter extends AbstractFileGenerator implements FileGeneratorInferface
{

    protected $fileName;

    const FILE_EXTENSION = '.json';

    public function mapAndGenerateSchema($data)
    {

        foreach($data as $table => $fields)
        {

            $fileName = $table . JsonAdapter::FILE_EXTENSION;

            $fileContent = $this->convert($this->mapToFormat($table, $fields));

            var_dump($fileContent);

            $this->create($fileName, $fileContent);

        }

    }

    private function mapToFormat($table, $fields)
    {

        $formattedFields = array();

        foreach($fields as $fieldName => $fieldAttributes)
        {

            if(strstr($fieldAttributes->Type, 'unsigned'))
            {

                $splitType = explode(' ', $fieldAttributes->Type);
                $type = $splitType[0];
                $unsigned = true;

            }
            else
            {

                $type = $fieldAttributes->Type;
                $unsigned = false;

            }

            if(strstr($fieldAttributes->Extra, 'auto_increment')){ $autoIncrement = true;}else{ $autoIncrement = false;}
            if($fieldAttributes->Null == 'NO'){ $null = false;}else{ $null = true;}

            $formattedFields[$fieldName] = array(
                'type' => $type,
                'null' => $null,
                'unsigned' => $unsigned,
                'autoIncriment' => $autoIncrement
            );

        }

        $format = array(
            'schematic' => array(
                'name' => 'Schematic',
                'version' => '1.0'
            ),
            'database' => array(
                'general' => array(
                    'name' => 'Schematic auto generated schema',
                    'charset' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'engine' => 'InnoDB'
                ),
                'tables' => array(
                    $table => array(
                        'fields' => $formattedFields
                    )
                )
            )
        );

        return $format;

    }

    private function convert($content)
    {

        $result = @json_encode($content);

        if($result)
        {

            return $result;

        }
        else
        {

            throw new \Exception('Unable to convert content to JSON');

        }

    }

}