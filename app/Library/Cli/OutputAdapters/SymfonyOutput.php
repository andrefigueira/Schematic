<?php

namespace Library\Cli\OutputAdapters;

use Library\Cli\OutputInterface;
use Symfony\Component\Console\Output\Output;

class SymfonyOutput implements OutputInterface
{

    protected $output;

    public function __construct(Output $output)
    {

        $this->output = $output;

    }

    public function writeln($message)
    {

        $this->output->writeln('<info>' . $message . '</info>');

    }

}

