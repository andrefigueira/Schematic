<?php
/**
 * Defines what must be implemented for other file generators
 */

namespace Controllers\Migrations\Generators;

interface FileGeneratorInferface
{

    /**
     * Converts raw data in the specified format to stdObject, e.g. json -> stdObject, yaml -> stdObject, etc...
     *
     * @param $data
     * @return mixed
     */
    public function convertToObject($data);

    /**
     * Raw file contents are passed in, validate it is the correct format then bind to a stdObject as properties
     *
     * @param $data
     * @return mixed
     */
    public function convertToFormat($data);

    /**
     * Should read the data passed in and map it to a readable format compatible with the migrations engine
     *
     * @param $data
     * @return mixed
     */
    public function mapAndGenerateSchema($data);

}