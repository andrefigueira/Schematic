<?php
/**
 * Defines what must be implemented for other file generators
 */

namespace Controllers\Migrations\Generators;

interface FileGeneratorInferface
{

    /**
     * Should read the data passed in and map it to a readable format compatible with the migrations engine
     *
     * @param $data
     * @return mixed
     */
    public function mapAndGenerateSchema($data);

}