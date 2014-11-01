<?php

namespace Library\Logger;

interface LogInterface
{

    public function exists();

    public function write($message);

}