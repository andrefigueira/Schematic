<?php
/**
 * This class is a Json adapter is extends the abstract file generator and impliments the file generator interface,
 * It's used to map data from an object to a standard Schematic format so that it can be imported also.
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

namespace Library\Migrations\FileApi\Adapters;

use Library\Migrations\FileApi\AbstractFileGenerator;
use Library\Migrations\FileApi\FileGeneratorInferface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class JsonAdapter
 * @package Library\Migrations\FileApi\Adapters
 */
class JsonAdapter extends AbstractFileGenerator implements FileGeneratorInferface
{
    /** @var string Filename of the file we are attempting to create */
    protected $fileName;

    /** @var string The extension of the file to be generated */
    protected $fileExtension = '.json';

    /** @var object The instance of the output interface */
    protected $output;

    public function __construct(OutputInterface $outputInterface)
    {
        $this->output = $outputInterface;
    }

    /**
     * Maps and generates the schema file.
     *
     * @param $data
     * @throws \Exception
     * @return bool
     */
    public function mapAndGenerateSchema($data)
    {
        foreach ($data as $table => $fields) {
            $fileName = $table.$this->fileExtension;

            $fileContent = $this->convertToFormat($this->mapToFormat($table, $fields));

            if ($this->create($fileName, $fileContent)) {
                $this->output->writeln('<info>Created schema file</info> '.$fileName);
            }
        }
    }

    /**
     * Maps the tables and it's attributes to the format required.
     *
     * @param $table
     * @param $fields
     * @return array
     */
    private function mapToFormat($table, $fields)
    {
        $formattedFields = array();

        foreach ($fields as $fieldName => $fieldAttributes) {

            //Check if the field is unsigned, if so split the types and set as unsigned
            if (strstr($fieldAttributes->Type, 'unsigned')) {
                $splitType = explode(' ', $fieldAttributes->Type);
                $type = $splitType[0];
                $unsigned = true;
            } else {
                $type = $fieldAttributes->Type;
                $unsigned = false;
            }

            //Check if set to auto_increment, if so set the auto increment variable
            if (strstr($fieldAttributes->Extra, 'auto_increment')) {
                $autoIncrement = true;
            } else {
                $autoIncrement = false;
            }

            //Check if allows null and set it
            if ($fieldAttributes->null == 'NO') {
                $null = false;
            } else {
                $null = true;
            }

            //Check if has an index, if so then check which kind and set
            if ($fieldAttributes->Key !== '') {
                switch ($fieldAttributes->Key) {

                    case 'UNI':
                        $index = 'UNIQUE KEY';
                        break;

                    case 'PRI':
                        $index = 'PRIMARY KEY';
                        break;

                    case 'MUL':
                        $index = 'INDEX';
                        break;

                    default:
                        $index = null;
                }
            } else {
                $index = null;
            }

            //Check if has foreign keys if so set the foreign keys array
            if (isset($fieldAttributes->foreignKeys) && $fieldAttributes->foreignKeys !== null) {
                $foreignKeys = array(
                    'table' => $fieldAttributes->foreignKeys->REFERENCED_TABLE_NAME,
                    'field' => $fieldAttributes->foreignKeys->REFERENCED_COLUMN_NAME,
                    'on' => array(
                        'delete' => $fieldAttributes->foreignKeys->actions->DELETE_RULE,
                        'update' => $fieldAttributes->foreignKeys->actions->UPDATE_RULE,
                    ),
                );
            } else {
                $foreignKeys = null;
            }

            //Create the formatted fields
            $formattedFields[$fieldName] = array(
                'type' => $type,
                'null' => $null,
                'unsigned' => $unsigned,
                'autoIncrement' => $autoIncrement,
                'index' => $index,
                'foreignKey' => $foreignKeys,
            );

            //If no index is set remove from the mapper
            if ($index === null) {
                unset($formattedFields[$fieldName]['index']);
            }

            //If no foreign keys are set remove from the mapper
            if ($foreignKeys === null) {
                unset($formattedFields[$fieldName]['foreignKey']);
            }
        }

        //Map everything finally
        $format = array(
            'schematic' => array(
                'name' => APP_NAME,
                'version' => APP_VERSION,
            ),
            'database' => array(
                'general' => array(
                    'name' => $this->dbName,
                    'charset' => $this->dbVars->character_set_database,
                    'collation' => $this->dbVars->collation_database,
                    'engine' => $this->dbVars->default_storage_engine,
                ),
                'tables' => array(
                    $table => array(
                        'fields' => $formattedFields,
                    ),
                ),
            ),
        );

        return $format;
    }

    /**
     * Converts raw data to an object.
     *
     * @param $data
     * @return mixed|void
     * @throws \Exception
     */
    public function convertToObject($data)
    {
        $results = @json_decode($data);

        if (is_object($results)) {
            return $results;
        } else {
            throw new \Exception('An error occured converting the raw config data into JSON');
        }
    }

    /**
     * Converts the created content to the correct format and returns the result.
     *
     * @param $content
     * @return mixed|void
     * @throws \Exception
     */
    public function convertToFormat($content)
    {
        $result = @json_encode($content, JSON_PRETTY_PRINT);

        if ($result) {
            return $result;
        } else {
            throw new \Exception('Unable to convert content to JSON');
        }
    }
}
