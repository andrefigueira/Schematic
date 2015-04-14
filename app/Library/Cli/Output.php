<?php

namespace Library\Cli;

use Symfony\Component\Console\Output\ConsoleOutput;

class Output
{
    public static function writeln($messages)
    {
        $output = new ConsoleOutput();
        $output->writeln($messages);
    }
}
