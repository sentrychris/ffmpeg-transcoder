<?php

namespace Rowles\Console;

use Illuminate\Console\OutputStyle;

class OutputFormatter
{
    /** @var OutputStyle $output */
    protected OutputStyle $output;

    public function __construct($output) {
        $this->output = $output;
    }

    public function info(string $input) {
        $this->output->writeln('<fg=blue>[info]</> ' . $input);
    }

    public function success(string $input) {
        $this->output->writeln('<fg=green>[success]</> ' . $input);
    }

    public function warning(string $input) {
        $this->output->writeln('<fg=yellow>[success]</> ' . $input);
    }

    public function error (string $input) {
        $this->output->writeln('<fg=red>[error]</> '. $input);
    }
}