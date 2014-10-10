<?php

namespace Controllers\Logger;

interface LogInterface
{

    public function exists();

    public function write($message);

}