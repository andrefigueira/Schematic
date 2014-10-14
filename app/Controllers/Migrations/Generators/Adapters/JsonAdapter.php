<?php
/**
 * This class is a Json adapter is extends the abstract file generator and impliments the file generator interface,
 * It's used to map data from an object to a standard Schematic format so that it can be imported also.
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

namespace Controllers\Migrations\Generators\Adapters;

use Controllers\Migrations\Generators\AbstractFileGenerator;
use Controllers\Migrations\Generators\FileGeneratorInferface;
use Controllers\Cli\OutputInterface;

class JsonAdapter extends AbstractFileGenerator implements FileGeneratorInferface
{

    /** @var string Filename of the file we are attempting to create */
    protected $fileName;

    /** @var string The extension of the file to be generated */
    protected $fileExtension = '.json';

    /** @var object The instance of the output interface */
    protected $output;

    public function __construct(OutputInterface $output)
    {

        $this->output = $output;

    }

    /**
     * Maps and generates the schema file
     *
     * @param $data
     * @throws \Exception
     * @return bool
     */
    public function mapAndGenerateSchema($data)
    {

        foreach($data as $table => $fields)
        {

            $fileName = $table . $this->fileExtension;

            $fileContent = $this->convert($this->mapToFormat($table, $fields));

            if($this->create($fileName, $fileContent))
            {

                $this->output->writeln('<info>Created schema file ' . $fileName . '</info>');

            }

        }

    }

    /**
     * Maps the tables and it's attributes to the format required
     *
     * @param $table
     * @param $fields
     * @return array
     */
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
                'autoIncrement' => $autoIncrement
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

    /**
     * Converts the created content to the correct format and returns the result
     *
     * @param $content
     * @throws \Exception
     */
    private function convert($content)
    {

        $result = @json_encode($content, JSON_PRETTY_PRINT);

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